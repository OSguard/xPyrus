<?php
#
# xPyrus - Framework for Community and knowledge exchange
# Copyright (C) 2003-2008 UniHelp e.V., Germany
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, only version 3 of the
# License.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program. If not, see http://www.gnu.org/licenses/agpl.txt
#

#
# $Id: adodb.inc.php 5743 2008-03-25 19:48:14Z ads $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/lib/lib-sql/adodb-ads/adodb.inc.php $
#
# ADODB reimplementation just for PostgreSQL
#
# written by Andreas 'ads' Scherbaum <ads@unihelp.de>
# for the Unihelp project
#

# define some constants
define('ADODB_FETCH_DEFAULT',0);
define('ADODB_FETCH_NUM',1);
define('ADODB_FETCH_ASSOC',2);
define('ADODB_FETCH_BOTH',3);


# NewADOConnection()
#
# synonym for ADONewConnection
#
# parameter:
#  - database type (defaults to PostgreSQL in this case)
# return:
#  - ADODB handle (or false)
function &NewADOConnection($db = '') {
  $result = ADONewConnection($db);

  return $result;
}

# ADONewConnection()
#
# Instantiate a new Connection class for PostgreSQL
#
# parameter:
#  - database type (defaults to PostgreSQL in this case)
# return:
#  - ADODB handle (or false)
function &ADONewConnection($db = '') {

  $handle = new ADODB($db);

  return $handle;
}





/**
 * database abstraction layer (ok, only PostgreSQL available for performance reasons)
 */
class ADODB {

	private $dbtype = "";
	private $pconnect = false;
	private $_DB = false;
	private $fetchMode = false;
	public $debug = false;
	public $debug_sql_queries = false;
	public $debug_sql_timing = false;
	public $debug_hide_debugging = true;
	private $logfile = "";
	private $errormsg = "";
	private $transaction_started = false;
	private $transaction_nested = 0;
	private $transaction_starttrans_error = false;
	private $transaction_starttrans_nested = 0;
	private $transaction_starttrans_active = false;
	private $transaction_mode = "READ COMMITTED";
	private $transaction_rw = "READ WRITE";
	private $last_result = false;

	/**
	* Constructor. If parameter is passed, it will be the database driver name
	* @param string $dbtype the database type ('postgres' is allowed')
	* @return string database handle
	*/
   	public function __construct ($dbtype = "postgres") {
   		$this->setDBType($dbtype);
		$this->setPConnect(false);
   	}

	/**
	* shutdown the database connection
	*/
	public function __destruct () {
        if ($this->transaction_starttrans_nested > 0) {
            trigger_error('Open transaction on adodb-ads __destruct', E_USER_WARNING);
        }
		/* 
		 * I added comments because in most of the EntryModels
		 * a DB-connection is needed in their __destruct-method.
		 * If pg_close is called here, the DB-connection would
		 * not be available there.
		 * Better let PHP implicitly do the cleanup ...
		 *                                               [linap]
		 */
		/*
		if ($this->_DB !== false) {
			pg_close($this->_DB);
		}
		 */
	}

	/**
	* Set the database type
	* @param string $dbtype the database type ('postgres' is allowed')
	* @return none
	*/
   	private function setDBType ($dbtype) {
		if ($dbtype == "postgres") {
			$this->dbtype = $dbtype;
		} else {
			die("database driver not known: $dbtype");
		}
		return;
   	}

	/**
	* Set the fetch mode
	* @param string $fetchmode (ADODB_FETCH_DEFAULT|ADODB_FETCH_NUM|ADODB_FETCH_ASSOC|ADODB_FETCH_BOTH)
	* @return none
	*/
   	public function SetFetchMode ($fetchmode) {
		if ($fetchmode == ADODB_FETCH_DEFAULT) {
		} else if ($fetchmode == ADODB_FETCH_NUM) {
		} else if ($fetchmode == ADODB_FETCH_ASSOC) {
		} else if ($fetchmode == ADODB_FETCH_BOTH) {
		} else {
			die("unknown fetchMode: $fetchmode");
		}

		$this->fetchMode = $fetchmode;

		return;
   	}

	/**
	* Get the fetch mode
	* @return string fetch mode
	*/
   	public function GetFetchMode () {
		return $this->fetchMode;
   	}

