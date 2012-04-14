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
-- contains some sql and plpgsql functions
--
-- $Id: public-functions.sql 6210 2008-07-25 17:29:44Z trehn $
--

-- ###########################################################################
-- ###########################################################################
-- SQL functions

-- ###########################################################################
-- check an email address
-- parameter:
--  - email address
-- return:
--  - FALSE (for invalid)
--  - TRUE (for simple validation is true or length is 0)
CREATE OR REPLACE FUNCTION public.check_email(CHAR)
        RETURNS BOOLEAN
        AS $$
            SELECT CASE
              WHEN ($1 IS NULL) THEN (FALSE)
              WHEN (LENGTH($1) = 0) THEN (TRUE)
              WHEN (LENGTH(TRIM($1)) > 5 AND STRPOS($1, '@') > 0) THEN (TRUE)
              ELSE (FALSE)
            END;
        $$
        LANGUAGE 'sql';
COMMENT ON FUNCTION public.check_email(CHAR) IS 'does basic syntax checks on the given email address';

-- ###########################################################################
-- will be called on insert from a trigger and set insert_at
-- parameter:
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.set_insert_at()
        RETURNS TRIGGER
        AS $$
        BEGIN
            NEW.insert_at = NOW();
            NEW.last_change = NOW();
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_insert_at() IS 'trigger function which will set insert_at and last_change on INSERT';
CREATE OR REPLACE FUNCTION public.set_only_insert_at()
        RETURNS TRIGGER
        AS $$
        BEGIN
            NEW.insert_at = NOW();
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_only_insert_at() IS 'trigger function which will set insert_at on INSERT';
CREATE OR REPLACE FUNCTION public.set_password_on_insert()
        RETURNS TRIGGER
        AS $$
        BEGIN
            NEW.original_password = NEW.password;
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_password_on_insert() IS 'trigger function which makes sure the original password is saved on INSERT';

-- ###########################################################################
-- will be called on update from a trigger and set last_change
-- parameter:
--  - none (NEW, OLD by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.set_last_change_at()
        RETURNS TRIGGER
        AS $$
        BEGIN
            IF NEW.insert_at IS NULL THEN
                NEW.last_change = NOW();
                NEW.insert_at = OLD.insert_at;
                RETURN NEW;
            ELSE
                NEW.last_change = NOW();
                --NEW.last_change = NEW.insert_at;
            END IF;
                    
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_last_change_at() IS 'trigger function which will set last_change and restore insert_at on UPDATE';
CREATE OR REPLACE FUNCTION public.set_only_last_change_at()
        RETURNS TRIGGER
        AS $$
        BEGIN
            NEW.last_change = NOW();
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_only_last_change_at() IS 'trigger function which will set last_change on UPDATE';

-- ###########################################################################
-- replace substrings in string
-- str_replace(search, replace, text)
-- parameter:
--  - search string
--  - replace string
--  - text
-- return:
--  - data type text
CREATE OR REPLACE FUNCTION public.str_replace (VARCHAR, VARCHAR, TEXT)
        RETURNS TEXT
        AS $$
        DECLARE
            i INTEGER;
            string_search ALIAS FOR $1;
            string_replace ALIAS FOR $2;
            string_text ALIAS FOR $3;
            string_return TEXT;
            string_substr TEXT;
        BEGIN
            string_return := '';
            FOR i IN 1..LENGTH(string_text) LOOP
                string_substr := SUBSTR(string_text, i, LENGTH(string_search));
                IF string_substr = string_search
                THEN
                    string_return := string_return || string_replace;
                    i := i + LENGTH(string_search) - 1;
                ELSE
                    string_return := string_return || SUBSTR(string_text, i, 1);
                END IF;
            END LOOP;

            RETURN string_return;
        END;
        $$
        LANGUAGE 'plpgsql' WITH (iscachable);
COMMENT ON FUNCTION public.str_replace (VARCHAR, VARCHAR, TEXT) IS 'does a str_replace(), this function is almost obsoleted in newer versions of PostgreSQL';

-- ###########################################################################
-- sleep some time
-- parameter:
--  - number seconds to sleep
-- return:
--  - current time
CREATE OR REPLACE FUNCTION public.sleep (INTEGER)
        RETURNS TIME
        AS $$
        DECLARE
            seconds ALIAS FOR $1;
            later TIME;
            thetime TIME;
        BEGIN
            thetime := timeofday()::timestamp;
            later := thetime + (seconds::text || ' seconds')::interval;
            LOOP
                IF thetime >= later THEN
                    EXIT;
                ELSE
                    thetime := timeofday()::timestamp;
                END IF;
            END loop;

            RETURN later;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.sleep (INTEGER) IS 'sleep some time';

-- ###########################################################################
-- will be called on insert from a trigger,
--   spread user data (and user config) to other tables
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.spread_user_data()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'spread_user_data(schemaname)';
            END IF;

            -- create values in other tables

            -- USER EXTRA DATA

            -- user data which we dont need on every page
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_extra_data (id) VALUES (' || NEW.id || ')';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;
            
            -- statistics about users
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_stats (user_id) VALUES (' || NEW.id || ')';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;
            
            -- privacy settings
            --query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_privacy (user_id, data_name_id, data_value) SELECT ' || NEW.id || ', id, vis_table.vis_id FROM public.user_privacy_keys, (SELECT id AS vis_id FROM public.details_visible WHERE name=''no one'') AS vis_table';
            -- RAISE NOTICE 'query: %', query;
            --EXECUTE query;
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_privacy (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_privacy_keys WHERE data_name=''birthdate''), (SELECT id FROM public.details_visible WHERE name=''all''))';
            EXECUTE query;
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_privacy (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_privacy_keys WHERE data_name=''instant_messanger''), (SELECT id FROM public.details_visible WHERE name=''logged in''))';
            EXECUTE query;
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_privacy (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_privacy_keys WHERE data_name=''real_name''), (SELECT id FROM public.details_visible WHERE name=''no one''))';
            EXECUTE query;
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_privacy (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_privacy_keys WHERE data_name=''address''), (SELECT id FROM public.details_visible WHERE name=''no one''))';
            EXECUTE query;
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_privacy (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_privacy_keys WHERE data_name=''mail_address''), (SELECT id FROM public.details_visible WHERE name=''no one''))';
            EXECUTE query;
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_privacy (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_privacy_keys WHERE data_name=''telephone''), (SELECT id FROM public.details_visible WHERE name=''no one''))';
            EXECUTE query;
            
            -- USER DATA

            -- points
            -- DEBUG: give everyone 5 points to play around
            --        [flow points are scaled by 10 (cf. PointSourceModel)]
            query := 'UPDATE ' || quote_ident(schema_name) || '.users SET points_sum = points_sum + 5, points_flow = points_flow + 5 * (SELECT config_value::INTEGER FROM ' || quote_ident(schema_name) || '.global_config WHERE config_name=''POINT_SOURCES_FLOW_MULTIPLICATOR'') WHERE id = ' || NEW.id || '';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;
            -- unihelp_stress
            -- query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''unihelp_stress''), 0)';
            -- EXECUTE query;
            -- counter for how often users profile has been viewed
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''profile_views''), 0)';
            EXECUTE query;

            -- number of guestbook entries in users guestbook
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''gb_entries''), 0)';
            EXECUTE query;
            -- number of guestbook entries in users guestbook that have not been read
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''gb_entries_unread''), 0)';
            EXECUTE query;
                     
            -- number of private messages
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''pms''), 0)';
            EXECUTE query;
            -- number of private messages send
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''pms_sent''), 0)';
            EXECUTE query;
            -- number of private messages that have not been read
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''pms_unread''), 0)';
            EXECUTE query;

            -- number of blog entries in users blog

            -- number of blog entries in users blog
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''blog_entries''), 0)';
            EXECUTE query;
            -- number of forum entries made by this user
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''forum_entries''), 0)';
            EXECUTE query;
            -- number of forum ratings made for this user
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''forum_rating_count''), 0)';
            EXECUTE query;
            -- value of forum ratings made for this user
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''forum_rating''), 0)';
            EXECUTE query;
                     
            -- count of uploaded attachments
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''attachment_count''), 0)';
            EXECUTE query;
            -- online activity_index
            --query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''activity_index''), 0)';
            --EXECUTE query;


            -- some data moved from user table
            -- public pgp key
            -- query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''public_pgp_key''), '''')';
            -- EXECUTE query;
            -- description
            -- query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''description''), '''')';
            -- EXECUTE query;

            -- some data moved from user table into contact table
            -- homepage
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_contact_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_contact_data_keys WHERE data_name=''homepage''), '''')';
            EXECUTE query;
            -- Jabber Messenger
            -- query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_contact_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_contact_data_keys WHERE data_name=''im_jabber''), '''')';
            -- EXECUTE query;
            -- ICQ Messenger
            -- query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_contact_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_contact_data_keys WHERE data_name=''im_icq''), '''')';
            -- EXECUTE query;
            -- MSN Messenger
            -- query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_contact_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_contact_data_keys WHERE data_name=''im_msn''), '''')';
            -- EXECUTE query;
            -- Yahoo Messenger
            -- query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_contact_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_contact_data_keys WHERE data_name=''im_yim''), '''')';
            -- EXECUTE query;
            -- Skype
            -- query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_contact_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_contact_data_keys WHERE data_name=''skype''), '''')';
            -- EXECUTE query;

            -- some stats about courses
            -- number of course uploads
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''course_file_uploads''), 0)';
            EXECUTE query;
            -- number of course downloads
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''course_file_downloads''), 0)';
            EXECUTE query;
            -- number of course materials that have been downloaded from this user
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_data (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''course_file_downloads_other''), 0)';
            EXECUTE query;

            -- USER CONFIG

            -- commented by linap, 10-07-2006; data not needed at the moment, can be inserted as optional value
            -- number of blog entries to be shown on one page
            --query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_config (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_config_keys WHERE data_name=''blog_entries_per_page''), 3)';
            --EXECUTE query;
            -- number of guestbook entries to be shown on one page
            --query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_config (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_config_keys WHERE data_name=''gb_entries_per_page''), 10)';
            --EXECUTE query;
            -- free slots for feature addition
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_config (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_config_keys WHERE data_name=''feature_free_update_slots''), 0)';
            EXECUTE query;
             query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_config (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_config_keys WHERE data_name=''feature_total_update_slots''), 0)';
            EXECUTE query;
            -- next point level limit for slot generation
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_config (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_config_keys WHERE data_name=''feature_next_point_limit''), 50)';
            EXECUTE query;
            -- flag whether guestbook is public
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_config (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_config_keys WHERE data_name=''gb_public''), ''f'')';
            EXECUTE query;
            -- flag whether guestbook is public
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_config (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_config_keys WHERE data_name=''diary_public''), ''t'')';
            EXECUTE query;
            -- flag whether guestbook is public
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_config (user_id, data_name_id, data_value) VALUES (' || NEW.id || ', (SELECT id::integer AS id FROM public.user_config_keys WHERE data_name=''friendlist_public''), ''f'')';
            EXECUTE query;
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.spread_user_data() IS 'trigger function which will create entries in other tables, if an user is created';

-- ###########################################################################
-- will be called on insert from a trigger, set default role membership
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.set_user_default_role()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'set_user_default_role(schemaname)';
            END IF;

            -- create values in user_role_membership table
            -- add to role internal_users by default
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_role_membership (user_id, role_id)
                            VALUES (' || NEW.id || ',
                                    (SELECT id FROM ' || quote_ident(schema_name) || '.user_roles WHERE name=''internal_users''))';
            EXECUTE query;
            
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_role_membership (user_id, role_id)
                            VALUES (' || NEW.id || ',
                                    (SELECT id FROM ' || quote_ident(schema_name) || '.user_roles WHERE name=''features''))';
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_user_default_role() IS 'trigger function which will set the users default role';

-- ###########################################################################
-- get MAX(id) from a table
-- parameter:
--  - schema name (string)
--  - table name (string)
-- return:
--  - data type bigint
--    returns 0 in case of no entries
CREATE OR REPLACE FUNCTION public.max_id(VARCHAR, VARCHAR)
        RETURNS BIGINT
        AS $$
        DECLARE
            schema_name ALIAS FOR $1;
            table_name ALIAS FOR $2;
            max_id INTEGER;
            query TEXT;
            loop_record RECORD;
        BEGIN
            query := 'SELECT CAST(MAX(id) AS BIGINT) AS max_id FROM ' || quote_ident(schema_name) || '.' || quote_ident(table_name);
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            max_id := loop_record.max_id;
            IF max_id > 0 THEN
                -- do nothing
            ELSE
                max_id := 0;
            END IF;

            RETURN max_id;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.max_id(VARCHAR, VARCHAR) IS 'this function returns the max it for a table, 0 if no entries';

-- test:
-- SELECT public.max_id('__SCHEMA__', 'users');

-- ###########################################################################
-- function for spreading usergroup data
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.copy_groups()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            is_visible VARCHAR;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'copy_groups(schemaname)';
            END IF;
            -- get value of is_visible (cannot casted to a string)
            -- see: /dosc/current/static/datatype-boolean.html
            IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                IF NEW.is_visible = TRUE THEN
                    is_visible := 'true';
                ELSE
                    is_visible := 'false';
                END IF;
            END IF;
            -- we do not care about the group id
            IF TG_OP = 'INSERT' THEN
                query := 'INSERT INTO ' || quote_ident(schema_name) || '.groups
                                       (name, title, description, 
                                       infopage_raw, infopage_parsed, is_visible)
                               VALUES (' || quote_literal(NEW.name) || ',
                                       ' || quote_literal(NEW.title) || ',
                                       ' || quote_literal(NEW.description) || ',
                                       ' || quote_literal(NEW.infopage_raw) || ',
                                       ' || quote_literal(NEW.infopage_parsed) || ',
                                       ' || is_visible || ')';
            END IF;
            IF TG_OP = 'UPDATE' THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.groups
                             SET name=' || quote_literal(NEW.name) || ',
                                 description=' || quote_literal(NEW.description) || ',
                                 title' || quote_literal(NEW.title) || ',
                                 infopage_raw' || quote_literal(NEW.infopage_raw) || ',
                                 infopage_parsed' || quote_literal(NEW.infopage_parsed) || ',
                                 is_visible=' || is_visible || '
                           WHERE name=' || quote_literal(OLD.name) || '';
            END IF;
            IF TG_OP = 'DELETE' THEN
                query := 'DELETE FROM ' || quote_ident(schema_name) || '.groups
                                 WHERE name=' || quote_literal(OLD.name) || '';
            END IF;
            IF LENGTH(query) > 0 THEN
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.copy_groups() IS 'trigger function which copies changes in the public group table into the schema tables';

