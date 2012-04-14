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
-- contains the files attached to courses
--
-- $Id: schema-courses_files.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;

-- ###########################################################################
-- contains all course file upload categories

CREATE TABLE __SCHEMA__.courses_files_categories (
  id                      SERIAL                   PRIMARY KEY,
  name                    VARCHAR(50)              NOT NULL
                                                   UNIQUE
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___courses_files_categories ON __SCHEMA__.courses_files_categories(name);
GRANT SELECT,INSERT,UPDATE ON __SCHEMA__.courses_files_categories TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_categories TO GROUP __DB_ADMIN_GROUP__;


-- ###########################################################################
-- contains all course file upload categories

CREATE TABLE __SCHEMA__.courses_files_semesters (
  id                      SERIAL                   PRIMARY KEY,
  name                    VARCHAR(20)              NOT NULL
                                                   UNIQUE
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___courses_files_semesters ON __SCHEMA__.courses_files_semesters(name);
GRANT SELECT,INSERT,UPDATE ON __SCHEMA__.courses_files_semesters TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_semesters TO GROUP __DB_ADMIN_GROUP__;



-- ###########################################################################
-- contains the categories for course file ratings
CREATE TABLE __SCHEMA__.courses_files_ratings_categories (
  id                      INTEGER                  PRIMARY KEY,
  name                    VARCHAR(50)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  type                    VARCHAR(10)              NOT NULL
                                                   CHECK (type IN ('range','text','bool','special')),
  type_parameter          TEXT                     NOT NULL
                                                   DEFAULT ''
) WITHOUT OIDS;

--GRANT SELECT ON __SCHEMA__.courses_files_ratings_categories TO GROUP __DB_NORMAL_GROUP__;
-- DEBUG !!!!
GRANT SELECT,INSERT,UPDATE ON __SCHEMA__.courses_files_ratings_categories TO GROUP __DB_NORMAL_GROUP__;
-- DEBUG END
GRANT ALL ON __SCHEMA__.courses_files_ratings_categories TO GROUP __DB_ADMIN_GROUP__;

INSERT INTO __SCHEMA__.courses_files_ratings_categories (id,name,type,type_parameter)
    VALUES (1,'document description','range','1,6');
INSERT INTO __SCHEMA__.courses_files_ratings_categories (id,name,type,type_parameter)
    VALUES (2,'scientific level','range','1,6');
INSERT INTO __SCHEMA__.courses_files_ratings_categories (id,name,type,type_parameter)
    VALUES (3,'helpful','range','1,6');
INSERT INTO __SCHEMA__.courses_files_ratings_categories (id,name,type)
    VALUES (4,'freetext','text');


-- ###########################################################################
-- contains the meta data about course files
CREATE TABLE __SCHEMA__.courses_files (
  id                      BIGSERIAL                PRIMARY KEY,
  course_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses(id)
                                                   ON DELETE RESTRICT,
  category_id             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files_categories(id)
                                                   ON DELETE RESTRICT,
  semester_id             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files_semesters(id)
                                                   ON DELETE RESTRICT,
  author_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE RESTRICT,
  costs                   SMALLINT                 NOT NULL
                                                   DEFAULT 1
                                                   CHECK(costs >= 1),
  description             TEXT                     NOT NULL,
  download_number         INT                      NOT NULL
                                                   DEFAULT 0,
  insert_at               TIMESTAMPTZ              NOT NULL,
  file_name               VARCHAR(250)             NOT NULL,
  file_size               INT                      NOT NULL,
  file_type               VARCHAR(20)              NOT NULL
                                                   DEFAULT ''
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_files TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_files_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___courses_files_course_id ON __SCHEMA__.courses_files(course_id);
CREATE INDEX __SCHEMA___courses_files_category_id ON __SCHEMA__.courses_files(category_id);
CREATE INDEX __SCHEMA___courses_files_semester_id ON __SCHEMA__.courses_files(semester_id);
CREATE INDEX __SCHEMA___courses_files_insert_at ON __SCHEMA__.courses_files(insert_at);

-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___courses_file_insert BEFORE INSERT ON __SCHEMA__.courses_files FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();
-- update some stats
CREATE TRIGGER __SCHEMA___courses_file_upload AFTER INSERT OR UPDATE OR DELETE ON __SCHEMA__.courses_files FOR EACH ROW
               EXECUTE PROCEDURE public.update_course_file_upload('__SCHEMA__');

-- ###########################################################################
-- contains revisions of course files
CREATE TABLE __SCHEMA__.courses_files_revisions (
  id                      BIGSERIAL                PRIMARY KEY,
  file_id                 BIGINT                   REFERENCES __SCHEMA__.courses_files(id)
                                                   ON DELETE CASCADE,
  path                    VARCHAR(255)             NOT NULL
                                                   CHECK(LENGTH(path)>1),
  upload_time             TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  file_size               INT                      NOT NULL,
  file_type               VARCHAR(20)              NOT NULL
                                                   DEFAULT '',
  hash                    VARCHAR(64)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(hash) > 5)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_files_revisions TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_files_revisions_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_revisions TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_revisions_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- move deleted course files into attachment_old table for safe physical deletion
CREATE TRIGGER __SCHEMA___courses_remove_file BEFORE DELETE ON __SCHEMA__.courses_files_revisions FOR EACH ROW
               EXECUTE PROCEDURE public.move_old_attachments('__SCHEMA__');


-- ###########################################################################
-- contains the ratings of course files
CREATE TABLE __SCHEMA__.courses_files_ratings (
  id                      BIGSERIAL                PRIMARY KEY,
  file_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  time                    TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  UNIQUE(file_id, user_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_files_ratings TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_files_ratings_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_ratings TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_ratings_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___courses_files_ratings_file_id ON __SCHEMA__.courses_files_ratings(file_id);

-- updates the rated-field in download-table
CREATE TRIGGER __SCHEMA___courses_update_counter_file_rating AFTER INSERT ON __SCHEMA__.courses_files_ratings FOR EACH ROW
               EXECUTE PROCEDURE public.update_course_file_counter_rating('__SCHEMA__');

-- ###########################################################################
-- contains the ratings of course files
CREATE TABLE __SCHEMA__.courses_files_ratings_single (
  id                      BIGSERIAL                PRIMARY KEY,
  rating_id               BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files_ratings(id)
                                                   ON DELETE CASCADE,
  rating_category_id      INT                      NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files_ratings_categories(id)
                                                   ON DELETE CASCADE,
  rating                  TEXT                     DEFAULT NULL
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_files_ratings_single TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_files_ratings_single_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_ratings_single TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_ratings_single_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE TRIGGER __SCHEMA___courses_update_file_rating AFTER INSERT ON __SCHEMA__.courses_files_ratings_single FOR EACH ROW
               EXECUTE PROCEDURE public.update_course_file_rating('__SCHEMA__');

-- ###########################################################################
-- contains the median of ratings of the course files
CREATE TABLE __SCHEMA__.courses_files_ratings_median (
  id                      BIGSERIAL                PRIMARY KEY,
  file_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files(id)
                                                   ON DELETE CASCADE,
  rating_category_id      INT                      NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files_ratings_categories(id)
                                                   ON DELETE CASCADE,
  rating                  NUMERIC(6,2)             NOT NULL
                                                   CHECK(rating >= 0),
  rating_number           INT                      DEFAULT 0
                                                   NOT NULL
                                                   CHECK(rating_number >= 0),
  UNIQUE(file_id, rating_category_id)
) WITHOUT OIDS;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_files_ratings_median_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_files_ratings_median TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_ratings_median_id_seq TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_ratings_median TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___courses_files_ratings_median_file_id ON __SCHEMA__.courses_files_ratings_median(file_id);
CREATE INDEX __SCHEMA___courses_files_ratings_median_rating_category_id ON __SCHEMA__.courses_files_ratings_median(rating_category_id);

-- ###########################################################################
-- contains the download history of course files
CREATE TABLE __SCHEMA__.courses_files_downloads (
  id                      BIGSERIAL                PRIMARY KEY,
  file_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files(id)
                                                   ON DELETE CASCADE,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  already_rated           BOOLEAN                  NOT NULL
                                                   DEFAULT false,
  UNIQUE(file_id, user_id)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_files_downloads TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_files_downloads_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_downloads TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_downloads_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___courses_files_downloads_file_id ON __SCHEMA__.courses_files_downloads(file_id);
CREATE INDEX __SCHEMA___courses_files_downloads_user_id ON __SCHEMA__.courses_files_downloads(user_id);

CREATE TRIGGER __SCHEMA___courses_update_counter_file_download AFTER INSERT OR DELETE ON __SCHEMA__.courses_files_downloads FOR EACH ROW
               EXECUTE PROCEDURE public.update_counter_course_file_download('__SCHEMA__');
-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___courses_files_downloads_insert BEFORE INSERT ON __SCHEMA__.courses_files_downloads FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();

-- ###########################################################################
-- contains the annotations to course files
--
-- annotations should/could be outsourced into own table
--
/*
CREATE TABLE __SCHEMA__.courses_files_annotations (
  id                      BIGSERIAL                PRIMARY KEY,
  file_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.courses_files(id)
                                                   ON DELETE CASCADE,
  annotation              VARCHAR(50)              NOT NULL
                                                   CHECK(LENGTH(annotation)>=2),
  UNIQUE(file_id,annotation)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.courses_files_annotations TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.courses_files_annotations_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_annotations TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.courses_files_annotations_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___courses_files_annotations_file_id ON __SCHEMA__.courses_files_annotations(file_id);
CREATE INDEX __SCHEMA___courses_files_annotations_lannotation ON __SCHEMA__.courses_files_annotations(annotation);
*/

COMMIT;
