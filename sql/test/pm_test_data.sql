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
-- sample data for guestbook-db
--
-- $Id: pm_test_data.sql 5874 2008-05-03 11:18:26Z schnueptus $
--

INSERT INTO __SCHEMA__.pm
    (author_int,recipient_string,
     entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'), 'Bart_Simpson, Marge_Simpson',
      'PM-Test :thumbs:', '127.0.0.1' );

INSERT INTO __SCHEMA__.pm_for_users
        (pm_id, user_id_for)
    VALUES
        ( (SELECT MAX(id) FROM __SCHEMA__.pm),
          (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson') );
INSERT INTO __SCHEMA__.pm_for_users
        (pm_id, user_id_for)
    VALUES
        ( (SELECT MAX(id) FROM __SCHEMA__.pm),
          (SELECT id FROM __SCHEMA__.users WHERE username='Marge_Simpson') );

INSERT INTO __SCHEMA__.pm
    (author_int,recipient_string,
     entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='Carl'), 'HomerJaySimpson',
      'PM-Test 23 :wave:', '127.0.0.1' );

INSERT INTO __SCHEMA__.pm_for_users
        (pm_id, user_id_for)
    VALUES
        ( (SELECT MAX(id) FROM __SCHEMA__.pm),
          (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson') );
