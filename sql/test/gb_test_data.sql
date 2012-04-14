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
-- $Id: gb_test_data.sql 5955 2008-05-17 10:46:23Z schnueptus $
--

INSERT INTO __SCHEMA__.guestbook
    (user_id_for,
     author_int,
     entry_raw, post_ip, weighting)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
      (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      '[b]Servus[/b]', '127.0.0.1', 1 );
INSERT INTO __SCHEMA__.guestbook
    (user_id_for,
     author_int,
     entry_raw, post_ip,
     weighting)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),
      (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'), 
      'Von :biene: und :blume:', '127.0.0.1',
      1 );

INSERT INTO __SCHEMA__.guestbook
    (user_id_for,
     author_int,
     entry_raw, post_ip, weighting)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      (SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'), 
      '[i]ein falscher Eintrag[/i]', '127.0.0.1', 2 );

INSERT INTO __SCHEMA__.guestbook
    (user_id_for,
     author_int,
     entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      (SELECT id FROM __SCHEMA__.users WHERE username='Marge_Simpson'), 
      'check auto-sql-update 2nd', '127.0.0.1' );

INSERT INTO __SCHEMA__.guestbook
    (user_id_for,
     author_int,
     entry_raw, post_ip,
     comment, comment_time)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
      (SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'), 
      'Zur Abwechslung mal [i]ein falscher Eintrag[/i]', '127.0.0.1',
      'Der Eintrag ist gar nicht von Bart_Simpson!', timestamp '2006-12-12 12:13');

