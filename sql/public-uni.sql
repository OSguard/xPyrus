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
-- contains the unis
--
-- $Id: public-uni.sql 5858 2008-05-03 08:53:09Z trehn $
--


BEGIN;


-- ###########################################################################
-- contains all unis
CREATE TABLE public.uni (
  id                      INT                      NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(150)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  name_short              VARCHAR(30)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name_short) > 0),
  city                    INT                      NOT NULL
                                                   REFERENCES public.cities(id)
) WITHOUT OIDS;
COMMENT ON TABLE public.uni IS 'contains all known universities';
COMMENT ON COLUMN public.uni.name IS 'the name of the university';
COMMENT ON COLUMN public.uni.city IS 'reference to the city the university is in';
GRANT SELECT ON public.uni TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.uni TO GROUP __DB_ADMIN_GROUP__;


INSERT INTO public.uni (id, name, name_short, city)
                VALUES (1,
                        'University of Springfield',
                        'USF',
                        (SELECT id FROM cities WHERE name='Springfield'));

COMMIT;
