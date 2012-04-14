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

ALTER TABLE __SCHEMA__.guestbook ADD COLUMN idx_fulltext tsvector;
CREATE INDEX __SCHEMA___guestbook_idx_fulltext ON __SCHEMA__.guestbook USING gist(idx_fulltext);

-- for logging table
-- ALTER TABLE __SCHEMA__.guestbook_log ADD COLUMN idx_fulltext tsvector;

-- permission handling
GRANT SELECT ON public.pg_ts_cfg TO __DB_NORMAL_GROUP__;
GRANT SELECT ON public.pg_ts_cfgmap TO __DB_NORMAL_GROUP__;
GRANT SELECT ON public.pg_ts_dict TO __DB_NORMAL_GROUP__;
GRANT SELECT ON public.pg_ts_parser TO __DB_NORMAL_GROUP__;

GRANT ALL ON public.pg_ts_cfg TO __DB_ADMIN_GROUP__;
GRANT ALL ON public.pg_ts_cfgmap TO __DB_ADMIN_GROUP__;
GRANT ALL ON public.pg_ts_dict TO __DB_ADMIN_GROUP__;
GRANT ALL ON public.pg_ts_parser TO __DB_ADMIN_GROUP__;


COMMIT;
