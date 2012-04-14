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

// $Id: parse_entries.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/admin/parse_entries.php $

require_once dirname(__FILE__) . '/../conf/config.php';
require_once dirname(__FILE__) . '/../core/database.php';
require_once dirname(__FILE__) . '/../core/session.php';
require_once dirname(__FILE__) . '/../core/parser/parser_factory.php';

$createCompleteIndex = false;



$DB = Database::getHandle();
        
$q = 'SELECT COUNT(*) AS nr
        FROM ' . DB_SCHEMA . '.guestbook
       WHERE entry_time > \'2007-04-01\'';

$res = &$DB->execute($q);
if (!$res) {
    throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
}
$totalNumber = $res->fields['nr'];

// create index in bundles
$bundleSize = 150;

$parseSettings = array('enable_formatcode' => 1, 'enable_smileys' => 1);
$parsers = ParserFactory::createParserFromSettings($parseSettings);

for ($c = 0; $c < $totalNumber; $c += $bundleSize) {
    $DB->StartTrans();
    
    $q = 'SELECT id, entry_parsed, entry_raw
            FROM ' . DB_SCHEMA . '.guestbook
        ORDER BY id DESC
           LIMIT ' . $bundleSize . '
          OFFSET ' . $c;
    $res = &$DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }
    
    foreach ($res as $row) {
        if ($row['entry_parsed'] != '') {
            continue;
        }
        $text = $row['entry_raw'];
        foreach ($parsers as $p) {
            $text = $p->parse($text);
        }
        
        $q = 'UPDATE ' . DB_SCHEMA . '.guestbook
                 SET entry_parsed = ' . $DB->Quote($text) . '
               WHERE id = ' . $row['id'];
        $_res = &$DB->execute($q);
        if (!$_res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    $DB->CompleteTrans();
    
    echo "completed bundle $c of size $bundleSize entries\n";
}

?>
