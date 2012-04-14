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

require_once MODEL_DIR . '/base/base_model.php';
require_once CORE_DIR . '/interfaces/i_with_attachments.php';

abstract class BaseModelWithAttachments extends BaseModel implements IWithAttachments {
    /**
     * array of EntryAttachment objects representing the attachments of this entry
     * @var array
     */
    protected $attachments = null;
    protected $attachmentsToAdd = array();
    protected $attachmentsToDelete = array();
    protected $attachmentsMCToAdd = array();
    
  
    /**
     * some generic save methods for attachments
     */
     
    protected function saveAttachmentsToAdd($DB) {    	
        $attachments = $this->getAttachmentsToAdd();
        if ($this->attachments === null) {
            $this->attachments = array();
        }
        foreach ($attachments as $atm) {
            // insert attachment itsself
            $q = 'INSERT INTO ' . DB_SCHEMA . '.attachments
                        (path,file_type,file_size,author_id)
                    VALUES
                        (' . $DB->Quote( $atm->getFilePath() ) . ',
                         ' . $DB->Quote( $atm->getType() ) . ',
                         ' . $DB->Quote( $atm->getFileSize() ) . ',
                         ' . $DB->Quote( $this->author->id ) . ')';
            $res = &$DB->execute( $q );
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            $atm->id = Database::getCurrentSequenceId($DB, 'attachments','id');
            array_push($this->attachments, $atm);            
        }
        $this->attachmentsToAdd = null;
        return $attachments;
    }
    
    protected function saveAttachmentsRelationshipToAdd($DB, $attachments, $relationshipTableName) {
    	if (!$attachments) {
    		return;
    	}
        foreach ($attachments as $atm) {
            // insert relationship between attachment and guestbook entry
            $q = 'INSERT INTO ' . DB_SCHEMA . '.' . $relationshipTableName . '
                        (entry_id, attachment_id)
                    VALUES
                        ( ' . $DB->Quote($this->id). ',
                          ' . $DB->Quote($atm->id) . ')';
            $res = &$DB->execute( $q );
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
        }
    }
    
    protected function saveAttachmentsToDelete($DB, $relationshipTableName) {
    	$attachments = $this->getAttachmentsToDelete();
        foreach ($attachments as $atm_id) {
            // delete attachment-entry-relationship
            $q = 'DELETE FROM ' . DB_SCHEMA . '.' . $relationshipTableName . '
                        WHERE entry_id = ' . $DB->Quote($this->id) . '
                          AND attachment_id = ' . $DB->Quote($atm_id);
            $res = &$DB->execute( $q );
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }

            // delete attachment itsself
            $q = 'DELETE FROM ' . DB_SCHEMA . '.attachments
                        WHERE id = ' . $DB->Quote($atm_id);
            $res = &$DB->execute( $q );
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
        }
    }
  
    public function getAccumulatedAttachmentSize() {
    	$accAtmSize = 0;
        if ($this->attachments) {
            foreach ($this->attachments as $atm) {
            	if (!in_array($atm->id, $this->attachmentsToDelete)) {
                    $accAtmSize += $atm->getFileSize();
                }
            }
        }
        if ($this->attachmentsToAdd) {
            foreach ($this->attachmentsToAdd as $atm) {
                $accAtmSize += $atm->getFileSize();
            }
        }
        return $accAtmSize;
    }
  
    /**
     * gives all attachments of this entry
     * 
     * @return array array of EntryAttachmentModel objects
     */
    protected function _getAttachments($tableName) {
        // check, if attachments have not already been loaded
        if ($this->attachments == null and $this->id != null and $this->id > 0) {
            $DB = Database::getHandle();
            
            $q = 'SELECT attachment_id
                    FROM ' . DB_SCHEMA . '.' . $tableName . '
                   WHERE entry_id= ' . $DB->Quote($this->id);
            
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            
            // collect ids of entry attachments in an array
            $attachmentIds = array();
            foreach ($res as $k => $row) {
                array_push($attachmentIds, $row['attachment_id']);
            }
            
            $this->attachments = EntryAttachmentModel::getAttachmentsByIds($attachmentIds);
        }
        
        // filter out attachments to be deleted and add the ones to be added
        $attachmentsReal = array();
        if ($this->attachments) {
            foreach ($this->attachments as $atm) {
                if (!in_array($atm->id, $this->attachmentsToDelete)) {
                    array_push($attachmentsReal, $atm);
                }
            }
        }
        if ($this->attachmentsToAdd != null) {
            // set negative ids for attachments not yet in the database
            // setting ids is neccessary to reference them, e.g. in preview mode
            $id = -1;
            foreach ($this->attachmentsToAdd as $a) {
                $a->id = $id--;
                array_push($attachmentsReal, $a);
            }
        }
        return $attachmentsReal;
    }
  
    /**
     * adds an attachment to this entry
     * 
     * @param EntryAttachmentModel $attachment attachment to add
     */
    public function addAttachment($attachment) {
        array_push($this->attachmentsToAdd, $attachment);
    }
    
     /**
     * adds an attachment to this entry
     * 
     * @param EntryAttachmentModel $attachment attachment to add
     */
    public function addMCAttachment($attachment) {
        array_push($this->attachmentsMCToAdd, $attachment);
    }
    
    public function deleteAttachmentById($id) {
    	// in case of positive id, entry exists, so use attachmentsToDelete
    	if ($id>0) {
            array_push($this->attachmentsToDelete, $id);
        }
        // in case of negative id, entry has not been stored to database
        // so filter attachmentsToAdd
        else {
        	$attachments = array();
            // filter attachment with $id from attachmentsToAdd
            foreach ($this->attachmentsToAdd as $atm) {
            	if ($atm->id != $id) {
            		array_push($attachments, $atm);
            	} else {
            		// mark the no longer needed uploaded file
                    // so it will be deleted later
            		$result = AttachmentHandler::markFileForDeletion($atm->getFilePath());
                    
                    // if marking process was not successful, do not remove 
                    // attachment from array
                    if (!$result) {
                    	array_push($attachments, $atm);
                    }
            	}
            }
            $this->attachmentsToAdd = $attachments;
        }
    }
    
    public function getAttachmentsToAdd() {
        return $this->attachmentsToAdd;
    }
    
    public function getAttachmentsToDelete() {
        return $this->attachmentsToDelete;
    }
}

?>
