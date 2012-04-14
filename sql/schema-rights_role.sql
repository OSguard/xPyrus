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
-- contains the rights per role
--
-- $Id: schema-rights_role.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all role rights
CREATE TABLE __SCHEMA__.rights_role (
  id                      BIGSERIAL                PRIMARY KEY,
  role_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.user_roles(id)
                                                   ON DELETE CASCADE,
  right_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.rights(id)
                                                   ON DELETE CASCADE,
  right_granted           BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  UNIQUE(role_id,right_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.rights_role TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.rights_role_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.rights_role TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.rights_role_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___rights_role_role_id ON __SCHEMA__.rights_role(role_id);
CREATE INDEX __SCHEMA___rights_role_right_id ON __SCHEMA__.rights_role(right_id);

-- this trigger transforms inserts into updates to ensure unique-constraint
CREATE TRIGGER __SCHEMA___rights_role_insert BEFORE INSERT ON __SCHEMA__.rights_role FOR EACH ROW
               EXECUTE PROCEDURE public.validate_add_role_right('__SCHEMA__');

COMMIT;
