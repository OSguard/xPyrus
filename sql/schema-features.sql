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
-- contains the features (rights in disguise)
--
-- $Id: schema-features.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all features
CREATE TABLE __SCHEMA__.features (
  id                      SERIAL                   PRIMARY KEY,
  right_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.rights(id),
  description             VARCHAR(250)             NOT NULL
                                                   CHECK (LENGTH(description) > 10),
  description_english     VARCHAR(250)             NOT NULL
                                                   CHECK (LENGTH(description_english) > 10),
  picture_url             VARCHAR(20)              NOT NULL
                                                   DEFAULT '',
  point_level             INT                      NOT NULL
                                                   DEFAULT 50
                                                   CHECK (point_level > 0)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.features TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.features_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.features TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.features_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___features_right_id ON __SCHEMA__.features(right_id);

-- ###########################################################################
-- contains the relationship features to user
CREATE TABLE __SCHEMA__.user_features (
  id                      SERIAL                   PRIMARY KEY,
  feature_id              BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.features(id),
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id) ON DELETE CASCADE,
  UNIQUE(feature_id, user_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_features TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_features_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_features TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_features_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___user_features_user_id ON __SCHEMA__.user_features(user_id);

COMMIT;