	/**
	* Set if a persistant connection should be used
	* @param boolean $permanent
	* @return none
	*/
   	public function setPConnect ($permanent) {
		if ($permanent === false) {
			$this->pconnect = false;
		} else if ($permanent === true) {
			$this->pconnect = true;
		} else {
			die("type for setPConnect is invalid: $permanent");
		}
		return;
   	}

	/**
	* connect to database
	* @param string $server servername
	* @param string $username username for database
	* @param string $password password for database
	* @param string $dbname database name
	* @return boolean true or false if connect could be established
	*/
	public function Connect ($server, $username, $password, $dbname) {
		# build connection string
		$cs = array();
		# set hostname
		if (isset($server)) {
			# look if the hostname has a portnumber given
			if (strpos($server, ":")) {
				# yes, split by ':' and use host and port
				$port_split = explode(":", $server);
				array_push($cs, "host = '" . $port_split[0] . "'");
				array_push($cs, "port = '" . $port_split[1] . "'");
			} else {
				# use this string as host
				array_push($cs, "host = '" . $server . "'");
			}
		}
		# set username
		if (isset($username)) {
			array_push($cs, "user = '" . $username . "'");
		}
		# set password
		if (isset($password)) {
			array_push($cs, "password = '" . $password . "'");
		}
		# set database name
		if (isset($dbname)) {
			array_push($cs, "dbname = '" . $dbname . "'");
		}
		# now join the connection string
		$cs = trim(implode(" ", $cs));

		if ($this->pconnect === true) {
			$this->_DB = pg_pconnect($cs);
		} else {
			$this->_DB = pg_connect($cs);
		}
		#$this->logging("connect ok ...");

		if ($this->_DB === false) {
			# connect failed, abort
			return false;
		}

		return true;
	}

	/**
	* connect to database with persistent connection
	* @param string $server servername
	* @param string $username username for database
	* @param string $password password for database
	* @param string $dbname database name
	* @return boolean true or false if connect could be established
	*/
	public function PConnect ($server, $username, $password, $dbname) {
		# build connection string
		$cs = array();
		# set hostname
		if (isset($server)) {
			# look if the hostname has a portnumber given
			if (strpos($server, ":")) {
				# yes, split by ':' and use host and port
				$port_split = explode(":", $server);
				array_push($cs, "host = '" . $port_split[0] . "'");
				array_push($cs, "port = '" . $port_split[1] . "'");
			} else {
				# use this string as host
				array_push($cs, "host = '" . $server . "'");
			}
		}
		# set username
		if (isset($username)) {
			array_push($cs, "user = '" . $username . "'");
		}
		# set password
		if (isset($password)) {
			array_push($cs, "password = '" . $password . "'");
		}
		# set database name
		if (isset($dbname)) {
			array_push($cs, "dbname = '" . $dbname . "'");
		}
		# now join the connection string
		$cs = trim(implode(" ", $cs));

		# make connection persistent
		$this->setPConnect(true);

		if ($this->pconnect === true) {
			$this->_DB = pg_pconnect($cs);
		} else {
			$this->_DB = pg_connect($cs);
		}
		#$this->logging("connect ok ...");

		if ($this->_DB === false) {
			# connect failed, abort
			return false;
		}

		return true;
	}

	/**
	* execute a query
	* @param string $query query string
	* @param array $val array with values for prepared query (currently not implemented)
	* @return recordset result recordset
	*/
	public function Execute ($query, $val = false) {
		if ($this->_DB === false) {
			die ("you have to connect to the database");
		}

		$this->errormsg = false;
		# first decide, if this is a prepared query
		if (is_array($query)) {
			# yes, it is
			die("prepared queries are not yet implemented");
		} else {
			$this->log_sql_query($query);

			# start an implicit transaction, if not yet done
			$this->StartTrans();

			# no prepared query, just execute
			$time_start = microtime(true);
			$result = pg_query($this->_DB, $query);
			$time_end = microtime(true);

			if ($result !== false) {
				# create resultset now to avoid problems with
				# pg_affected_rows()
				$this->last_result = new ADODB_Result($result, $this);
				# store the last result set in database handle
				# needed for Affected_Rows() which is connection
				# based in AdoDB and result based in PHP
			}

			# store last error message
			$this->errormsg = pg_last_error($this->_DB);

			# calculate query execution time
			$time_run = $time_end - $time_start;
			$time_run = round($time_run * 1000) / 1000;
			$this->log_sql_timing($time_run);

			if ($result === false) {
				if ($this->transaction_starttrans_active === true) {
					# remember for the transaction if there was an error
					$this->transaction_starttrans_error = true;
					# decrement counter that was incremented by implicit
					# transaction above (FIX by trehn)
					$this->transaction_starttrans_nested--;
				}
				return false;
			}

			# commit this (maybe nested) transaction
			$this->CompleteTrans();

			return $this->last_result;
		}
	}

