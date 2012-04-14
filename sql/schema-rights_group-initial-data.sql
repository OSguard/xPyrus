--
-- xPyrus - Framework for Community and knowledge exchange
-- Copyright (C) 2003-2008 UniHelp e.V., Germany
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Affero General Public License as
-- published by the Free Software Foundation, only version 3 of the
-- License.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
-- GNU Affero General Public License for more details.
--
-- You should have received a copy of the GNU Affero General Public License
-- along with this program. If not, see http://www.gnu.org/licenses/agpl.txt
--


BEGIN;

-- insert some default rights for every group

INSERT INTO __SCHEMA__.rights_group (group_id, right_id) 
  SELECT  DISTINCT ON (id) id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('GROUP_INFOPAGE_EDIT')) FROM __SCHEMA__.groups ;
  
INSERT INTO __SCHEMA__.rights_group (group_id, right_id) 
  SELECT  DISTINCT ON (id) id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('NEWS_ENTRY_ADD')) FROM __SCHEMA__.groups ;
  
INSERT INTO __SCHEMA__.rights_group (group_id, right_id) 
  SELECT  DISTINCT ON (id) id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('NEWS_ENTRY_EDIT')) FROM __SCHEMA__.groups ;
  
INSERT INTO __SCHEMA__.rights_group (group_id, right_id) 
  SELECT  DISTINCT ON (id) id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('GROUP_OWN_ADMIN')) FROM __SCHEMA__.groups ;

INSERT INTO __SCHEMA__.rights_group (group_id, right_id) 
  SELECT  DISTINCT ON (id) id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('FORUM_GROUP_MODERATOR')) FROM __SCHEMA__.groups ;
  
INSERT INTO __SCHEMA__.rights_group (group_id, right_id) 
  SELECT  DISTINCT ON (id) id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('FORUM_GROUP_THREAD_ENTRY_ADD')) FROM __SCHEMA__.groups ;  

-- give group_rights to user

INSERT INTO __SCHEMA__.rights_user_group (user_id, group_id, right_id) 
  SELECT  DISTINCT ON (id) user_id, group_id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('GROUP_INFOPAGE_EDIT')) FROM __SCHEMA__.user_group_membership ;
  
INSERT INTO __SCHEMA__.rights_user_group (user_id, group_id, right_id) 
  SELECT  DISTINCT ON (id) user_id, group_id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('NEWS_ENTRY_ADD')) FROM __SCHEMA__.user_group_membership ;
  
INSERT INTO __SCHEMA__.rights_user_group (user_id, group_id, right_id) 
  SELECT  DISTINCT ON (id) user_id, group_id , (SELECT  DISTINCT ON (id) id FROM __SCHEMA__.rights WHERE is_group_specific AND name IN ('NEWS_ENTRY_EDIT')) FROM __SCHEMA__.user_group_membership ;  

COMMIT;