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
-- $Id: blog_test_data.sql 5955 2008-05-17 10:46:23Z schnueptus $
--

INSERT INTO __SCHEMA__.blog
    (user_id, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'ein [b]sinn[/b]loser Text',
      '127.0.0.1' );
    
INSERT INTO __SCHEMA__.blog
    (user_id, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
      'ein [b]sinn[/b]voller Text mit Smileys :D und einem Anhang:',
      '127.0.0.1' );    
INSERT INTO __SCHEMA__.attachments
    (path, file_size, file_type,author_id)
  VALUES
    ( './userfiles/users/3/9388787cdb0893b7_unihelp_pic.jpg', 16891, 'image',
    (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson') );
INSERT INTO __SCHEMA__.blog_attachments
    (entry_id, attachment_id)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog),
      (SELECT MAX(id) FROM __SCHEMA__.attachments) );
      
INSERT INTO __SCHEMA__.blog
    (user_id, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
      'Mal ein normaler Dateianhang :wave:', '127.0.0.1' );
INSERT INTO __SCHEMA__.attachments
    (path, file_size, file_type,author_id)
  VALUES
    ( './userfiles/users/3/f29124a070bde56d_catull_carmina.txt.bz2', 33706, 'misc',
    (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'));
INSERT INTO __SCHEMA__.blog_attachments
    (entry_id, attachment_id)
  VALUES
    ( (SELECT MAX(id) FROM __SCHEMA__.blog),
      (SELECT MAX(id) FROM __SCHEMA__.attachments) );