	/**
	* quote a parameter
	* @param string $parameter parameter string
	* @return string $return quoted input string
	*/
	public function Quote ($parameter) {
		return "'" . pg_escape_string($parameter) . "'";
	}

	/**
	* return the latest errorstring
	* @return string $error latest errorstring
	*/
   	public function ErrorMsg () {
		return $this->errormsg;
   	}

	/**
	* return the latest errornumber
	* @return integer $errornr latest errornumber
	*/
   	public function ErrorNo () {
		# PG only returns an error string, no error number
		die("ErrNo() is not implemented");
   	}

	/**
	* return the number of affected rows
	* @return integer $affected_rows number of affected rows
	*/
   	public function Affected_Rows () {
		if ($this->_DB === false) {
			die ("you have to connect to the database");
		}

		return $this->last_result->Affected_Rows();
   	}


	/**
	* start a transaction
	*/
	public function BeginTrans() {
		/* if you ever try to use this function for production
		 * use: Execute() is using StartTrans()
		 */
		if ($this->_DB === false) {
			die ("you have to connect to the database");
		}

		die("please use a monitored transaction with StartTrans() instead of BeginTrans()");
	}

	/**
	* finish a transaction
	*/
	public function CommitTrans($ok = true) {
		if ($this->_DB === false) {
			die ("you have to connect to the database");
		}

		die("please use a monitored transaction with CompleteTrans() instead of CommitTrans()");
	}

	/**
	* rollback a transaction
	*/
	public function RollbackTrans() {
		if ($this->_DB === false) {
			die ("you have to connect to the database");
		}

		die("please use a monitored transaction with CompleteTrans() instead of RollbackTrans()");
	}

	/**
	 * start a monitored transaction
	 */
	public function StartTrans () {
		if ($this->_DB === false) {
			die ("you have to connect to the database");
		}
		//trigger_error($this->transaction_starttrans_nested+1 . 'open',E_USER_WARNING);
        if ($this->transaction_starttrans_active === true) {
			# just increase the counter
			$this->transaction_starttrans_nested++;
			return true;
		}

		# start a transaction
		if ($this->_begintrans() === false) {
			die("could not start a transaction with StartTrans()");
		}
		$this->transaction_starttrans_active = true;
		$this->transaction_starttrans_nested = 1;
		$this->transaction_starttrans_error = false;
		return true;
	}

	/**
     * finish a transaction
     */
	public function CompleteTrans () {
		if ($this->_DB === false) {
			die ("you have to connect to the database");
		}
        //trigger_error($this->transaction_starttrans_nested . 'close',E_USER_WARNING);
		if ($this->transaction_starttrans_active !== true) {
			# called without StartTrans()
			trigger_error("you called CompleteTrans() without prior StartTrans()", E_USER_WARNING);
            return false;
		}
		
        if ($this->transaction_starttrans_nested > 1) {
			 #just decrease the counter;
			$this->transaction_starttrans_nested--;
			return true;
		}

		# commit transaction
		$this->transaction_starttrans_nested = 0;
        $transactionProperlyFinished = false;
		if ($this->transaction_starttrans_error === true) {
			# an error occured during executing, rollback
			$this->_rollbacktrans();
		} else {
			# no error, commit
			$this->_committrans();
            $transactionProperlyFinished = true;
		}
        # leave transaction mode (FIX by trehn)
        $this->transaction_starttrans_active = false;

		return $transactionProperlyFinished;
	}

	/**
	* mark a transaction as failed
	*/
	public function FailTrans () {
		$this->transaction_starttrans_error = true;
	}

