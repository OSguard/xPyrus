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
--
-- sample data for rights-db (schema part)
--


-- deactivated, look in user_test_data.txt!


--
-- role specific stuff
--

--> used in core -> moved to schema-role_rights-initial-data.sql

--
-- user specific stuff, only demo ;)
--
INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='kyle'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BANNER_ADMIN'),
            true );
INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='schnueptus'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BANNER_ADMIN'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='kyle'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FORUM_CATEGORY_ADMIN'),
            true );
INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='kyle'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='USER_RIGHT_ADMIN'),
            true );
INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='kyle'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='ROLE_ADMIN'),
            true );
INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='kyle'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='GROUP_ADMIN'),
            true );
INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='kyle'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='NEWS_ENTRY_ADD'),
            true );
INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='kyle'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='NEWS_ENTRY_EDIT'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='schnueptus'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FORUM_CATEGORY_ADMIN'),
            true );
