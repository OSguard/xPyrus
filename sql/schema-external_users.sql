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
-- $Id: schema-external_users.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all external users

-- notes: how to authenticate the user? Token, Session?

CREATE TABLE __SCHEMA__.external_users (
  id                      BIGSERIAL                PRIMARY KEY,
  last_change             TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  insert_at               TIMESTAMPTZ              NOT NULL,
  username                VARCHAR(30)              NOT NULL,
  city_id                 INTEGER                  NOT NULL
                                                   REFERENCES public.cities(id),
  external_id             BIGINT                   NOT NULL,
  UNIQUE(city_id, external_id)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___external_users_username ON __SCHEMA__.external_users(username);
CREATE INDEX __SCHEMA___external_users_username_lower ON __SCHEMA__.external_users(LOWER(username));
GRANT ALL ON __SCHEMA__.external_users TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.external_users_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.external_users TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.external_users_id_seq TO GROUP __DB_ADMIN_GROUP__;
-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___external_users_insert BEFORE INSERT ON __SCHEMA__.external_users FOR EACH ROW
               EXECUTE PROCEDURE public.set_insert_at();
-- set the current timestamp on 'last_change'
CREATE TRIGGER __SCHEMA___external_users_update BEFORE UPDATE ON __SCHEMA__.external_users FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_change_at();
-- protect against id changes
CREATE RULE __SCHEMA___external_users_upd
            AS ON UPDATE TO __SCHEMA__.external_users
            WHERE old.id != new.id
            DO INSTEAD nothing;


COMMIT;
