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

CREATE TABLE __SCHEMA__.mail (
    id                      BIGSERIAL                PRIMARY KEY,
    insert_at               TIMESTAMPTZ              NOT NULL
                                                     DEFAULT NOW(),
    sent                    BOOLEAN                  NOT NULL
                                                     DEFAULT false,
    sent_at                 TIMESTAMPTZ              NULL,
    mail_from_name          VARCHAR(150)             NOT NULL
                                                     DEFAULT '',
    mail_from               VARCHAR(150)             NOT NULL
                                                     DEFAULT '',
    mail_to                 VARCHAR(150)             NOT NULL
                                                     DEFAULT '',
    mail_subject            VARCHAR(150)             NOT NULL
                                                     DEFAULT '',
    mail_body               TEXT                     NOT NULL
                                                     DEFAULT ''
) WITHOUT OIDS;

GRANT SELECT,INSERT,UPDATE,DELETE ON __SCHEMA__.mail TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.mail_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.mail TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.mail_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___mail_unsent ON __SCHEMA__.mail((NOT sent));

-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___mail_insert BEFORE INSERT ON __SCHEMA__.mail FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();

COMMIT;
