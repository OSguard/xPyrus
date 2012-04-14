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
--default (internal_users)
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
			  (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ENTRY_ADD') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ENTRY_EDIT') );
--INSERT INTO __SCHEMA__.rights_role
--    (role_id, right_id)
--  VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
--           (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ENTRY_DELETE') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ENTRY_QUOTE') );
            
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BLOG_ENTRY_ADD') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BLOG_ENTRY_EDIT') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BLOG_ENTRY_DELETE') );

INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BLOG_ADVANCED_CREATE') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='blog_owners'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BLOG_ADVANCED_OWN_ADMIN') );

INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FRIENDLIST_MODIFY') );

INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FEATURE_SELECT') );

INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FORUM_THREAD_ENTRY_ADD') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FORUM_THREAD_ENTRY_EDIT') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FORUM_THREAD_RATING') );

INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='PROFILE_MODIFY') );

INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='PM_READ_MESSAGES') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='PM_ADD_USER_MESSAGES') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='PM_SENDTO_GROUP') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='PM_SENDTO_COURSE') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='PM_DEL_USER_MESSAGES') );

INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='COURSES_FILE_UPLOAD') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='COURSES_FILE_EDIT') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='COURSES_FILE_DOWNLOAD') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='COURSES_FILE_RATING') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='COURSES_FILE_SEARCH') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='USER_SEARCH_ADVANCED') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='LOGIN') );
INSERT INTO __SCHEMA__.rights_role
        (role_id, right_id)
    VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FEATURE_BOX_REARRANGEMENT') );

INSERT INTO __SCHEMA__.rights_role
        (role_id, right_id)
    VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='internal_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='POST_SHOUTBOX') );

--
-- external users
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='external_users'),
			  (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ENTRY_ADD') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='external_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FORUM_THREAD_ENTRY_ADD') );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='external_users'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='LOGIN') );


--
-- card games
INSERT INTO __SCHEMA__.rights_role
     (role_id, right_id, right_granted)
  VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='card_yellow_red'),
     (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ENTRY_ADD'),
     false );
INSERT INTO __SCHEMA__.rights_role
     (role_id, right_id, right_granted)
  VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='card_red'),
     (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ENTRY_ADD'),
     false );
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='card_red'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='LOGIN'),
            false);

INSERT INTO __SCHEMA__.rights_role (role_id, right_id, right_granted) 
    SELECT role.id, r.id, 'f'
      FROM __SCHEMA__.rights AS r,
           __SCHEMA__.user_roles AS role
     WHERE role.name = 'guests'
       AND NOT r.is_group_specific
       AND NOT r.default_allowed
       AND r.id NOT IN (SELECT id FROM __SCHEMA__.rights WHERE name IN ('LOGIN', 'PROFILE_MODIFY'));
