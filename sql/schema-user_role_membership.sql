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
-- contains the membership of users in roles
--
-- $Id: schema-user_role_membership.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains the membership of users in roles
CREATE TABLE __SCHEMA__.user_role_membership (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  user_external_id        BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE CASCADE,
  role_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.user_roles(id)
                                                   ON DELETE CASCADE,
  UNIQUE(user_id,user_external_id,role_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_role_membership TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_role_membership_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_role_membership TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_role_membership_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___user_role_membership_user_id ON __SCHEMA__.user_role_membership(user_id);
CREATE INDEX __SCHEMA___user_role_membership_user_external_id ON __SCHEMA__.user_role_membership(user_external_id);
CREATE INDEX __SCHEMA___user_role_membership_role_id ON __SCHEMA__.user_role_membership(role_id);


COMMIT;