-- ###########################################################################
-- function for spreading user_roles data
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.copy_user_roles()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            is_visible VARCHAR;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'copy_user_roles(schemaname)';
            END IF;
            -- we do not care about the role id
            IF TG_OP = 'INSERT' THEN
                query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_roles
                                       (name, description)
                                VALUES (' || quote_literal(NEW.name) || ',
                                        ' || quote_literal(NEW.description) || ')';
            END IF;
            IF TG_OP = 'UPDATE' THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_roles
                              SET name=' || quote_literal(NEW.name) || ',
                                  description=' || quote_literal(NEW.description) || '
                            WHERE name=' || quote_literal(OLD.name) || '';
            END IF;
            IF TG_OP = 'DELETE' THEN
                query := 'DELETE FROM ' || quote_ident(schema_name) || '.user_roles
                                 WHERE name=' || quote_literal(OLD.name) || '';
            END IF;
            IF LENGTH(query) > 0 THEN
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.copy_user_roles() IS 'trigger function which copies changes in the public role table into the schema tables';

-- ###########################################################################
-- function for spreading rights data
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.copy_rights()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            default_allowed VARCHAR;
            is_group_specific VARCHAR;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'copy_rights(schemaname)';
            END IF;
            -- get value of default_allowed (cannot casted to a string)
            -- see: /dosc/current/static/datatype-boolean.html
            IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                IF NEW.default_allowed = TRUE THEN
                    default_allowed := 'true';
                ELSE
                    default_allowed := 'false';
                END IF;
            END IF;
            -- get value of is_group_specific (cannot casted to a string)
            -- see: /dosc/current/static/datatype-boolean.html
            IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                IF NEW.is_group_specific = TRUE THEN
                    is_group_specific := 'true';
                ELSE
                    is_group_specific := 'false';
                END IF;
            END IF;
            -- we do not care about the group id
            IF TG_OP = 'INSERT' THEN
                query := 'INSERT INTO ' || quote_ident(schema_name) || '.rights
                                      (name, description, default_allowed,is_group_specific)
                               VALUES (' || quote_literal(NEW.name) || ',
                                       ' || quote_literal(NEW.description) || ',
                                       ' || default_allowed || ',
                                       ' || is_group_specific || ')';
            END IF;
            IF TG_OP = 'UPDATE' THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.rights
                             SET name=' || quote_literal(NEW.name) || ',
                                 description=' || quote_literal(NEW.description) || ',
                                 default_allowed=' || default_allowed || ',
                                 is_group_specific=' || is_group_specific || '
                           WHERE name=' || quote_literal(OLD.name) || '';
            END IF;
            IF TG_OP = 'DELETE' THEN
                query := 'DELETE FROM ' || quote_ident(schema_name) || '.rights
                                WHERE name=' || quote_literal(OLD.name) || '';
            END IF;
            IF LENGTH(query) > 0 THEN
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
            END IF;
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.copy_rights() IS 'trigger function which copies changes in the public rights table into the schema tables';

-- ###########################################################################
-- function for spreading global configuration data
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.copy_global_config()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'copy_global_config(schemaname)';
            END IF;

            -- we do not care about the group id
            IF TG_OP = 'INSERT' THEN
                query := 'INSERT INTO ' || quote_ident(schema_name) || '.global_config
                                      (config_name, config_value, description)
                               VALUES (' || quote_literal(NEW.config_name) || ',
                                       ' || quote_literal(NEW.config_value) || ',
                                       ' || quote_literal(NEW.description) || ')';
            ELSE
               RAISE EXCEPTION 'call copy_global_config() only with an ON INSERT trigger';
            END IF;

            IF LENGTH(query) > 0 THEN
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
            END IF;
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.copy_global_config() IS 'trigger function which copies changes in the public config table into the schema tables';

-- ###########################################################################
-- function for spreading global configuration data
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.copy_point_sources()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'copy_point_sources(schemaname)';
            END IF;

            -- we do not care about the group id
            IF TG_OP = 'INSERT' THEN
                query := 'INSERT INTO ' || quote_ident(schema_name) || '.point_sources
                                      (name, points_sum_gen, points_flow_gen)
                               VALUES (' || quote_literal(NEW.name) || ',
                                       ' || quote_literal(NEW.points_sum_gen) || ',
                                       ' || quote_literal(NEW.points_flow_gen) || ')';
            ELSE
               RAISE EXCEPTION 'call copy_point_sources() only with an ON INSERT trigger';
            END IF;

            IF LENGTH(query) > 0 THEN
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
            END IF;
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.copy_point_sources() IS 'trigger function which copies changes in the public config table into the schema tables';


-- ###########################################################################
-- function for protecting already used usernames
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.username_add()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'username_add(schemaname)';
            END IF;

            -- check username
            query := 'SELECT old_username, new_username
                        FROM ' || quote_ident(schema_name) || '.user_old_nicks
                       WHERE LOWER(old_username) = ' || quote_literal(LOWER(NEW.username)) || '
                          OR LOWER(new_username) = ' || quote_literal(LOWER(NEW.username)) || '';
            -- RAISE NOTICE 'query: %', query;
            -- workaround since EXECUTE is not able to return a result
            FOR loop_record IN EXECUTE query LOOP
                -- we should only get an exception, if the username was already in use
                RAISE EXCEPTION 'username was/is already in use';
            END LOOP;

            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.username_add() IS 'trigger function which protects old usernames';

-- ###########################################################################
-- function for logging username changes
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.username_changes()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'username_changes(schemaname)';
            END IF;

            -- check username
            IF LOWER(NEW.username) != LOWER(OLD.username) THEN

                -- check username
                query := 'SELECT old_username, new_username
                            FROM ' || quote_ident(schema_name) || '.user_old_nicks
                           WHERE LOWER(old_username) = ' || quote_literal(LOWER(NEW.username)) || '
                              OR LOWER(new_username) = ' || quote_literal(LOWER(NEW.username)) || '';
                -- RAISE NOTICE 'query: %', query;
                -- workaround since EXECUTE is not able to return a result
                FOR loop_record IN EXECUTE query LOOP
                    -- we should only get an exception, if the username was already in use
                    RAISE EXCEPTION 'username was/is already in use';
                END LOOP;

                query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_old_nicks
                                      (user_id, old_username, new_username)
                               VALUES (' || NEW.id || ',
                                       ' || quote_literal(OLD.username) || ',
                                       ' || quote_literal(NEW.username) || ')';
                IF LENGTH(query) > 0 THEN
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                END IF;
            END IF;

            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.username_changes() IS 'trigger function which copies username changes into a logging table';

-- ###########################################################################
-- function for logging username deletions
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.username_deleted()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'username_changes(schemaname)';
            END IF;

            -- save username
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_old_nicks
                                  (user_id, old_username, new_username)
                           VALUES (' || OLD.id || ',
                                   ' || quote_literal(OLD.username) || ',
                                   NULL)';
            IF LENGTH(query) > 0 THEN
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.username_deleted() IS 'trigger function which copies username deletes into a logging table';

-- ###########################################################################
-- function for protecting already used email addresses
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.uniemail_add()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'uniemail_add(schemaname)';
            END IF;

            -- an empty email string is not neccessarily wrong
            IF NEW.uni_email = '' THEN
                RETURN NEW;
            END IF;
            
            -- check email address
            query := 'SELECT email_address
                        FROM ' || quote_ident(schema_name) || '.user_old_email_addresses
                       WHERE LOWER(email_address) = ' || quote_literal(LOWER(NEW.uni_email)) || '';
            -- RAISE NOTICE 'query: %', query;
            -- workaround since EXECUTE is not able to return a result
            FOR loop_record IN EXECUTE query LOOP
                -- we should only get an exception, if the email address was already in use
                RAISE EXCEPTION 'uni_email was/is already in use';
            END LOOP;

            -- insert this email address into the log table
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_old_email_addresses
                                  (user_id, email_address)
                           VALUES (' || NEW.id || ',
                                   ' || quote_literal(NEW.uni_email) || ')';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;


            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.uniemail_add() IS 'trigger function which protects old email addresses';

-- ###########################################################################
-- function for logging email address changes
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.uniemail_changes()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'uniemail_changes(schemaname)';
            END IF;

            -- check uni email
            IF LOWER(NEW.uni_email) != LOWER(OLD.uni_email) THEN

                -- check email address
                query := 'SELECT email_address
                            FROM ' || quote_ident(schema_name) || '.user_old_email_addresses
                           WHERE LOWER(email_address) = ' || quote_literal(LOWER(NEW.uni_email)) || '';
                -- RAISE NOTICE 'query: %', query;
                -- workaround since EXECUTE is not able to return a result
                FOR loop_record IN EXECUTE query LOOP
                    -- we should only get an exception, if the email address was already in use
                    RAISE EXCEPTION 'uni_email was/is already in use';
                END LOOP;

                query := 'INSERT INTO ' || quote_ident(schema_name) || '.user_old_email_addresses
                                      (user_id, email_address)
                               VALUES (' || NEW.id || ',
                                       ' || quote_literal(NEW.uni_email) || ')';
                IF LENGTH(query) > 0 THEN
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                END IF;
            END IF;

            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.uniemail_changes() IS 'trigger function which copies uni_email changes into a logging table';

-- ###########################################################################
-- for wiki system
-- will be called on insert from a trigger and set is_current
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
/*
CREATE OR REPLACE FUNCTION public.set_current_in_wiki()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'set_current_in_wiki(schemaname)';
            END IF;
                query := 'UPDATE ' || quote_ident(schema_name) || '.wiki
                             SET is_current = FALSE
                           WHERE wiki_entry_name = ' || quote_literal(NEW.wiki_entry_name) || '
                             AND wiki_namespace = ' || quote_literal(NEW.wiki_namespace) || '
                             AND id < ' || NEW.id;
                EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_current_in_wiki() IS 'trigger function which sets timestamps in wiki';
*/

-- ###########################################################################
-- function for set primary study path
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.primary_study_path()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'primary_study_path(schemaname)';
            END IF;

            -- set any other study path to secondary
            IF TG_OP = 'UPDATE' THEN
                IF OLD.id = NEW.id AND OLD.user_id = NEW.user_id THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.study_path_per_student
                                 SET primary_course=FALSE
                               WHERE study_path_id != ' || NEW.study_path_id || '
                                 AND user_id = ' || NEW.user_id || '
                                 AND primary_course = TRUE';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                END IF;
            END IF;
            IF TG_OP = 'INSERT' THEN
               -- triggered action is only required, when a new primary course is added
                    IF NEW.primary_course = TRUE THEN
                   query := 'UPDATE ' || quote_ident(schema_name) || '.study_path_per_student
                                SET primary_course=FALSE
                              WHERE study_path_id != ' || NEW.study_path_id || '
                                AND user_id = ' || NEW.user_id || '
                                AND primary_course = TRUE';
                   -- RAISE NOTICE 'query: %', query;
                   EXECUTE query;
               END IF;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.primary_study_path() IS 'trigger function which sets the primary study path and resets all other study path for a student';


