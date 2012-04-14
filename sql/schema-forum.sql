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
-- contains the all database tables neccessary for the forum
--
-- $Id: schema-forum.sql 6210 2008-07-25 17:29:44Z trehn $
--

--
-- TODO: create trigger for DELETE to update counters!!!
-- TODO: default template
-- TODO: list with templates (from filesystem) in admin area for a new forum
--


--  Moderatoren
--  Default Moderatoren

BEGIN;


-- ###########################################################################
-- contains all categories that fora are divided into
--
-- note: description is allowed to used with formatcode/html/smileys
CREATE TABLE __SCHEMA__.forum_categories (
  id                      BIGSERIAL                PRIMARY KEY,
  name                    VARCHAR(150)             NOT NULL
                                                   DEFAULT '',
  description_raw         TEXT                     NOT NULL
                                                   DEFAULT '',
  description_parsed      TEXT                     NOT NULL
                                                   DEFAULT '',
  position                INT                      NOT NULL
                                                   DEFAULT 0,
  number_of_forums        INT                      NOT NULL
                                                   DEFAULT 0,
  number_of_threads       INT                      NOT NULL
                                                   DEFAULT 0,
  default_template        VARCHAR(200)             NOT NULL
                                                   DEFAULT 'default.tpl',
  category_type           VARCHAR(10)              NOT NULL
                                                   DEFAULT 'default'
                                                   CHECK (category_type IN ('default','course','group','course_old','market'))
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.forum_categories IS 'contains all forum categories for this city';
COMMENT ON COLUMN __SCHEMA__.forum_categories.name IS 'the category name';
COMMENT ON COLUMN __SCHEMA__.forum_categories.number_of_forums IS 'number of forums in this category';
COMMENT ON COLUMN __SCHEMA__.forum_categories.number_of_threads IS 'number of threads in this category';
COMMENT ON COLUMN __SCHEMA__.forum_categories.default_template IS 'the default template for a new forum in this category';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_categories TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_categories_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_categories TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_categories_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE TRIGGER __SCHEMA___update_category_position BEFORE INSERT OR DELETE ON __SCHEMA__.forum_categories FOR EACH ROW
               EXECUTE PROCEDURE public.update_category_position('__SCHEMA__');

-- ###########################################################################
-- contains default moderators who will added to a new forum
--
CREATE TABLE __SCHEMA__.forum_default_moderator (
  id                      BIGSERIAL                PRIMARY KEY,
  category_id             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_categories(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  UNIQUE(category_id, user_id)
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.forum_default_moderator IS 'contains default moderators who will added to a new forum';
COMMENT ON COLUMN __SCHEMA__.forum_default_moderator.category_id IS 'category id for which this setting does apply';
COMMENT ON COLUMN __SCHEMA__.forum_default_moderator.user_id IS 'user id of the potential new moderator';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_default_moderator TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_default_moderator_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_default_moderator TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_default_moderator_id_seq TO GROUP __DB_ADMIN_GROUP__;
CREATE INDEX __SCHEMA___forum_default_moderator_category_id ON __SCHEMA__.forum_default_moderator(category_id);


-- ###########################################################################
-- contains category moderators
--
CREATE TABLE __SCHEMA__.forum_category_moderator (
  id                      BIGSERIAL                PRIMARY KEY,
  category_id             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_categories(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  UNIQUE(category_id, user_id)
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.forum_category_moderator IS 'contains category moderators';
COMMENT ON COLUMN __SCHEMA__.forum_category_moderator.category_id IS 'category id';
COMMENT ON COLUMN __SCHEMA__.forum_category_moderator.user_id IS 'user id of the moderator';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_category_moderator TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_category_moderator_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_category_moderator TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_category_moderator_id_seq TO GROUP __DB_ADMIN_GROUP__;
CREATE INDEX __SCHEMA___forum_category_moderator_category_id ON __SCHEMA__.forum_category_moderator(category_id);
CREATE INDEX __SCHEMA___forum_category_moderator_user_id ON __SCHEMA__.forum_category_moderator(user_id);


-- should better use the word 'fora' instead of forums because of my latin heart,
-- but took forums for better readability...

-- ###########################################################################
-- contains the forums/fora in one category
--
-- note: description is allowed to used with formatcode/html/smileys
CREATE TABLE __SCHEMA__.forum_fora (
  id                      BIGSERIAL                PRIMARY KEY,
  category_id             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_categories(id),
  forum_parent_id         BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.forum_fora(id)
                                                   ON DELETE CASCADE,
  name                    VARCHAR(150)             NOT NULL
                                                   DEFAULT '',
  description_raw         TEXT                     NOT NULL
                                                   DEFAULT '',
  description_parsed      TEXT                     NOT NULL
                                                   DEFAULT '',
  position                INT                      NOT NULL
                                                   DEFAULT 0,
--  disable_logoff_visit    BOOLEAN                  NOT NULL
--                                                   DEFAULT FALSE,
  may_contain_news        BOOLEAN                 NOT NULL
                                                    DEFAULT false,
  visible                 INTEGER                  NOT NULL
                                                   DEFAULT 1
                                                   REFERENCES public.details_visible(id),
  important               BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  enable_formatcode       BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  enable_html             BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  enable_smileys          BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  enable_points           BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  enable_postings         BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  number_of_threads       INT                      NOT NULL
                                                   DEFAULT 0,
  number_of_entries       INT                      NOT NULL
                                                   DEFAULT 0,
  forum_template          VARCHAR(200)             NOT NULL
                                                   DEFAULT 'default.tpl',
  last_entry              BIGINT                   DEFAULT NULL
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_fora TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_fora_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_fora TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_fora_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___forum_fora_category_id ON __SCHEMA__.forum_fora(category_id);
CREATE INDEX __SCHEMA___forum_fora_forum_parent_id ON __SCHEMA__.forum_fora(forum_parent_id);
CREATE INDEX __SCHEMA___forum_fora_visible ON __SCHEMA__.forum_fora(visible);

-- update counters of higher level table
CREATE TRIGGER __SCHEMA___update_counter_forum AFTER INSERT OR DELETE ON __SCHEMA__.forum_fora FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_forum('__SCHEMA__');

-- ###########################################################################
-- contains forum moderators
--
CREATE TABLE __SCHEMA__.forum_moderator (
  id                      BIGSERIAL                PRIMARY KEY,
  forum_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_fora(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  UNIQUE(forum_id, user_id)
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.forum_moderator IS 'contains category moderators';
COMMENT ON COLUMN __SCHEMA__.forum_moderator.forum_id IS 'forum id';
COMMENT ON COLUMN __SCHEMA__.forum_moderator.user_id IS 'user id of the moderator';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_moderator TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_moderator_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_moderator TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_moderator_id_seq TO GROUP __DB_ADMIN_GROUP__;
CREATE INDEX __SCHEMA___forum_moderator_forum_id ON __SCHEMA__.forum_moderator(forum_id);
CREATE INDEX __SCHEMA___forum_moderator_user_id ON __SCHEMA__.forum_moderator(user_id);

-- ###########################################################################
-- contains tags to a forum
--
CREATE TABLE __SCHEMA__.forum_tag (
  id                      BIGSERIAL                PRIMARY KEY,
  forum_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_fora(id)
                                                   ON DELETE CASCADE,
  tag_id                 BIGINT                    NOT NULL
                                                   REFERENCES __SCHEMA__.tag(id)
                                                   ON DELETE CASCADE,
  UNIQUE(forum_id, tag_id)
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.forum_tag IS 'contains tags to that a forum belong';
COMMENT ON COLUMN __SCHEMA__.forum_tag.forum_id IS 'forum id';
COMMENT ON COLUMN __SCHEMA__.forum_tag.tag_id IS 'id of the tag';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_tag TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_tag_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_tag TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_tag_id_seq TO GROUP __DB_ADMIN_GROUP__;
CREATE INDEX __SCHEMA___forum_tag_index ON __SCHEMA__.forum_tag(forum_id, tag_id);
CREATE INDEX __SCHEMA___forum_tag_forum_id ON __SCHEMA__.forum_tag(forum_id);
CREATE INDEX __SCHEMA___forum_tag_tag_id ON __SCHEMA__.forum_tag(tag_id);

-- ###########################################################################
-- contains the threads/topics in one forum
--
-- note:  one thread can link to another one by the "link_to_thread" attribute
--        this is especially useful when threads are moved from one forum to another
--        in case the attribute equals 0 it should be read as "does not link to any other thread"
CREATE TABLE __SCHEMA__.forum_threads (
  id                      BIGSERIAL                PRIMARY KEY,
  forum_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_fora(id)
                                                   ON DELETE CASCADE,
  caption                 VARCHAR(200)             NOT NULL
                                                   DEFAULT '',
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  link_to_thread          BIGINT                   NULL,
  is_visible              BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  is_closed               BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  is_sticky               BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  first_entry             BIGINT                   NULL,
  last_entry              BIGINT                   NULL,
  last_entry_time         TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  number_of_entries       INT                      NOT NULL
                                                   DEFAULT 0,
  number_of_views         INT                      NOT NULL
                                                   DEFAULT 0
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_threads TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_threads_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_threads TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_threads_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___forum_threads_forum_id ON __SCHEMA__.forum_threads(forum_id);
CREATE INDEX __SCHEMA___forum_threads_is_sticky ON __SCHEMA__.forum_threads(is_sticky);
CREATE INDEX __SCHEMA___forum_threads_link_to_thread ON __SCHEMA__.forum_threads(link_to_thread);
CREATE INDEX __SCHEMA___forum_threads_visible_thread ON __SCHEMA__.forum_threads(is_visible) WHERE link_to_thread IS NULL;
CREATE INDEX __SCHEMA___forum_threads_last_entry_time ON __SCHEMA__.forum_threads(last_entry_time);

-- set correct insert time
CREATE TRIGGER __SCHEMA___forum_threads_insert BEFORE INSERT ON __SCHEMA__.forum_threads FOR EACH ROW
               EXECUTE PROCEDURE public.set_entry_time();

-- update counters of higher level tables
CREATE TRIGGER __SCHEMA___forum_update_counter_thread_ins AFTER INSERT ON __SCHEMA__.forum_threads FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_thread('__SCHEMA__');
CREATE TRIGGER __SCHEMA___forum_update_counter_thread_del BEFORE DELETE ON __SCHEMA__.forum_threads FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_thread('__SCHEMA__');

-- add missing foreign keys
ALTER TABLE __SCHEMA__.forum_threads ADD FOREIGN KEY (link_to_thread) REFERENCES __SCHEMA__.forum_threads(id) ON DELETE CASCADE;


-- ###########################################################################
-- containts user's forum ratings of other users
CREATE TABLE __SCHEMA__.forum_thread_ratings (
  id                      BIGSERIAL                PRIMARY KEY,
  thread_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_threads(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  rated_user_id           BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  rating                  SMALLINT                NOT NULL,
  insert_at               TIMESTAMPTZ              NOT NULL,
  UNIQUE(thread_id, user_id, rated_user_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_thread_ratings TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_thread_ratings_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_ratings TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_ratings_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___forum_thread_ratings_thread_id ON __SCHEMA__.forum_thread_ratings(thread_id);

-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___forum_thread_ratings_insert BEFORE INSERT ON __SCHEMA__.forum_thread_ratings FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();
CREATE TRIGGER __SCHEMA___forum_thread_ratings_insert_or_delete AFTER INSERT OR DELETE ON __SCHEMA__.forum_thread_ratings FOR EACH ROW
               EXECUTE PROCEDURE public.update_user_forum_rating('__SCHEMA__');
-- ###########################################################################
-- contains the entries in one thread/topic
CREATE TABLE __SCHEMA__.forum_thread_entries (
  id                      BIGSERIAL                PRIMARY KEY,
  thread_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_threads(id)
                                                   ON DELETE CASCADE,
  author_int              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL,
  author_ext              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE SET NULL,
  group_id                BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.groups(id),
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  caption                 VARCHAR(200)             NOT NULL
                                                   DEFAULT '',
  entry_raw               TEXT                     NOT NULL
                                                   DEFAULT '',
  entry_parsed            TEXT                     NOT NULL
                                                   DEFAULT '',
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7),
--nr_in_thread            INTEGER                  NOT NULL
--                                                 DEFAULT 0,
  enable_anonymous        BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  enable_formatcode       BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  enable_html             BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  enable_smileys          BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE
) WITHOUT OIDS;


GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_thread_entries TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_thread_entries_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_entries TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_entries_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___forum_thread_entries_author_int ON __SCHEMA__.forum_thread_entries(author_int);
CREATE INDEX __SCHEMA___forum_thread_entries_author_ext ON __SCHEMA__.forum_thread_entries(author_ext);

CREATE INDEX __SCHEMA___forum_thread_entries_thread_id ON __SCHEMA__.forum_thread_entries(thread_id);
CREATE INDEX __SCHEMA___forum_thread_entries_entry_time ON __SCHEMA__.forum_thread_entries(entry_time);

-- set correct insert and update time
CREATE TRIGGER __SCHEMA___forum_thread_entries_insert BEFORE INSERT ON __SCHEMA__.forum_thread_entries FOR EACH ROW
               EXECUTE PROCEDURE public.set_entry_time();
CREATE TRIGGER __SCHEMA___forum_thread_entries_update BEFORE UPDATE ON __SCHEMA__.forum_thread_entries FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_update();

-- update counters of higher level tables and user_data counter
-- also sets first_/last_entry for related thread
CREATE TRIGGER __SCHEMA___update_counter_thread_entry_ins AFTER INSERT ON __SCHEMA__.forum_thread_entries FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_thread_entry('__SCHEMA__');
CREATE TRIGGER __SCHEMA___update_counter_thread_entry_del BEFORE DELETE ON __SCHEMA__.forum_thread_entries FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_thread_entry('__SCHEMA__');

-- substract a point from user for anonymous posting
--CREATE TRIGGER __SCHEMA___forum_anonymous_posting AFTER INSERT ON __SCHEMA__.forum_thread_entries FOR EACH ROW
--               EXECUTE PROCEDURE public.forum_anonymous_posting('__SCHEMA__');

-- count the entries no
CREATE TRIGGER __SCHEMA___update_user_forum_entries AFTER INSERT OR UPDATE OR DELETE ON __SCHEMA__.forum_thread_entries FOR EACH ROW
               EXECUTE PROCEDURE public.update_user_forum_entries('__SCHEMA__');

-- resort the entries for a thread
-- never fire this trigger for an update!
-- we no longer have a 'nr_in_thread' column
-- CREATE TRIGGER __SCHEMA___update_user_forum_pos_number AFTER INSERT OR DELETE ON __SCHEMA__.forum_thread_entries FOR EACH ROW
--                EXECUTE PROCEDURE public.update_user_forum_pos_number('__SCHEMA__');
-- DROP TRIGGER __SCHEMA___update_user_forum_pos_number ON __SCHEMA__.forum_thread_entries;
-- ALTER TABLE __SCHEMA__.forum_thread_entries DROP COLUMN nr_in_thread;
-- ALTER TABLE __SCHEMA__.forum_thread_entries_log DROP COLUMN nr_in_thread;

-- add missing foreign keys
ALTER TABLE __SCHEMA__.forum_threads ADD FOREIGN KEY (first_entry) REFERENCES __SCHEMA__.forum_thread_entries(id) ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE __SCHEMA__.forum_threads ADD FOREIGN KEY (last_entry) REFERENCES __SCHEMA__.forum_thread_entries(id) ON DELETE SET NULL DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE __SCHEMA__.forum_fora ADD FOREIGN KEY (last_entry) REFERENCES __SCHEMA__.forum_thread_entries(id) ON DELETE SET NULL DEFERRABLE INITIALLY DEFERRED;

-- ###########################################################################
-- contains the attachments of thread/topic entries
CREATE TABLE __SCHEMA__.forum_thread_entries_attachments (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_thread_entries(id)
                                                   ON DELETE CASCADE,
  attachment_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.attachments(id)
                                                   ON DELETE CASCADE,
  UNIQUE(entry_id, attachment_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_thread_entries_attachments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_thread_entries_attachments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_entries_attachments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_entries_attachments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___forum_thread_entries_attachments_entry_id ON __SCHEMA__.forum_thread_entries_attachments(entry_id);
CREATE INDEX __SCHEMA___forum_thread_entries_attachments_attachment_id ON __SCHEMA__.forum_thread_entries_attachments(attachment_id);

-- cascade attachment deletion to attachment table
CREATE TRIGGER __SCHEMA___thread_entries_remove_attachment AFTER DELETE ON __SCHEMA__.forum_thread_entries_attachments FOR EACH ROW
               EXECUTE PROCEDURE public.remove_attachments('__SCHEMA__');


-- ###########################################################################
-- contains the fora of user groups
CREATE TABLE __SCHEMA__.groups_forums (
  id                      SERIAL                   PRIMARY KEY,
  group_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.groups(id)
                                                   ON DELETE CASCADE,
  forum_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_fora(id),
  is_default              BOOLEAN                 NOT NULL
                                                   DEFAULT FALSE
) WITHOUT OIDS;
-- TODO: create trigger that only one forum can be default
-- FIXME: create trigger that default forum can not be intern
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.groups_forums TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.groups_forums_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.groups_forums TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.groups_forums_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___groups_forums_group_id ON __SCHEMA__.groups_forums(group_id);

-- ###########################################################################
-- contains the time when a user has read a thread the last time
CREATE TABLE __SCHEMA__.forum_thread_read (
  id                      BIGSERIAL                PRIMARY KEY,
  thread_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_threads(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  read                    TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  UNIQUE(thread_id, user_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_thread_read TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_thread_read_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_read TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_read_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___forum_thread_read_thread_user ON __SCHEMA__.forum_thread_read(thread_id, user_id);

-- ###########################################################################
-- contains the time when a user has visited a forum the last time
CREATE TABLE __SCHEMA__.forum_forum_read (
  id                      BIGSERIAL                PRIMARY KEY,
  forum_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_fora(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  read                    TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  UNIQUE(forum_id, user_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_forum_read TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_forum_read_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_forum_read TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_forum_read_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___forum_forum_read_forum_user ON __SCHEMA__.forum_forum_read(forum_id, user_id);
CREATE INDEX __SCHEMA___forum_forum_read_user ON __SCHEMA__.forum_forum_read(user_id);

-- ###########################################################################
-- contains forum abo
--
CREATE TABLE __SCHEMA__.forum_abo (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  thread_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_threads(id)
                                                   ON DELETE CASCADE,
  UNIQUE(user_id, thread_id)
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.forum_abo IS 'contains forum abo';
COMMENT ON COLUMN __SCHEMA__.forum_abo.thread_id IS 'thread id';
COMMENT ON COLUMN __SCHEMA__.forum_abo.user_id IS 'user id of the abo';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.forum_abo TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_abo_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_abo TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_abo_id_seq TO GROUP __DB_ADMIN_GROUP__;
CREATE INDEX __SCHEMA___forum_abo_thread_id ON __SCHEMA__.forum_abo(thread_id);
CREATE INDEX __SCHEMA___forum_abo_user_id ON __SCHEMA__.forum_abo(user_id);

COMMIT;