	/**
	* get transaction status
	*/
	public function HasFailedTrans () {
		if ($this->transaction_starttrans_active !== true) {
			# called without StartTrans()
			return false;
		}

		return $this->transaction_starttrans_error;
	}

	/**
	* set transaction mode
	* @param string $mode transaction level mode
	*/
	public function SetTransactionMode ($mode) {
		if ($mode == "READ UNCOMMITTED") {
			$this->transaction_mode = $mode;
		} else if ($mode == "READ COMMITTED") {
			$this->transaction_mode = $mode;
		} else if ($mode == "READ COMMITTED") {
			$this->transaction_mode = $mode;
		} else if ($mode == "REPEATABLE READ") {
			$this->transaction_mode = $mode;
		} else if ($mode == "SERIALIZABLE") {
			$this->transaction_mode = $mode;
		} else if ($mode == "READ WRITE") {
			$this->transaction_rw = $mode;
		} else if ($mode == "READ ONLY") {
			$this->transaction_rw = $mode;
		}
	}

	/**
	* internal function: start a transaction
	*/
	private function _begintrans () {
		# start a transaction
		$query = "START TRANSACTION ISOLATION LEVEL " . $this->transaction_mode . " " . $this->transaction_rw;
		$result = pg_query($this->_DB, $query);

		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	* internal function: rollback a transaction
	*/
	private function _rollbacktrans () {
		# rollback a transaction
		$result = pg_query($this->_DB, "ROLLBACK");

		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	* internal function: commit a transaction
	*/
	private function _committrans () {
		# end a transaction
		$result = pg_query($this->_DB, "END TRANSACTION");

		if ($result === false) {
			return false;
		}
		return true;
	}


	/**
	* log a message
	* @param string $logstring message to log
	*/
	private function logging ($logstring) {
		if ($this->debug === false) {
			return;
		}
		if ($this->debug !== false and $this->debug !== true) {
			if ($this->debug == 0 or
		    	    $this->debug == "n" or
			    $this->debug == "no") {
				return;
			}
		}
		if ($this->debug_hide_debugging === true) {
			addDebugOutput("debug SQL: \n$logstring");
		} else {
			print "\ndebug SQL: \n$logstring\n\n";
		}
	}

	/**
	* log sql query
	* @param string $query SQL query to log
	*/
	private function log_sql_query ($query) {
		if ($this->debug === false) {
			return;
		}
		if ($this->debug_sql_queries === true) {
			if ($this->debug_hide_debugging === true) {
				addDebugOutput("\nquery: \n$query");
			} else {
				print "\nquery: \n$query\n\n";
			}
		}
	}

	/**
	* log the timing for a query
	* @param real $time_run timing to log
	*/
	private function log_sql_timing ($time_run) {
		if ($this->debug === false) {
			return;
		}
		if ($this->debug_sql_queries === true) {
			if ($this->debug_hide_debugging === true) {
				addDebugOutput("query time: $time_run");
			} else {
				print "\nquery time: $time_run\n";
			}
		}
	}

}


/**
 * database result class
 */
class ADODB_Result implements Iterator {

	private $result = false;
        private $caller = false;
        private $pos = -1;
        private $number_of = -1;
	private $fetchMode = false;
	private $phpfetchMode = false;
	private $affectedrows = false;
	public $EOF = false;
	public $fields = false;

	/**
	* Constructor.
	* @param string $result database result
	* @return string database result handle
	*/
   	public function __construct ($result = false, $caller = false) {
		global $ADODB_FETCH_MODE;

		if ($result === false) {
			die("you have to submit a result");
		}
		if ($caller === false) {
			die("you have to submit a caller object");
		}

		# save parameters
		$this->result = $result;
		$this->caller = $caller;
		$this->pos = -1;
		$this->number_of = pg_num_rows($this->result);
		# store number of affected rows since pg returns it per result
		# and adodb returns it per connection
		$this->affectedrows = pg_affected_rows($this->result);

		# validate EOF
		if ($this->number_of == 0) {
			$this->EOF = true;
			return false;
		}

		# save current callermode
		if ($this->caller->GetFetchMode() !== false) {
			# FetchMode is set, ignore global variable
			$this->SetFetchMode($this->caller->GetFetchMode());
		} else if (isset($ADODB_FETCH_MODE)) {
			# use global variable
			$this->SetFetchMode($ADODB_FETCH_MODE);
		}

		# calculate fetchmode for this result set
		$fetchmode = ADODB_FETCH_NUM;
		if ($this->GetFetchMode() === false) {
			# set to numeric
			$fetchmode = PGSQL_BOTH;
		} else if ($this->GetFetchMode() == ADODB_FETCH_DEFAULT) {
			# set to numeric
			$fetchmode = PGSQL_BOTH;
		} else if ($this->GetFetchMode() == ADODB_FETCH_NUM) {
			# set to numeric
			$fetchmode = PGSQL_NUM;
		} else if ($this->GetFetchMode() == ADODB_FETCH_ASSOC) {
			# set to string keys
			$fetchmode = PGSQL_ASSOC;
		} else if ($this->GetFetchMode() == ADODB_FETCH_BOTH) {
			# set to string keys
			$fetchmode = PGSQL_BOTH;
		}
		$this->phpfetchMode = $fetchmode;

		# init first set
		$this->next();
		return $this->fields;
   	}

	/**
	* destroy the result set
	*/
	public function __destruct() {
		/* 
         * I added comments because in most of the EntryModels
         * a DB-connection is needed in their __destruct-method.
         * If pg_close is called here, the DB-connection would
         * not be available there.
         * Better let PHP implicitly do the cleanup ...
         *                                               [linap]
         */
        /*if ($this->result != null) {
            $var = pg_free_result($this->result);
        }*/
	}

	/**
	* Set the fetch mode
	* @param string $fetchmode (ADODB_FETCH_DEFAULT|ADODB_FETCH_NUM|ADODB_FETCH_ASSOC|ADODB_FETCH_BOTH)
	* @return none
	*/
   	public function SetFetchMode ($fetchmode) {
		if ($fetchmode == ADODB_FETCH_DEFAULT) {
		} else if ($fetchmode == ADODB_FETCH_NUM) {
		} else if ($fetchmode == ADODB_FETCH_ASSOC) {
		} else if ($fetchmode == ADODB_FETCH_BOTH) {
		} else {
			die("unknown fetchMode: $fetchmode");
		}

		$this->fetchMode = $fetchmode;

		return;
   	}

	/**
	* Get the fetch mode
	* @return string fetch mode
	*/
   	public function GetFetchMode () {
		return $this->fetchMode;
   	}

	/**
	* return the current result set
	* @return array $fields result set
	*/
	public function current () {
		return $this->fields;
        }

	/**
	* return the current position in the result set
	* @return integer $pos the current result position
	*/
	public function key () {
		return $this->pos;
        }

	/**
	* return if the result has more valid results
	* @return boolean $EOF if there is a result
	*/
	public function valid () {
		if ($this->EOF === true) {
			return false;
		}
		if (($this->pos) <= $this->number_of and $this->number_of > 0) {
			return true;
		} else {
			$this->EOF = false;
			return false;
		}
	}

	/**
	* reset the result set
	*/
	public function rewind () {
		$this->pos = -1;
		$this->next();
		return;
        }

	/**
	* function for nonexistent variables
	* @param string $name the variable name
	*/
	public function __get ($name) {
		print "__get: $name<br />\n";
	}

	/**
	* function for nonexistent functions
	* @param string $name the functions name
	* @param string $values the functions values
	*/
	public function __call ($name, $values) {
		print "__call: $name<br />\n";
	}

	/**
	* forward the result set to the next result
	* @return boolean $next if the next result in the set is valid
	*/
	public function next () {
		if ($this->EOF) {
			return false;
		}

		$this->pos++;

		if ($this->pos >= $this->number_of) {
			$this->fields = false;
			$this->EOF = true;
			return false;
		}

		# get data from result
		$result = @pg_fetch_array($this->result, $this->pos, $this->phpfetchMode);

		$this->fields = $result;

		if (is_array($result)) {
			return true;
		}
		$this->fields = false;
		$this->EOF = true;
		return false;
	}
    
	/**
	* just a wrapper around next()
	* @return boolean $next if the next result in the set is valid
	*/
	public function MoveNext(){
		return $this->next();
	}
    
	/**
	* return the number of affected rows
	* @return integer $affected_rows number of affected rows
	*/
   	public function Affected_Rows () {
		return $this->affectedrows;
   	}

}

?>
