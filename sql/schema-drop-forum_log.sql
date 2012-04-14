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
-- contains all sql commands to drop schema logging tables for the forum
--
-- $Id: schema-drop-forum_log.sql 5743 2008-03-25 19:48:14Z ads $
--

-- forum_thread_entries_attachments table
DROP TRIGGER __SCHEMA___forum_thread_entries_attachments_log_chg ON __SCHEMA__.forum_thread_entries_attachments;
TRUNCATE TABLE __SCHEMA__.forum_thread_entries_attachments_log;
DROP TABLE __SCHEMA__.forum_thread_entries_attachments_log;
DROP SEQUENCE __SCHEMA__.forum_thread_entries_attachments_log_id;

-- forum_thread_entries table
DROP TRIGGER __SCHEMA___forum_thread_entries_log_chg ON __SCHEMA__.forum_thread_entries;
TRUNCATE TABLE __SCHEMA__.forum_thread_entries_log;
DROP TABLE __SCHEMA__.forum_thread_entries_log;
DROP SEQUENCE __SCHEMA__.forum_thread_entries_log_id;
