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
-- sample data for forum-db
--
-- $Id: forum_test_data.sql 5883 2008-05-03 13:34:34Z schnueptus $
--




INSERT INTO __SCHEMA__.forum_category_moderator 
	(category_id, user_id)    
  VALUES
  	((SELECT id FROM __SCHEMA__.forum_categories WHERE name='Fächer'),
  	(SELECT id FROM __SCHEMA__.users WHERE username='Marge_Simpson'));
  	
INSERT INTO __SCHEMA__.forum_category_moderator 
	(category_id, user_id)    
  VALUES
  	((SELECT id FROM __SCHEMA__.forum_categories WHERE name='Fächer'),
  	(SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'));

INSERT INTO __SCHEMA__.forum_default_moderator 
	(category_id, user_id)    
  VALUES
  	((SELECT id FROM __SCHEMA__.forum_categories WHERE name='Fächer'),
  	(SELECT id FROM __SCHEMA__.users WHERE username='Rektor'));

INSERT INTO __SCHEMA__.forum_default_moderator 
	(category_id, user_id)    
  VALUES
  	((SELECT id FROM __SCHEMA__.forum_categories WHERE name='Fächer'),
  	(SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'));


INSERT INTO __SCHEMA__.forum_threads

    (forum_id, caption)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_fora WHERE name='Hochschulpolitik'),
      'rotes Fahrrad gesucht' );

INSERT INTO __SCHEMA__.forum_thread_entries
    (thread_id, author_int, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_threads WHERE caption='rotes Fahrrad gesucht'),
      (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'Ich suche ein [b]rotes[/b] Fahrrad',
      '127.0.0.1' );
INSERT INTO __SCHEMA__.forum_thread_entries
    (thread_id, author_int, group_id, entry_raw, post_ip, caption)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_threads WHERE caption='rotes Fahrrad gesucht'),
      (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      (SELECT id FROM __SCHEMA__.groups WHERE name='Krusty Fanclub'),
      'Ich suche ein [b]blaues[/b] Zweirad',
      '127.0.0.2',
      'Jetzt in blau' );

INSERT INTO __SCHEMA__.forum_thread_entries
    (thread_id, author_int, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_threads WHERE caption='rotes Fahrrad gesucht'),
      (SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),
      'Ich suche ein [b]rotes[/b] Fahrrad',
      '127.0.0.1' );
INSERT INTO __SCHEMA__.forum_thread_entries
    (thread_id, author_int, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_threads WHERE caption='rotes Fahrrad gesucht'),
      (SELECT id FROM __SCHEMA__.users WHERE username='Milhouse'),
      'Ich suche ein [b]rotes[/b] Fahrrad',
      '127.0.0.1' );
INSERT INTO __SCHEMA__.forum_thread_entries
    (thread_id, author_int, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_threads WHERE caption='rotes Fahrrad gesucht'),
      (SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
      'Ich suche ein [b]rotes[/b] Fahrrad',
      '127.0.0.1' );

INSERT INTO __SCHEMA__.forum_thread_entries
    (thread_id, author_int, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_threads WHERE caption='rotes Fahrrad gesucht'),
      (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
      'Ich suche ein [b]rotes[/b] Fahrrad',
      '127.0.0.1' );
INSERT INTO __SCHEMA__.forum_thread_entries
    (thread_id, author_int, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_threads WHERE caption='rotes Fahrrad gesucht'),
      (SELECT id FROM __SCHEMA__.users WHERE username='Carl'),
      'Ich suche ein [b]rotes[/b] Fahrrad',
      '127.0.0.1' );
INSERT INTO __SCHEMA__.forum_thread_entries
    (thread_id, author_int, entry_raw, post_ip)
  VALUES
    ( (SELECT id FROM __SCHEMA__.forum_threads WHERE caption='rotes Fahrrad gesucht'),
      (SELECT id FROM __SCHEMA__.users WHERE username='Mr_Burns'),
      'Ich suche ein [b]rotes[/b] Fahrrad',
      '127.0.0.1' );

