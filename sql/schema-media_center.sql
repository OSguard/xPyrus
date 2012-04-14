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
-- contains structures of the media center
--
-- $Id: schema-media_center.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains directories inside the media center
CREATE TABLE __SCHEMA__.media_dir (
  id                      BIGSERIAL                PRIMARY KEY,
  name                    VARCHAR(100)             NOT NULL,
  type                    VARCHAR(10)              NOT NULL,
  parent_dir              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.media_dir(id)
                                                   ON DELETE RESTRICT,
  owner_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  visible_for             INT                      NOT NULL
                                                   REFERENCES public.details_visible(id)
                                                   ON DELETE RESTRICT,
  description             TEXT                     NOT NULL
                                                   DEFAULT '',
  number_of_files         INT                      NOT NULL
                                                   DEFAULT 0,
  number_of_subdirs       INT                      NOT NULL
                                                   DEFAULT 0                                                   
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.media_dir TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.media_dir_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.media_dir TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.media_dir_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___media_dir_owner_id ON __SCHEMA__.media_dir(owner_id);

COMMIT;
