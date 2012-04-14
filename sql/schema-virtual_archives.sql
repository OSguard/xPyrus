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
-- contains structures for building virtual archives
--
-- $Id: schema-virtual_archives.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;

-- ###########################################################################
-- contains virtual archives that may contain various other files
CREATE TABLE __SCHEMA__.virtual_archives (
  id                      BIGSERIAL                PRIMARY KEY,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  author_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  name                    VARCHAR(100)             NOT NULL,
  description             VARCHAR(250)             NOT NULL
                                                   DEFAULT '',
  UNIQUE(author_id, name)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.virtual_archives TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.virtual_archives_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.virtual_archives TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.virtual_archives_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___virtual_archives ON __SCHEMA__.virtual_archives(author_id);

-- ###########################################################################
-- contains the files of virtual archives coming from "attachments"
CREATE TABLE __SCHEMA__.virtual_archives_attachments (
  id                      BIGSERIAL                PRIMARY KEY,
  archive_id              BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.virtual_archives(id)
                                                   ON DELETE CASCADE,
  attachment_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.attachments(id)
                                                   ON DELETE CASCADE,
  UNIQUE(archive_id, attachment_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.virtual_archives_attachments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.virtual_archives_attachments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.virtual_archives_attachments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.virtual_archives_attachments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___virtual_archives_attachments_archive_id ON __SCHEMA__.virtual_archives_attachments(archive_id);
CREATE INDEX __SCHEMA___virtual_archives_attachments_attachment_id ON __SCHEMA__.virtual_archives_attachments(attachment_id);

COMMIT;
