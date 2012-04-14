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
-- contains the rights
--
-- $Id: public-rights.sql 5743 2008-03-25 19:48:14Z ads $
--

-- note:
-- this table stays in the public schema, the functions should
-- be applied to any 'per-schema' groups table

BEGIN;


-- ###########################################################################
-- contains all rights
CREATE TABLE public.rights (
  id                      SERIAL                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(200)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  description             VARCHAR(250)             NOT NULL
                                                   CHECK (LENGTH(description) > 10),
  default_allowed         BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  is_group_specific       BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON public.rights TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON public.rights_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.rights TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON public.rights_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX public_rights_name ON public.rights(name);


-- trigger
--   the trigger is applied in each schema (city), once per schema

-- rights


COMMIT;
