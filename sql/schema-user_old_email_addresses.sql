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
-- contains the old email addresses
--
-- $Id: schema-user_old_email_addresses.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all old email addresses

CREATE TABLE __SCHEMA__.user_old_email_addresses (
  id                      BIGSERIAL                PRIMARY KEY,
  insert_at               TIMESTAMPTZ              NOT NULL,
  user_id                 BIGINT                   NULL,
  email_address           VARCHAR(150)             NOT NULL
                                                   CHECK (CHECK_EMAIL(email_address))
) WITHOUT OIDS;

GRANT ALL ON __SCHEMA__.user_old_email_addresses TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_old_email_addresses_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_old_email_addresses TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_old_email_addresses_id_seq TO GROUP __DB_ADMIN_GROUP__;
-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___user_old_email_insert BEFORE INSERT ON __SCHEMA__.user_old_email_addresses FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_insert_at();

-- check for old email address
CREATE TRIGGER __SCHEMA___old_email_add BEFORE INSERT ON __SCHEMA__.user_extra_data FOR EACH ROW
               EXECUTE PROCEDURE public.uniemail_add('__SCHEMA__');
-- log every changed email address
CREATE TRIGGER __SCHEMA___uniemail_changes BEFORE UPDATE ON __SCHEMA__.user_extra_data FOR EACH ROW
               EXECUTE PROCEDURE public.uniemail_changes('__SCHEMA__');






COMMIT;
