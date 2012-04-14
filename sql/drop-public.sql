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
-- drop public tables and functions
--
-- $Id: drop-public.sql 5743 2008-03-25 19:48:14Z ads $
--

-- mail types
TRUNCATE TABLE public.mail_types;
DROP TABLE public.mail_types CASCADE;

-- tags
TRUNCATE TABLE public.tag;
DROP TABLE public.tag CASCADE;

-- user languages
TRUNCATE TABLE public.point_sources;
DROP TABLE public.point_sources CASCADE;

-- user languages
TRUNCATE TABLE public.user_languages;
DROP TABLE public.user_languages CASCADE;

-- friend types
TRUNCATE TABLE public.friend_types;
DROP TABLE public.friend_types CASCADE;

-- rules and tables
DROP RULE public_countries_prot_del ON public.countries;
DROP RULE public_countries_prot_upd ON public.countries;

DROP RULE public_cities_prot_del ON public.cities;
DROP RULE public_cities_prot_upd ON public.cities;

-- old names
TRUNCATE TABLE public.user_groups;
DROP TABLE public.user_groups CASCADE;
-- new names
TRUNCATE TABLE public.groups;
DROP TABLE public.groups CASCADE;

TRUNCATE TABLE public.user_roles;
DROP TABLE public.user_roles CASCADE;

TRUNCATE TABLE public.rights;
DROP TABLE public.rights CASCADE;

TRUNCATE TABLE public.global_config;
DROP TABLE public.global_config CASCADE;

-- table for email regexp
TRUNCATE TABLE public.email_regexp;
DROP TABLE public.email_regexp CASCADE;

TRUNCATE TABLE public.uni;
DROP TABLE public.uni CASCADE;

TRUNCATE TABLE public.cities;
DROP TABLE public.cities CASCADE;

TRUNCATE TABLE public.countries;
DROP TABLE public.countries CASCADE;

TRUNCATE TABLE public.person_types;
DROP TABLE public.person_types CASCADE;

TRUNCATE TABLE public.user_privacy_keys;
DROP TABLE public.user_privacy_keys CASCADE;

TRUNCATE TABLE public.user_data_keys;
DROP TABLE public.user_data_keys CASCADE;

TRUNCATE TABLE public.user_contact_data_keys;
DROP TABLE public.user_contact_data_keys CASCADE;

TRUNCATE TABLE public.user_config_keys;
DROP TABLE public.user_config_keys CASCADE;

TRUNCATE TABLE public.details_visible;
DROP TABLE public.details_visible CASCADE;

TRUNCATE TABLE public.study_status;
DROP TABLE public.study_status CASCADE;

-- old functions
-- FIX: only for historic reasons, remove before production
DROP FUNCTION __SCHEMA__.str_replace (VARCHAR, VARCHAR, TEXT) CASCADE;
DROP FUNCTION __SCHEMA__.set_last_change_at() CASCADE;
DROP FUNCTION __SCHEMA__.set_insert_at() CASCADE;
DROP FUNCTION __SCHEMA__.check_email(CHAR) CASCADE;
DROP FUNCTION __SCHEMA__.spread_user_data() CASCADE;
DROP FUNCTION str_replace (VARCHAR, VARCHAR, TEXT) CASCADE;
DROP FUNCTION set_last_change_at() CASCADE;
DROP FUNCTION set_insert_at() CASCADE;
DROP FUNCTION public.set_password_on_insert() CASCADE;
DROP FUNCTION check_email(CHAR) CASCADE;
DROP FUNCTION spread_user_data() CASCADE;
DROP FUNCTION public.spread_user_data(VARCHAR) CASCADE;
DROP FUNCTION public.set_current() CASCADE;


-- functions
--

-- the following DROPs were commented by trehn,
-- created new DROPs from grep "CREATE OR REPLACE FUNCTION" public-functions.sql
--
-- DROP FUNCTION public.str_replace (VARCHAR, VARCHAR, TEXT) CASCADE;
-- DROP FUNCTION public.set_last_change_at() CASCADE;
-- DROP FUNCTION public.set_insert_at() CASCADE;
-- DROP FUNCTION public.set_only_insert_at() CASCADE;
-- DROP FUNCTION public.check_email(CHAR) CASCADE;
-- DROP FUNCTION public.spread_user_data() CASCADE;
-- DROP FUNCTION public.max_id(VARCHAR, VARCHAR) CASCADE;
-- DROP FUNCTION public.copy_user_groups(VARCHAR) CASCADE;
-- DROP FUNCTION public.copy_user_groups() CASCADE;
-- DROP FUNCTION public.username_changes() CASCADE;
-- DROP FUNCTION public.set_current_in_wiki() CASCADE;
-- DROP FUNCTION public.copy_rights() CASCADE;
-- DROP FUNCTION public.copy_global_config() CASCADE;

