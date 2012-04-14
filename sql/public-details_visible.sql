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
-- contains the values for schema.users.details_visible_for
--
-- $Id: public-details_visible.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains all unis
CREATE TABLE public.details_visible (
  id                      INT                      NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(150)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  is_normal               BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  access_type             VARCHAR(7)              CHECK (access_type IN ('general', 'user', 'group')) DEFAULT 'general'
) WITHOUT OIDS;
COMMENT ON TABLE public.details_visible IS 'contains all known groups which can see user details';
COMMENT ON COLUMN public.details_visible.name IS 'the name of the group';
GRANT SELECT ON public.details_visible TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.details_visible TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_details_visible_name ON public.details_visible(name);

-- visibility levels
INSERT INTO public.details_visible (id, name)
                VALUES (1, 'all');
INSERT INTO public.details_visible (id, name)
                VALUES (2, 'logged in');
INSERT INTO public.details_visible (id, name, access_type)
                VALUES (3, 'on friendlist', 'user');
INSERT INTO public.details_visible (id, name, access_type)
                VALUES (10, 'no one', 'user');
INSERT INTO public.details_visible (id, name, is_normal)
                VALUES (42, 'adminmode', false);
INSERT INTO public.details_visible (id, name, access_type)
                VALUES (4, 'group', 'group');



COMMIT;
