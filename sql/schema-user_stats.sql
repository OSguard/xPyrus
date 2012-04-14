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
-- Comments in PostgreSQL start with --
-- or C-Style with /* */
--
-- $Id: schema-user_stats.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains online time and point statistics about users

CREATE TABLE __SCHEMA__.user_stats (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   UNIQUE
                                                   REFERENCES
                                                   __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  online_time             INT [10]                 NOT NULL
                                                   DEFAULT ARRAY[0,0,0,0,0,0,0,0,0,0],
  level_points            INT [10]                 NOT NULL
                                                   DEFAULT ARRAY[0,0,0,0,0,0,0,0,0,0]
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_stats_user_id ON __SCHEMA__.user_stats(user_id);
GRANT ALL ON __SCHEMA__.user_stats TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_stats_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_stats TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_stats_id_seq TO GROUP __DB_ADMIN_GROUP__;


COMMIT;
