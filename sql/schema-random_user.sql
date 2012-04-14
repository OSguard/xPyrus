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
-- contains the table for selecting a pseudo-random user
--
-- $Id: schema-random_user.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all users for random user selection
CREATE TABLE __SCHEMA__.random_user (
  id                      BIGINT                   NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   UNIQUE
                                                   REFERENCES __SCHEMA__.users(id),
  next_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.random_user(id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.random_user TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.random_user TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___random_user_user_id ON __SCHEMA__.random_user(user_id);
CREATE INDEX __SCHEMA___random_user_next_id ON __SCHEMA__.random_user(next_id);

CREATE TABLE __SCHEMA__.random_user_data (
  id                      INT                      NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id = 1),
  ru_id                   BIGINT                   REFERENCES __SCHEMA__.random_user(id),
  max_id                  BIGINT                   NOT NULL
                                                   DEFAULT 0
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.random_user_data TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.random_user_data TO GROUP __DB_ADMIN_GROUP__;

INSERT INTO __SCHEMA__.random_user_data
            (id, ru_id, max_id)
     VALUES (1, NULL, 0);
-- protect against deletes
CREATE RULE __SCHEMA__random_user_data_del
            AS ON DELETE TO __SCHEMA__.random_user_data
            DO INSTEAD nothing;

COMMIT;
