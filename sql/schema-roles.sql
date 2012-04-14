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
-- contains the user roles
--
-- $Id: schema-roles.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table is 'per-schema', a global table will be created later
-- and spread some default groups into all schema tables

BEGIN;


-- ###########################################################################
-- contains all user roles
CREATE TABLE __SCHEMA__.user_roles (
  id                      SERIAL                   PRIMARY KEY,
  name                    VARCHAR(200)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  description             VARCHAR(250)             NOT NULL
                                                   CHECK (LENGTH(description) > 10)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_roles TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_roles_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_roles TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_roles_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___user_roles_name ON __SCHEMA__.user_roles(name);

-- import global group data from public.user_roles
CREATE TRIGGER __SCHEMA___copy_user_roles AFTER INSERT OR UPDATE OR DELETE ON public.user_roles FOR EACH ROW
               EXECUTE PROCEDURE public.copy_user_roles('__SCHEMA__');

-- groups

-- copy default groups from public table
INSERT INTO __SCHEMA__.user_roles (name, description)
            SELECT name, description
              FROM public.user_roles;

COMMIT;
