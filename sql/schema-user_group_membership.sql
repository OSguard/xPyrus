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
-- contains the membership of users in groups
--
-- $Id: schema-user_group_membership.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all user rights
CREATE TABLE __SCHEMA__.user_group_membership (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  group_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.groups(id)
                                                   ON DELETE CASCADE,
  UNIQUE(user_id,group_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_group_membership TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_group_membership_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_group_membership TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_group_membership_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE TRIGGER __SCHEMA___user_group_membership_delete AFTER DELETE ON __SCHEMA__.user_group_membership FOR EACH ROW
               EXECUTE PROCEDURE public.user_group_membership_delete('__SCHEMA__');

CREATE INDEX __SCHEMA___user_group_membership_user_id ON __SCHEMA__.user_group_membership(user_id);
CREATE INDEX __SCHEMA___user_group_membership_group_id ON __SCHEMA__.user_group_membership(group_id);


COMMIT;
