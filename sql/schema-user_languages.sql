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
-- contains the languages per user
--
-- $Id: schema-user_languages.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;

-- note for knowledge:
--  0: not spoken
--  1: basic knowledge
--  2: normal knowledge
--  3: good knowledge
--  4: native language


-- ###########################################################################
-- contains all user languages
CREATE TABLE __SCHEMA__.user_languages (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  language_id             INT                      NOT NULL
                                                   REFERENCES public.user_languages(id)
                                                   ON DELETE CASCADE,
  knowledge               INT                      NOT NULL
                                                   CHECK (knowledge >= 0 AND knowledge <= 4)
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.user_languages TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_languages_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_languages TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_languages_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___user_languages_user_id ON __SCHEMA__.user_languages(user_id);
CREATE INDEX __SCHEMA___user_languages_language_id ON __SCHEMA__.user_languages(language_id);


COMMIT;
