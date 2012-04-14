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
-- contains the user who have visited a user site
--
-- $Id: schema-site_visits.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all user page visits
CREATE TABLE __SCHEMA__.site_visits (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  visitor_id              BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  visit_time              TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW()
) WITHOUT OIDS;
GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.site_visits TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.site_visits_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.site_visits TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.site_visits_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___site_visits_user_id ON __SCHEMA__.site_visits(user_id);
CREATE INDEX __SCHEMA___site_visits_visitor_id ON __SCHEMA__.site_visits(visitor_id);


COMMIT;
