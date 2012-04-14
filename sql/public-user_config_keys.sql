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
-- contains the keys for schema.user_config
--
-- $Id: public-user_config_keys.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table stays in the public schema, the keys apply
-- to schema.user_config

BEGIN;


-- ###########################################################################
-- contains all global keys for schema.user_config
CREATE TABLE public.user_config_keys (
  id                      INTEGER                  PRIMARY KEY
                                                   CHECK (id > 0),
  data_name             VARCHAR(50)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(data_name) > 0)
) WITHOUT OIDS;
GRANT SELECT ON public.user_config_keys TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.user_config_keys TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_user_config_keys_name ON public.user_config_keys(data_name);

-- insert config keys
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (1, 'blog_entries_per_page');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (2, 'gb_entries_per_page');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (3, 'boxes_left');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (4, 'boxes_right');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (5, 'boxes_minimized');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (6, 'feature_next_point_limit');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (7, 'feature_free_update_slots');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (8, 'feature_total_update_slots');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (9, 'gb_public');
--INSERT INTO public.user_config_keys (id, data_name)
--     VALUES (10, 'has_advanced_blog');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (11, 'no_basic_studies');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (12, 'guestbook_filter_show');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (13, 'blog_filter_show');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (14, 'diary_public');
INSERT INTO public.user_config_keys (id, data_name)
     VALUES (15, 'friendlist_public');

COMMIT;
