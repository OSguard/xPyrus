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
-- contains global config settings
--
-- $Id: schema-global_config.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table is 'per-schema', a global table will be created later
-- and spread some default configuration settings into all schema tables

BEGIN;


-- ###########################################################################
-- contains global config settings
CREATE TABLE __SCHEMA__.global_config (
  id                      BIGSERIAL                PRIMARY KEY,
  config_name             VARCHAR(50)             NOT NULL,
  config_value            TEXT                     NOT NULL
                                                   DEFAULT '',  
  description             VARCHAR(250)            NOT NULL
                                                   CHECK (LENGTH(description) > 10),
  UNIQUE (config_name)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.global_config TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.global_config_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.global_config TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.global_config_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___global_config_config_name ON __SCHEMA__.global_config(config_name);

-- import global global configuration settings from public.global_config
CREATE TRIGGER __SCHEMA___copy_global_config AFTER INSERT ON public.global_config FOR EACH ROW
               EXECUTE PROCEDURE public.copy_global_config('__SCHEMA__');

-- rights

-- copy default configuration settings from public table
INSERT INTO __SCHEMA__.global_config (config_name, config_value, description)
            SELECT config_name, config_value, description
              FROM public.global_config;

COMMIT;
