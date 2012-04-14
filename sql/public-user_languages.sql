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
-- contains the languages a user may speak
--
-- $Id: public-user_languages.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains all user spoken languages
CREATE TABLE public.user_languages (
  id                      INT                      NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name_en                 VARCHAR(150)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name_en) > 0)
) WITHOUT OIDS;
COMMENT ON TABLE public.user_languages IS 'contains the languages known to Unihelp and available for an user';
COMMENT ON COLUMN public.user_languages.name_en IS 'the english language name';
GRANT SELECT ON public.user_languages TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.user_languages TO GROUP __DB_ADMIN_GROUP__;


-- Languages
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'German');
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'English');
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'French');
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'Spanish');
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'Portuguese');
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'Russian');
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'Polish');
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'Chinese');
INSERT INTO public.user_languages (id, name_en)
                VALUES ((SELECT public.max_id('public', 'user_languages') + 1),
                        'Esperanto');




COMMIT;
