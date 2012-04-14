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
-- contains some sql and plpgsql functions for use in schema
--
-- $Id: schema-functions.sql 5743 2008-03-25 19:48:14Z ads $
--

-- ###########################################################################
-- ###########################################################################
-- SQL functions

-- ###########################################################################
-- add an user to the online list
-- parameter:
--  - user id (bigint)
--  - external user id (bigint)
--  - session id (text)
-- return:
--  - if user is inserted (1) or updated (2) (integer)
CREATE OR REPLACE FUNCTION __SCHEMA__.set_user_online(BIGINT, BIGINT, TEXT)
        RETURNS INTEGER
        AS $$
        DECLARE
            query TEXT := '';
            loop_record RECORD;
            user_id ALIAS FOR $1;
            user_external_id ALIAS FOR $2;
            session_id ALIAS FOR $3;
            number_entries INTEGER;
            old_uid INTEGER;
            old_ueid INTEGER;
            old_id INTEGER;
        BEGIN
            -- search if user is already in the user_online table
            -- lock rows, if found
            query := 'SELECT id, user_id, user_external_id
                        FROM __SCHEMA__.user_online
                       WHERE session_id=' || quote_literal(session_id);
            -- RAISE NOTICE 'query: %', query;
            -- workaround since EXECUTE is not able to return a result
            number_entries := 0;
            FOR loop_record IN EXECUTE query LOOP
                number_entries := number_entries + 1;
                old_uid := loop_record.user_id; -- session_id is unique, so at most one row is returned
                old_id := loop_record.id;
                old_ueid := loop_record.user_external_id;
            END LOOP;
            IF number_entries > 0 THEN
                -- if user id has changed, just update
                IF user_id <> 0 AND (old_uid <> user_id OR old_uid IS NULL) THEN
                    query := 'UPDATE __SCHEMA__.user_online
                                SET online_since=NOW(),
                                    user_id = ' || user_id || '
                              WHERE id=' || old_id || '';
                    EXECUTE query;
                ELSIF user_external_id <> 0 AND (old_ueid <> user_external_id OR old_ueid IS NULL) THEN
                    query := 'UPDATE __SCHEMA__.user_online
                                SET online_since=NOW(),
                                    user_external_id = ' || user_external_id || '
                              WHERE id=' || old_id || '';
                    EXECUTE query;
                ELSIF old_uid IS NOT NULL AND user_id = 0 AND user_external_id = 0 THEN
                    query := 'UPDATE __SCHEMA__.user_online
                                SET online_since=NOW(),
                                    user_id = NULL,
                                    user_external_id = NULL
                              WHERE id=' || old_id || '';
                    EXECUTE query;
                END IF;
                -- in other cases we don't need to do anything here
                RETURN 2;
            ELSE
                -- lock table in exlusive mode
                -- if the database is busy updating different users,
                -- a deadlock is possible so we lock the table in
                -- exclusive mode to make sure, no other connection is
                -- changing this table
                LOCK TABLE __SCHEMA__.user_online
                        IN SHARE ROW EXCLUSIVE MODE;

                -- search if user is already in the user_online table
                query := 'SELECT id
                            FROM __SCHEMA__.user_online
                          WHERE session_id=' || quote_literal(session_id);
                -- RAISE NOTICE 'query: %', query;
                -- workaround since EXECUTE is not able to return a result
                number_entries := 0;
                FOR loop_record IN EXECUTE query LOOP
                    number_entries := number_entries + 1;
                END LOOP;

                IF number_entries > 0 THEN
                    -- discard INSERT
                    RETURN 2;
                END IF;

                -- no user found, insert new data
                -- if seems valid, insert normal record
                IF user_id > 0 THEN
                    query := 'INSERT INTO __SCHEMA__.user_online
                                        (user_id, online_since, session_id)
                                VALUES (' || user_id || ', NOW(), ' || quote_literal(session_id) || ')';
                ELSIF user_external_id > 0 THEN 
                    query := 'INSERT INTO __SCHEMA__.user_online
                                        (user_external_id, online_since, session_id)
                                VALUES (' || user_external_id || ', NOW(), ' || quote_literal(session_id) || ')';
                -- otherwise insert a NULL for unknown user
                ELSE
                    query := 'INSERT INTO __SCHEMA__.user_online
                                        (user_id, user_external_id, online_since, session_id)
                                VALUES (NULL, NULL, NOW(), ' || quote_literal(session_id) || ')';
                END IF;
                EXECUTE query;
                RETURN 1;
            END IF;
        END;
        $$
        LANGUAGE 'plpgsql';


