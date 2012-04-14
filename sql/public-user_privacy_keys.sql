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
-- contains the keys for schema.user_privacy
--
-- $Id: public-user_privacy_keys.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table stays in the public schema, the keys apply
-- to schema.user_privacy

BEGIN;


-- ###########################################################################
-- contains all global keys for schema.user_privacy
CREATE TABLE public.user_privacy_keys (
  id                      INTEGER                  PRIMARY KEY
                                                   CHECK (id > 0),
  data_name               VARCHAR(50)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(data_name) > 0)
) WITHOUT OIDS;
GRANT SELECT ON public.user_privacy_keys TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.user_privacy_keys TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_user_privacy_keys_name ON public.user_privacy_keys(data_name);

-- insert privacy keys
INSERT INTO public.user_privacy_keys (id, data_name)
     VALUES (1, 'birthdate');
INSERT INTO public.user_privacy_keys (id, data_name)
     VALUES (2, 'real_name');
INSERT INTO public.user_privacy_keys (id, data_name)
     VALUES (3, 'address');
INSERT INTO public.user_privacy_keys (id, data_name)
     VALUES (4, 'mail_address');
INSERT INTO public.user_privacy_keys (id, data_name)
     VALUES (5, 'instant_messanger');
INSERT INTO public.user_privacy_keys (id, data_name)
     VALUES (6, 'telephone');


COMMIT;
