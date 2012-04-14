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
-- $Id: banner_test_data.sql 5874 2008-05-03 11:18:26Z schnueptus $
--

INSERT INTO __SCHEMA__.attachments
    (path, file_size, file_type,author_id)
  VALUES
    ( '/images/banner/wm2006.gif', 47396, 'image',
    (SELECT id FROM __SCHEMA__.users WHERE username='Rektor') );
INSERT INTO __SCHEMA__.banner
    (name, author_int, author_ext,
     attachment_id, dest_url,
     width, height,
     start_date, end_date,
     post_ip,
     random_rate)
  VALUES
    ( 'WM', (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'), NULL,
      (SELECT MAX(id) FROM __SCHEMA__.attachments), 'http://127.0.0.1/',
      468, 60,
      CURRENT_DATE, CURRENT_DATE + interval '4 days',
      '127.0.0.1',
      10);
      
INSERT INTO __SCHEMA__.attachments
    (path, file_size, file_type,author_id)
  VALUES
    ( '/images/banner/6b133fb7fa455661_toolbar.jpg', 11675, 'image',
    (SELECT id FROM __SCHEMA__.users WHERE username='Rektor') );
INSERT INTO __SCHEMA__.banner
    (name, author_int, author_ext,
     attachment_id, dest_url,
     width, height,
     start_date, end_date,
     post_ip,
     random_rate)
  VALUES
    ( 'Toolbar', (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'), NULL,
      (SELECT MAX(id) FROM __SCHEMA__.attachments), 'http://127.0.0.1/',
      468, 60,
      CURRENT_DATE, CURRENT_DATE + interval '4 days',
      '127.0.0.1',
      20);

SELECT __SCHEMA__.update_random_banner(id) FROM __SCHEMA__.banner;