-- ###########################################################################
-- updates users' feature slots
-- parameter:
--  - user id (bigint)
--  - level point step between two feature slots  (bigint)
-- return:
--  - true, if new slots have been added, or false otherwise
CREATE OR REPLACE FUNCTION __SCHEMA__.update_feature_slots(BIGINT, INTEGER [])
        RETURNS BOOLEAN
        AS $$
        DECLARE
            query TEXT := '';
            loop_record RECORD;
            _user_id ALIAS FOR $1;
            level_delta ALIAS FOR $2;
            level_points INTEGER;
            level_next INTEGER;
            slots_total INTEGER;
            slots_new INTEGER;
        BEGIN
            SELECT
              INTO level_points points_sum
              FROM __SCHEMA__.users
             WHERE id = _user_id;
             
            SELECT
              INTO level_next CAST(data_value AS INTEGER)
              FROM __SCHEMA__.user_config
             WHERE user_id = _user_id
               AND data_name_id = (SELECT id FROM public.user_config_keys WHERE data_name = 'feature_next_point_limit');
               
            SELECT
              INTO slots_total CAST(data_value AS INTEGER)
              FROM __SCHEMA__.user_config
             WHERE user_id = _user_id
               AND data_name_id = (SELECT id FROM public.user_config_keys WHERE data_name = 'feature_total_update_slots');

            -- if nothing happens, user gets no new slots
            slots_new := 0;

            LOOP
                IF level_next IS NULL OR level_points < level_next THEN
                    EXIT;
                END IF;
                -- compute next level
                -- pg-arrays start at offset 1
                level_next := level_next + level_delta[1 + slots_total + slots_new];
                slots_new := slots_new + 1;
                
                --RAISE NOTICE '% % %', slots_new, level_next, level_points;
            END LOOP;

            IF slots_new > 0 THEN
                UPDATE __SCHEMA__.user_config
                   SET data_value = CAST(data_value AS INTEGER) + slots_new
                 WHERE user_id = _user_id
                   AND data_name_id = (SELECT id FROM public.user_config_keys WHERE data_name = 'feature_free_update_slots');
                   
                UPDATE __SCHEMA__.user_config
                   SET data_value = CAST(data_value AS INTEGER) + slots_new
                 WHERE user_id = _user_id
                   AND data_name_id = (SELECT id FROM public.user_config_keys WHERE data_name = 'feature_total_update_slots');
                   
                UPDATE __SCHEMA__.user_config
                   SET data_value = level_next
                 WHERE user_id = _user_id
                   AND data_name_id = (SELECT id FROM public.user_config_keys WHERE data_name = 'feature_next_point_limit');
                RETURN TRUE;
            ELSE
                RETURN FALSE;
            END IF;
        END;
        $$
        LANGUAGE 'plpgsql';


