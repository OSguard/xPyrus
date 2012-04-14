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
-- $Id: schema-news.sql 3857 2007-03-10 21:13:05Z trehn $
--

BEGIN;

-- ###########################################################################
-- contains the attachments of news entries
CREATE TABLE __SCHEMA__.event_category (
  id                      BIGSERIAL                PRIMARY KEY,
  name            TEXT                       NOT NULL
                                                   DEFAULT ''
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.event_category TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.event_category_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.event_category TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.event_category_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- ###########################################################################
-- contains the news entries
CREATE TABLE __SCHEMA__.event (
  id                      BIGSERIAL                PRIMARY KEY,
  author_int              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL,
  group_id                BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.groups(id),
  category_id                BIGINT                  NULL
                                                   REFERENCES __SCHEMA__.event_category(id),
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  start_date              TIMESTAMPTZ                     NOT NULL,
  end_date                TIMESTAMPTZ                     NOT NULL,
  visible                INTEGER                  NOT NULL
                                                   DEFAULT 1
                                                   REFERENCES public.details_visible(id),
  caption                 VARCHAR(200)             NOT NULL
                                                   CHECK(LENGTH(caption) >= 5),
  entry_raw            TEXT                       NOT NULL
                                                   DEFAULT '',
  entry_parsed         TEXT                       NOT NULL
                                                   DEFAULT '',
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.event TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.event_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.event TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.event_id_seq TO GROUP __DB_ADMIN_GROUP__;

--CREATE INDEX __SCHEMA___news_user_id ON __SCHEMA__.news(user_id);
--CREATE INDEX __SCHEMA___news_target_date ON __SCHEMA__.news(target_date);
CREATE INDEX __SCHEMA___event_start_date ON __SCHEMA__.event(start_date);
CREATE INDEX __SCHEMA___event_end_date ON __SCHEMA__.event(end_date);

-- set correct update time
CREATE TRIGGER __SCHEMA___event_update BEFORE UPDATE ON __SCHEMA__.event FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_update();

COMMIT;
