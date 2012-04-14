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
INSERT INTO __SCHEMA__.user_warnings
        (user_id, warning_type, declared_until, reason)
    VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
            'y',
            NOW() + '6 days'::interval,
            'unzuechtiges Verhalten mit einem Schaeferhund' );

INSERT INTO __SCHEMA__.user_warnings
        (user_id, warning_type, declared_until, reason)
    VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
            'ry',
            NOW() + '2 seconds'::interval,
            'unzuechtiges Verhalten mit einem Wal' );

INSERT INTO __SCHEMA__.user_role_membership
        (user_id, role_id)
    VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
            (SELECT id FROM __SCHEMA__.user_roles WHERE name='card_yellow') );

INSERT INTO __SCHEMA__.user_features
        (user_id, feature_id)
   ( 
       (SELECT (SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),id 
          FROM __SCHEMA__.features) 
   );
   
INSERT INTO __SCHEMA__.user_config
        (user_id, data_name_id, data_value)
    (
        (SELECT (SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),id,'user_login,courses:1,birthday_personal:1,blog:1,user_online:1,courses_files:1' 
          FROM user_config_keys WHERE data_name ='boxes_left' ) 
        
    );
    
INSERT INTO __SCHEMA__.user_config
        (user_id, data_name_id, data_value)
    (
        (SELECT (SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),id,'user_search:1,shoutbox:1,friendslist:1,birthday:1' 
          FROM user_config_keys WHERE data_name ='boxes_right' ) 
        
    );    
