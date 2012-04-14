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
-- contains the guestbook entries
--
-- $Id: schema-guestbook.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- one problem with guestbook entries is, that they could
-- refer to an other unihelp server
-- so we will use the nickname instead the userid, but we will
-- also store the userid (in case, we need it)

-- this has another benefit: we could update all entries of a
-- user, if the username changes, we also could set the user id to NULL,
-- if the user gets dropped ... the message will remain

BEGIN;


-- ###########################################################################
-- contains the guestbook entries
CREATE TABLE __SCHEMA__.guestbook (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id_for             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  author_int              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL,
  author_ext              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE SET NULL,
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  entry_raw               TEXT                     NOT NULL
                                                   DEFAULT '',
  entry_parsed            TEXT                     NOT NULL
                                                   DEFAULT '',
  comment                 TEXT                     NOT NULL
                                                   DEFAULT '',
  comment_time            TIMESTAMPTZ              NULL,
  weighting               INT                      NOT NULL
                                                   DEFAULT 0,
  is_unread               BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7),
  enable_formatcode       BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  enable_html             BOOLEAN                  NOT NULL
                                                   DEFAULT false,
  enable_smileys          BOOLEAN                  NOT NULL
                                                   DEFAULT true
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.guestbook TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.guestbook_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.guestbook TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.guestbook_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___guestbook_user_id_for ON __SCHEMA__.guestbook(user_id_for);
-- for fast where user_id_for = $X order by entry_time desc limit $y
CREATE INDEX __SCHEMA___guestbook_user_id_entry_time ON __SCHEMA__.guestbook(user_id_for, entry_time);
CREATE INDEX __SCHEMA___guestbook_author_int ON __SCHEMA__.guestbook(author_int);
CREATE INDEX __SCHEMA___guestbook_author_ext ON __SCHEMA__.guestbook(author_ext);


-- set correct update time
CREATE TRIGGER __SCHEMA___guestbook_ins_or_update BEFORE INSERT OR UPDATE ON __SCHEMA__.guestbook FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_update();

-- update unread entries counter if neccessary
CREATE TRIGGER __SCHEMA___guestbook_update_counter_gb_entry_unread AFTER UPDATE ON __SCHEMA__.guestbook FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_gb_entry_unread('__SCHEMA__');

-- adjust some counters
CREATE TRIGGER __SCHEMA___update_counter_gb_entry AFTER INSERT OR DELETE ON __SCHEMA__.guestbook FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_gb_entry('__SCHEMA__');


-- ###########################################################################
-- contains the attachments of guestbook entries
CREATE TABLE __SCHEMA__.guestbook_attachments (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.guestbook(id)
                                                   ON DELETE CASCADE,
  attachment_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.attachments(id)
                                                   ON DELETE CASCADE,
  UNIQUE(entry_id, attachment_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.guestbook_attachments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.guestbook_attachments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.guestbook_attachments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.guestbook_attachments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___guestbook_attachments_entry_id ON __SCHEMA__.guestbook_attachments(entry_id);
CREATE INDEX __SCHEMA___guestbook_attachments_attachment_id ON __SCHEMA__.guestbook_attachments(attachment_id);

-- cascade attachment deletion to attachment table
CREATE TRIGGER __SCHEMA___guestbook_remove_attachment AFTER DELETE ON __SCHEMA__.guestbook_attachments FOR EACH ROW
               EXECUTE PROCEDURE public.remove_attachments('__SCHEMA__');

COMMIT;