DROP FUNCTION public.check_email(CHAR) CASCADE;
DROP FUNCTION public.set_insert_at() CASCADE;
DROP FUNCTION public.set_only_insert_at() CASCADE;
DROP FUNCTION public.set_last_change_at() CASCADE;
DROP FUNCTION public.set_only_last_change_at() CASCADE;
DROP FUNCTION public.str_replace (VARCHAR, VARCHAR, TEXT) CASCADE;
DROP FUNCTION public.sleep (INTEGER) CASCADE;
DROP FUNCTION public.spread_user_data() CASCADE;
DROP FUNCTION public.set_user_default_role() CASCADE;
DROP FUNCTION public.max_id(VARCHAR, VARCHAR) CASCADE;
DROP FUNCTION public.copy_user_groups() CASCADE;
DROP FUNCTION public.copy_user_roles() CASCADE;
DROP FUNCTION public.copy_rights() CASCADE;
DROP FUNCTION public.copy_global_config() CASCADE;
DROP FUNCTION public.username_add() CASCADE;
DROP FUNCTION public.username_changes() CASCADE;
DROP FUNCTION public.username_deleted() CASCADE;
DROP FUNCTION public.set_current_in_wiki() CASCADE;
DROP FUNCTION public.primary_study_path() CASCADE;
DROP FUNCTION public.update_counter_forum() CASCADE;
DROP FUNCTION public.update_counter_thread() CASCADE;
DROP FUNCTION public.update_counter_thread_entry() CASCADE;
DROP FUNCTION public.update_counter_thread_entry_for_forum() CASCADE;
DROP FUNCTION public.update_user_forum_pos_number() CASCADE;
DROP FUNCTION public.update_counter_gb_entry() CASCADE;
DROP FUNCTION public.update_counter_blog_entry() CASCADE;
DROP FUNCTION public.update_counter_course_file_download() CASCADE;
DROP FUNCTION public.update_course_file_upload() CASCADE;
DROP FUNCTION public.update_course_file_rating() CASCADE;
DROP FUNCTION public.update_course_file_counter_rating() CASCADE;
DROP FUNCTION public.course_file_check_hash() CASCADE;
DROP FUNCTION public.set_last_update() CASCADE;
DROP FUNCTION public.set_entry_time() CASCADE;
DROP FUNCTION public.move_old_attachments() CASCADE;
DROP FUNCTION public.validate_add_new_friend() CASCADE;
DROP FUNCTION public.validate_update_friend() CASCADE;
DROP FUNCTION public.validate_add_user_online() CASCADE;
DROP FUNCTION public.remove_attachments() CASCADE;
DROP FUNCTION public.validate_add_user_right() CASCADE;
DROP FUNCTION public.validate_add_role_right() CASCADE;
DROP FUNCTION public.forum_anonymous_posting() CASCADE;
DROP FUNCTION public.insert_into_rb(TEXT, INT) CASCADE;
DROP FUNCTION public.get_next_rb(TEXT) CASCADE;
DROP FUNCTION public.delete_from_rb(TEXT, INT) CASCADE;
DROP FUNCTION public.random_user_add() CASCADE;
DROP FUNCTION public.random_user_del() CASCADE;
DROP FUNCTION public.copy_groups() CASCADE;
DROP FUNCTION public.del_user_group_has_right() CASCADE;
DROP FUNCTION public.get_next_random_user(TEXT) CASCADE;
DROP FUNCTION public.delete_from_random_user(TEXT, INT) CASCADE;
DROP FUNCTION public.insert_into_random_user(TEXT, INT) CASCADE;
DROP FUNCTION public.delete_news_forum_thread() CASCADE;
DROP FUNCTION public.delete_news_forum_thread_attachments() CASCADE;
DROP FUNCTION public.insert_course() CASCADE;
DROP FUNCTION public.insert_news_forum_thread() CASCADE;
DROP FUNCTION public.insert_news_forum_thread_attachments() CASCADE;
DROP FUNCTION public.insert_user_group() CASCADE;
DROP FUNCTION public.trim_shoutbox() CASCADE;
DROP FUNCTION public.update_counter_attachment() CASCADE;
DROP FUNCTION public.update_counter_blog_advanced_comment() CASCADE;
DROP FUNCTION public.update_counter_blog_advanced_trackback() CASCADE;
DROP FUNCTION public.update_counter_pm() CASCADE;
DROP FUNCTION public.update_counter_thread_entry_for_forum(TEXT, BIGINT, BIGINT) CASCADE;
DROP FUNCTION public.update_counter_thread_for_forum(TEXT, BIGINT, BIGINT) CASCADE;
DROP FUNCTION public.update_news_forum_thread() CASCADE;
DROP FUNCTION public.update_online_user_stats() CASCADE;
DROP FUNCTION public.update_point_user_stats() CASCADE;
DROP FUNCTION public.update_user_forum_entries() CASCADE;
DROP FUNCTION public.validate_add_user_group_has_right() CASCADE;
DROP FUNCTION public.validate_add_user_group_right() CASCADE;
