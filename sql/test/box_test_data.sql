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
INSERT INTO __SCHEMA__.box_shoutbox (entry_raw, user_id) (SELECT 'who is your daddy?', id FROM __SCHEMA__.users WHERE username != 'HomerJaySimpson' AND flag_invisible = false AND flag_activated = true);
INSERT INTO __SCHEMA__.box_shoutbox (entry_raw, user_id) (SELECT 'I have no daddy!', id FROM __SCHEMA__.users WHERE username = 'Rektor');

