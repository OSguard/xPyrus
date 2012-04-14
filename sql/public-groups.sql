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
-- contains the global user groups + functions
--
-- $Id: public-groups.sql 5883 2008-05-03 13:34:34Z schnueptus $
--

-- note:
-- this table stays in the public schema, the functions should
-- be applied to any 'per-schema' groups table

BEGIN;


-- ###########################################################################
-- contains all global user groups
CREATE TABLE public.groups (
  id                      SERIAL                   PRIMARY KEY
                                                   CHECK (id > 0),
  title                   VARCHAR(250)             NOT NULL
                                                   DEFAULT 'Group',
  name                    VARCHAR(200)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  description             VARCHAR(250)             NOT NULL
                                                   CHECK (LENGTH(description) > 10),
  infopage_raw            TEXT                     NOT NULL
                                                   DEFAULT '',
  infopage_parsed         TEXT                     NOT NULL
                                                   DEFAULT '',
  is_visible              BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE
) WITHOUT OIDS;
COMMENT ON TABLE public.groups IS 'contains unihelp groups who should be available in every city';
COMMENT ON COLUMN public.groups.name IS 'the group name';
COMMENT ON COLUMN public.groups.description IS 'the group description';
COMMENT ON COLUMN public.groups.is_visible IS 'defines if the group should be visible or for internal use';
GRANT SELECT ON public.groups TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT ON public.groups_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.groups TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON public.groups_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_groups_name ON public.groups(name);


-- trigger
--   the trigger is applied in each schema (city), once per schema

-- groups



COMMIT;
