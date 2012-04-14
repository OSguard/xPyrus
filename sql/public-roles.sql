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
-- contains the global user roles + functions
--
-- $Id: public-roles.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table stays in the public schema, the functions should
-- be applied to any 'per-schema' groups table

BEGIN;


-- ###########################################################################
-- contains all global user roles
CREATE TABLE public.user_roles (
  id                      SERIAL                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(200)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  description             VARCHAR(250)             NOT NULL
                                                   CHECK (LENGTH(description) > 10)
) WITHOUT OIDS;
GRANT SELECT ON public.user_roles TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT ON public.user_roles_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.user_roles TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON public.user_roles_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_user_roles_name ON public.user_roles(name);


-- trigger
--   the trigger is applied in each schema (city), once per schema

-- roles
INSERT INTO public.user_roles
            (name,
             description)
      VALUES ('internal_users',
              'all logged-in internal users');

INSERT INTO public.user_roles
            (name,
             description)
      VALUES ('external_users',
              'all logged-in external users');

INSERT INTO public.user_roles
            (name,
             description)
      VALUES ('features',
              'role that has been granted all feature rights');

INSERT INTO public.user_roles
            (name,
             description)
      VALUES ('card_yellow',
              'role that applies to all users that have been shown a yellow card');
INSERT INTO public.user_roles
            (name,
             description)
      VALUES ('card_yellow_red',
              'role that applies to all users that have been shown a yellow-red card');
INSERT INTO public.user_roles
            (name,
             description)
      VALUES ('card_red',
              'role that applies to all users that have been shown a red card');

INSERT INTO public.user_roles
            (name,
             description)
      VALUES ('blog_owners',
              'all users who have a blog on unihelp');

INSERT INTO public.user_roles (name, description)
    VALUES ('guests', 'user with a guest account and passive rights only');

COMMIT;
