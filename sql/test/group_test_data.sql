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
-- user/group membership
--

INSERT INTO __SCHEMA__.groups
            (title, name,
             description, is_visible)
      VALUES ('Gruppe', 'Krusty Fanclub',
              'Krusty Fanclub', true);


            
INSERT INTO __SCHEMA__.user_group_membership
		(user_id, group_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
            (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub') );
INSERT INTO __SCHEMA__.user_group_membership
		(user_id, group_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
            (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub') );
INSERT INTO __SCHEMA__.user_group_membership
		(user_id, group_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Meggy_Simpson'),
            (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub') );
INSERT INTO __SCHEMA__.user_group_membership
		(user_id, group_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),
            (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub') );
INSERT INTO __SCHEMA__.user_group_membership
		(user_id, group_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Marge_Simpson'),
            (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub') );
INSERT INTO __SCHEMA__.user_group_membership
		(user_id, group_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Carl'),
            (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub') );



            
-- some rights
INSERT INTO __SCHEMA__.rights_group
		(group_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='NEWS_ENTRY_ADD') );
INSERT INTO __SCHEMA__.rights_group
		(group_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='NEWS_ENTRY_EDIT') );
INSERT INTO __SCHEMA__.rights_group
		(group_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='GROUP_OWN_ADMIN') );
INSERT INTO __SCHEMA__.rights_user_group
		(user_id, group_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
                 (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
                 (SELECT id FROM __SCHEMA__.rights WHERE name='NEWS_ENTRY_ADD') );
INSERT INTO __SCHEMA__.rights_user_group
		(user_id, group_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
                 (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
                 (SELECT id FROM __SCHEMA__.rights WHERE name='NEWS_ENTRY_EDIT') );
INSERT INTO __SCHEMA__.rights_user_group
		(user_id, group_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
                 (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
                 (SELECT id FROM __SCHEMA__.rights WHERE name='GROUP_OWN_ADMIN') );

