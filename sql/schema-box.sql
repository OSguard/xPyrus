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
-- contains all known boxes
CREATE TABLE __SCHEMA__.box_type (
  id                BIGSERIAL                PRIMARY KEY,
  name              VARCHAR(100)             NOT NULL UNIQUE,
  multi_instance    boolean					 NOT NULL
                                             DEFAULT FALSE
) WITHOUT OIDS;
CREATE INDEX __SCHEMA__box_type_id ON __SCHEMA__.box_type(id);
CREATE INDEX __SCHEMA__box_type_name ON __SCHEMA__.box_type(name);
GRANT ALL ON __SCHEMA__.box_type TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.box_type_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.box_type TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.box_type_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- contains user config values (all configuration data for the platform)
CREATE TABLE __SCHEMA__.box_config (
  id                      BIGSERIAL                PRIMARY KEY,
  box_id                  BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.box_type(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  instance                INTEGER                  NOT NULL
                                                   DEFAULT 1 
                                                   CHECK(instance > 0),
  data_key				  VARCHAR(254)             NOT NULL
   											       CHECK(LENGTH(data_key) > 4),
  data_value              TEXT                     NOT NULL
                                                   DEFAULT '',
  UNIQUE (box_id, user_id, instance, data_key)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___box_config_1 ON __SCHEMA__.box_config(box_id, user_id, instance);
CREATE INDEX __SCHEMA___box_config_2 ON __SCHEMA__.box_config(box_id, user_id, instance, data_key);

GRANT ALL ON __SCHEMA__.box_config TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.box_config_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.box_config TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.box_config_id_seq TO GROUP __DB_ADMIN_GROUP__;

INSERT INTO __SCHEMA__.box_type(name, multi_instance) VALUES('user_online'  , FALSE);
INSERT INTO __SCHEMA__.box_type(name, multi_instance) VALUES('birthday', FALSE);
INSERT INTO __SCHEMA__.box_type(name, multi_instance) VALUES('birthday_personal', FALSE);
INSERT INTO __SCHEMA__.box_type(name, multi_instance) VALUES('rss_reader'   , TRUE);
INSERT INTO __SCHEMA__.box_type(name, multi_instance) VALUES('courses_files' , TRUE);
INSERT INTO __SCHEMA__.box_type(name, multi_instance) VALUES('friendslist'  , FALSE);

-- contains user config values (all configuration data for the platform)
CREATE TABLE __SCHEMA__.box_shoutbox (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  entry_raw               TEXT		               NOT NULL
  												   DEFAULT '',
  entry_parsed            TEXT		               NOT NULL
  												   DEFAULT '',
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW()
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___box_shoutbox_date ON __SCHEMA__.box_shoutbox(entry_time);

GRANT ALL ON __SCHEMA__.box_shoutbox TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.box_shoutbox TO GROUP __DB_ADMIN_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.box_shoutbox_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.box_shoutbox_id_seq TO GROUP __DB_ADMIN_GROUP__;


