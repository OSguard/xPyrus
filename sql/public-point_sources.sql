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
-- contains the sources for points
--
-- $Id: public-point_sources.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains all point sources
--
CREATE TABLE public.point_sources (
  id                      SMALLINT                 NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(30)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  points_sum_gen          SMALLINT                 NOT NULL
                                                   CHECK(points_sum_gen >= 0),
  points_flow_gen         SMALLINT                 NOT NULL -- points_flow may be negative (buying features)
) WITHOUT OIDS;
COMMENT ON TABLE public.point_sources IS 'contains all sources of unihelp points';
GRANT SELECT ON public.point_sources TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.point_sources TO GROUP __DB_ADMIN_GROUP__;

INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (1, 'GB_ENTRY', 1, 10);
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (2, 'GB_ENTRY_ADMIN', 1, 2);
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (3, 'COURSE_FILE_BOUGHT', 1, 20);
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (4, 'COURSE_FILE_RATED', 1, 10);
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (5, 'FORUM_ANONYMOUS_POSTING', 0, -10);
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (6, 'FORUM_POSTING', 0, 10);
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (7, 'PM_SENT', 0, -10);
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (8, 'PM_SENT_COURSE', 0, -1); 
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (9, 'PM_SENT_GROUP', 0, -50);
INSERT INTO public.point_sources 
        (id, name, points_sum_gen, points_flow_gen) 
    VALUES (10, 'USER_CANVASS', 20, 300);

COMMIT;
