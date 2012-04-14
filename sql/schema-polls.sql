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
-- $Id: schema-polls.sql 5743 2008-03-25 19:48:14Z ads $

BEGIN;

-- contains polls which can be created by every user

-- contains the basic data for every poll
CREATE TABLE __SCHEMA__.polls (
  id                      BIGSERIAL                PRIMARY KEY,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  poll_name               VARCHAR(200)             NOT NULL,
  -- if date_start is null, there is no start date for the poll
  date_start              DATE                     NULL,
  -- if date_stop is null, there is no end date for the poll
  date_stop               DATE                     NULL,
  -- if the poll is public, users from other unihelp portals or from
  -- the www can participate
  poll_is_public          BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  -- the user id will be saved in any case to avoid repeated poll answers by
  -- a single user, but the poll creator will only see anonymous reports
  -- in case the poll is not anonym, we should display a warning
  poll_is_anonym          BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  UNIQUE(user_id, poll_name)
) WITHOUT OIDS;

GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.polls TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.polls_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.polls TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.polls_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___polls_insert BEFORE INSERT ON __SCHEMA__.polls FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();


-- this table contains the possible question types
CREATE TABLE __SCHEMA__.poll_question_types (
  id                      INTEGER                  PRIMARY KEY,
  -- this is the internal name,
  -- the website should use a translated name
  question_type_name      VARCHAR(50)              NOT NULL
                                                   UNIQUE
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.poll_question_types TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.poll_question_types_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.poll_question_types TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.poll_question_types_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- simple question, answer can be yes or no
INSERT INTO poll_question_types (id, question_type_name)
     VALUES (1, 'yes_no');
-- request an answer between 0 and 100%
INSERT INTO poll_question_types (id, question_type_name)
     VALUES (2, 'percent value');
-- pick one answer
INSERT INTO poll_question_types (id, question_type_name)
     VALUES (3, 'one answer');
-- multiple answers are possible
INSERT INTO poll_question_types (id, question_type_name)
     VALUES (4, 'multiple choice answer');
-- number as answer is requested
INSERT INTO poll_question_types (id, question_type_name)
     VALUES (5, 'integer value');
-- free text line
INSERT INTO poll_question_types (id, question_type_name)
     VALUES (6, 'text line input');
-- free text field (possible more than one line)
INSERT INTO poll_question_types (id, question_type_name)
     VALUES (7, 'text field input');
-- to be continued


-- contains the questions for a poll
CREATE TABLE __SCHEMA__.poll_questions (
  id                      BIGSERIAL                PRIMARY KEY,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  poll_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.polls(id)
                                                   ON DELETE CASCADE,
  question_number         INTEGER                  NOT NULL
                                                   CHECK (question_number > 0),
  question_type           INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.poll_question_types(id),
  -- the question text displayed to the user
  question_text           TEXT                     NOT NULL,
  -- is an answer required?
  answer_required         BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  -- maybe we should move this data into another table?
  -- contains additional informations
  -- like all answers for a multiple choise question
  additional_data         TEXT                     NOT NULL
                                                   DEFAULT '',
  UNIQUE(poll_id, question_text),
  UNIQUE(poll_id, question_number)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.poll_questions TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.poll_questions_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.poll_questions TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.poll_questions_id_seq TO GROUP __DB_ADMIN_GROUP__;


-- contains the user answers for a poll
CREATE TABLE __SCHEMA__.poll_user_answers (
  id                      BIGSERIAL                PRIMARY KEY,
  insert_at               TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  question_id             BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.poll_questions(id)
                                                   ON DELETE CASCADE,
  -- NULL because it can be an external user
  user_id_int             BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  user_id_ext             BIGINT                   NULL
                                                   REFERENCES __SCHEMA__.external_users(id)
                                                   ON DELETE CASCADE,
  -- this column should be used for storing some kind of data to
  -- recognize the web user (like session id)
  web_client              TEXT                     NULL,
  answer                  TEXT                     NOT NULL
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.poll_user_answers TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.poll_user_answers_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.poll_user_answers TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.poll_user_answers_id_seq TO GROUP __DB_ADMIN_GROUP__;



COMMIT;
