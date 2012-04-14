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
-- sample data for news-db
--
-- $Id: news_test_data.sql 5883 2008-05-03 13:34:34Z schnueptus $
--

INSERT INTO __SCHEMA__.news
    (author_int, group_id,
     start_date, end_date, caption,
     opener_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
      CURRENT_DATE, CURRENT_DATE + interval '4 days',
      'Mauritius',
      '[b]Release[/b] [i]Party[/i]',
      '127.0.0.1' );

INSERT INTO __SCHEMA__.news
    (author_int, group_id,
     start_date, end_date, caption,
     opener_raw, post_ip, is_visible)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
      CURRENT_DATE, CURRENT_DATE + interval '400 days',
      'A Test Review news',
      'Please Review me [b]Release[/b] [i]Party[/i]',
      '127.0.0.1', false);
      
INSERT INTO __SCHEMA__.news
    (author_int, group_id,
     start_date, end_date, caption,
     opener_raw, entry_raw, post_ip, is_visible)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
      (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
      CURRENT_DATE, CURRENT_DATE + interval '400 days',
      'Test-Accounts',
      'Die Test-Accounts:
      
      HomerJaySimpson // matrix (Verwalter von Organisation "Krusty Fanclub" )
      Bart_Simpson // matrix (normaler User)
      Lisa_Simpson // matrix (normaler User -  alle Features)
      Mr_Burns // matrix (Alle Rechte)','Viel Spass',
      '127.0.0.1', true);


