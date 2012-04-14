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
-- logging for forum_thread_entries and forum_thread_entries_attachments table
--
-- $Id: schema-forum_log.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;

-- forum_thread_entries
SELECT * INTO __SCHEMA__.forum_thread_entries_log FROM __SCHEMA__.forum_thread_entries LIMIT 0;
ALTER TABLE __SCHEMA__.forum_thread_entries_log ADD COLUMN trigger_mode VARCHAR(10);
ALTER TABLE __SCHEMA__.forum_thread_entries_log ADD COLUMN trigger_tuple VARCHAR(5);
ALTER TABLE __SCHEMA__.forum_thread_entries_log ADD COLUMN trigger_changed TIMESTAMPTZ;
ALTER TABLE __SCHEMA__.forum_thread_entries_log ADD COLUMN trigger_user VARCHAR(32);
ALTER TABLE __SCHEMA__.forum_thread_entries_log ADD COLUMN trigger_id BIGINT;
CREATE SEQUENCE __SCHEMA__.forum_thread_entries_log_id;
SELECT SETVAL('__SCHEMA__.forum_thread_entries_log_id', 1, FALSE);
ALTER TABLE __SCHEMA__.forum_thread_entries_log ALTER COLUMN trigger_id SET DEFAULT NEXTVAL('__SCHEMA__.forum_thread_entries_log_id');
-- now activate the history function
-- you have to give the log table name!
CREATE TRIGGER __SCHEMA___forum_thread_entries_log_chg AFTER UPDATE OR INSERT OR DELETE ON __SCHEMA__.forum_thread_entries FOR EACH ROW
               EXECUTE PROCEDURE public.table_log('forum_thread_entries_log', 1, '__SCHEMA__');
CREATE INDEX __SCHEMA___forum_thread_entries_log_id ON __SCHEMA__.forum_thread_entries_log(id);
GRANT SELECT,INSERT ON __SCHEMA__.forum_thread_entries_log TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_thread_entries_log_id TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_entries_log TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_entries_log_id TO GROUP __DB_ADMIN_GROUP__;


-- forum_thread_entries_attachments
SELECT * INTO __SCHEMA__.forum_thread_entries_attachments_log FROM __SCHEMA__.forum_thread_entries_attachments LIMIT 0;
ALTER TABLE __SCHEMA__.forum_thread_entries_attachments_log ADD COLUMN trigger_mode VARCHAR(10);
ALTER TABLE __SCHEMA__.forum_thread_entries_attachments_log ADD COLUMN trigger_tuple VARCHAR(5);
ALTER TABLE __SCHEMA__.forum_thread_entries_attachments_log ADD COLUMN trigger_changed TIMESTAMPTZ;
ALTER TABLE __SCHEMA__.forum_thread_entries_attachments_log ADD COLUMN trigger_user VARCHAR(32);
ALTER TABLE __SCHEMA__.forum_thread_entries_attachments_log ADD COLUMN trigger_id BIGINT;
CREATE SEQUENCE __SCHEMA__.forum_thread_entries_attachments_log_id;
SELECT SETVAL('__SCHEMA__.forum_thread_entries_attachments_log_id', 1, FALSE);
ALTER TABLE __SCHEMA__.forum_thread_entries_attachments_log ALTER COLUMN trigger_id SET DEFAULT NEXTVAL('__SCHEMA__.forum_thread_entries_attachments_log_id');
-- now activate the history function
-- you have to give the log table name!
CREATE TRIGGER __SCHEMA___forum_thread_entries_attachments_log_chg AFTER UPDATE OR INSERT OR DELETE ON __SCHEMA__.forum_thread_entries_attachments FOR EACH ROW
               EXECUTE PROCEDURE public.table_log('forum_thread_entries_attachments_log', 1, '__SCHEMA__');
CREATE INDEX __SCHEMA___forum_thread_entries_attachments_log_id ON __SCHEMA__.forum_thread_entries_attachments_log(id);
GRANT SELECT,INSERT ON __SCHEMA__.forum_thread_entries_attachments_log TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.forum_thread_entries_attachments_log_id TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_entries_attachments_log TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.forum_thread_entries_attachments_log_id TO GROUP __DB_ADMIN_GROUP__;


COMMIT;
