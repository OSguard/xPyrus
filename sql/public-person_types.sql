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
-- contains the person types
--
-- $Id: public-person_types.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains the person types
CREATE TABLE public.person_types (
  id                      INT                      NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(150)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0)
) WITHOUT OIDS;
COMMENT ON TABLE public.person_types IS 'contains the person types (roles), every user is in one of the groups';
COMMENT ON COLUMN public.person_types.name IS 'the role name';
GRANT SELECT ON public.person_types TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.person_types TO GROUP __DB_ADMIN_GROUP__;


-- person types
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Unihelp Mitarbeiter');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Student');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Dozent');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Professor');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Alumni');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Gruppe');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'unbekannt');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'gelöscht');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Wissenschaftlicher Mitarbeiter Uni/FH');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Technische Mitarbeiter Uni/FH');
INSERT INTO public.person_types (id, name)
                         VALUES ((SELECT public.max_id('public', 'person_types') + 1),
                                 'Gast-Zugang');



COMMIT;
