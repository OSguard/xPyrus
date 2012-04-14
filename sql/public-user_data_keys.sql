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
-- contains the keys for schema.user_data
--
-- $Id: public-user_data_keys.sql 6210 2008-07-25 17:29:44Z trehn $
--

-- note:
-- this table stays in the public schema, the keys apply
-- to schema.user_data

BEGIN;


-- ###########################################################################
-- contains all global keys for schema.user_data
CREATE TABLE public.user_data_keys (
  id                      INTEGER                  PRIMARY KEY
                                                   CHECK (id > 0),
  data_name               VARCHAR(50)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(data_name) > 0)
) WITHOUT OIDS;
GRANT SELECT ON public.user_data_keys TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.user_data_keys TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_user_data_keys_name ON public.user_data_keys(data_name);

-- insert data keys
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (1, 'blog_entries');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (2, 'forum_entries');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (3, 'gb_entries');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (4, 'gb_entries_unread');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (5, 'pms');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (20, 'pms_sent');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (6, 'pms_unread');

INSERT INTO public.user_data_keys (id, data_name)
     VALUES (7, 'profile_views');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (8, 'unihelp_stress');

INSERT INTO public.user_data_keys (id, data_name)
     VALUES (9, 'public_pgp_key');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (10, 'description_parsed');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (11, 'description_raw');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (12, 'course_file_uploads');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (13, 'course_file_downloads');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (14, 'course_file_downloads_other');
   
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (16, 'attachment_count');

INSERT INTO public.user_data_keys (id, data_name)
     VALUES (18, 'forum_rating');
INSERT INTO public.user_data_keys (id, data_name)
     VALUES (19, 'forum_rating_count');

COMMIT;
