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
-- contains the tags
--
-- $Id: schema-tag.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;

-- ###########################################################################
-- contains all tags
CREATE TABLE __SCHEMA__.tag (
  id                      BIGSERIAL                NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY,
  name                    VARCHAR(150)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0)
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.tag IS 'contains all known tags';
COMMENT ON COLUMN __SCHEMA__.tag.name IS 'the name of the tag';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.tag TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.tag_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.tag TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.tag_id_seq TO GROUP __DB_ADMIN_GROUP__;

COMMIT;
