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
-- contains all faculty and study path' of a city(!)
--
-- $Id: schema-study-path.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains all faculty
CREATE TABLE __SCHEMA__.faculty (
  id                      SERIAL                   PRIMARY KEY,
  name                    VARCHAR(200)             NOT NULL
                                                   CHECK (LENGTH(name) > 0),
  name_english            VARCHAR(200)             NOT NULL,
  name_short              VARCHAR(30)              NOT NULL,
  description             VARCHAR(250)             NOT NULL
                                                   DEFAULT '',
  uni_id                  INTEGER                  NOT NULL
                                                   REFERENCES public.uni(id),
  UNIQUE(name, uni_id),
  UNIQUE(name_short, uni_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.faculty TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.faculty_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.faculty TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.faculty_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___faculty_name ON __SCHEMA__.faculty(name);



-- note:
-- this table is 'per-city' in case, 2 or more unis or FHs in a city
-- share a study path

-- ###########################################################################
-- contains all study_path
CREATE TABLE __SCHEMA__.study_path (
  id                      SERIAL                   PRIMARY KEY,
  name                    VARCHAR(200)             NOT NULL
                                                   CHECK (LENGTH(name) > 0),
  uni_id                  INTEGER                  NOT NULL
                                                   REFERENCES public.uni(id)
                                                   ON DELETE RESTRICT,
  name_english            VARCHAR(200)             NOT NULL,
  name_short              VARCHAR(30)              NOT NULL,
  description             VARCHAR(250)             NOT NULL
                                                   DEFAULT '',
  is_available            BOOLEAN                  NOT NULL
                                                   DEFAULT true,
  UNIQUE(name,uni_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.study_path TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.study_path_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.study_path TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___study_path_name ON __SCHEMA__.study_path(name);

-- ###########################################################################
-- contains tags to a study path
--
CREATE TABLE __SCHEMA__.study_path_tag (
  id                      BIGSERIAL                PRIMARY KEY,
  study_path_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.study_path(id)
                                                   ON DELETE CASCADE,
  tag_id                  BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.tag(id)
                                                   ON DELETE CASCADE,
  UNIQUE(study_path_id, tag_id)
) WITHOUT OIDS;
COMMENT ON TABLE __SCHEMA__.study_path_tag IS 'contains tags to that a study path belongs';
COMMENT ON COLUMN __SCHEMA__.study_path_tag.study_path_id IS 'study path id';
COMMENT ON COLUMN __SCHEMA__.study_path_tag.tag_id IS 'id of the tag';
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.study_path_tag TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.study_path_tag_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_tag TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_tag_id_seq TO GROUP __DB_ADMIN_GROUP__;
CREATE INDEX __SCHEMA___study_path_tag_index ON __SCHEMA__.study_path_tag(study_path_id, tag_id);
CREATE INDEX __SCHEMA___study_path_tag_study_path_id ON __SCHEMA__.study_path_tag(study_path_id);
CREATE INDEX __SCHEMA___study_path_tag_tag_id ON __SCHEMA__.study_path_tag(tag_id);

-- study path per faculty
/*CREATE TABLE __SCHEMA__.study_path_per_faculty (
  id                      BIGSERIAL                PRIMARY KEY,
  faculty_id              INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.faculty(id)
                                                   ON DELETE RESTRICT,
  study_path_id           INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.study_path(id)
                                                   ON DELETE RESTRICT,
  UNIQUE(faculty_id, study_path_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.study_path_per_faculty TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.study_path_per_faculty_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_per_faculty TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_per_faculty_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___study_path_per_faculty_faculty_id ON __SCHEMA__.study_path_per_faculty(faculty_id);
CREATE INDEX __SCHEMA___study_path_per_faculty_study_path_id ON __SCHEMA__.study_path_per_faculty(study_path_id);

-- study path per faculty
CREATE TABLE __SCHEMA__.study_path_per_university (
  id                      BIGSERIAL                PRIMARY KEY,
  university_id           INTEGER                  NOT NULL
                                                   REFERENCES public.uni(id)
                                                   ON DELETE RESTRICT,
  study_path_id           INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.study_path(id)
                                                   ON DELETE RESTRICT,
  UNIQUE(university_id, study_path_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.study_path_per_university TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.study_path_per_university_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_per_university TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_per_university_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___study_path_per_university_university_id ON __SCHEMA__.study_path_per_university(university_id);
CREATE INDEX __SCHEMA___study_path_per_university_study_path_id ON __SCHEMA__.study_path_per_university(study_path_id);*/


-- study path per student
CREATE TABLE __SCHEMA__.study_path_per_student (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  study_path_id           INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.study_path(id)
                                                   ON DELETE RESTRICT,
  primary_course          BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  study_status            INTEGER                  NOT NULL
                                                   DEFAULT 1
                                                   REFERENCES public.study_status(id),
  UNIQUE(user_id, study_path_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.study_path_per_student TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.study_path_per_student_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_per_student TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.study_path_per_student_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___study_path_per_student_user_id ON __SCHEMA__.study_path_per_student(user_id);
CREATE INDEX __SCHEMA___study_path_per_student_study_path_id ON __SCHEMA__.study_path_per_student(study_path_id);
CREATE INDEX __SCHEMA___study_path_per_student_primary_course ON __SCHEMA__.study_path_per_student(primary_course);

-- import global rights data from public.rights
CREATE TRIGGER __SCHEMA___set_primary_course_per_student AFTER INSERT OR UPDATE ON __SCHEMA__.study_path_per_student FOR EACH ROW
               EXECUTE PROCEDURE public.primary_study_path('__SCHEMA__');



-- ###########################################################################
-- contains all courses
CREATE TABLE __SCHEMA__.courses (
  id                      BIGSERIAL                PRIMARY KEY,
  name                    VARCHAR(200)             NOT NULL
                                                   CHECK (LENGTH(name) > 0),
  name_english            VARCHAR(200)             NOT NULL,
  name_short              VARCHAR(50)              NOT NULL,
  UNIQUE(name)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE TRIGGER __SCHEMA___courses_insert AFTER INSERT ON __SCHEMA__.courses FOR EACH ROW 
               EXECUTE PROCEDURE public.insert_course('__SCHEMA__');
CREATE INDEX __SCHEMA___courses_name ON __SCHEMA__.courses(name);

-- ###########################################################################
-- contains additional data about courses
CREATE TABLE __SCHEMA__.courses_data (
  course_id               BIGINT                   PRIMARY KEY
                                                   REFERENCES __SCHEMA__.courses(id),
  forum_id                BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.forum_fora(id),
  UNIQUE(course_id,forum_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_data TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_data TO GROUP __DB_ADMIN_GROUP__;

-- ###########################################################################
-- contains courses-user relationship
CREATE TABLE __SCHEMA__.courses_per_student (
  id                      BIGSERIAL                PRIMARY KEY,
  course_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses(id)
                                                   ON DELETE RESTRICT,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  UNIQUE(course_id,user_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_per_student TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_per_student_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_per_student TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_per_student_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___courses_per_student_user_id ON __SCHEMA__.courses_per_student(user_id);
CREATE INDEX __SCHEMA___courses_per_student_course_id ON __SCHEMA__.courses_per_student(course_id);

-- ###########################################################################
-- contains courses-study_path relationship
CREATE TABLE __SCHEMA__.courses_per_study_path (
  id                      BIGSERIAL                PRIMARY KEY,
  course_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses(id)
                                                   ON DELETE RESTRICT,
  study_path_id           BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.study_path(id)
                                                   ON DELETE RESTRICT,
  semester_min            SMALLINT                 NOT NULL
                                                   CHECK(semester_min >= 1 AND semester_min <= 15),
  semester_max            SMALLINT                 NOT NULL
                                                   CHECK(semester_max >= 1 AND semester_max <= 15 AND semester_max >= semester_min),
  UNIQUE(course_id, study_path_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_per_study_path TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_per_study_path_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_per_study_path TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_per_study_path_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___courses_per_study_path_course_id ON __SCHEMA__.courses_per_study_path(course_id);
CREATE INDEX __SCHEMA___courses_per_study_path_study_path_id ON __SCHEMA__.courses_per_study_path(study_path_id);

COMMIT;
