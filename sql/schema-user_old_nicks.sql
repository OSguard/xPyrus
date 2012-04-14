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
-- contains the old nicks for an user
--
-- $Id: schema-user_old_nicks.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all old nicks for an users

CREATE TABLE __SCHEMA__.user_old_nicks (
  id                      BIGSERIAL                PRIMARY KEY,
  insert_at               TIMESTAMPTZ              NOT NULL,
  user_id                 BIGINT                   NOT NULL,
  old_username            VARCHAR(50)              NOT NULL,
  new_username            VARCHAR(50)              NULL
) WITHOUT OIDS;

GRANT ALL ON __SCHEMA__.user_old_nicks TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_old_nicks_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_old_nicks TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_old_nicks_id_seq TO GROUP __DB_ADMIN_GROUP__;
-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___user_old_nicks_insert BEFORE INSERT ON __SCHEMA__.user_old_nicks FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();

-- check for old usernames
CREATE TRIGGER __SCHEMA___username_add BEFORE INSERT ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.username_add('__SCHEMA__');
-- log every changed username
CREATE TRIGGER __SCHEMA___username_changes BEFORE UPDATE ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.username_changes('__SCHEMA__');
-- log every deleted username
CREATE TRIGGER __SCHEMA___username_deleted AFTER DELETE ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.username_deleted('__SCHEMA__');

-- protect against changes
-- CREATE RULE __SCHEMA___user_old_nicks_upd
--             AS ON UPDATE TO __SCHEMA__.user_old_nicks
--             DO INSTEAD nothing;
-- CREATE RULE __SCHEMA___user_old_nicks_del
--             AS ON DELETE TO __SCHEMA__.user_old_nicks
--             DO INSTEAD nothing;


COMMIT;
