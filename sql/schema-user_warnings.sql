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
-- Comments in PostgreSQL start with --
-- or C-Style with /* */
--
-- $Id: schema-user_warnings.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains warnings of users

CREATE TABLE __SCHEMA__.user_warnings (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES
                                                   __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  warning_type            VARCHAR(3)               NOT NULL
                                                   CHECK(warning_type IN ('y', 'ry', 'r', 'g')),
  reason                  TEXT                     NOT NULL,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  declared_until          TIMESTAMPTZ              NOT NULL,
  role_corrected          BOOLEAN                  NOT NULL
                                                   DEFAULT false
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_warnings_user_id ON __SCHEMA__.user_warnings(user_id);
CREATE INDEX __SCHEMA___user_warnings_declared_until ON __SCHEMA__.user_warnings(declared_until);
GRANT ALL ON __SCHEMA__.user_warnings TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_warnings_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_warnings TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_warnings_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE TRIGGER __SCHEMA___user_warnings_insert BEFORE INSERT ON __SCHEMA__.user_warnings FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();
CREATE INDEX __SCHEMA___user_warnings_role_corrected ON __SCHEMA__.user_warnings(role_corrected);

COMMIT;
