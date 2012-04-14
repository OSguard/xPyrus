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
-- contains the entry attachments
--
-- $Id: schema-attachments.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains attachments of entries (such as forum-, gb-, blog-entries)
CREATE TABLE __SCHEMA__.attachments (
  id                      BIGSERIAL                PRIMARY KEY,
  path                    VARCHAR(255)             NOT NULL,
  file_size               INT                      NOT NULL,
  upload_time             TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  file_type               VARCHAR(20)              NOT NULL
                                                   DEFAULT '',
  author_id               BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL
                                                   /*,
  dir                     BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.media_dir(id)
                                                   ON DELETE RESTRICT,
  count_reference         BIGINT                   NOT NULL
                                                   DEFAULT 0
                                                   CHECK(count_reference >= 0),
  description             TEXT                     NOT NULL
                                                   DEFAULT ''*/
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.attachments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.attachments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.attachments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.attachments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___attachments_file_type ON __SCHEMA__.attachments(file_type);
CREATE INDEX __SCHEMA___attachments_author ON __SCHEMA__.attachments(author_id);

-- trigger to update attachment counter for the user
CREATE TRIGGER __SCHEMA___update_counter_attachments AFTER INSERT OR DELETE ON __SCHEMA__.attachments FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_attachment('__SCHEMA__');

-- ###########################################################################
-- contains attachments of entries that have been deleted
CREATE TABLE __SCHEMA__.attachments_old (
  id                      BIGSERIAL                PRIMARY KEY,
  path                    VARCHAR(255)             NOT NULL,
  upload_time             TIMESTAMPTZ              NOT NULL,
  delete_time             TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW()
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.attachments_old TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.attachments_old_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.attachments_old TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.attachments_old_id_seq TO GROUP __DB_ADMIN_GROUP__;


-- trigger to backup information about deleted attachments
CREATE TRIGGER __SCHEMA___move_old_attachments BEFORE DELETE ON __SCHEMA__.attachments FOR EACH ROW
               EXECUTE PROCEDURE public.move_old_attachments('__SCHEMA__');



COMMIT;
