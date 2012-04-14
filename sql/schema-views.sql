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
-- some views to make life on sql console easier
--
BEGIN;

CREATE VIEW __SCHEMA__.view_user_with_extra AS
    SELECT u.*, ue.first_name, ue.last_name, ue.public_email, ue.private_email, ue.uni_email
      FROM __SCHEMA__.users u,
           __SCHEMA__.user_extra_data ue
     WHERE u.id = ue.id;

CREATE VIEW __SCHEMA__.view_user_with_config AS
    SELECT uc.id, u.id AS uid, u.username, uc.data_name_id, uck.data_name, uc.data_value
      FROM __SCHEMA__.user_config uc,
           __SCHEMA__.users u,
           public.user_config_keys uck
     WHERE u.id = uc.user_id
       AND uc.data_name_id = uck.id;

CREATE VIEW __SCHEMA__.view_user_with_data AS
    SELECT uc.id, u.id AS uid, u.username, uc.data_name_id, uck.data_name, uc.data_value
      FROM __SCHEMA__.user_data uc,
           __SCHEMA__.users u,
           public.user_data_keys uck
     WHERE u.id = uc.user_id
       AND uc.data_name_id = uck.id;

CREATE VIEW __SCHEMA__.view_user_with_contact_data AS
    SELECT uc.id, u.id AS uid, u.username, uc.data_name_id, uck.data_name, uc.data_value
      FROM __SCHEMA__.user_contact_data uc,
           __SCHEMA__.users u,
           public.user_contact_data_keys uck
     WHERE u.id = uc.user_id
       AND uc.data_name_id = uck.id;

COMMIT;
