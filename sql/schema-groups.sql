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
-- contains the user groups
--
-- $Id: schema-groups.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table is 'per-schema', a global table will be created later
-- and spread some default groups into all schema tables

BEGIN;


-- ###########################################################################
-- contains all user groups
CREATE TABLE __SCHEMA__.groups (
  id                      SERIAL                   PRIMARY KEY,
  title                   VARCHAR(250)             NOT NULL
                                                   DEFAULT 'group',
  name                    VARCHAR(200)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  description             VARCHAR(250)             NOT NULL
                                                   CHECK (LENGTH(description) > 10),
  infopage_raw            TEXT                     NOT NULL
                                                   DEFAULT '',
  infopage_parsed         TEXT                     NOT NULL
                                                   DEFAULT '',
  grouppic_file           VARCHAR(250)             NOT NULL
                                                   DEFAULT '',
  is_visible              BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.groups TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.groups_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.groups TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.groups_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___groups_name ON __SCHEMA__.groups(name);

-- import global group data from public.groups
CREATE TRIGGER __SCHEMA___copy_groups AFTER INSERT OR UPDATE OR DELETE ON public.groups FOR EACH ROW
               EXECUTE PROCEDURE public.copy_groups('__SCHEMA__');
CREATE TRIGGER __SCHEMA___courses_insert AFTER INSERT ON __SCHEMA__.groups FOR EACH ROW 
                EXECUTE PROCEDURE public.insert_user_group('__SCHEMA__');
-- groups

-- copy default groups from public table
-- moved to test data

COMMIT;
