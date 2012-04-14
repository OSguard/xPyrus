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
-- contains global config settings
--
-- $Id: public-global_config.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table stays in the public schema, the functions should
-- be applied to any 'per-schema' groups table

BEGIN;


-- ###########################################################################
-- contains global config settings
CREATE TABLE public.global_config (
  id                      BIGSERIAL                PRIMARY KEY
                                                   CHECK (id > 0),
  config_name             VARCHAR(50)             NOT NULL,
  config_value            TEXT                     NOT NULL
                                                   DEFAULT '',  
  description             VARCHAR(250)            NOT NULL
                                                   CHECK (LENGTH(description) > 10),
  UNIQUE (config_name)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON public.global_config TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON public.global_config_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.global_config TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON public.global_config_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_global_config_config_name ON public.global_config(config_name);

INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_MAX_LENGTH_RAW', '20000', 'the maximal number of characters the raw entry may consist of');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_MAX_SMILEYS', '20', 'the maximal number of smileys that are parsed in an arbitrary entry');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_MAX_INLINE_IMAGES', '20', 'the maximal number of image tags ([img][/img]) that are parsed in an arbitrary entry');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_MAX_ATTACHMENT_SIZE_CUMMULATED', '200', 'the cumulated maximum size in KB that attachments of entries may have');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_MAX_ATTACHMENT_SIZE', '100', 'the maximum size in KB that _one_ attachment in an entry may have');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_BLOG_MAX_ATTACHMENT_SIZE_CUMMULATED', '500', 'the cumulated maximum size in KB that attachments of blog entries may have');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_BLOG_MAX_ATTACHMENT_SIZE', '200', 'the maximum size in KB that _one_ attachment in a blog entry may have');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_NEWS_MAX_ATTACHMENT_SIZE', '250', 'the maximum size in KB that _one_ attachment in of a news entry may have');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('ENTRY_NEWS_MAX_ATTACHMENT_SIZE_CUMMULATED', '500', 'the cumulated maximum size in KB that attachments of news entries may have');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('COURSE_MAX_FILE_SIZE', '10000', 'the maximum size in KB that _one_ course file upload may have');

INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('COURSE_SUBSIDIES_MAX_FILE', '100', 'the number of files up to which file download is subsidised');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('COURSE_SUBSIDIES_MAX_DOWNLOAD', '100', 'the number of downloads up to which file download is subsidised');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('COURSE_SUBSIDIES_MAX_USER', '100', 'the number of users up to which file download is subsidised');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('COURSE_SUBSIDIES_SUBVENTION', '2', 'number of points by which file download is subsidised');
INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('COURSE_SUBSIDIES_ENABLED', 'f', 'whether file download is subsidised');

INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('MAX_LOGIN_ERRORS', '10', 'number of failing logins that are allowed before IP blacklist mode is activated');

INSERT INTO public.global_config (config_name, config_value, description)
    VALUES ('POINT_SOURCES_FLOW_MULTIPLICATOR', '10', 'multiplicator of economic points (x points in DB correspond to 1 point for use)');

COMMIT;