-- ###########################################################################
-- for forum system
-- will be called on inserting a new course
--   from a trigger and create a related forum
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.insert_course()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            ts TEXT; -- temp string
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'insert_course(schemaname)';
            END IF;

            IF TG_OP <> 'INSERT' THEN
                RAISE EXCEPTION 'insert_course() only on INSERT';
            END IF;
            
            ts := quote_literal(NEW.name);
            ts := substring (ts from 2 for length(ts)-2);
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.forum_fora
                                  (category_id, name, description_raw, enable_postings)
                           VALUES ( (SELECT id
                                       FROM ' || quote_ident(schema_name) || '.forum_categories
                                      WHERE name=''Fächer''),
                                  ' || quote_literal(NEW.name) || ', ''Forum zum Fach ' || ts || ''', TRUE)';
            --RAISE NOTICE 'query: %', query;
            EXECUTE query;
            
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.courses_data
                                  (course_id, forum_id)
                           VALUES
                                  (' || NEW.id || ', currval(''' || schema_name || '.forum_fora_id_seq''))';
            --RAISE NOTICE 'query: %', query;
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.insert_course() IS 'trigger function which inserts all relevant data for a new course';

-- for forum system
-- will be called on inserting a new user group
--   from a trigger and create a related forum
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.insert_user_group()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '''';
            schema_name TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'insert_user_group(schemaname)';
            END IF;

            IF TG_OP <> 'INSERT' THEN
                RAISE EXCEPTION 'insert_user_group() only on INSERT';
            END IF;

            query := 'INSERT INTO ' || quote_ident(schema_name) || '.forum_fora
                                  (category_id, forum_parent_id, name, description_raw, enable_postings)
                           VALUES ( (SELECT id
                                       FROM ' || quote_ident(schema_name) || '.forum_categories
                                      WHERE name=''Studentenleben''),
                                    (SELECT id
                                       FROM ' || quote_ident(schema_name) || '.forum_fora
                                      WHERE name=''Organisationen''),
                                  ' || quote_literal(NEW.name) || ', ''Forum zur Organisation ' || NEW.name || ' '',  TRUE)';
            EXECUTE query;
            
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.groups_forums
                                  (group_id, forum_id, is_default)
                           VALUES
                                  (' || NEW.id || ', currval(''' || schema_name || '.forum_fora_id_seq''), TRUE)';
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.insert_user_group() IS 'trigger function which inserts all relevant data for a new user group';


-- will be called on inserting a new thread entry into a thread
-- from a trigger
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_user_forum_entries()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_thread(schemaname)';
            END IF;

            IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                IF NEW.author_ext IS NOT NULL OR NEW.author_int IS NULL THEN
                    RETURN NULL;
                END IF;
            ELSIF TG_OP = 'DELETE' THEN
                IF OLD.author_ext IS NOT NULL OR OLD.author_int IS NULL THEN
                    RETURN NULL;
                END IF;
            END IF;

            IF TG_OP = 'INSERT' THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                             SET data_value = data_value::INTEGER + 1
                           WHERE user_id=' || quote_literal(NEW.author_int) || '
                             AND data_name_id = (SELECT id
                                                   FROM public.user_data_keys
                                                  WHERE data_name=''forum_entries'')';
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
            ELSIF TG_OP = 'DELETE' THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                             SET data_value = data_value::INTEGER - 1
                           WHERE user_id=' || quote_literal(OLD.author_int) || '
                             AND data_name_id = (SELECT id
                                                   FROM public.user_data_keys
                                                  WHERE data_name=''forum_entries'')';
                EXECUTE query;
            ELSIF TG_OP = 'UPDATE' THEN
                -- update author stats, if author changed
                IF NEW.author_int <> OLD.author_int THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = data_value::INTEGER + 1
                               WHERE user_id=' || quote_literal(NEW.author_int) || '
                                 AND data_name_id = (SELECT id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''forum_entries'')';
                    EXECUTE query;
                    
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = data_value::INTEGER - 1
                               WHERE user_id=' || quote_literal(OLD.author_int) || '
                                 AND data_name_id = (SELECT id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''forum_entries'')';
                    EXECUTE query;
               END IF;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_user_forum_entries() IS 'trigger function which updates the thread entry count for a user';

-- will be called on inserting or deleting a new thread rating
-- from a trigger
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_user_forum_rating()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            uid BIGINT;
            rating SMALLINT;
            number_delta BIGINT;
            loop_record RECORD;
            rating_count BIGINT;
            rating_value REAL;
    
        BEGIN

-- FIXME: check for performance

            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_user_forum_rating(schemaname)';
            END IF;
            
            IF TG_OP = 'INSERT' THEN
                uid := NEW.rated_user_id;
                rating := NEW.rating;
                number_delta := 1;
            ELSIF TG_OP = 'DELETE' THEN
                uid := OLD.rated_user_id;
                -- undo old rating
                rating := - OLD.rating;
                number_delta := -1;
            ELSE
                RAISE EXCEPTION 'dont call public.update_user_forum_rating on update';
            END IF;
            
            query := 'SELECT data_value::INTEGER AS data_value FROM ' || quote_ident(schema_name) || '.user_data
                       WHERE user_id='|| quote_literal(uid) || '
                         AND data_name_id = (SELECT id
                                               FROM public.user_data_keys
                                              WHERE data_name=''forum_rating_count'')';
            EXECUTE query INTO loop_record;
            rating_count := loop_record.data_value;

   -- we use this to handle a small count of evaluations. If there was only one user with +1, for example, we don't want to have a rating of 100%
   
   IF rating_count <=7 THEN
    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                         SET data_value = data_value::REAL + ' || rating || '
                       WHERE user_id=' || quote_literal(uid) || '
                         AND data_name_id = (SELECT id
                                               FROM public.user_data_keys
                                              WHERE data_name=''forum_rating'')';
    EXECUTE query;
  ELSE

    query := 'SELECT data_value::REAL AS data_value FROM ' || quote_ident(schema_name) || '.user_data
      WHERE user_id='|| quote_literal(uid) || '
                         AND data_name_id = (SELECT id
                                               FROM public.user_data_keys
                                              WHERE data_name=''forum_rating'')';
  EXECUTE query INTO loop_record;
  rating_value := loop_record.data_value;

  rating_value := ((rating_value * rating_count)/7+ rating)*7/(rating_count+number_delta);
  -- we use this function because:
  -- P_o: old points (rating_value), P_n: new Points, U-: Number of users with negativ evaluation, U+: Number of users with positiv evaluation
  -- U_o: old number of evaluations (rating_count), U_n: new number of evaluations
  --
  -- U_n= U_o + number_delta
  -- P_o = (U+ + U-)/U_o * 7     we scale with 7 because of the graphic
  -- ==> U+ + U- = P_o /7 * U_o and P_n /7 * U_n = U+ + U- + rating
  -- ==> P_n = ((P_o*U_o)/7 + rating ) / U_n

    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                         SET data_value = ' || rating_value || '
                       WHERE user_id=' || quote_literal(uid) || '
                         AND data_name_id = (SELECT id
                                               FROM public.user_data_keys
                                              WHERE data_name=''forum_rating'')';
  EXECUTE query;

  END IF;

     query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                         SET data_value = data_value::INTEGER + ' || number_delta || '
                       WHERE user_id=' || quote_literal(uid) || '
                         AND data_name_id = (SELECT id
                                               FROM public.user_data_keys
                                              WHERE data_name=''forum_rating_count'')';
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_user_forum_rating() IS 'trigger function which updates the forum rating for a user';

-- will be called on inserting a new news entry into a forum
--   from a trigger and insert the news thread and the first thread entry
--
-- important: BOOLEAN values can't concated with a string so we use the CASE expression
--
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.insert_news_forum_thread()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            newline TEXT := '\n\n';
            schema_name TEXT;
            seq_name TEXT;
            seq_name_entry TEXT;
            number_delta INT := 0;
            forum_id BIGINT := 0;
            searchAttachment TEXT;
            loop_record RECORD;
            max_id BIGINT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'insert_news_forum_thread(schemaname)';
            END IF;
            
            IF NEW.is_visible THEN
            
                seq_name := quote_ident(schema_name) || '.forum_threads_id_seq';
                query := 'INSERT INTO ' || quote_ident(schema_name) || '.forum_threads 
                                      (forum_id, caption, is_sticky, is_visible) 
                               VALUES ((SELECT id
                                          FROM ' || quote_ident(schema_name) || '.forum_fora
                                         WHERE name=''Bekanntmachungen''), ' || 
                                       quote_literal(NEW.caption) || ', ' || 
                                       CASE WHEN NEW.is_sticky
                                            THEN 'true'
                                            ELSE 'false'
                                       END || ', ' || 
                                       CASE WHEN NEW.is_visible
                                            THEN 'true'
                                            ELSE 'false'
                                       END || ')';
                EXECUTE query;
                
                seq_name_entry := quote_ident(schema_name) || '.forum_thread_entries_id_seq';
                query := 'INSERT INTO ' || quote_ident(schema_name) || '.forum_thread_entries
                                      (thread_id, caption, author_int, group_id, entry_raw, post_ip)
                               VALUES ((SELECT CURRVAL(''' || seq_name || ''')),
                                       ' || quote_literal(NEW.caption) || ',
                                       ' || NEW.author_int || ', ' || NEW.group_id || ',
                                       ' || quote_literal('[opener]' || NEW.opener_raw || '[/opener]' || newline || NEW.entry_raw) || ', ' || quote_literal(NEW.post_ip) || ')';

                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
                
                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_threads
                             SET first_entry = (SELECT CURRVAL(''' || seq_name_entry || ''')),
                                 last_entry = (SELECT CURRVAL(''' || seq_name_entry || '''))
                           WHERE id = (SELECT CURRVAL(''' || seq_name || '''))';
                EXECUTE query;
                
                NEW.thread_id := (SELECT CURRVAL(seq_name));
            
            END IF;
            
            -- searchAttachment := 'SELECT attachment_id FROM ' || quote_ident(schema_name)  || '.news_attachments WHERE id = ' || NEW.id;
            
            -- RAISE NOTICE 'query: %', query;
            -- only executed once, because of MAX()
            -- workaround since EXECUTE is not able to return a result
            --FOR loop_record IN EXECUTE searchAttachment LOOP
            --    max_id := loop_record.max_id;
            --END LOOP;
            --IF max_id > 0 THEN
                 -- add atachments to thread entrie
             --   query := 'INSERT INTO ' || quote_ident(schema_name) || '.forum_thread_entries_attachments
             --                         (entry_id, attachment_id)
             --                  VALUES ( ' ||  (SELECT CURRVAL(seq_name_entrie)) || ',
             --                          ( ' || searchAttachment || '))';

                -- RAISE NOTICE 'query: %', query;
             --   EXECUTE query;
             --END IF;            
            
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.insert_news_forum_thread() IS 'trigger function which insert the thread and the first entry in the forum for a news';

-- will be called on insert a news attachment
--   from a trigger
--
-- important: BOOLEAN values can't concated with a string so we use the CASE expression
--
-- parameter:
--  - schema name (string)
--  - none (NEW, OLD by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.insert_news_forum_thread_attachments()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;            
            number_delta INT := 0;
            entrie_id  BIGINT := 0;
            search_entry TEXT;
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'insert_news_forum_thread_attachments(schemaname)';
            END IF;
                        
            search_entry := 'SELECT id AS entry_id FROM ' || quote_ident(schema_name) || '.forum_thread_entries WHERE ' ||
                                    'thread_id = (SELECT thread_id FROM ' || quote_ident(schema_name) || '.news WHERE id = ' || NEW.entry_id || ')';
            EXECUTE search_entry INTO loop_record;
            entrie_id := loop_record.entry_id;
             -- add atachments to thread entrie
            query := 'INSERT INTO ' || quote_ident(schema_name) || '.forum_thread_entries_attachments
                                  (entry_id, attachment_id)
                           VALUES ( ' ||  entrie_id || ',
                                   ( ' || NEW.attachment_id || '))';

            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;
                      
            
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.insert_news_forum_thread_attachments() IS 'trigger function which insert the attachment of forum_thread_entry in the forum for a news';

-- will be called on delete a news attachment 
--   from a trigger and update the news entry
--
-- important: BOOLEAN values can't concated with a string so we use the CASE expression
--
-- parameter:
--  - schema name (string)
--  - none (NEW, OLD by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.delete_news_forum_thread_attachments()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;            
            number_delta INT := 0;
            entrie_id  BIGINT := 0;
            search_entry TEXT;
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'delete_news_forum_thread_attachments(schemaname)';
            END IF;
                        
            search_entry := 'SELECT id AS entry_id FROM ' || quote_ident(schema_name) || '.forum_thread_entries WHERE ' ||
                                    'thread_id = (SELECT thread_id FROM ' || quote_ident(schema_name) || '.news WHERE id = ' || OLD.entry_id || ')';
            EXECUTE search_entry INTO loop_record;
            entrie_id := loop_record.entry_id;
             -- add atachments to thread entrie
            query := 'DELETE FROM ' || quote_ident(schema_name) || '.forum_thread_entries_attachments
                                  WHERE entry_id= ' || entrie_id || 'and attachment_id=' || OLD.attachment_id || '';

            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;
               
            query := 'UPDATE ' || quote_ident(schema_name) || '.forum_thread_entries
                             SET entry_parsed = '''' WHERE id= ' || entrie_id;

            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;               
            
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.delete_news_forum_thread_attachments() IS 'trigger function which delete the attachment of forum_thread_entry in the forum for a news';

-- will be called on update a news into 
--   from a trigger and update the news thread
--
-- important: BOOLEAN values can't concated with a string so we use the CASE expression
--
-- parameter:
--  - schema name (string)
--  - none (NEW, OLD by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_news_forum_thread()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT := 0;
            forum_id BIGINT := 0;
            seq_name TEXT;
            seq_name_entry TEXT;
            newline TEXT := '\n\n';
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_news_forum_thread(schemaname)';
            END IF;
            
            -- if something change
            IF OLD.caption != NEW.caption OR OLD.opener_raw != NEW.opener_raw OR OLD.entry_raw != NEW.entry_raw OR OLD.is_sticky != NEW.is_sticky OR OLD.is_visible != NEW.is_visible THEN
                -- if thread allready exist update
                IF OLD.thread_id IS NOT NULL THEN
                
                    query := 'UPDATE ' || quote_ident(schema_name) || '.forum_threads 
                                 SET caption=' || quote_literal(NEW.caption) || ',
                                     is_sticky=' || CASE WHEN NEW.is_sticky
                                                         THEN 'true'
                                                         ELSE 'false'
                                                    END || ',
                                     is_visible=' || CASE WHEN NEW.is_visible
                                                          THEN 'true'
                                                          ELSE 'false'
                                                      END || '
                               WHERE id=' || quote_literal(NEW.thread_id);

                    EXECUTE query;
                
                    query := 'UPDATE ' || quote_ident(schema_name) || '.forum_thread_entries 
                                 SET last_update_time=now(), 
                                     post_ip=' || quote_literal(NEW.post_ip) || ', 
                                     entry_raw=' || quote_literal('[opener]' || NEW.opener_raw || '[/opener]' || '\n\n' || NEW.entry_raw) || ', 
                                     entry_parsed=''''
                               WHERE id= (SELECT first_entry
                                            FROM ' || quote_ident(schema_name) || '.forum_threads
                                           WHERE id=' || quote_literal(NEW.thread_id) || ')';
            
                    EXECUTE query;
                
                ELSE
                    -- thread dont exist an new are visible 
                    -- create thread
                    IF NEW.is_visible THEN
                    
                        seq_name := quote_ident(schema_name) || '.forum_threads_id_seq';
                        query := 'INSERT INTO ' || quote_ident(schema_name) || '.forum_threads 
                                              (forum_id, caption, is_sticky, is_visible) 
                                       VALUES ((SELECT id
                                                  FROM ' || quote_ident(schema_name) || '.forum_fora
                                                 WHERE name=''Bekanntmachungen''), ' || 
                                               quote_literal(NEW.caption) || ', ' || 
                                               CASE WHEN NEW.is_sticky
                                                    THEN 'true'
                                                    ELSE 'false'
                                               END || ', ' || 
                                               CASE WHEN NEW.is_visible
                                                    THEN 'true'
                                                    ELSE 'false'
                                               END || ')';
                        EXECUTE query;
                        
                        seq_name_entry := quote_ident(schema_name) || '.forum_thread_entries_id_seq';
                        query := 'INSERT INTO ' || quote_ident(schema_name) || '.forum_thread_entries
                                              (thread_id, caption, author_int, group_id, entry_raw, post_ip)
                                       VALUES ((SELECT CURRVAL(''' || seq_name || ''')),
                                               ' || quote_literal(NEW.caption) || ',
                                               ' || NEW.author_int || ', ' || NEW.group_id || ',
                                               ' || quote_literal('[opener]' || NEW.opener_raw || '[/opener]' || newline || NEW.entry_raw) || ', ' || quote_literal(NEW.post_ip) || ')';

                        -- RAISE NOTICE 'query: %', query;
                        EXECUTE query;
                        
                        query := 'UPDATE ' || quote_ident(schema_name) || '.forum_threads
                                     SET first_entry = (SELECT CURRVAL(''' || seq_name_entry || ''')),
                                         last_entry = (SELECT CURRVAL(''' || seq_name_entry || '''))
                                   WHERE id = (SELECT CURRVAL(''' || seq_name || '''))';
                        EXECUTE query;
                        
                        NEW.thread_id := (SELECT CURRVAL(seq_name));
                    
                    END IF;
                
                END IF;
            END IF;
                                
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_news_forum_thread() IS 'trigger function which insert the thread and the first entry in the forum for a news';

-- will be called on deletion of a news 
--   from a trigger and delete the news thread
-- parameter:
--  - schema name (string)
--  - none (OLD by trigger)
-- return:
--  - data type trigger
--CREATE OR REPLACE FUNCTION public.delete_news_forum_thread()
--        RETURNS TRIGGER
--        AS $$
--        DECLARE
--            query TEXT := '';
--            schema_name TEXT;
--            number_delta INT := 0;
--            forum_id BIGINT := 0;
--        BEGIN
--            -- validate the parameter
--            IF TG_NARGS = 1 THEN
--                schema_name := TG_ARGV[0];
--            ELSE
--                RAISE EXCEPTION 'delete_news_forum_thread(schemaname)';
--            END IF;
--            
--            -- check, if we have something to do
--            IF OLD.thread_id IS NULL THEN
--                RETURN NULL;
--            END IF;
--            
--            --query := 'SELECT public.delete_thread(' || quote_ident(schema_name) || ','|| quote_literal(OLD.thread_id) ||')';
--            --query := 'SELECT FROM ' || quote_ident(schema_name) || '.forum_threads 
--            --           WHERE id=' || quote_literal(OLD.thread_id);
--
--            EXECUTE query;
--
--            RETURN NULL;
--        END;
--        $$
--        LANGUAGE 'plpgsql';
--COMMENT ON FUNCTION public.delete_news_forum_thread() IS 'trigger function which delete the thread in the forum for a news';

-- will be called on inserting/deleting a new thread into a forum
--   from a trigger and substract a point for anonymous entry
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
-- CREATE OR REPLACE FUNCTION public.forum_anonymous_posting()
--         RETURNS TRIGGER
--        AS $$
--        DECLARE
--            query TEXT := '';
--            schema_name TEXT;
--            loop_record RECORD;
--        BEGIN
--            -- validate the parameter
--            IF TG_NARGS = 1 THEN
--                schema_name := TG_ARGV[0];
--            ELSE
--                RAISE EXCEPTION 'forum_anonymous_posting(schemaname)';
--            END IF;
--
--            IF NEW.author_int IS NULL THEN
--                -- posting is from an extern author
--                RETURN NULL;
--            END IF;

            -- get user data
/*            IF NEW.enable_anonymous = TRUE THEN
                query := 'SELECT points_flow, points_sum
                            FROM ' || quote_ident(schema_name) || '.users
                           WHERE id = ' || NEW.author_int;
                -- RAISE NOTICE 'query: %', query;
                -- workaround since EXECUTE is not able to return a result
                FOR loop_record IN EXECUTE query LOOP
                    IF loop_record.points_flow <= 0 THEN
                        RAISE EXCEPTION 'user has not enough points for an anonymous posting';
                    END IF;
                END LOOP;

                query := 'UPDATE ' || quote_ident(schema_name) || '.users
                             SET points_flow = points_flow + (SELECT points_flow_gen
                                                                FROM point_sources
                                                               WHERE name=''FORUM_ANONYMOUS_POSTING'')
                           WHERE id = ' || NEW.author_int;
                EXECUTE query;
            END IF;*/

--            RETURN NULL;
--        END;
--        $$
--        LANGUAGE 'plpgsql';
--COMMENT ON FUNCTION public.forum_anonymous_posting() IS 'trigger function which updates user points for an anonymous posting';


-- will be called on inserting/deleting a guestbook entry
--   from a trigger and update the counters in user_data
--
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_gb_entry()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT;
            uid BIGINT := 0;
            /*weight INT;
            points_normal INT := 0;
            points_admin INT := 0;
            points_normal_gen RECORD;
            points_admin_gen RECORD;*/
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_gb_entry(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                uid := NEW.user_id_for;
                /*weight := NEW.weighting;
                IF weight > 1 THEN
                    points_normal := 1;
                    points_admin := weight - 1;
                ELSIF weight < -1 THEN
                    points_normal := -1;
                    points_admin := weight + 1;
                ELSIF weight = 1 OR weight = -1 THEN
                    points_normal := weight;
                END IF;*/
                number_delta := 1;
            ELSIF TG_OP = 'DELETE' THEN
                uid := OLD.user_id_for;
                /*weight := OLD.weighting;
                IF weight > 1 THEN
                    points_normal := 1;
                    points_admin := weight - 1;
                ELSIF weight < -1 THEN
                    points_normal := -1;
                    points_admin := weight + 1;
                ELSE
                    points_normal := weight;
                END IF;*/
                number_delta := -1;
            END IF;
            /*-- RECORD assignment seems not possible in 7.4
            SELECT
              INTO points_normal_gen *
              FROM point_sources
             WHERE name = 'GB_ENTRY';
            SELECT
              INTO points_admin_gen *
              FROM point_sources
             WHERE name = 'GB_ENTRY_ADMIN';

            IF weight <> 0 THEN
                -- update users points in user_table
                query := 'UPDATE ' || quote_ident(schema_name) || '.users
                             SET points_flow = points_flow + ' || points_normal_gen.points_flow_gen || ' 
                                                              * ' || number_delta || ' * ' || points_normal || '
                                                           + ' || points_admin_gen.points_flow_gen || ' 
                                                              * '|| number_delta || ' * ' || points_admin || ',
                                 points_sum = points_sum + ' || points_normal_gen.points_sum_gen || ' 
                                                            * ' || number_delta || ' * ' || points_normal || '
                                                          + ' || points_admin_gen.points_sum_gen || ' 
                                                            * ' || number_delta || ' * ' || points_admin || '
                           WHERE id = ' || uid || '';
                EXECUTE query;
            END IF;*/

            -- update number of unread guestbook entries, if entry is inserted as unread or old entry
            -- has not been read yet
            IF TG_OP = 'INSERT' THEN
                IF NEW.is_unread THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                               WHERE user_id = ' || uid || '
                                 AND data_name_id = (SELECT id::integer AS id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''gb_entries_unread'') ';
                    EXECUTE query;
                END IF;
            ELSIF TG_OP = 'DELETE' THEN
                IF OLD.is_unread THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                               WHERE user_id = ' || uid || '
                                 AND data_name_id = (SELECT id::integer AS id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''gb_entries_unread'') ';
                    EXECUTE query;
                END IF;
            END IF;
               
               -- update total gb entry counter
               query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                             SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                           WHERE user_id = ' || uid || '
                             AND data_name_id = (SELECT id::integer AS id FROM public.user_data_keys WHERE data_name=''gb_entries'') ';
               EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_gb_entry() IS 'trigger function which updates gb counter';

-- will be called on updating a guestbook entry
--   from a trigger and update the counter of unread gb entries
--
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_gb_entry_unread()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT;
            uid BIGINT := 0;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_gb_entry_unread(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP <> 'UPDATE' THEN
                RAISE EXCEPTION 'update_counter_gb_entry_unread must only be called on UPDATE';
                RETURN NULL;
            END IF;
            
            IF NEW.user_id_for <> OLD.user_id_for THEN
                RAISE EXCEPTION 'changing guestbook entrys recipient is not supported by update_counter_gb_entry_unread';
            END IF;
            
            IF NEW.is_unread AND NOT OLD.is_unread THEN
                number_delta :=  1;
            ELSIF NOT NEW.is_unread AND OLD.is_unread THEN
                number_delta := -1;
            ELSE
                -- nothing to adjust here
                RETURN NULL;
            END IF;
            
            query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                            SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                        WHERE user_id = ' || NEW.user_id_for || '
                            AND data_name_id = (SELECT id::integer AS id
                                                FROM public.user_data_keys
                                                WHERE data_name=''gb_entries_unread'') ';
            EXECUTE query;
            
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_gb_entry_unread() IS 'trigger function which updates gb counter for unread entries';

-- will be called on inserting/deleting a private message
--   from a trigger and update the counters in user_data
--
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_pm()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT;
            uid BIGINT := 0;
            points_cost RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_pm(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                uid := NEW.user_id_for;
                number_delta := 1;
            ELSIF TG_OP = 'UPDATE' THEN
                uid := NEW.user_id_for;
            ELSIF TG_OP = 'DELETE' THEN
                uid := OLD.user_id_for;
                number_delta := -1;
            END IF;


            -- update number of unread pms, if pm is inserted as unread or old entry
            -- has not been read yet
            IF TG_OP = 'INSERT' THEN
                IF NEW.is_unread THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                               WHERE user_id = ' || uid || '
                                 AND data_name_id = (SELECT id::integer AS id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''pms_unread'') ';
                    EXECUTE query;
                END IF;
            ELSIF TG_OP = 'UPDATE' THEN
                -- check, if number of unread entries has changed
                IF NEW.is_unread AND NOT OLD.is_unread THEN
                    number_delta := 1;
                ELSIF NOT NEW.is_unread AND OLD.is_unread THEN
                    number_delta := -1;
                ELSE
                    number_delta := 0;
                END IF;
             
                -- if so, update counter
                IF number_delta <> 0 THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                               WHERE user_id = ' || uid || '
                                 AND data_name_id = (SELECT id::integer AS id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''pms_unread'') ';
                    EXECUTE query;
                END IF;
            ELSIF TG_OP = 'DELETE' THEN
                IF OLD.is_unread THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                               WHERE user_id = ' || uid || '
                                 AND data_name_id = (SELECT id::integer AS id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''pms_unread'') ';
                    EXECUTE query;
                END IF;
            END IF;
               
            -- update total pm counter on INSERT AND DELETE
            IF TG_OP <> 'UPDATE' THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                             SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                           WHERE user_id = ' || uid || '
                             AND data_name_id = (SELECT id::integer AS id
                                                   FROM public.user_data_keys
                                                  WHERE data_name=''pms'') ';
                EXECUTE query;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_pm() IS 'trigger function which updates pm counter';

-- will be called on inserting/deleting a private message
--   from a trigger and update the counters in user_data
--
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_pm_sent()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT;
            uid BIGINT := 0;
            points_cost RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_pm_sent(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                uid := NEW.author_int;
                number_delta := 1;
            ELSIF TG_OP = 'UPDATE' THEN
                IF NEW.author_has_deleted <> OLD.author_has_deleted THEN
                    uid := NEW.author_int;
                    IF NEW.author_has_deleted THEN
                        number_delta := -1;
                    ELSE
                        number_delta := 1;
                    END IF;
                ELSE
                    -- nothing to do here
                    RETURN NULL;
                END IF;
            ELSIF TG_OP = 'DELETE' THEN
                uid := OLD.author_int;
                number_delta := -1;
            END IF;
    
    
            IF uid IS NULL THEN
                -- if user is not internal, we have nothing to do here
                RETURN NULL;
            END IF;

            -- update counter
            query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                         SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                        WHERE user_id = ' || uid || '
                          AND data_name_id = (SELECT id::integer AS id
                                                FROM public.user_data_keys
                                               WHERE data_name=''pms_sent'') ';
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_pm_sent() IS 'trigger function which updates sent pm counter';

-- will be called on inserting/deleting a attachment
--   from a trigger and update the counters in user_data
--
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_attachment()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_attachment(schemaname)';
            END IF;

            IF TG_OP = 'INSERT' THEN
                IF NEW.author_id IS NOT NULL THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                SET data_value = CAST(data_value AS INTEGER)+1
                            WHERE user_id = ' || NEW.author_id || '
                                AND data_name_id = (SELECT id::integer AS id
                                                    FROM public.user_data_keys
                                                    WHERE data_name=''attachment_count'') ';
                    EXECUTE query;
                END IF;
            ELSIF TG_OP = 'DELETE' THEN
                IF OLD.author_id IS NOT NULL THEN
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                SET data_value = CAST(data_value AS INTEGER)-1
                            WHERE user_id = ' || OLD.author_id || '
                                AND data_name_id = (SELECT id::integer AS id
                                                    FROM public.user_data_keys
                                                    WHERE data_name=''attachment_count'') ';
                    EXECUTE query;
                END IF;
            END IF;
            
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_attachment() IS 'trigger function which updates attachment counter';

-- will be called on inserting/deleting a blog entry
--   from a trigger and update the counters in user_data
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_blog_entry()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT;
            uid BIGINT := 0;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_blog_entry(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                uid := NEW.user_id;
                number_delta := 1;
            ELSIF TG_OP = 'DELETE' THEN
                uid := OLD.user_id;
                number_delta := -1;
            END IF;

            -- update per-user counter in user_data
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                             SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                           WHERE user_id = ' || uid || '
                             AND data_name_id = (SELECT id::integer AS id
                                                   FROM public.user_data_keys
                                                  WHERE data_name=''blog_entries'') ';
                EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_blog_entry() IS 'trigger function which updates blog counter';

-- will be called on inserting/deleting a blog comment
--   from a trigger and update the counters in blog_advanced
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_blog_advanced_comment()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT;
            entry_id BIGINT := 0;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_blog_advanced_comment(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                entry_id := NEW.entry_id;
                number_delta := 1;
            ELSIF TG_OP = 'DELETE' THEN
                entry_id := OLD.entry_id;
                number_delta := -1;
            END IF;

            -- update per-user counter in user_data
                query := 'UPDATE ' || quote_ident(schema_name) || '.blog_advanced
                             SET comments = comments+('||number_delta||')
                           WHERE id = ' || entry_id;
                EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_blog_advanced_comment() IS 'trigger function which updates blog advanced comment counter';

-- will be called on inserting/deleting a blog trackback
--   from a trigger and update the counters in blog_advanced
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_blog_advanced_trackback()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT;
            entry_id BIGINT := 0;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_blog_advanced_trackback(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                entry_id := NEW.entry_id;
                number_delta := 1;
            ELSIF TG_OP = 'DELETE' THEN
                entry_id := OLD.entry_id;
                number_delta := -1;
            END IF;

            -- update per-user counter in user_data
                query := 'UPDATE ' || quote_ident(schema_name) || '.blog_advanced
                             SET trackbacks = trackbacks+('||number_delta||')
                           WHERE id = ' || entry_id;
                EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_blog_advanced_comment() IS 'trigger function which updates blog advanced trackback counter';

-- will be called on updating a gb/forum-entry to keep
--   update date up to date...
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.set_last_update()
        RETURNS TRIGGER
        AS $$
        BEGIN
            IF NEW.last_update_time IS NULL THEN
                NEW.last_update_time = NOW();
            END IF;
            
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_last_update() IS 'trigger function which updates the last_update_time column';

-- will be called on inserting a gb/forum-entry to set
--   date up to date...
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.set_entry_time()
        RETURNS TRIGGER
        AS $$
        BEGIN
            IF NEW.entry_time IS NULL THEN
                NEW.entry_time = NOW();
                NEW.last_update_time = NOW();
            END IF;
            
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_entry_time() IS 'trigger function which sets the entry_time column';

-- ###########################################################################
-- for course system
-- will be called on inserting/deleting a course file upload
--   from a trigger and update the stored counters
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_course_file_upload()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT := 0;
            author_id BIGINT := 0;
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_course_file_upload(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                author_id := NEW.author_id;
                number_delta := 1;
            ELSIF TG_OP = 'DELETE' THEN
                author_id := OLD.author_id;
                number_delta := -1;
            END IF;

            IF TG_OP <> 'UPDATE' THEN
                -- update per-user upload counter in user_data
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                             SET data_value = CAST(data_value AS INTEGER)+('||number_delta||')
                           WHERE user_id = ' || author_id || '
                             AND data_name_id = (SELECT id::integer AS id
                                                   FROM public.user_data_keys
                                                  WHERE data_name=''course_file_uploads'') ';
        
                EXECUTE query;
            ELSE -- UPDATE here
                IF NEW.author_id <> OLD.author_id THEN
                    -- lock table in exlusive mode
                    -- if the database is busy updating different users,
                    -- a deadlock is possible so we lock the table in
                    -- exclusive mode to make sure, no other connection is
                    -- changing this table
                    query := 'LOCK TABLE ' || quote_ident(schema_name) || '.user_data
                                      IN SHARE ROW EXCLUSIVE MODE';
                    EXECUTE query;
                    -- update per-user upload counter in user_data
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = CAST(data_value AS INTEGER) + 1
                               WHERE user_id = ' || NEW.author_id || '
                                 AND data_name_id = (SELECT id::integer AS id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''course_file_uploads'') ';
        
                    EXECUTE query;
                    
                    -- update per-user upload counter in user_data
                    query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                                 SET data_value = CAST(data_value AS INTEGER) - 1
                               WHERE user_id = ' || OLD.author_id || '
                                 AND data_name_id = (SELECT id::integer AS id
                                                       FROM public.user_data_keys
                                                      WHERE data_name=''course_file_uploads'') ';
        
                    EXECUTE query;
                END IF;
            END IF;

            IF TG_OP = 'INSERT' THEN
                -- INSERT a default median rating row
                -- first get all current rating categories
                query := 'SELECT id
                            FROM ' || quote_ident(schema_name) || '.courses_files_ratings_categories
                           WHERE type=''range'' ';
                FOR loop_record IN EXECUTE query LOOP
                    query := 'INSERT INTO ' || quote_ident(schema_name) || '.courses_files_ratings_median
                                          (file_id,rating_category_id,rating)
                                   VALUES (' || NEW.id || ',' || loop_record.id || ',0)';
                    EXECUTE query;
                END LOOP;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';

-- will be called on inserting a course file download
--   from a trigger and update the stored counters
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_counter_course_file_download()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            diff INTEGER;
            uid BIGINT;
            fid BIGINT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_course_file_download(schemaname)';
            END IF;

            
            IF TG_OP = 'INSERT' THEN
                uid := NEW.user_id;
                fid := NEW.file_id;
                diff := 1;
            ELSIF TG_OP = 'DELETE' THEN
                uid := OLD.user_id;
                fid := OLD.file_id;
                diff := -1;
            ELSE
                RAISE EXCEPTION 'call update_counter_course_file_download only on INSERT or DELETE';
            END IF;
            
            -- lock table in exlusive mode
            -- if the database is busy updating different users,
            -- a deadlock is possible so we lock the table in
            -- exclusive mode to make sure, no other connection is
            -- changing this table
            query := 'LOCK TABLE ' || quote_ident(schema_name) || '.courses_files,
                                 ' || quote_ident(schema_name) || '.user_data
                              IN ACCESS EXCLUSIVE MODE';
            EXECUTE query;

            -- update per-file counter in courses_files
            query := 'UPDATE ' || quote_ident(schema_name) || '.courses_files
                         SET download_number = download_number + (' || diff || ')
                       WHERE id = ' || fid;
            EXECUTE query;
            
          -- update per-user counter in user_data of downloader
            query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                         SET data_value = CAST(data_value AS INTEGER) + (' || diff || ')
                       WHERE user_id = ' || uid || '
                         AND data_name_id = (SELECT id::integer AS id
                                               FROM public.user_data_keys
                                              WHERE data_name=''course_file_downloads'') ';
            EXECUTE query;

            -- update per-user counter in user_data of file author
            query := 'UPDATE ' || quote_ident(schema_name) || '.user_data
                         SET data_value = CAST(data_value AS INTEGER) + (' || diff || ')
                       WHERE user_id = (SELECT author_id
                                          FROM ' || quote_ident(schema_name) || '.courses_files
                                         WHERE id=' || fid || ')
                         AND data_name_id = (SELECT id::integer AS id
                                               FROM public.user_data_keys
                                              WHERE data_name=''course_file_downloads_other'') ';
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';

-- will be called on inserting a course file rating in a certain category
--   from a trigger and update the stored rating median
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_course_file_rating()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            file_id BIGINT;
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_course_file_rating(schemaname)';
            END IF;

            --  differentiation between INSERT, UPDATE and DELETE
            IF TG_OP <> 'INSERT' THEN
                RAISE EXCEPTION 'call update_course_file_rating only on INSERT';
            END IF;

            -- determine corresponding file_id to this rating
            query := 'SELECT file_id
                        FROM ' || quote_ident(schema_name) || '.courses_files_ratings
                       WHERE id=' || NEW.rating_id;
            -- extract file_id from result
            EXECUTE query INTO loop_record;
            file_id := loop_record.file_id;

            -- update summarizing rating in courses_files_rating_median
            query := 'UPDATE ' || quote_ident(schema_name) || '.courses_files_ratings_median
                         SET rating = data.rat_avg,
                             rating_number = data.rat_nr
                        FROM (SELECT AVG(CAST(rs.rating AS SMALLINT)) AS rat_avg,
                                     COUNT(rs.rating) AS rat_nr
                                FROM ' || quote_ident(schema_name) || '.courses_files_ratings_single AS rs,
                                     ' || quote_ident(schema_name) || '.courses_files_ratings_categories AS rc
                               WHERE rs.rating_category_id = ' || NEW.rating_category_id || '
                                  AND rs.rating_category_id = rc.id
                                  AND rc.type = ''range''
                                  AND rs.rating_id IN (SELECT id
                                                         FROM ' || quote_ident(schema_name) || '.courses_files_ratings
                                                        WHERE file_id=' || file_id || ') ) AS data
                       WHERE file_id = ' || file_id || '
                         AND rating_category_id = ' || NEW.rating_category_id;
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';

-- will be called on inserting a course file rating
--   from a trigger and update the rated flag in download table
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.update_course_file_counter_rating()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_course_file_counter_rating(schemaname)';
            END IF;

            --  differentiation between INSERT and DELETE
            IF TG_OP <> 'INSERT' THEN
                RAISE EXCEPTION 'call update_course_file_counter_rating only on INSERT';
            END IF;

            -- update per-file counter in courses_files_information
            query := 'UPDATE ' || quote_ident(schema_name) || '.courses_files_downloads
                         SET already_rated = true
                       WHERE file_id = ' || NEW.file_id;
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';

-- will be called on inserting a course file
--   from a trigger and check if the file already exists
--   via hash
-- parameter:
--  - schema name (string)
--  - none (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.course_file_check_hash()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            duplicate BOOLEAN;
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'course_file_check_hash(schemaname)';
            END IF;

            --  differentiation between INSERT and DELETE
            IF TG_OP <> 'INSERT' THEN
                RAISE EXCEPTION 'call course_file_check_hash only on INSERT';
            END IF;

            query := 'SELECT COUNT(file_id) AS c
                        FROM '|| quote_ident(schema_name) ||'.courses_files_information
                       WHERE hash=' || quote_literal(NEW.hash);
            EXECUTE query INTO loop_record;
            duplicate = loop_record.c;

            -- do not allow duplicate files
            IF duplicate THEN
                RAISE EXCEPTION 'trying to INSERT duplicate file - hash already existing';
            END IF;

            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';

-- ###########################################################################
-- for attachments to all kind of entries
--
-- whenever an entry of attachment_table is going to be deleted, it is copied
--  to the attachments_old table before
CREATE OR REPLACE FUNCTION public.move_old_attachments()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'move_old_attachments(schemaname)';
            END IF;
                query := 'INSERT INTO ' || quote_ident(schema_name) || '.attachments_old
                                      (path,upload_time)
                               VALUES
                                      (' || quote_literal(OLD.path) || ',' || quote_literal(OLD.upload_time) || '
                                      )';
                EXECUTE query;
            RETURN OLD;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.move_old_attachments() IS 'trigger function which creates informations about deleted attachments (for filesystem cleanup)';


-- ###########################################################################
-- for friend table
--
-- whenever a new entry is added this function validates that this combination
--  does not already exist
--  if it does, just skip the insert
--  (unique without error)
CREATE OR REPLACE FUNCTION public.validate_add_new_friend()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            loop_record RECORD;
            number_entries INTEGER;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'validate_add_new_friend(schemaname)';
            END IF;
            -- build query for friend validation
            query := 'SELECT COUNT(id) AS nr_entries
                        FROM ' || quote_ident(schema_name) || '.user_friends
                       WHERE user_id=CAST(' || NEW.user_id || ' AS BIGINT)
                         AND friend_id=CAST(' || NEW.friend_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            number_entries := loop_record.nr_entries;
            IF number_entries > 0 THEN
                -- do nothing
                RAISE NOTICE 'is already a friend';
                RETURN NULL;
            ELSE
                -- insert new data
                RETURN NEW;
            END IF;
        END;
        $$
        LANGUAGE 'plpgsql';
-- whenever an entry is changed validate that user_id and friend_id
--  is not changed, only the type ...
CREATE OR REPLACE FUNCTION public.validate_update_friend()
        RETURNS TRIGGER
        AS $$
        BEGIN
            -- validate the data
            IF NEW.id != OLD.id THEN
                RAISE NOTICE 'You cannot change the id!';
                RETURN NULL;
            END IF;
            IF NEW.user_id != OLD.user_id THEN
                RAISE NOTICE 'You cannot change the user_id!';
                RETURN NULL;
            END IF;
            IF NEW.friend_id != OLD.friend_id THEN
                RAISE NOTICE 'You cannot change the friend_id!';
                RETURN NULL;
            END IF;
            -- everything ok, continue with update
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';

-- ###########################################################################
-- for user-online table
--
-- whenever a new entry is added this function skips insertion,
--  if user is already marked as online
--  (unique without error)
/*CREATE OR REPLACE FUNCTION public.validate_add_user_online()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            loop_record RECORD;
            number_entries INTEGER;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'validate_add_user_online(schemaname)';
            END IF;
            -- build query for user online check
            query := 'SELECT COUNT(id) AS nr_entries
                        FROM ' || quote_ident(schema_name) || '.user_online
                       WHERE user_id=CAST(' || NEW.user_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            -- workaround since EXECUTE is not able to return a result
            FOR loop_record IN EXECUTE query LOOP
                number_entries := loop_record.nr_entries;
            END LOOP;
            IF number_entries > 0 THEN
                -- do nothing
                RAISE NOTICE 'is already online';
                RETURN NULL;
            ELSE
                -- insert new data
                RETURN NEW;
            END IF;
        END;
        $$
        LANGUAGE 'plpgsql';*/

CREATE OR REPLACE FUNCTION public.update_online_user_stats()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            seconds_online INTEGER;
            seconds_today INTEGER;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_online_user_stats(schemaname)';
            END IF;
            
            -- if we have no user to operate on, we can return here
            IF OLD.user_id IS NULL THEN
                RETURN NULL;
            END IF;
            
            -- calculate online time in seconds and seconds of the current day
            seconds_online := CAST(EXTRACT(EPOCH FROM now()-OLD.online_since) AS INTEGER);
            seconds_today := CAST(EXTRACT(EPOCH FROM now()-date_trunc('days',now())) AS INTEGER);

            IF seconds_online <= seconds_today THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_stats
                             SET online_time[1] = online_time[1] + CAST(' || seconds_online || ' AS INT)
                           WHERE user_id=CAST(' || OLD.user_id || ' AS BIGINT)';
            ELSE
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_stats
                             SET online_time[1] = online_time[1] + CAST(' || seconds_today || ' AS INT),
                                 online_time[2] = online_time[2] + CAST(' || (seconds_online-seconds_today) || ' AS INT)
                           WHERE user_id=CAST(' || OLD.user_id || ' AS BIGINT)';
            END IF;
            EXECUTE query;
            
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_online_user_stats() IS 'trigger function which updates online time stats in user stats';

CREATE OR REPLACE FUNCTION public.update_point_user_stats()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            points_delta INTEGER;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_point_user_stats(schemaname)';
            END IF;
            
            -- check, if level points have changed
            points_delta := NEW.points_sum - OLD.points_sum;
            IF points_delta <> 0 THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.user_stats
                             SET level_points[1] = level_points[1] + CAST(' || points_delta || ' AS INT)
                           WHERE user_id=CAST(' || OLD.id || ' AS BIGINT)';
                EXECUTE query;
            END IF;
            
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_point_user_stats() IS 'trigger function which updates online time stats in user stats';

-- ###########################################################################
-- for *_attachment tables
--
-- whenever a entry-attachment-relationship is deleted, delete the attachments themselfs
CREATE OR REPLACE FUNCTION public.remove_attachments()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'remove_attachments(schemaname)';
            END IF;
            -- build query for attachment deletion
            query := 'DELETE FROM ' || quote_ident(schema_name) || '.attachments
                       WHERE id=' || OLD.attachment_id;
            EXECUTE query;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.remove_attachments() IS 'trigger function which deletes attachments after a removal from *_attachments table';

-- ###########################################################
-- for rights_user table
--
-- whenever a new entry is added this function transforms it into
--  an update operation, if user/right-combination is already there
--  (unique without error)
CREATE OR REPLACE FUNCTION public.validate_add_user_right()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            grant_string TEXT;
            loop_record RECORD;
            number_entries INTEGER;
            is_group_specific boolean;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'validate_add_user_right(schemaname)';
            END IF;
            
            -- no check for external users
            IF NEW.user_id IS NULL THEN
                RETURN NEW;
            END IF;
            
            -- build query for user/right-not-is_group_specific check
            query := 'SELECT is_group_specific
                        FROM ' || quote_ident(schema_name) || '.rights
                       WHERE id=CAST(' || NEW.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            is_group_specific := loop_record.is_group_specific;
            
            IF is_group_specific = true THEN
                RETURN NULL;
            END IF;
            
            -- build query for user/right-combination check
            query := 'SELECT COUNT(id) AS nr_entries
                        FROM ' || quote_ident(schema_name) || '.rights_user
                       WHERE user_id=CAST(' || NEW.user_id || ' AS BIGINT)
                         AND right_id=CAST(' || NEW.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            number_entries := loop_record.nr_entries;
            IF number_entries > 0 THEN
                -- convert boolean value into string
                IF NEW.right_granted THEN
                  grant_string:='true';
                ELSE
                  grant_string:='false';
                END IF;

                -- do an update instead of insertion
                query := 'UPDATE ' || quote_ident(schema_name) || '.rights_user
                             SET right_granted=' || grant_string || '
                           WHERE user_id=CAST(' || NEW.user_id || ' AS BIGINT)
                             AND right_id=CAST(' || NEW.right_id || ' AS BIGINT)';
                EXECUTE query;
                RETURN NULL;
            ELSE
                -- insert new data
                RETURN NEW;
            END IF;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.validate_add_user_right() IS 'trigger function which performs insert/update actions on user rights';

-- ###########################################################
-- for rights_role table
--
-- whenever a new entry is added this function transforms it into
--  an update operation, if role/right-combination is already there
--  (unique without error)
CREATE OR REPLACE FUNCTION public.validate_add_role_right()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            grant_string TEXT;
            loop_record RECORD;
            number_entries INTEGER;
            is_group_specific boolean;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'validate_add_role_right(schemaname)';
            END IF;
            
            -- build query for user/right-not-is_group_specific check
            query := 'SELECT is_group_specific
                        FROM ' || quote_ident(schema_name) || '.rights
                       WHERE id=CAST(' || NEW.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            is_group_specific := loop_record.is_group_specific;
            
            IF is_group_specific = true THEN
                RETURN NULL;
            END IF;
            
            -- build query for user/right-combination check
            query := 'SELECT COUNT(id) AS nr_entries
                        FROM ' || quote_ident(schema_name) || '.rights_role
                       WHERE role_id=CAST(' || NEW.role_id || ' AS BIGINT)
                         AND right_id=CAST(' || NEW.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            number_entries := loop_record.nr_entries;
            IF number_entries > 0 THEN
                -- convert boolean value into string
                IF NEW.right_granted THEN
                  grant_string:='true';
                ELSE
                  grant_string:='false';
                END IF;

                -- do an update instead of insertion
                query := 'UPDATE ' || quote_ident(schema_name) || '.rights_role
                             SET right_granted=' || grant_string || '
                           WHERE role_id=CAST(' || NEW.role_id || ' AS BIGINT)
                             AND right_id=CAST(' || NEW.right_id || ' AS BIGINT)';
                EXECUTE query;
                RETURN NULL;
            ELSE
                -- insert new data
                RETURN NEW;
            END IF;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.validate_add_user_right() IS 'trigger function which performs insert/update actions on role rights';


-- ###########################################################
-- for user-group-rights table
--
-- whenever a new entry is added this function transforms it into
--  an update operation, if user/right-combination is already there
--  (unique without error)
CREATE OR REPLACE FUNCTION public.validate_add_user_group_right()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            grant_string TEXT;
            loop_record RECORD;
            number_entries INTEGER;
            is_group_specific boolean;
            is_member INT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'validate_add_user_group_right(schemaname)';
            END IF;
            
            /* dont need any more - view validate_add_user_group_has_right
            -- build query for user/right-not-is_group_specific check
            query := 'SELECT is_group_specific
                        FROM ' || quote_ident(schema_name) || '.rights
                       WHERE id=CAST(' || NEW.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            -- workaround since EXECUTE is not able to return a result
            FOR loop_record IN EXECUTE query LOOP
                is_group_specific := loop_record.is_group_specific;
            END LOOP;
            
            IF is_group_specific = false THEN
                RETURN NULL;
            END IF;*/ 
            
            -- build query for user-is_group_member check
            query := 'SELECT count(id) AS is_member
                        FROM ' || quote_ident(schema_name) || '.user_group_membership
                       WHERE user_id=CAST(' || NEW.user_id || ' AS BIGINT)
                         AND group_id=CAST(' || NEW.group_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            is_member := loop_record.is_member;
            
            IF is_member = 0 THEN
                RETURN NULL;
            END IF;
            
            /*-- build query for user/right-combination check*/
            query := 'SELECT COUNT(id) AS nr_entries
                        FROM ' || quote_ident(schema_name) || '.rights_group
                       WHERE group_id=CAST(' || NEW.group_id || ' AS BIGINT)
                         AND right_id=CAST(' || NEW.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            number_entries := loop_record.nr_entries;
            
            IF number_entries = 0 THEN
                RETURN NULL;
            END IF;
            
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.validate_add_user_group_right() IS 'trigger function which performs insert/update actions on group rights';

-- ###########################################################
-- for user-group--has-rights table
--
-- whenever a new entry is added this function catch the error
-- if user/right-combination is already there
--  (unique without error)
CREATE OR REPLACE FUNCTION public.validate_add_user_group_has_right()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            grant_string TEXT;
            loop_record RECORD;
            number_entries INTEGER;
            is_group_specific boolean;
            is_member INT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'validate_add_user_group_has_right(schemaname)';
            END IF;
            
            -- build query for user/right-not-is_group_specific check
            query := 'SELECT is_group_specific
                        FROM ' || quote_ident(schema_name) || '.rights
                       WHERE id=CAST(' || NEW.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            is_group_specific := loop_record.is_group_specific;
            
            IF is_group_specific = false THEN
                RETURN NULL;
            END IF;
            
            /*-- build query for user/right-combination check */
            query := 'SELECT COUNT(id) AS nr_entries
                        FROM ' || quote_ident(schema_name) || '.rights_group
                       WHERE group_id=CAST(' || NEW.group_id || ' AS BIGINT)
                         AND right_id=CAST(' || NEW.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            number_entries := loop_record.nr_entries;
            IF number_entries > 0 THEN
                RETURN NULL;
            END IF;
            
            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.validate_add_user_group_has_right() IS 'trigger function which performs insert/update actions on group has rights';

-- ###########################################################
-- for user_group_membership table
--
-- whenever a new entry is added this function catch the error
-- if user/right-combination is already there
--  (unique without error)
CREATE OR REPLACE FUNCTION public.del_user_group_has_right()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            grant_string TEXT;
            loop_record RECORD;
            number_entries INTEGER;
            is_group_specific boolean;
            is_member INT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'del_user_group_has_right(schemaname)';
            END IF;
            
           /*-- build query for user/right-combination check */
            query := 'DELETE
                        FROM ' || quote_ident(schema_name) || '.rights_user_group
                       WHERE group_id=CAST(' || OLD.group_id || ' AS BIGINT)
                         AND right_id=CAST(' || OLD.right_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;

            RETURN OLD;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.del_user_group_has_right() IS 'trigger function which performs del actions on group has rights';

-- ###########################################################
-- for user-group--has-rights table
--
-- clean rights if user remove from group
CREATE OR REPLACE FUNCTION public.user_group_membership_delete()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            grant_string TEXT;
            loop_record RECORD;
            number_entries INTEGER;
            is_group_specific boolean;
            is_member INT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'user_group_membership_delete(schemaname)';
            END IF;
            
           /*-- build query for user/right-combination check */
            query := 'DELETE
                        FROM ' || quote_ident(schema_name) || '.rights_user_group
                       WHERE group_id=CAST(' || OLD.group_id || ' AS BIGINT)
                         AND user_id=CAST(' || OLD.user_id || ' AS BIGINT)';
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query;

            RETURN OLD;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.user_group_membership_delete() IS 'trigger function which performs del actions on user group membership';


CREATE OR REPLACE FUNCTION public.insert_into_random_user(schema_name TEXT, insert_id INT)
        RETURNS INTEGER
        AS $$
        DECLARE
            query TEXT;
            loop_record RECORD;
            loop_record2 RECORD;
        BEGIN
            -- first lock tables to make sure, we are the only one to change the table
--             LOCK TABLE ringbuffer.rb, ringbuffer.rb_data
--                     IN SHARE ROW EXCLUSIVE MODE;
            -- check that this entry is not yet in the table
            query := 'SELECT COUNT(id) AS number_count
                        FROM ' || quote_ident(schema_name) || '.random_user
                       WHERE user_id = ' || quote_literal(insert_id) || '';
            EXECUTE query INTO loop_record;
            IF loop_record.number_count > 0
            THEN
                -- RAISE NOTICE 'user is already in ringbuffer: %', insert_id;
            ELSE
                -- now check, if the data table is empty
                query := 'SELECT *
                            FROM ' || quote_ident(schema_name) || '.random_user_data
                           LIMIT 1
                             FOR UPDATE';
                EXECUTE query INTO loop_record;
                -- RAISE NOTICE 'max id: %', loop_record.max_id;
                IF loop_record.max_id > 0
                THEN
                    -- need the current last record
                    query := 'SELECT *
                                FROM ' || quote_ident(schema_name) || '.random_user
                               WHERE id = ' || quote_literal(loop_record.max_id) || '
                                 FOR UPDATE';
                    EXECUTE query INTO loop_record2;
                    IF loop_record2.next_id < 1
                    THEN
                        RAISE EXCEPTION 'should have a next_id, but missing it';
                    END IF;
                    -- insert new record at end of table
                    -- RAISE NOTICE 'insert at end of table with id: %', loop_record.max_id + 1;
                    query := 'INSERT INTO ' || quote_ident(schema_name) || '.random_user
                                          (id, user_id, next_id)
                                   VALUES (' || quote_literal(loop_record.max_id + 1) || ',
                                           ' || quote_literal(insert_id) || ',
                                           ' || quote_literal(loop_record2.next_id) || ')';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                    -- update last record
                    query := 'UPDATE ' || quote_ident(schema_name) || '.random_user
                                 SET next_id=' || quote_literal(loop_record.max_id + 1) || '
                               WHERE id=' || quote_literal(loop_record.max_id) || '';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                    -- update pointer table
                    query := 'UPDATE ' || quote_ident(schema_name) || '.random_user_data
                                 SET max_id = ' || quote_literal(loop_record.max_id + 1) || '
                               WHERE id = 1';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                ELSE
                    -- just insert as first value
                    -- RAISE NOTICE 'insert first value into ringbuffer ...';
                    query := 'INSERT INTO ' || quote_ident(schema_name) || '.random_user
                                          (id, user_id, next_id)
                                   VALUES (1, ' || quote_literal(insert_id) || ', 1)';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                    -- update pointer table
                    query := 'UPDATE ' || quote_ident(schema_name) || '.random_user_data
                                 SET max_id = ' || quote_literal(loop_record.max_id + 1) || ',
                                     ru_id = 1
                               WHERE id = 1';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                END IF;
            END IF;

            RETURN 1;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.insert_into_random_user(schema_name TEXT, insert_id INT) IS 'this function inserts an userid into the ringbuffer';

-- IMPORTANT: use this function in a subselect, something like:
-- SELECT * FROM user WHERE id=(SELECT get_next_random_user());
-- otherwise this function will be called for comparing every id and so will
-- an unpredictable result
CREATE OR REPLACE FUNCTION public.get_next_random_user(schema_name TEXT)
        RETURNS INTEGER
        AS $$
        DECLARE
            query TEXT;
            loop_record RECORD;
        BEGIN
            -- first lock tables to make sure, we are the only one to change the table
--             LOCK TABLE ringbuffer.rb, ringbuffer.rb_data
--                     IN SHARE ROW EXCLUSIVE MODE;
            -- now check, if the table is empty
            query := 'SELECT max_id, ru_id
                        FROM ' || quote_ident(schema_name) || '.random_user_data
                       LIMIT 1
                         FOR UPDATE';
            EXECUTE query INTO loop_record;
            -- RAISE NOTICE 'max id: %', loop_record.max_id;
            IF loop_record.max_id = 0
            THEN
                -- RAISE NOTICE 'ringbuffer is empty!';
                RETURN NULL;
            ELSE
                -- get the value the pointer is currently on
                -- RAISE NOTICE 'get value from ringbuffer ...';
--                 query := 'SELECT *
--                             FROM ' || quote_ident(schema_name) || '.random_user
--                            WHERE id = (SELECT ru_id
--                                          FROM ' || quote_ident(schema_name) || '.random_user_data
--                                         LIMIT 1)
--                              FOR UPDATE';
                query := 'SELECT *
                            FROM ' || quote_ident(schema_name) || '.random_user
                           WHERE id = ' || loop_record.ru_id || '
                             FOR UPDATE';
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query INTO loop_record;
                -- RAISE NOTICE 'current user id: %', loop_record.user_id;
                -- RAISE NOTICE 'next id: %', loop_record.next_id;
                -- update pointer table
                query := 'UPDATE ' || quote_ident(schema_name) || '.random_user_data
                             SET ru_id=' || quote_literal(loop_record.next_id) || '
                           WHERE id = 1';
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
                -- return result
                -- RAISE NOTICE 'res: %', loop_record.user_id;
                RETURN loop_record.user_id;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.get_next_random_user(schema_name TEXT) IS 'this function returns the next userid from the ringbuffer';

CREATE OR REPLACE FUNCTION public.delete_from_random_user(schema_name TEXT, delete_id INT)
        RETURNS INTEGER
        AS $$
        DECLARE
            query TEXT;
            loop_record_data RECORD;
            loop_record RECORD;
            loop_record2 RECORD;
        BEGIN
            -- first lock tables to make sure, we are the only one to change the table
--             LOCK TABLE ringbuffer.rb, ringbuffer.rb_data
--                     IN SHARE ROW EXCLUSIVE MODE;
            -- now check, if the table is empty
            query := 'SELECT *
                        FROM ' || quote_ident(schema_name) || '.random_user_data
                       LIMIT 1
                         FOR UPDATE';
            -- RAISE NOTICE 'max id: %', loop_record.max_id;
            EXECUTE query INTO loop_record_data;
            -- RAISE NOTICE 'max id: %', loop_record.max_id;
            IF loop_record_data.max_id IS NULL
            THEN
                -- RAISE NOTICE 'ringbuffer is empty!';
                RETURN NULL;
            ELSE
                -- get the value which should be deleted
                -- RAISE NOTICE 'delete value from ringbuffer ...';
                query := 'SELECT *
                            FROM ' || quote_ident(schema_name) || '.random_user
                           WHERE user_id = ' || quote_literal(delete_id) || '
                             FOR UPDATE';
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query INTO loop_record;
                IF loop_record.id IS NULL
                THEN
                    -- nothing to delete
                    -- RAISE NOTICE 'nothing to delete ...';
                    RETURN NULL;
                END IF;
                IF loop_record.id = loop_record.next_id
                THEN
                    -- we only have this value, update pointer table
                    -- (next_id == id)
                    -- RAISE NOTICE 'delete last entry from ringbuffer (%) ...', loop_record.id;
                    query := 'UPDATE ' || quote_ident(schema_name) || '.random_user_data
                                 SET ru_id = NULL,
                                     max_id = 0
                               WHERE id = 1';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                ELSE
                    -- RAISE NOTICE 'delete an entry from ringbuffer (%) ...', loop_record.id;
                    -- get the value which is referring us
                    query := 'SELECT *
                                FROM ' || quote_ident(schema_name) || '.random_user
                               WHERE next_id = ' || quote_literal(loop_record.id) || '
                                 FOR UPDATE';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query INTO loop_record2;
                    -- now twist the pointer from last to the next record
                    query := 'UPDATE ' || quote_ident(schema_name) || '.random_user
                                 SET next_id = ' || quote_literal(loop_record.next_id) || '
                               WHERE id= ' || quote_literal(loop_record2.id) || '';
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query;
                    IF loop_record_data.ru_id = loop_record.id
                    THEN
                        -- we are deleting the next entry
                        query := 'UPDATE ' || quote_ident(schema_name) || '.random_user_data
                                     SET ru_id = ' || quote_literal(loop_record.next_id) || ',
                                         max_id = (SELECT MAX(next_id)
                                                     FROM ' || quote_ident(schema_name) || '.random_user)
                                   WHERE id = 1';
                        -- RAISE NOTICE 'query: %', query;
                        EXECUTE query;
                    ELSE
                        -- we are deleting another entry
                        query := 'UPDATE ' || quote_ident(schema_name) || '.random_user_data
                                     SET max_id = (SELECT MAX(next_id)
                                                     FROM ' || quote_ident(schema_name) || '.random_user)
                                   WHERE id = 1';
                        -- RAISE NOTICE 'query: %', query;
                        EXECUTE query;
                    END IF;
                END IF;
                -- delete the dataset
                query := 'DELETE FROM ' || quote_ident(schema_name) || '.random_user
                           WHERE id = ' || quote_literal(loop_record.id) || '';
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
                -- return deleted result
                RETURN loop_record.user_id;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.delete_from_random_user(schema_name TEXT, delete_id INT) IS 'this function deletes an userid from the ringbuffer';

-- ###########################################################################
-- function for adding user into randum_user
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.random_user_add()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'random_user_add(schemaname)';
            END IF;

            -- check flags first
            IF NEW.flag_invisible OR NOT NEW.flag_active OR NOT NEW.flag_activated THEN
                query := 'SELECT public.delete_from_random_user(' || quote_literal(schema_name) || ',
                                                                    ' || quote_literal(NEW.id) || ')';
                EXECUTE query;
                -- we can abort function here, insertion MUST NOT take place
                RETURN NULL;
            END IF;
            
            IF TG_OP = 'UPDATE' THEN
                -- if userpic has been changed, delete from queue
                IF NEW.userpic_file <> OLD.userpic_file THEN
                    query := 'SELECT public.delete_from_random_user(' || quote_literal(schema_name) || ',
                                                                    ' || quote_literal(NEW.id) || ')';
                    EXECUTE query;
                END IF;
                -- if new userpic has been added or existing one changed, add to queue
                IF NEW.userpic_file <> OLD.userpic_file AND NEW.userpic_file <> '' THEN
                    query := 'SELECT public.insert_into_random_user(' || quote_literal(schema_name) || ',
                                                                ' || quote_literal(NEW.id) || ')';
                    EXECUTE query;
                END IF;
            ELSIF TG_OP = 'INSERT' AND NEW.userpic_file <> '' THEN
                -- go safe way and try to delete existing entries
                query := 'SELECT public.delete_from_random_user(' || quote_literal(schema_name) || ',
                                                                    ' || quote_literal(NEW.id) || ')';
                EXECUTE query;
                
                -- if user with non empty userpic has been added, add him to queue
                query := 'SELECT public.insert_into_random_user(' || quote_literal(schema_name) || ',
                                                                ' || quote_literal(NEW.id) || ')';
                EXECUTE query;
            END IF;

            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.random_user_add() IS 'trigger function which add a user to the random user table';


-- #########################################################################
-- function for setting user random value
CREATE OR REPLACE FUNCTION public.set_user_random()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'set_user_random(schemaname)';
            END IF;

            -- check flags first
            IF NEW.flag_invisible OR NOT NEW.flag_active OR NOT NEW.flag_activated THEN
                NEW.rand := 0.0;
                RETURN NEW;
            END IF;
            
            IF TG_OP = 'UPDATE' THEN
                -- if new userpic has been added or existing one changed, add to queue
                IF NEW.userpic_file <> OLD.userpic_file THEN
                    IF NEW.userpic_file <> '' THEN
                        NEW.rand := RANDOM();
                    ELSE
                        NEW.rand := 0.0;
                    END IF;
                END IF;
            ELSIF TG_OP = 'INSERT' AND NEW.userpic_file <> '' THEN
                NEW.rand := RANDOM();
            END IF;

            RETURN NEW;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.set_user_random() IS 'trigger function which add changes user''s random value';


-- ###########################################################################
-- function for deleting user from randum_user
-- parameter:
--  - schema name (as string)
--  - (NEW by trigger)
-- return:
--  - data type trigger
CREATE OR REPLACE FUNCTION public.random_user_del()
        RETURNS TRIGGER
        AS $$
        DECLARE
            schema_name TEXT;
            query TEXT := '';
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'random_user_del(schemaname)';
            END IF;
            -- delete old value on update
            IF TG_OP = 'DELETE'
            THEN
                query := 'SELECT public.delete_from_random_user(' || quote_literal(schema_name) || ',
                                                                ' || OLD.id || ')';
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query;
            END IF;

            RETURN OLD;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.random_user_del() IS 'trigger function which deletes a user fromo the random user table';


-- for group-rights table
--
-- whenever a new entry is added, this function _deletes_ all
--  other right entries that conflict with the new entry via UNIQUE constraint
--  (unique without error)
-- CREATE OR REPLACE FUNCTION public.validate_add_role_right()
--         RETURNS TRIGGER
--         AS $$
--         DECLARE
--            query TEXT := '''';
--            schema_name TEXT;
--            grant_string TEXT;
--            loop_record RECORD;
--            number_entries INTEGER;
--        BEGIN
--            -- validate the parameter
--            IF TG_NARGS = 1 THEN
--                schema_name := TG_ARGV[0];
--            ELSE
--                RAISE EXCEPTION ''validate_add_role_right(schemaname)'';
--            END IF;
--            -- build query for deletion
--            query := ''DELETE FROM '' || quote_ident(schema_name) || ''.rights_role
--                             WHERE right_id=CAST('' || NEW.right_id || '' AS BIGINT)'';
--            EXECUTE query;
--            -- insert new data
--            RETURN NEW;
--        END;
--        $$
--        LANGUAGE 'plpgsql';

-- updates statistics (#entries, #threads) for one forum, and calls itself recursively
-- to update parent forums
-- parameter:
--   - schema name
--   - start forum id (bigint)
--   - delta for #threads (int)
--   - delta for #entries (int)
-- returns:
--   - '' (text)
CREATE OR REPLACE FUNCTION public.update_counters_for_forum(TEXT, BIGINT, INT, INT)
        RETURNS TEXT
        AS $$
        DECLARE
            query TEXT := '';
            schema_name ALIAS FOR $1;
            forum_id ALIAS FOR $2;
            number_delta_threads ALIAS FOR $3;
            number_delta_entries ALIAS FOR $4;
            loop_record RECORD;
            forum_parent_id BIGINT;
        BEGIN
            query := 'UPDATE ' || quote_ident(schema_name) || '.forum_fora
                         SET number_of_threads = number_of_threads + ('|| number_delta_threads ||'),
                             number_of_entries = number_of_entries + ('|| number_delta_entries ||')
                       WHERE id = ' || quote_literal(forum_id);
            EXECUTE query;
        
            query := 'SELECT forum_parent_id FROM ' || schema_name || '.forum_fora WHERE id = ' || forum_id;
            EXECUTE query INTO loop_record;
            forum_parent_id := loop_record.forum_parent_id;
        
            IF forum_parent_id IS NOT NULL THEN
                query := 'SELECT public.update_counters_for_forum(''' || schema_name || ''',' ||  forum_parent_id ||', ' || number_delta_threads || ', ' || number_delta_entries || ')';
                EXECUTE query;
            END IF;
        
            RETURN '';
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counters_for_forum(TEXT, BIGINT, INT, INT) IS 'function which updates counters for a forum ';

-- updates statistics (#entries, #threads) for one forum, and calls itself recursively
-- to update parent forums
-- parameter:
--   - schema name
--   - start forum id (bigint)
--   - delta for #threads (int)
--   - delta for #entries (int)
--   - id of last entry to set or NULL to determine automatically (BIGINT)
--   - id of thread to skip while determining last entry automatically; may be set to 0 but not NULL (BIGINT)
-- returns:
--   - '' (text)
CREATE OR REPLACE FUNCTION public.update_counters_for_forum_with_last_entry(TEXT, BIGINT, INT, INT, BIGINT, BIGINT)
        RETURNS TEXT
        AS $$
        DECLARE
            query TEXT := '';
            schema_name ALIAS FOR $1;
            forum_id ALIAS FOR $2;
            number_delta_threads ALIAS FOR $3;
            number_delta_entries ALIAS FOR $4;
            last_entry ALIAS FOR $5;
            last_entry_thread ALIAS FOR $6;
            loop_record RECORD;
            forum_parent_id BIGINT;
            visible INT;
        BEGIN
            -- lock table in exlusive mode
            -- if the database is busy updating different users,
            -- a deadlock is possible so we lock the table in
            -- exclusive mode to make sure, no other connection is
            -- changing this table
            query := 'LOCK TABLE ' || quote_ident(schema_name) || '.forum_fora
                              IN SHARE ROW EXCLUSIVE MODE';
            EXECUTE query;
            IF last_entry IS NOT NULL THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_fora
                             SET number_of_entries = number_of_entries + ('|| number_delta_entries ||'),
                                 number_of_threads = number_of_threads + ('|| number_delta_threads ||'),
                                 last_entry = ' || quote_literal(last_entry) || '
                           WHERE id = ' || quote_literal(forum_id);
            ELSE
                query := 'SELECT id 
                            FROM ' || quote_ident(schema_name) || '.forum_thread_entries
                           WHERE id IN (SELECT last_entry 
                                          FROM ' || quote_ident(schema_name) || '.forum_fora 
                                         WHERE forum_parent_id = ' || quote_literal(forum_id) || '
                                     UNION
                                        SELECT last_entry 
                                          FROM ' || quote_ident(schema_name) || '.forum_threads 
                                         WHERE forum_id = ' || quote_literal(forum_id) || '
                                       )
                        ORDER BY entry_time DESC
                           LIMIT 1';
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query INTO loop_record;
                forum_parent_id := loop_record.id;
                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_fora
                            SET number_of_entries = number_of_entries + ('|| number_delta_entries ||'),
                                number_of_threads = number_of_threads + ('|| number_delta_threads ||'),
                                last_entry = (SELECT id 
                                                FROM ' || quote_ident(schema_name) || '.forum_thread_entries
                                               WHERE id IN (SELECT last_entry 
                                                              FROM ' || quote_ident(schema_name) || '.forum_fora 
                                                             WHERE forum_parent_id = ' || quote_literal(forum_id) || '
                                                         UNION
                                                            SELECT last_entry 
                                                              FROM ' || quote_ident(schema_name) || '.forum_threads 
                                                             WHERE forum_id = ' || quote_literal(forum_id) || '
                                                               AND id <> ' || quote_literal(last_entry_thread) || '
                                                           )
                                            ORDER BY entry_time DESC
                                               LIMIT 1)
                        WHERE id = ' || forum_id ;
            END IF;
            EXECUTE query;
        
        
            query := 'SELECT forum_parent_id, visible
                        FROM ' || quote_ident(schema_name) || '.forum_fora
                       WHERE id = ' || forum_id;
            -- RAISE NOTICE 'query: %', query;
            EXECUTE query INTO loop_record;
            forum_parent_id := loop_record.forum_parent_id;
            visible := loop_record.visible;
        
            IF forum_parent_id IS NOT NULL THEN
                IF last_entry IS NOT NULL THEN
                
                    query := 'SELECT  visible
                            FROM ' || quote_ident(schema_name) || '.forum_fora
                           WHERE id = ' || forum_parent_id;
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query INTO loop_record;
                    
                    IF visible = loop_record.visible THEN
                        query := 'SELECT public.update_counters_for_forum_with_last_entry(''' || schema_name || ''',' ||  forum_parent_id ||', ' || number_delta_threads || ', ' || number_delta_entries || ', ' || last_entry || ', ' || last_entry_thread || ')';
                    END IF;    
                ELSE
                    query := 'SELECT public.update_counters_for_forum_with_last_entry(''' || schema_name || ''',' ||  forum_parent_id ||', ' || number_delta_threads || ', ' || number_delta_entries || ', NULL, ' || last_entry_thread || ')';
                END IF;
                EXECUTE query;
            END IF;
        
            RETURN '';
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counters_for_forum(TEXT, BIGINT, INT, INT) IS 'function which updates counters and last entry for a forum ';

-- updates statistics for a thread entry, if it is inserted or deleted
-- parameter:
--   - schema name
-- returns:
--   - NULL on INSERT, OLD on DELETE (via trigger)
CREATE OR REPLACE FUNCTION public.update_counter_thread_entry()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            loop_record RECORD;
            thread_id BIGINT;
            first_entry BIGINT;
            forum_id BIGINT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
               schema_name := TG_ARGV[0];
            ELSE
               RAISE EXCEPTION 'update_counter_thread_entry(schemaname)';
            END IF;

            -- lock table in exlusive mode
            -- if the database is busy updating different users,
            -- a deadlock is possible so we lock the table in
            -- exclusive mode to make sure, no other connection is
            -- changing this table
            query := 'LOCK TABLE ' || quote_ident(schema_name) || '.forum_threads,
                                 ' || quote_ident(schema_name) || '.forum_fora,
                                 ' || quote_ident(schema_name) || '.forum_thread_entries
                              IN SHARE ROW EXCLUSIVE MODE';

            -- get parameters
            IF TG_OP = 'INSERT' THEN
                thread_id := NEW.thread_id;
                
                -- get thread data
                query := 'SELECT first_entry, forum_id
                            FROM ' || quote_ident(schema_name) || '.forum_threads
                           WHERE id = ' || thread_id;
                -- RAISE NOTICE 'query: %', query;
                EXECUTE query INTO loop_record;
                -- store thread data
                first_entry := loop_record.first_entry;
                forum_id := loop_record.forum_id;
                
                IF first_entry IS NULL THEN
                    first_entry = NEW.id;
                END IF;
                
                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_threads
                             SET number_of_entries = number_of_entries + 1,
                                 first_entry = ' || first_entry || ',
                                 last_entry = ' || NEW.id || ',
                                 last_entry_time = ' || quote_literal(NEW.entry_time) || '
                           WHERE id = ' || thread_id;
                EXECUTE query;
                
                query := 'SELECT public.update_counters_for_forum_with_last_entry(''' || schema_name || ''',' ||  forum_id ||', 0, 1, ' || NEW.id || ', 0)';
                        EXECUTE query;
                
                RETURN NULL;
                
            ELSIF TG_OP = 'DELETE' THEN
                IF OLD.thread_id IS NOT NULL THEN
                    thread_id := OLD.thread_id;
                    
                    -- do not set first_entry here
                    -- if first entry of a thread is deleted
                    -- the whole thread must be deleted
                    query := 'UPDATE ' || quote_ident(schema_name) || '.forum_threads
                                SET number_of_entries = number_of_entries - 1,
                                    last_entry = (SELECT id 
                                                    FROM ' || quote_ident(schema_name) || '.forum_thread_entries
                                                   WHERE thread_id = ' || quote_literal(thread_id) || '
                                                     AND id <> ' || quote_literal(OLD.id) || '
                                                ORDER BY entry_time DESC
                                                   LIMIT 1),
                                    last_entry_time = (SELECT entry_time 
                                                         FROM ' || quote_ident(schema_name) || '.forum_thread_entries
                                                        WHERE thread_id = ' || quote_literal(thread_id) || '
                                                          AND id <> ' || quote_literal(OLD.id) || '
                                                     ORDER BY entry_time DESC
                                                        LIMIT 1)
                              WHERE id = ' || thread_id;
                    EXECUTE query;
                    
                    -- get thread data
                    query := 'SELECT forum_id
                                FROM ' || quote_ident(schema_name) || '.forum_threads
                               WHERE id = ' || thread_id;
                    -- RAISE NOTICE 'query: %', query;
                    EXECUTE query INTO loop_record;
                    -- store thread data
                    forum_id := loop_record.forum_id;
                    
                    IF forum_id IS NOT NULL THEN
                        query := 'SELECT public.update_counters_for_forum_with_last_entry(''' || schema_name || ''',' ||  forum_id ||', 0, -1, NULL, 0)';
                        EXECUTE query;
                    END IF;
                END IF;
                
                RETURN OLD;
            END IF;
            
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';

-- updates statistics for a thread, if it is inserted or deleted
-- parameter:
--   - schema name
-- returns:
--   - NULL on INSERT, OLD on DELETE (via trigger)
CREATE OR REPLACE FUNCTION public.update_counter_thread()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            loop_record RECORD;
            forum_id BIGINT;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
               schema_name := TG_ARGV[0];
            ELSE
               RAISE EXCEPTION 'update_counter_thread(schemaname)';
            END IF;

            -- get parameters
            IF TG_OP = 'INSERT' THEN
                forum_id := NEW.forum_id;
                query := 'SELECT public.update_counters_for_forum(''' || schema_name || ''',' ||  forum_id ||', 1, 0)';
                EXECUTE query;
                
                -- update higher level structure: the category
                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_categories
                             SET number_of_threads = number_of_threads + 1
                           WHERE id = (SELECT category_id
                                         FROM ' || quote_ident(schema_name) || '.forum_fora
                                        WHERE id = ' || forum_id || ' ) ';
                EXECUTE query;
                
                RETURN NULL;
                
            ELSIF TG_OP = 'DELETE' THEN
                IF OLD.forum_id IS NOT NULL THEN
                    forum_id := OLD.forum_id;
                    
                    query := 'SELECT public.update_counters_for_forum_with_last_entry(''' || schema_name || ''',' ||  forum_id ||', -1, -' || OLD.number_of_entries || ', NULL, ' || OLD.id || ')';
                    EXECUTE query;
                    
                    -- update higher level structure: the category
                    query := 'UPDATE ' || quote_ident(schema_name) || '.forum_categories
                                 SET number_of_threads = number_of_threads - 1
                               WHERE id = (SELECT category_id
                                             FROM ' || quote_ident(schema_name) || '.forum_fora
                                            WHERE id = ' || forum_id || ' ) ';
                    EXECUTE query;
                END IF;
                
                RETURN OLD;
            END IF;
            
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION public.reposition_forum(TEXT, BIGINT, SMALLINT)
        RETURNS VOID
        AS $$
        DECLARE
            query TEXT := '';
            schema_name ALIAS FOR $1;
            forum_id ALIAS FOR $2;
            delta_position ALIAS FOR $3;
            forum_neighbour_id BIGINT;
            loop_record RECORD;
        BEGIN
            query := 'SELECT id
                        FROM ' || quote_ident(schema_name) || '.forum_fora
                       WHERE id <> ' || quote_literal(forum_id) || '
                         AND position = (SELECT position
                                           FROM ' || quote_ident(schema_name) || '.forum_fora 
                                          WHERE id = ' || quote_literal(forum_id) || ') + (' || CAST(delta_position AS TEXT) || ')
                         AND ((forum_parent_id IS NOT NULL 
                                AND forum_parent_id = (SELECT COALESCE(forum_parent_id, 0)
                                                         FROM ' || quote_ident(schema_name) || '.forum_fora 
                                                        WHERE id = ' || quote_literal(forum_id) || ')
                            OR (forum_parent_id IS NULL 
                                AND category_id = (SELECT category_id
                                                     FROM ' || quote_ident(schema_name) || '.forum_fora 
                                                    WHERE id = ' || quote_literal(forum_id) || ')))
                             )';
            EXECUTE query INTO loop_record;
            forum_neighbour_id := loop_record.id;
            
            IF forum_neighbour_id IS NULL THEN
                RETURN;
            END IF;
            
            query := 'UPDATE ' || quote_ident(schema_name) || '.forum_fora 
                         SET position = position + (' || CAST(delta_position AS TEXT) || ')
                       WHERE id = ' || quote_literal(forum_id);
            EXECUTE query;
            
            query := 'UPDATE ' || quote_ident(schema_name) || '.forum_fora 
                         SET position = position - (' || CAST(delta_position AS TEXT) || ')
                       WHERE id = ' || quote_literal(forum_neighbour_id);
            EXECUTE query;
        END;
        $$
        LANGUAGE 'plpgsql';

-- updates statistics for a forum, if it is inserted or deleted
-- parameter:
--   - schema name
-- returns:
--   - NULL on INSERT, OLD on DELETE (via trigger)
CREATE OR REPLACE FUNCTION public.update_counter_forum()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            number_delta INT := 0;
            category_id BIGINT := 0;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_counter_forum(schemaname)';
            END IF;

--             -- lock table to avoid deadlocks
--             query := 'LOCK TABLE ' || quote_ident(schema_name) || '.forum_categories,
--                                  ' || quote_ident(schema_name) || '.forum_fora
--                               IN ROW EXCLUSIVE MODE';
--             EXECUTE query;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                category_id := NEW.category_id;
                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_categories
                             SET number_of_forums = number_of_forums + 1
                           WHERE id = ' || category_id;
                EXECUTE query;

                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_fora
                             SET position = COALESCE((SELECT MAX(position) 
                                               FROM ' || quote_ident(schema_name) || '.forum_fora
                                              WHERE id <> ' || quote_literal(NEW.id) || '
                                                AND ((forum_parent_id IS NOT NULL AND forum_parent_id = ' || quote_literal(COALESCE(NEW.forum_parent_id, 0)) || ')
                                                 OR (forum_parent_id IS NULL AND category_id = ' || quote_literal(NEW.category_id) || '))
                                                     ), 0) + 1
                           WHERE id = ' || quote_literal(NEW.id);
                EXECUTE query;
                
                RETURN NULL;
            ELSIF TG_OP = 'DELETE' THEN
                IF OLD.category_id IS NOT NULL THEN
                    category_id := OLD.category_id;
                    query := 'UPDATE ' || quote_ident(schema_name) || '.forum_categories
                                 SET number_of_forums = number_of_forums - 1
                               WHERE id = ' || category_id;
                    EXECUTE query;
                END IF;
                
                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_fora
                             SET position = position - 1
                           WHERE position > ' || quote_literal(OLD.position) || ' 
                             AND ((forum_parent_id IS NOT NULL AND forum_parent_id = ' || quote_literal(COALESCE(OLD.forum_parent_id, 0)) || ')
                              OR (forum_parent_id IS NULL AND category_id = ' || quote_literal(COALESCE(OLD.category_id, 0)) || '))';
                EXECUTE query;
                
                query := 'SELECT public.update_counters_for_forum_with_last_entry(''' || schema_name || ''',' ||  OLD.id ||', -' || OLD.number_of_threads || ', -' || OLD.number_of_entries || ', NULL, 0)';
                EXECUTE query;
                
                RETURN OLD;
            END IF;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_counter_forum() IS 'trigger function which updates all counter in the forum';


CREATE OR REPLACE FUNCTION public.update_category_position()
        RETURNS TRIGGER
        AS $$
        DECLARE
            query TEXT := '';
            schema_name TEXT;
            new_position INT := 0;
            loop_record RECORD;
        BEGIN
            -- validate the parameter
            IF TG_NARGS = 1 THEN
                schema_name := TG_ARGV[0];
            ELSE
                RAISE EXCEPTION 'update_category_position(schemaname)';
            END IF;

            -- save relevant values in variables for better differentiation between INSERT and DELETE
            IF TG_OP = 'INSERT' THEN
                query := 'SELECT COALESCE(MAX(position), 0) + 1 AS new_position
                            FROM ' || quote_ident(schema_name) || '.forum_categories';
                EXECUTE query INTO loop_record;
                new_position := loop_record.new_position;

                NEW.position := new_position;
                RETURN NEW;
            ELSIF TG_OP = 'DELETE' THEN
                query := 'UPDATE ' || quote_ident(schema_name) || '.forum_categories
                             SET position = position - 1
                           WHERE position > ' || quote_literal(OLD.position);
                EXECUTE query;
                
                RETURN OLD;
            END IF;
            
            RETURN NULL;
        END;
        $$
        LANGUAGE 'plpgsql';
COMMENT ON FUNCTION public.update_category_position() IS 'trigger function which sets correct position-values for categories';

CREATE OR REPLACE FUNCTION public.reposition_category(TEXT, BIGINT, SMALLINT)
        RETURNS VOID
        AS $$
        DECLARE
            query TEXT := '';
            schema_name ALIAS FOR $1;
            category_id ALIAS FOR $2;
            delta_position ALIAS FOR $3;
            category_neighbour_id BIGINT;
            loop_record RECORD;
        BEGIN
            query := 'SELECT id
                        FROM ' || quote_ident(schema_name) || '.forum_categories
                       WHERE id <> ' || quote_literal(category_id) || '
                         AND position = (SELECT position
                                           FROM ' || quote_ident(schema_name) || '.forum_categories 
                                          WHERE id = ' || quote_literal(category_id) || ') + (' || CAST(delta_position AS TEXT) || ')';
            EXECUTE query INTO loop_record;
            category_neighbour_id := loop_record.id;
            
            IF category_neighbour_id IS NULL THEN
                RETURN;
            END IF;
            
            query := 'UPDATE ' || quote_ident(schema_name) || '.forum_categories 
                         SET position = position + (' || CAST(delta_position AS TEXT) || ')
                       WHERE id = ' || quote_literal(category_id);
            EXECUTE query;
            
            query := 'UPDATE ' || quote_ident(schema_name) || '.forum_categories 
                         SET position = position - (' || CAST(delta_position AS TEXT) || ')
                       WHERE id = ' || quote_literal(category_neighbour_id);
            EXECUTE query;
        END;
        $$
        LANGUAGE 'plpgsql';
      
