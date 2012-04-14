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
-- contains the banner rotating entries
--
-- $Id: schema-banner.sql 5743 2008-03-25 19:48:14Z ads $
--

-- the banner which can be shown by our banner rotating system

BEGIN;


-- ###########################################################################
-- contains the banner rotating entries
CREATE TABLE __SCHEMA__.banner (
  id                      BIGSERIAL                PRIMARY KEY,
  name                    VARCHAR(100)             NOT NULL
                                                   CHECK(LENGTH(name) >= 5),
  author_int              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL,
  author_ext              BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE SET NULL,
  attachment_id           BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.attachments(id)
                                                   ON DELETE CASCADE,
  --target link for a extern banner
  banner_url              text                     NULL,
  --target link for the advertisment                                                   
  dest_url                TEXT                     NOT NULL
                                                   CHECK(LENGTH(dest_url) >= 10),
  --how height is our minimal size?
  height                  INT4                     NOT NULL
                                                   CHECK(height >= 20),  
  --how width is our minimal size?
  width                   INT4                     NOT NULL
                                                   CHECK(width >= 20),  
  is_visible              BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  entry_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  last_update_time        TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  start_date              DATE                     NOT NULL,
  end_date                DATE                     NOT NULL,
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7),
  random_rate             INT2                     NOT NULL
                                                   CHECK(random_rate >= 0 AND random_rate <= 100),
  banner_views            INTEGER                  NOT NULL
                                                   DEFAULT 0,
  banner_clicks           INTEGER                  NOT NULL
                                                   DEFAULT 0
  -- CHECK(author_int IS NOT NULL OR author_ext IS NOT NULL)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.banner TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.banner_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.banner TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.banner_id_seq TO GROUP __DB_ADMIN_GROUP__;

--CREATE INDEX __SCHEMA___banner_user_id ON __SCHEMA__.banner(user_id);
-- set correct update time
CREATE TRIGGER __SCHEMA___banner_update BEFORE UPDATE ON __SCHEMA__.banner FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_update();

-- cascade attachment deletion to attachment table
CREATE TRIGGER __SCHEMA___banner_remove_attachment AFTER DELETE ON __SCHEMA__.banner FOR EACH ROW
               EXECUTE PROCEDURE public.remove_attachments('__SCHEMA__');


-- ###########################################################################
-- register clicks on banners
CREATE TABLE __SCHEMA__.banner_clicks (
  id                      BIGSERIAL                PRIMARY KEY,
  banner_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.banner(id)
                                                   ON DELETE CASCADE,
  user_int                BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE SET NULL,
  click_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  user_ext                BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE SET NULL,
  ip                      VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(ip) >= 7)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.banner_clicks TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.banner_clicks_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.banner_clicks TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.banner_clicks_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE TABLE __SCHEMA__.banner_select(
  id                      BIGSERIAL                PRIMARY KEY,
  banner_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.banner(id)
                                                   ON DELETE CASCADE,
  /*banner_views            INTEGER                  NOT NULL
                                                   DEFAULT 0,*/
  rand                    DOUBLE PRECISION         NOT NULL
                                                   DEFAULT RANDOM()
  /*banner_url              text                     NULL,
  dest_url                TEXT                     NOT NULL*/
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.banner_select TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.banner_select_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.banner_select TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.banner_select_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___banner_rand ON __SCHEMA__.banner_select(rand);

-- ###########################################################################
-- register clicks on banners
CREATE TABLE __SCHEMA__.banner_show (
  banner_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.banner(id)
                                                   ON DELETE CASCADE,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW()
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.banner_show TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.banner_show TO GROUP __DB_ADMIN_GROUP__;

COMMIT;
