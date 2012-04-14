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
-- Comments in PostgreSQL start with --
-- or C-Style with /* */
--
-- $Id: schema-user_login_errors.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains login errors of users

CREATE TABLE __SCHEMA__.user_login_errors (
  id                      BIGSERIAL                PRIMARY KEY,
  username                VARCHAR(50)              NOT NULL,
  insert_at               TIMESTAMPTZ              NOT NULL,
  post_ip                 VARCHAR(64)              NOT NULL
                                                   CHECK(LENGTH(post_ip) >= 7),
  password                VARCHAR(40)              NOT NULL
) WITHOUT OIDS;
GRANT ALL ON __SCHEMA__.user_login_errors TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_login_errors_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_login_errors TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_login_errors_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___user_login_errors_username ON __SCHEMA__.user_login_errors(username);

-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___user_login_errors_insert BEFORE INSERT ON __SCHEMA__.user_login_errors FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();


-- remove john doe user
-- better set user-FK to null instead of using real dummy user
--                    (linap)
--
/*
INSERT INTO __SCHEMA__.users
	(username, flag_activated, flag_active, flag_invisible, uni_id, person_type,
     nationality_id, gender, birthdate, password, points_sum, points_flow, first_login,
	 last_login)
VALUES 
	('JohnDoe', true, true, false,
             (SELECT id
                FROM public.uni
               WHERE name='Otto-von-Guericke-Universität Magdeburg'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Magdeburg')),
             (SELECT id
                FROM public.person_types
               WHERE name='Unihelp Mitarbeiter'),
             (SELECT id
                FROM public.countries
               WHERE nationality='deutsch'),
             'm', '1958-11-18', '3c25b01657254677d3e1a8fd1f0742c5d489bd39', '1', '1', NOW(), NOW());*/

COMMIT;
