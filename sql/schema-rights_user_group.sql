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
-- contains the rights per user and group
--
-- $Id: schema-rights_user_group.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;

-- ###########################################################################
-- contains all rights that group may grant to their members
CREATE TABLE __SCHEMA__.rights_group (
  id                      BIGSERIAL                PRIMARY KEY,
  group_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.groups(id)
                                                   ON DELETE CASCADE,
  right_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.rights(id)
                                                   ON DELETE CASCADE,
  UNIQUE(group_id,right_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.rights_group TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.rights_group_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.rights_group TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.rights_group_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE TRIGGER __SCHEMA___rights_group_insert BEFORE INSERT ON __SCHEMA__.rights_group FOR EACH ROW
               EXECUTE PROCEDURE public.validate_add_user_group_has_right('__SCHEMA__');
CREATE TRIGGER __SCHEMA___del_user_group_has_right_delete BEFORE DELETE ON __SCHEMA__.rights_group FOR EACH ROW
               EXECUTE PROCEDURE public.del_user_group_has_right('__SCHEMA__');

CREATE INDEX __SCHEMA___rights_group_group_id ON __SCHEMA__.rights_group(group_id);
CREATE INDEX __SCHEMA___rights_group_right_id ON __SCHEMA__.rights_group(right_id);


-- ###########################################################################
-- contains all rights that user have on behalf of their __group__ membership
CREATE TABLE __SCHEMA__.rights_user_group (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  group_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.groups(id)
                                                   ON DELETE CASCADE,
  right_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.rights(id)
                                                   ON DELETE CASCADE,
  UNIQUE(user_id,group_id,right_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.rights_user_group TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.rights_user_group_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.rights_user_group TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.rights_user_group_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___rights_user_group_user_id ON __SCHEMA__.rights_user_group(user_id);
CREATE INDEX __SCHEMA___rights_user_group_group_id ON __SCHEMA__.rights_user_group(group_id);
CREATE INDEX __SCHEMA___rights_user_group_right_id ON __SCHEMA__.rights_user_group(right_id);

CREATE TRIGGER __SCHEMA___rights_user_group_insert BEFORE INSERT ON __SCHEMA__.rights_user_group FOR EACH ROW
               EXECUTE PROCEDURE public.validate_add_user_group_right('__SCHEMA__');

COMMIT;
