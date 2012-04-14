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
BEGIN;

CREATE TABLE __SCHEMA__.sessions (
    id          BIGSERIAL       PRIMARY KEY,
    user_agent  VARCHAR(200)    NOT NULL,
    insert_at   TIMESTAMPTZ     NOT NULL
                                DEFAULT NOW(),
    user_id     BIGINT          NULL
                                REFERENCES __SCHEMA__.users(id) ON DELETE CASCADE,
    session_id  VARCHAR(128)    NOT NULL
) WITHOUT OIDS;

GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.sessions TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.sessions_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.sessions TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.sessions_id_seq TO GROUP __DB_ADMIN_GROUP__;

COMMIT;
