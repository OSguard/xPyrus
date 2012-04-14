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
-- $Id: schema-user_expiration.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;

-- ###########################################################################
-- contains users who are subject to additional constraints

CREATE TABLE __SCHEMA__.user_expiration (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   UNIQUE
                                                   REFERENCES
                                                   __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  expires                 TIMESTAMPTZ              NOT NULL
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_expiration_user_id ON __SCHEMA__.user_expiration(user_id);
GRANT ALL ON __SCHEMA__.user_expiration TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_expiration_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_expiration TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_expiration_id_seq TO GROUP __DB_ADMIN_GROUP__;

COMMIT;
