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
-- contains the blog entries
--
-- $Id: schema-blog.sql 5743 2008-03-25 19:48:14Z ads $
--

-- the blog entries structure is a subset of the guestbook entries structure

BEGIN;


-- ###########################################################################
-- contains the blog entries
CREATE TABLE __SCHEMA__.blog (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  entry_raw               TEXT                     NOT NULL
                                                   DEFAULT '',
  entry_parsed            TEXT                     NOT NULL
                                                   DEFAULT '',
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7),
  enable_formatcode       BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  enable_html             BOOLEAN                  NOT NULL
                                                   DEFAULT false,
  enable_smileys          BOOLEAN                  NOT NULL
                                                   DEFAULT true
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_user_id ON __SCHEMA__.blog(user_id);

-- set correct update time
CREATE TRIGGER __SCHEMA___blog_ins_or_update BEFORE INSERT OR UPDATE ON __SCHEMA__.blog FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_update();
-- adjust some counters
CREATE TRIGGER __SCHEMA___update_counter_blog_entry AFTER INSERT OR DELETE ON __SCHEMA__.blog FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_blog_entry('__SCHEMA__');
			   
-- ###########################################################################
-- contains the attachments of blog entries
CREATE TABLE __SCHEMA__.blog_attachments (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.blog(id)
                                                   ON DELETE CASCADE,
  attachment_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.attachments(id)
                                                   ON DELETE CASCADE,
  UNIQUE(entry_id, attachment_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_attachments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_attachments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_attachments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_attachments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_attachments_entry_id ON __SCHEMA__.blog_attachments(entry_id);
CREATE INDEX __SCHEMA___blog_attachments_attachment_id ON __SCHEMA__.blog_attachments(attachment_id);

-- cascade attachment deletion to attachment table
CREATE TRIGGER __SCHEMA___blog_remove_attachment AFTER DELETE ON __SCHEMA__.blog_attachments FOR EACH ROW
               EXECUTE PROCEDURE public.remove_attachments('__SCHEMA__');

COMMIT;
