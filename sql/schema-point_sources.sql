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
-- $Id: schema-point_sources.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains all point sources
--
CREATE TABLE __SCHEMA__.point_sources (
  id                      SERIAL                   NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY,
  name                    VARCHAR(30)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  points_sum_gen          SMALLINT                 NOT NULL
                                                   CHECK(points_sum_gen >= 0),
  points_flow_gen         SMALLINT                 NOT NULL -- points_flow may be negative (buying features)
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.point_sources IS 'contains all sources of unihelp points';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.point_sources TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.point_sources_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.point_sources TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.point_sources_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- import point sources data from public.point_sources
CREATE TRIGGER __SCHEMA___copy_point_sources AFTER INSERT OR UPDATE OR DELETE ON public.point_sources FOR EACH ROW
               EXECUTE PROCEDURE public.copy_point_sources('__SCHEMA__');

-- rights

-- copy default point sources from public table
INSERT INTO __SCHEMA__.point_sources (name, points_sum_gen, points_flow_gen)
            SELECT name, points_sum_gen, points_flow_gen
              FROM public.point_sources;

COMMIT;
