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
-- contains the rights
--
-- $Id: schema-rights.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table is 'per-schema', a global table will be created later
-- and spread some default rights into all schema tables

BEGIN;


-- ###########################################################################
-- contains all rights
CREATE TABLE __SCHEMA__.rights (
  id                      SERIAL                   PRIMARY KEY,
  name                    VARCHAR(200)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  description             VARCHAR(250)             NOT NULL
                                                   CHECK (LENGTH(description) > 10),
  default_allowed         BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  is_group_specific       BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.rights TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.rights_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.rights TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.rights_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___rights_name ON __SCHEMA__.rights(name);

-- import global rights data from public.rights
CREATE TRIGGER __SCHEMA___copy_rights AFTER INSERT OR UPDATE OR DELETE ON public.rights FOR EACH ROW
               EXECUTE PROCEDURE public.copy_rights('__SCHEMA__');

-- rights

-- copy default rights from public table
INSERT INTO __SCHEMA__.rights (name, description, default_allowed, is_group_specific)
            SELECT name, description, default_allowed, is_group_specific
              FROM public.rights;


COMMIT;