-- ###########################################################################
-- tries to find a path in the friendship-graph between two users
-- parameter:
--  - user id of user 1(bigint)
--  - user id of user 2 (bigint)
-- return:
--  - empty string, if no way found; string consisting of usernames on the
--    way, separated by #, in reverse order (string)
CREATE OR REPLACE FUNCTION __SCHEMA__.small_world(BIGINT, BIGINT)
        RETURNS TEXT
        AS $$
        DECLARE
            query TEXT := '';
            loop_record RECORD;
            dummy_record RECORD;
            small_world TEXT := '';
            uid BIGINT;
            level INT4 := 0;
            user_id ALIAS FOR $1;
            friend_id ALIAS FOR $2;
            table_name TEXT := 'bfs'|| user_id;
            dummy BOOLEAN := false;
        BEGIN
            -- create temporary table that holds nodes to visit
            query := 'CREATE TEMP TABLE '||table_name||' (uid BIGINT, level INT4, puid BIGINT, marked BOOLEAN) ON COMMIT DROP';
            EXECUTE query;
            -- start node is inserted
            query := 'INSERT INTO '||table_name||'
                           VALUES (' || user_id || ',0,0,true)';
            EXECUTE query;
            LOOP
                -- increase depth-level of BFS
                level:=level+1;
                -- if next for-loop is entered, dummy will be set to false
                dummy:=true;
                -- traverse all adjacent nodes
                FOR loop_record IN EXECUTE 'SELECT *
                                              FROM __SCHEMA__.user_friends AS f
                                             WHERE f.user_id IN (SELECT '||table_name||'.uid
                                                                   FROM '||table_name||'
                                                                  WHERE marked=true)' LOOP
                    -- RAISE NOTICE 'FID UID % %', loop_record.user_id, loop_record.friend_id;
                    -- if target found
                    IF loop_record.friend_id=friend_id THEN
                        -- set dummy to true, that outer loop will also be exited
                        dummy:=true;
                        uid:=loop_record.user_id;
                        -- start building return string
                        SELECT
                          INTO loop_record username
                          FROM __SCHEMA__.users
                         WHERE id=friend_id;
                        small_world:=loop_record.username;
                        -- break loop
                        EXIT;
                    ELSE
                        dummy:=false;
                        -- check, if new node has already been visited
                        query:='(SELECT '||table_name||'.uid
                                   FROM '||table_name||'
                                  WHERE marked=false
                                    AND '||table_name||'.uid='||loop_record.friend_id||')';
                        -- RAISE NOTICE 'query %', query;
                        FOR dummy_record IN EXECUTE query LOOP
                            dummy:=true;
                        END LOOP;
                        -- if not visited, add it into table
                        IF NOT dummy THEN
                            query := 'INSERT INTO '||table_name||'
                                           VALUES (' || loop_record.friend_id || ','|| level ||',' || loop_record.user_id || ',true)';
                            EXECUTE query;
                        ELSE
                            -- if node has been visited, only set dummy to false, so that outer loop will _not_ be exited
                            dummy:=false;
                        END IF;
                    END IF;
                END LOOP;
                -- exit loop, if required
                EXIT WHEN dummy;
                -- mark all nodes of last level as visited
                query := 'UPDATE '||table_name||'
                             SET marked=false
                           WHERE level='|| (level-1);
                EXECUTE query;
            END LOOP;
            -- reconstruct usernames of user-nodes on the found way
            WHILE uid>0 LOOP
                -- decrement depth level to search in
                level := level - 1;                
                SELECT INTO loop_record username FROM __SCHEMA__.users WHERE id=uid;
                small_world:=small_world || '#' || loop_record.username;
                FOR loop_record IN EXECUTE 'SELECT puid
                                              FROM '||table_name||'
                                             WHERE '||table_name||'.uid='||uid||'
                                               AND level='||level LOOP
                    uid:=loop_record.puid;
                END LOOP;
            END LOOP;

            RETURN small_world;
        END;
        $$
        LANGUAGE 'plpgsql';

