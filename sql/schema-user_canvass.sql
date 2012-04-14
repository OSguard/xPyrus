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
-- contains all information for user canvass
CREATE TABLE __SCHEMA__.user_canvass (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  email                   VARCHAR(100)             NOT NULL,
  hash                    VARCHAR(40)              NOT NULL
) WITHOUT OIDS;

GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_canvass TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_canvass_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_canvass TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_canvass_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___user_canvass_insert BEFORE INSERT ON __SCHEMA__.user_canvass FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();

CREATE INDEX __SCHEMA___user_canvass_user_id ON __SCHEMA__.user_canvass(user_id);


COMMIT;
