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
-- contains the private messages 
--
-- $Id: schema-pm.sql 5743 2008-03-25 19:48:14Z ads $
--

-- private messages for UniHelp

BEGIN;


-- ###########################################################################
-- contains the private messages
--
-- receiving user is stored in a separate table to allow pms with multiple
-- destination users
CREATE TABLE __SCHEMA__.pm (
  id                      BIGSERIAL                PRIMARY KEY,
  author_int              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL,
  author_ext              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE SET NULL,
  previous_pm             BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.pm(id)
                                                   ON DELETE SET NULL,
  caption                 VARCHAR(200)             NOT NULL
                                                   DEFAULT '',
  recipient_string        VARCHAR(200)             NOT NULL,    -- store recipient string, because usernames are non descriptive for e.g. group messages
  entry_time              TIMESTAMPTZ              NOT NULL
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
                                                   DEFAULT true,
  author_has_deleted      BOOLEAN                  NOT NULL
                                                   DEFAULT false 
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.pm TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.pm_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.pm TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.pm_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___pm_author_int ON __SCHEMA__.pm(author_int);
CREATE INDEX __SCHEMA___pm_author_ext ON __SCHEMA__.pm(author_ext);

-- adjust some counters
CREATE TRIGGER __SCHEMA___update_counter_pm_sent AFTER INSERT OR UPDATE OR DELETE ON __SCHEMA__.pm FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_pm_sent('__SCHEMA__');

-- ###########################################################################
-- contains the relationship between private messages and the
-- receiving user
CREATE TABLE __SCHEMA__.pm_for_users (
  id                      BIGSERIAL                PRIMARY KEY,
  pm_id                   BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.pm(id)
                                                   ON DELETE CASCADE,
  user_id_for             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  is_unread               BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  UNIQUE(pm_id,user_id_for)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.pm_for_users TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.pm_for_users_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.pm_for_users TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.pm_for_users_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___pm_for_users_user_id_for ON __SCHEMA__.pm_for_users(user_id_for);
CREATE INDEX __SCHEMA___pm_for_users_pm_id ON __SCHEMA__.pm_for_users(pm_id);

-- adjust some counters
CREATE TRIGGER __SCHEMA___update_counter_pm AFTER INSERT OR UPDATE OR DELETE ON __SCHEMA__.pm_for_users FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_pm('__SCHEMA__');


-- ###########################################################################
-- contains the attachments of pm entries
CREATE TABLE __SCHEMA__.pm_attachments (
  id                      BIGSERIAL                PRIMARY KEY,
  entry_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.pm(id)
                                                   ON DELETE CASCADE,
  attachment_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.attachments(id)
                                                   ON DELETE CASCADE,
  UNIQUE(entry_id, attachment_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.pm_attachments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.pm_attachments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.pm_attachments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.pm_attachments_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___pm_attachments_entry_id ON __SCHEMA__.pm_attachments(entry_id);
CREATE INDEX __SCHEMA___pm_attachments_attachment_id ON __SCHEMA__.pm_attachments(attachment_id);

-- cascade attachment deletion to attachment table
--CREATE TRIGGER __SCHEMA___pm_remove_attachment AFTER DELETE ON __SCHEMA__.pm_attachments FOR EACH ROW
--               EXECUTE PROCEDURE public.remove_attachments('__SCHEMA__');

COMMIT;