-- ###########################################################################
-- returns id of randomly chosen user
-- parameter:
--  - random seed (double precision)
-- return:
--  - id of user
-- NOTE (linap): we don't this function any more
/*
CREATE OR REPLACE FUNCTION __SCHEMA__.get_random_user(double precision)
        RETURNS BIGINT
        AS $$
        DECLARE
            max_id BIGINT;
            ret_id BIGINT;
            loop_counter INT;
        BEGIN
            -- set random seed given by parameter
            SELECT INTO max_id setseed($1);

            -- initialize loop counter, so we can abort after a certain amount
            -- of iterations
            loop_counter := 0;
            LOOP
                -- get random id bound for users table
                SELECT
                  INTO max_id trunc(RANDOM()*last_value)-1
                  FROM __SCHEMA__.users_id_seq;
                -- RAISE NOTICE ' Q %', max_id;
                SELECT
                  INTO ret_id u.id
                  FROM __SCHEMA__.users AS u
                 WHERE u.id > max_id
                 LIMIT 1;

                -- exit loop, when maximal iteration number is reached
                loop_counter := loop_counter + 1;
                EXIT WHEN loop_counter > 5;
                -- exit loop, when a user is found
                EXIT WHEN ret_id > 0;
            END LOOP;

            IF ret_id > 0 THEN
                -- if random id was found, return that
                RETURN ret_id;
            ELSE
                -- if no random id was chosen, return max(id)
                SELECT
                  INTO ret_id MAX(id)
                  FROM __SCHEMA__.users;
                RETURN ret_id;
            END IF;
        END;
        $$
        LANGUAGE 'plpgsql';
*/
-- ###########################################################################
-- returns id of randomly chosen banner
-- the distribution is given by random_rate attribute
-- return:
--  - banner row
CREATE OR REPLACE FUNCTION __SCHEMA__.get_random_banner()
        RETURNS __SCHEMA__.banner
        AS $$
        DECLARE
            loop_record __SCHEMA__.banner%ROWTYPE;
            dummy_record RECORD;
        BEGIN
          -- need to determinde banner_id first
          -- separately, becuase of return type loop_record
            SELECT
              INTO loop_record b.*
              FROM __SCHEMA__.banner b,
                   __SCHEMA__.banner_select bs
             WHERE bs.rand > (SELECT RANDOM() OFFSET 0)
               AND b.id = bs.banner_id
          ORDER BY bs.rand ASC
             LIMIT 1;
            
            IF loop_record IS NULL THEN
                RETURN NULL;
            END IF;
            
            INSERT INTO __SCHEMA__.banner_show (banner_id) VALUES (loop_record.id);

            RETURN loop_record;
        END;
        $$
        LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION __SCHEMA__.update_random_banner(BIGINT)
        RETURNS boolean
        AS $$
        DECLARE
            query TEXT := '';
            loop_record RECORD;
            id ALIAS FOR $1;
            banner_views INTEGER := 0;
            random_rate INTEGER;
            banner_url TEXT;
            dest_url TEXT;
            attachment_id BIGINT;
            start_date date;
            end_date date;
            is_visible bool;
        BEGIN
            
            query := 'SELECT *
                        FROM __SCHEMA__.banner
                       WHERE id = ' || id || '';
            
            FOR loop_record IN EXECUTE query LOOP
                    banner_url := loop_record.banner_url;
                    dest_url := loop_record.dest_url;
                    attachment_id := loop_record.attachment_id;
                    random_rate := loop_record.random_rate;
                    start_date := loop_record.start_date;
                    end_date := loop_record.end_date;
                    is_visible := loop_record.is_visible;
            END LOOP;
            
            /*
            IF attachment_id IS NOT NULL THEN
            
                query:= 'SELECT path FROM __SCHEMA__.attachments WHERE id = '||attachment_id;
                FOR loop_record IN EXECUTE query LOOP
                    banner_url := loop_record.path;
                END LOOP;
            
            END IF;

            query := 'SELECT SUM(banner_views) AS banner_views 
                        FROM __SCHEMA__.banner_select 
                       WHERE banner_id = ' || id || '
                    GROUP BY banner_id';
                    
            FOR loop_record IN EXECUTE query LOOP
                    banner_views := loop_record.banner_views;
            END LOOP;
            */
            
            query := 'DELETE FROM __SCHEMA__.banner_select WHERE banner_id = ' || id;
            EXECUTE query;
                
            /*query := 'UPDATE __SCHEMA__.banner
                         SET banner_views = banner_views + ' || banner_views || '
                       WHERE id = '|| id;
            
            EXECUTE query;*/
            
            IF NOT is_visible THEN
               RETURN FALSE;
            END IF;
            
            IF start_date > current_date THEN
               RETURN FALSE;
            END IF;
            
            IF end_date < current_date THEN
               RETURN FALSE;
            END IF;
            
            FOR i IN 1..random_rate LOOP
                -- insert this banner into the select table
                query := 'INSERT INTO __SCHEMA__.banner_select
                                      (banner_id, rand)
                               VALUES (' || id || ',
                                       random())';
                EXECUTE query;
            END LOOP;
            
            -- ensure that we have a banner with maximal rand value
            UPDATE __SCHEMA__.banner_select
               SET rand = 1.0
             WHERE rand = COALESCE((SELECT MAX(rand) FROM __SCHEMA__.banner_select HAVING MAX(rand) < 1.0), 42.0);
        
            RETURN TRUE;
        END;
        $$
        LANGUAGE 'plpgsql';