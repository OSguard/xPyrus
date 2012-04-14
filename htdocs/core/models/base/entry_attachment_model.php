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

# $Id: entry_attachment_model.php 5743 2008-03-25 19:48:14Z ads $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/entry_attachment_model.php $

require_once MODEL_DIR . '/base/file_model.php';

class EntryAttachmentModel extends FileModel {    
    /**
     * constructor
     *
     * @param int $id id of attachment
     * @param string $path path to the attachment on the server
     * @param string $absolutePath absolute path to the attachment on the server
     * @param int $size size of the file
     * @param string $type type of the attachment (see class-constants)
     * @param string $hash hash value on attachment (defaults to null)
     */
    public function __construct( $id, $path, $absolutePath, $size, $type, $hash=null) {
        parent::__construct($id, $path, $absolutePath, $size, $type, $hash);
    }
  
    /**
     * returns an array of attachment entries with specified ids
     * <b>note:</b> the array preserves the order of the given ids
     *
     * @param array $ids array of int: the ids of attachments to retrieve
     * @return array array of EntryAttachmentModel
     * @throws DBException on database error
     */
    public static function getAttachmentsByIds($ids) {
        $DB = Database::getHandle();
        
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, path, file_size, file_type
                FROM ' . DB_SCHEMA . '.attachments
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up array to keep order of entries to retrieve
        // orderArray is hash with key [id] and value [position of id in $ids]
        $orderArray = array();
        for ($i=0;$i<count($ids);$i++) {
            $orderArray[$ids[$i]] = $i;
        }

        // build up temporary array out of which return array will be created;
        // pre-fill array with null, so that insertion order of entries does
        // not matter
        $entries = array_fill(0,count($ids),null);
        foreach ($res as $k => $row) {
            $attachment = new EntryAttachmentModel($row['id'], $row['path'], null, $row['file_size'], $row['file_type']);
            
            // put in right position in return array
            $entries[$orderArray[$attachment->id]] = $attachment;
        }
        
        return $entries;
    }
    
    /**
     * returns the attachment specified by given id
     *
     * @param int id of attachment to retrieve
     * @return EntryAttachmentModel
     * @throws DBException on database error
     */
    public static function getAttachmentById($id) {
        $DB = Database::getHandle();
        
        // if no entry is to fetch, return null
        if (empty($id)) return null;
        
        $q = 'SELECT id, path, file_size, file_type
                FROM ' . DB_SCHEMA . '.attachments
               WHERE id = ' . $DB->Quote($id);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
        	return null;
        }
        
        $attachment = new EntryAttachmentModel($res->fields['id'], $res->fields['path'], 
            null, $res->fields['file_size'], $res->fields['file_type']);
        
        return $attachment;
    }
    
    /**
     * collects all attachments given user has uploaded
     * @param UserModel $user author of attachments to select
     * @return array array of EntryAttachmentModel
     * @throws DBException
     */
    public static function getAttachmentsByAuthor($user, $limit = 10, $offset = 0) {
        $DB = Database::getHandle();

        $q = 'SELECT a.*
                FROM ' . DB_SCHEMA. '.attachments AS a
               WHERE author_id = ' . $DB->Quote($user->id) . '
            ORDER BY upload_time DESC';
        
                
        $q .= ' LIMIT '. $limit . ' OFFSET ' . $offset;
        
        //var_dump( $q );
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $attachments = array();        
        foreach ($res as $row) {
            $file = new EntryAttachmentModel (
                $row['id'],
                $row['path'],
                null,
                $row['file_size'],
                $row['file_type']
               );
            array_push($attachments, $file);
            //if ($row['blog']) var_dump(GuestbookEntry::getEntryById($row['blog']));
        }
        return $attachments;
    }
    
}

?>
