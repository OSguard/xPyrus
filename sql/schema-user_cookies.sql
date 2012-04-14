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


-- ###########################################################################
-- contains all identifiers (used in cookies) for users
CREATE TABLE __SCHEMA__.user_cookies (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
                                                   -- don't set user_id UNIQUE to allow multiple cookies per user
  identifier              CHAR(40)                 NOT NULL,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW()
) WITHOUT OIDS;

GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_cookies TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_cookies_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_cookies TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_cookies_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___user_cookies_insert BEFORE INSERT ON __SCHEMA__.user_cookies FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();

CREATE INDEX __SCHEMA___user_cookies_user_id ON __SCHEMA__.user_cookies(user_id);
CREATE INDEX __SCHEMA___user_cookies_identifier ON __SCHEMA__.user_cookies(identifier);

COMMIT;
