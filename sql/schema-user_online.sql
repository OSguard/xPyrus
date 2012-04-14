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
-- contains all online users
CREATE TABLE __SCHEMA__.user_online (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  user_external_id        BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE CASCADE,
  online_since            TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  session_id              VARCHAR(40)              NOT NULL,
  UNIQUE(session_id)
) WITHOUT OIDS;

GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_online TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_online_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_online TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_online_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___user_online_user_id ON __SCHEMA__.user_online(user_id);
CREATE INDEX __SCHEMA___user_online_session_id ON __SCHEMA__.user_online(session_id);

-- validate inserts
/*CREATE TRIGGER __SCHEMA___validate_add_user_online BEFORE INSERT ON __SCHEMA__.user_online FOR EACH ROW
               EXECUTE PROCEDURE public.validate_add_user_online('__SCHEMA__');*/
CREATE TRIGGER __SCHEMA___update_online_user_stats AFTER DELETE ON __SCHEMA__.user_online FOR EACH ROW
               EXECUTE PROCEDURE public.update_online_user_stats('__SCHEMA__');

COMMIT;
