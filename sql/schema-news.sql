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
-- contains the news entries
--
-- $Id: schema-news.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains the news entries
CREATE TABLE __SCHEMA__.news (
  id                      BIGSERIAL                PRIMARY KEY,
  author_int              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL,
  group_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.groups(id),
  thread_id               BIGINT                   DEFAULT NULL
                                                   REFERENCES __SCHEMA__.forum_threads(id) ON DELETE CASCADE,
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  start_date              DATE                     NOT NULL,
  end_date                DATE                     NOT NULL,
  caption                 VARCHAR(200)             NOT NULL
                                                   CHECK(LENGTH(caption) >= 5),
  opener_raw              TEXT                     NOT NULL
                                                   DEFAULT '',
  opener_parsed           TEXT                     NOT NULL
                                                   DEFAULT '',
  entry_raw               TEXT                     NOT NULL
                                                   DEFAULT '',
  entry_parsed            TEXT                     NOT NULL
                                                   DEFAULT '',
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7),
  is_sticky               BOOLEAN                  NOT NULL
                                                   DEFAULT false,
  enable_formatcode       BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  enable_html             BOOLEAN                  NOT NULL
                                                   DEFAULT false,
  enable_smileys          BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  is_visible              BOOLEAN                  NOT NULL
                                                   DEFAULT true  
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.news TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.news_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.news TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.news_id_seq TO GROUP __DB_ADMIN_GROUP__;

--CREATE INDEX __SCHEMA___news_user_id ON __SCHEMA__.news(user_id);
--CREATE INDEX __SCHEMA___news_target_date ON __SCHEMA__.news(target_date);
CREATE INDEX __SCHEMA___news_start_date ON __SCHEMA__.news(start_date);
CREATE INDEX __SCHEMA___news_end_date ON __SCHEMA__.news(end_date);

-- set correct update time
CREATE TRIGGER __SCHEMA___news_update BEFORE INSERT OR UPDATE ON __SCHEMA__.news FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_update();
--CREATE TRIGGER __SCHEMA___news_insert_thread BEFORE INSERT ON __SCHEMA__.news FOR EACH ROW
--               EXECUTE PROCEDURE public.insert_news_forum_thread('__SCHEMA__');
--CREATE TRIGGER magdeburg_news_update_thread BEFORE UPDATE ON __SCHEMA__.news FOR EACH ROW
--			   EXECUTE PROCEDURE update_news_forum_thread('__SCHEMA__');
--CREATE TRIGGER magdeburg_news_delete_thread AFTER DELETE ON __SCHEMA__.news FOR EACH ROW
--			   EXECUTE PROCEDURE delete_news_forum_thread('__SCHEMA__');
-- ###########################################################################
-- contains the attachments of news entries
CREATE TABLE __SCHEMA__.news_attachments (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.news(id)
                                                   ON DELETE CASCADE,
  attachment_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.attachments(id)
                                                   ON DELETE CASCADE
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.news_attachments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.news_attachments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.news_attachments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.news_attachments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___news_attachments_entry_id ON __SCHEMA__.news_attachments(entry_id);
CREATE INDEX __SCHEMA___news_attachments_attachment_id ON __SCHEMA__.news_attachments(attachment_id);

-- cascade attachment deletion to attachment table
CREATE TRIGGER __SCHEMA___news_remove_attachment AFTER DELETE ON __SCHEMA__.news_attachments FOR EACH ROW
               EXECUTE PROCEDURE public.remove_attachments('__SCHEMA__');
--CREATE TRIGGER __SCHEMA___delete_news_forum_thread_attachments AFTER DELETE ON __SCHEMA__.news_attachments FOR EACH ROW
--               EXECUTE PROCEDURE public.delete_news_forum_thread_attachments('__SCHEMA__');
--CREATE TRIGGER __SCHEMA___insert_news_forum_thread_attachments BEFORE INSERT ON __SCHEMA__.news_attachments FOR EACH ROW
--               EXECUTE PROCEDURE public.insert_news_forum_thread_attachments('__SCHEMA__');
COMMIT;
