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
-- contains the keys for schema.user_contact_data
--
-- $Id: public-user_contact_data_keys.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table stays in the public schema, the keys apply
-- to schema.user_contact_data

BEGIN;


-- ###########################################################################
-- contains all global keys for schema.user_contact_data
CREATE TABLE public.user_contact_data_keys (
  id                      INTEGER                  PRIMARY KEY
                                                   CHECK (id > 0),
  data_name               VARCHAR(50)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(data_name) > 0)
) WITHOUT OIDS;
GRANT SELECT ON public.user_contact_data_keys TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.user_contact_data_keys TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_user_contact_data_keys_name ON public.user_contact_data_keys(data_name);

-- insert data keys
INSERT INTO public.user_contact_data_keys (id, data_name)
     VALUES (1, 'homepage');
INSERT INTO public.user_contact_data_keys (id, data_name)
     VALUES (2, 'im_jabber');
INSERT INTO public.user_contact_data_keys (id, data_name)
     VALUES (3, 'im_icq');
INSERT INTO public.user_contact_data_keys (id, data_name)
     VALUES (4, 'im_msn');
INSERT INTO public.user_contact_data_keys (id, data_name)
     VALUES (5, 'im_aim');
INSERT INTO public.user_contact_data_keys (id, data_name)
     VALUES (6, 'skype');
INSERT INTO public.user_contact_data_keys (id, data_name)
     VALUES (7, 'im_yahoo');     

COMMIT;
