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
-- contains the awards
CREATE TABLE __SCHEMA__.award (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  name                    TEXT                     NOT NULL
                                                   DEFAULT '',
  icon                    TEXT                     NULL,
  link                    TEXT                     NULL
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.award TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.award_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.award TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.award_id_seq TO GROUP __DB_ADMIN_GROUP__;



-- ###########################################################################
-- contains the user awards
CREATE TABLE __SCHEMA__.user_award (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  award_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.award(id)
                                                   ON DELETE CASCADE,
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  rank                    INT                      NOT NULL
                                                   DEFAULT 0
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_award TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_award_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_award TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_award_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___user_award_user_id ON __SCHEMA__.user_award(user_id);

COMMIT;