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
-- contains the user_friends/unwanted persons
--
-- $Id: schema-user_friends.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all user_friends/unwanted persons for a user

CREATE TABLE __SCHEMA__.user_friends (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  friend_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  friend_type             INTEGER                  NOT NULL
                                                   REFERENCES public.friend_types(id),
  CHECK(user_id != friend_id),
  UNIQUE(user_id, friend_id)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_friends_user_id ON __SCHEMA__.user_friends(user_id);
CREATE INDEX __SCHEMA___user_friends_friend_id ON __SCHEMA__.user_friends(friend_id);
CREATE INDEX __SCHEMA___user_friends_friend_type ON __SCHEMA__.user_friends(friend_type);

GRANT ALL ON __SCHEMA__.user_friends TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_friends_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_friends TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_friends_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- validate inserts
CREATE TRIGGER __SCHEMA___validate_add_new_friend BEFORE INSERT ON __SCHEMA__.user_friends FOR EACH ROW
               EXECUTE PROCEDURE public.validate_add_new_friend('__SCHEMA__');
-- validate updates
CREATE TRIGGER __SCHEMA___validate_update_friend BEFORE UPDATE ON __SCHEMA__.user_friends FOR EACH ROW
               EXECUTE PROCEDURE public.validate_update_friend();


COMMIT;
