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
-- FIXME: correct limit values
INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='FRIENDLIST_EXTENDED_CATEGORIES'), 1, 'Du kannst die Freunde in Deiner Freundesliste kategorisieren', 'You may categorize friends in your friendlist');
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FRIENDLIST_EXTENDED_CATEGORIES') );

INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='GB_FILTER'), 1, 'Du kannst in Deinem Gästebuch Einträge filtern', 'You may filter your own guestbook');
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='GB_FILTER') );

INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='GB_ENTRY_COMMENT'), 1, 'Du kannst Einträge in Deinem Gästebuch kommentieren', 'You may comment on entries in your own guestbook');
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ENTRY_COMMENT') );

INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='BLOG_FILTER'), 1, 'Du kannst in allen Tagebüchern Einträge filtern', 'You may filter your all blogs');
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BLOG_FILTER') );
            
INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='FEATURE_SMALLWORLD'), 1, 'Du kannst eine Small-World-Anfrage starten', 'You may run a small-world query');
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FEATURE_SMALLWORLD') );

INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='FEATURE_REVERSE_FRIENDLIST'), 1, 'Du kannst Dir anzeigen, welche User Dich auf ihrer Freundesliste haben', 'You may be shown all users that have you on their friendlist');
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FEATURE_REVERSE_FRIENDLIST') );

INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='GB_ADVANCED_STATS'), 1, 'Du kannst Dir vertiefte Statistiken zu Deinem Gästebuch anzeigen lassen', 'You may be shown advanced statistics about your guestbook');
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='GB_ADVANCED_STATS') );

INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='PM_ADD_ATTACHMENT'), 1, 'Du kannst zu Deinen PMs Anhänge hinzufügen.', 'You may send attachments with a pm');
INSERT INTO __SCHEMA__.rights_role
		(role_id, right_id)
	VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='PM_ADD_ATTACHMENT') );

INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='FEATURE_BOX_REARRANGEMENT'), 1, 'Du kannst die Boxen in der linken und rechten Spalte neu anordnen.', 'You may rearrange the boxes in the left and right column.');
INSERT INTO __SCHEMA__.rights_role
        (role_id, right_id)
    VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FEATURE_BOX_REARRANGEMENT') );
            
INSERT INTO __SCHEMA__.features (right_id,point_level,description,description_english)
       VALUES ((SELECT id FROM __SCHEMA__.rights WHERE name ='PM_SENDTO_FRIENDS'), 1, 'Du kannst eine Rundmail als PM an Deine Freundesliste schicken.', 'You may send a user pm to your friendlist');
INSERT INTO __SCHEMA__.rights_role
        (role_id, right_id)
    VALUES ( (SELECT id FROM __SCHEMA__.user_roles WHERE name='features'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='PM_SENDTO_FRIENDS') );            

