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
-- $Id: schema-blog_advanced.sql 5743 2008-03-25 19:48:14Z ads $
--

-- the blog entries structure is a subset of the guestbook entries structure

BEGIN;


-- ###########################################################################
-- contains the blog entries
CREATE TABLE __SCHEMA__.blog_advanced (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  group_id                BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.groups(id),
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  title                   VARCHAR(250)             NOT NULL
                                                   CHECK(LENGTH(title) >= 2),
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
                                                   DEFAULT true,
  allow_comments          BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  comment_notification    VARCHAR(5)               NOT NULL
                                                   CHECK(comment_notification IN ('none', 'pm', 'email'))
                                                   DEFAULT 'pm',
  comments                INT                      NOT NULL
                                                   DEFAULT 0,
  trackbacks              INT                      NOT NULL
                                                   DEFAULT 0
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_advanced TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_advanced_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_advanced_user_id ON __SCHEMA__.blog_advanced(user_id);
CREATE INDEX __SCHEMA___blog_advanced_group_id ON __SCHEMA__.blog_advanced(group_id);

-- set correct update time
CREATE TRIGGER __SCHEMA___blog_advanced_update BEFORE UPDATE ON __SCHEMA__.blog_advanced FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_update();
-- adjust some counters
--CREATE TRIGGER __SCHEMA___update_counter_blog_advanced_entry AFTER INSERT OR DELETE ON __SCHEMA__.blog_advanced FOR EACH ROW
--               EXECUTE PROCEDURE public.update_counter_blog_advanced_entry('__SCHEMA__');

-- ###########################################################################
-- contains the blog configuration
CREATE TABLE __SCHEMA__.blog_advanced_config (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NULL
                                                   UNIQUE
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  group_id                BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.groups(id)
                                                   UNIQUE,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  title                   VARCHAR(100)             NOT NULL
                                                   CHECK(LENGTH(title) >= 2),
  subtitle                VARCHAR(150)             NOT NULL,
  flag_invisible          BOOLEAN                  NOT NULL
                                                   DEFAULT (FALSE)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_advanced_config TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_advanced_config_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_config TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_config_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_advanced_config_user_id ON __SCHEMA__.blog_advanced_config(user_id);

-- set correct update time
CREATE TRIGGER __SCHEMA___blog_advanced_config_update BEFORE INSERT ON __SCHEMA__.blog_advanced_config FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();

-- ###########################################################################
-- contains the attachments of blog entries
CREATE TABLE __SCHEMA__.blog_advanced_attachments (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.blog_advanced(id)
                                                   ON DELETE CASCADE,
  attachment_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.attachments(id)
                                                   ON DELETE CASCADE,
  UNIQUE(entry_id, attachment_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_advanced_attachments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_advanced_attachments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_attachments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_attachments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_advanced_attachments_entry_id ON __SCHEMA__.blog_advanced_attachments(entry_id);
CREATE INDEX __SCHEMA___blog_advanced_attachments_attachment_id ON __SCHEMA__.blog_advanced_attachments(attachment_id);

-- cascade attachment deletion to attachment table
CREATE TRIGGER __SCHEMA___blog_advanced_remove_attachment AFTER DELETE ON __SCHEMA__.blog_advanced_attachments FOR EACH ROW
               EXECUTE PROCEDURE public.remove_attachments('__SCHEMA__');


-- ###########################################################################
-- contains the categories of blog entries
CREATE TABLE __SCHEMA__.blog_advanced_categories (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  group_id                BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.groups(id),
  name                    VARCHAR(80)              NOT NULL
                                                   CHECK(LENGTH(name) >= 2),
  parent_id               BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.blog_advanced_categories(id)
                                                   ON DELETE SET NULL,
  UNIQUE(user_id, name)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_advanced_categories TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_advanced_categories_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_categories TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_categories_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_advanced_categories_user_id ON __SCHEMA__.blog_advanced_categories(user_id);

-- ###########################################################################
-- contains the relationship between blog entries and categories
CREATE TABLE __SCHEMA__.blog_advanced_entriescat (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.blog_advanced(id)
                                                   ON DELETE CASCADE,
  category_id             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.blog_advanced_categories(id)
                                                   ON DELETE CASCADE,
  UNIQUE(entry_id, category_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_advanced_entriescat TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_advanced_entriescat_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_entriescat TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_entriescat_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_advanced_entriescat_entry_id ON __SCHEMA__.blog_advanced_entriescat(entry_id);
CREATE INDEX __SCHEMA___blog_advanced_entriescat_category_id ON __SCHEMA__.blog_advanced_entriescat(category_id);

-- ###########################################################################
-- contains comments on blog entries
CREATE TABLE __SCHEMA__.blog_advanced_comments (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.blog_advanced(id)
                                                   ON DELETE CASCADE,
  author_int              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL,
  author_ext              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE SET NULL,
  author_name             VARCHAR(50)              NOT NULL
                                                   CHECK(LENGTH(author_name) > 1),
  comment                 TEXT                     NOT NULL
                                                   CHECK(LENGTH(comment) > 1),
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7),
  email                   VARCHAR(80)              NOT NULL
                                                   DEFAULT ''
                                                   CHECK (CHECK_EMAIL(email))
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_advanced_comments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_advanced_comments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_comments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_comments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_advanced_comments_entry_id ON __SCHEMA__.blog_advanced_comments(entry_id);

CREATE TRIGGER __SCHEMA___blog_advanced_comments_counter AFTER INSERT OR DELETE ON __SCHEMA__.blog_advanced_comments FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_blog_advanced_comment('__SCHEMA__');

-- ###########################################################################
-- contains trackbacks on blog entries
CREATE TABLE __SCHEMA__.blog_advanced_trackbacks (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.blog_advanced(id)
                                                   ON DELETE CASCADE,
  weblog_name             VARCHAR(200)             NOT NULL,
  weblog_url              VARCHAR(200)             NOT NULL,
  title                   TEXT                     NOT NULL,
  body                    TEXT                     NOT NULL,
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_advanced_trackbacks TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_advanced_trackbacks_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_trackbacks TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_trackbacks_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___blog_advanced_trackbacks_entry_id ON __SCHEMA__.blog_advanced_trackbacks(entry_id);

CREATE TRIGGER __SCHEMA___blog_advanced_trackbacks_counter AFTER INSERT OR DELETE ON __SCHEMA__.blog_advanced_trackbacks FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_blog_advanced_trackback('__SCHEMA__');


-- ###########################################################################
-- contains subscriptions for notification on new blog comments
CREATE TABLE __SCHEMA__.blog_advanced_subscription (
    id                      BIGSERIAL                PRIMARY KEY,
    insert_at               TIMESTAMPTZ              NOT NULL
                                                     DEFAULT NOW(),
    user_id                 BIGINT                   NOT NULL
                                                     REFERENCES __SCHEMA__.users(id)
                                                     ON DELETE CASCADE,
    entry_id                BIGINT                   NOT NULL
                                                     REFERENCES __SCHEMA__.blog_advanced(id)
                                                     ON DELETE CASCADE,
    notification_type       VARCHAR(5)               NOT NULL
                                                     CHECK(notification_type IN ('pm', 'email'))
                                                     DEFAULT 'pm',
    UNIQUE(user_id, entry_id)
) WITHOUT OIDS;

GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.blog_advanced_subscription TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.blog_advanced_subscription_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_subscription TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.blog_advanced_subscription_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___blog_advanced_subscription_insert BEFORE INSERT ON __SCHEMA__.blog_advanced_subscription FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();

COMMIT;
