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
-- sample data for blog-db
--
-- $Id: blog_advanced_test_data.sql 5883 2008-05-03 13:34:34Z schnueptus $
--

-- some bloggers
INSERT INTO __SCHEMA__.blog_advanced_config
    (user_id, title, subtitle)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'xPyrus Blog - HomerJaySimpson',
      'Heute die Welt, morgen das Sonnensystem!');

      
INSERT INTO __SCHEMA__.blog_advanced_categories
    (user_id, name)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'iX');
INSERT INTO __SCHEMA__.blog_advanced_categories
    (user_id, name)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'Soziologische Studien');
INSERT INTO __SCHEMA__.blog_advanced_categories
    (user_id, name)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'c''t');


----------------------------------------
-- entry
INSERT INTO __SCHEMA__.blog_advanced
    (user_id, title, entry_raw, entry_time, post_ip, comments)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'Hello World',
      'ein [b]sinn[/b]loser Text',
      NOW() - interval '1 month 4 days',
      '127.0.0.1',
      0 );
INSERT INTO __SCHEMA__.blog_advanced_comments
    (entry_id, author_name, comment, post_ip)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog_advanced),
      'Rainer Zufall',
      'Tolles Blog!',
      '127.0.0.1');

INSERT INTO __SCHEMA__.blog_advanced_entriescat
    (entry_id, category_id)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog_advanced),
      (SELECT id FROM __SCHEMA__.blog_advanced_categories WHERE name='Soziologische Studien') );

----------------------------------------
-- entry

INSERT INTO __SCHEMA__.blog_advanced
    (user_id, title, entry_raw, entry_time, post_ip, comments)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'Brüssel',
      '[code=php]<?php phpinfo(); ?>[/code]',
      NOW() - interval '4 days',
      '127.0.0.1',
      0);
INSERT INTO __SCHEMA__.blog_advanced_entriescat
    (entry_id, category_id)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog_advanced),
      (SELECT id FROM __SCHEMA__.blog_advanced_categories WHERE name='iX') );
INSERT INTO __SCHEMA__.blog_advanced_entriescat
    (entry_id, category_id)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog_advanced),
      (SELECT id FROM __SCHEMA__.blog_advanced_categories WHERE name='c''t') );


----------------------------------------
-- entry
INSERT INTO __SCHEMA__.blog_advanced
    (user_id, title, entry_raw, post_ip, comments)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'Brüssel II',
      '[i]Belgien[/i]',
      '127.0.0.1',
      0);
INSERT INTO __SCHEMA__.blog_advanced_comments
    (entry_id, author_int, author_name, comment, post_ip)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog_advanced),
      (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
      'schnueptus',
      '^^',
      '127.0.0.1');
INSERT INTO __SCHEMA__.blog_advanced_trackbacks
    (entry_id,weblog_name, title, weblog_url, body, post_ip)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog_advanced),
      'Another Blog', 'YAB',
      'http://wordpress.sunburner/',
      'Yet another ...',
      '192.168.152.130');
INSERT INTO __SCHEMA__.blog_advanced_entriescat
    (entry_id, category_id)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog_advanced),
      (SELECT id FROM __SCHEMA__.blog_advanced_categories WHERE name='iX') );
     
