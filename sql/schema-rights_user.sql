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
-- contains the rights per user
--
-- $Id: schema-rights_user.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all user rights
CREATE TABLE __SCHEMA__.rights_user (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  user_external_id        BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE CASCADE,
  right_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.rights(id)
                                                   ON DELETE CASCADE,
  right_granted           BOOLEAN                 NOT NULL
                                                   DEFAULT true,
  UNIQUE(user_id,user_external_id,right_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.rights_user TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.rights_user_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.rights_user TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.rights_user_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___rights_user_user_id ON __SCHEMA__.rights_user(user_id);
CREATE INDEX __SCHEMA___rights_user_user_external_id ON __SCHEMA__.rights_user(user_external_id);
CREATE INDEX __SCHEMA___rights_user_right_id ON __SCHEMA__.rights_user(right_id);

-- this trigger checks that no group rights are inserted for users
CREATE TRIGGER __SCHEMA___rights_user_insert BEFORE INSERT ON __SCHEMA__.rights_user FOR EACH ROW
               EXECUTE PROCEDURE public.validate_add_user_right('__SCHEMA__');

COMMIT;
