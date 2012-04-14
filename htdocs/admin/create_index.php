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

// $Id: create_index.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/admin/create_index.php $

require_once dirname(__FILE__) . '/../conf/config.php';
require_once dirname(__FILE__) . '/../core/database.php';
require_once dirname(__FILE__) . '/../core/session.php';
require_once dirname(__FILE__) . '/../core/models/base/base_model.php';
require_once dirname(__FILE__) . '/../core/parser/parser_factory.php';

$createCompleteIndex = false;



$DB = Database::getHandle();
        
$q = 'SELECT COUNT(*) AS nr
        FROM ' . DB_SCHEMA . '.forum_thread_entries';
if (!$createCompleteIndex) {
    $q .= ' WHERE idx_fulltext IS NULL';
}

$res = &$DB->execute($q);
if (!$res) {
    throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
}
$totalNumber = $res->fields['nr'];

// create index in bundles
$bundleSize = 2000;

$normalizer = ParserFactory::getRawParser();
$keyValue = array();

for ($c = 0; $c < $totalNumber; $c += $bundleSize) {
    $DB->StartTrans();
    
    $q = 'SELECT id, caption, entry_raw
            FROM ' . DB_SCHEMA . '.forum_thread_entries';
    if (!$createCompleteIndex) {
        $q .= ' WHERE idx_fulltext IS NULL';
    }
    $q .= ' 
         ORDER BY id
            LIMIT ' . $bundleSize;
    $res = &$DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }
    
    foreach ($res as $row) {
        $normalizedString = $row['caption'] . ' ' . $row['entry_raw'];
        $normalizer->parse($normalizedString);
        $keyValue['idx_fulltext'] = 'to_tsvector(\'simple\', ' . $DB->quote($normalizedString) . ')';
        
        $q = BaseModel::buildSqlStatement('forum_thread_entries', $keyValue, false, 'id = ' . $DB->quote($row['id']));
        $_res = &$DB->execute($q);
        if (!$_res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    $DB->CompleteTrans();
    
    echo "completed bundle $c of size $bundleSize\n";
}

?>
