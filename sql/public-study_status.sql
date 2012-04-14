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
-- contains the status for every study path
--
-- $Id: public-study_status.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains all study stati
CREATE TABLE public.study_status (
  id                      INT                      NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(150)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0)
) WITHOUT OIDS;
COMMENT ON TABLE public.study_status IS 'contains all known study path stati';
COMMENT ON COLUMN public.study_status.name IS 'the name of the study path status';
GRANT SELECT ON public.study_status TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.study_status TO GROUP __DB_ADMIN_GROUP__;


-- study path
-- id 1 must be 'unknown'
INSERT INTO public.study_status (id, name)
                                 VALUES (1, 'unknown');
INSERT INTO public.study_status (id, name)
                                 VALUES (2, 'aborted');
INSERT INTO public.study_status (id, name)
                                 VALUES (3, 'basic study');
INSERT INTO public.study_status (id, name)
                                 VALUES (4, 'main course');
INSERT INTO public.study_status (id, name)
                                 VALUES (5, 'Bachelor');
INSERT INTO public.study_status (id, name)
                                 VALUES (6, 'Master');
INSERT INTO public.study_status (id, name)
                                 VALUES (7, 'Diplom');
INSERT INTO public.study_status (id, name)
                                 VALUES (8, 'Diplom (FH)');
INSERT INTO public.study_status (id, name)
                                 VALUES (9, 'Magister');
INSERT INTO public.study_status (id, name)
                                 VALUES (10, 'Doktor');
INSERT INTO public.study_status (id, name)
                                 VALUES (11, 'Dipl-Ing.');
INSERT INTO public.study_status (id, name)
                                 VALUES (12, 'Dipl-Ing. (FH)');




COMMIT;
