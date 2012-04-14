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
-- contains all sql commands to clean the database from schema tables
--
-- $Id: drop-schema.sql 6214 2008-07-25 18:11:50Z trehn $
--

DROP TABLE __SCHEMA__.forum_thread_read;
DROP TABLE __SCHEMA__.forum_forum_read;

-- soccer stuff
DROP TABLE __SCHEMA__.soccer_tipps_winner;
DROP TABLE __SCHEMA__.soccer_tipps_ranking;
DROP TABLE __SCHEMA__.soccer_tipps;
DROP TABLE __SCHEMA__.soccer_games;
DROP TABLE __SCHEMA__.soccer_game_types;
DROP TABLE __SCHEMA__.soccer_teams;
DROP TABLE __SCHEMA__.soccer_stadiums;
DROP TABLE __SCHEMA__.soccer_tournaments;

DROP TABLE __SCHEMA__.sessions;

DROP TABLE __SCHEMA__.user_award;
DROP TABLE __SCHEMA__.award;

DROP TABLE __SCHEMA__.market_item_attachments;
DROP TABLE __SCHEMA__.market_item;
DROP TABLE __SCHEMA__.market_category;

DROP TABLE __SCHEMA__.user_expiration;

-- point sources
DROP TABLE __SCHEMA__.point_sources;

-- user recycle bin
DROP TABLE __SCHEMA__.user_recycle;

-- login error log
DROP TABLE __SCHEMA__.user_login_errors;

-- user mail data
DROP TABLE __SCHEMA__.user_mails;
DROP TABLE __SCHEMA__.mail;

-- canvass data
DROP TABLE __SCHEMA__.user_canvass;

-- random user
DROP RULE __SCHEMA__random_user_data_del ON __SCHEMA__.random_user_data;
DROP TABLE __SCHEMA__.random_user_data;
DROP TABLE __SCHEMA__.random_user;

-- drop user statistics
DROP TABLE __SCHEMA__.user_stats;

-- drop user warnings
DROP TABLE __SCHEMA__.user_warnings;

-- drop smileys
DROP TABLE __SCHEMA__.smileys;

-- drop advanced blog
DROP TABLE __SCHEMA__.blog_advanced_trackbacks;
DROP TABLE __SCHEMA__.blog_advanced_comments;
DROP TABLE __SCHEMA__.blog_advanced_entriescat;
DROP TABLE __SCHEMA__.blog_advanced_categories;
DROP TABLE __SCHEMA__.blog_advanced_attachments;
DROP TABLE __SCHEMA__.blog_advanced_subscription;
DROP TABLE __SCHEMA__.blog_advanced;
DROP TABLE __SCHEMA__.blog_advanced_config;

-- drop feature system
DROP TABLE __SCHEMA__.user_features;
DROP TABLE __SCHEMA__.features;

-- drop cookie identifiers
DROP TABLE __SCHEMA__.user_cookies CASCADE;

--private messages stuff
DROP TABLE __SCHEMA__.pm_for_users CASCADE;
DROP TABLE __SCHEMA__.pm_attachments CASCADE;
DROP TABLE __SCHEMA__.pm CASCADE;

--banner rotating stuff
DROP FUNCTION __SCHEMA__.get_random_banner () CASCADE;
DROP FUNCTION __SCHEMA__.update_random_banner () CASCADE;
DROP TABLE __SCHEMA__.banner_clicks CASCADE;
DROP TABLE __SCHEMA__.banner_show CASCADE;
DROP TABLE __SCHEMA__.banner_select CASCADE;
DROP TABLE __SCHEMA__.banner CASCADE;

-- virtual archives stuff
DROP TABLE __SCHEMA__.virtual_archives_attachments CASCADE;
DROP TABLE __SCHEMA__.virtual_archives CASCADE;

-- courses stuff
DROP TRIGGER __SCHEMA___courses_update_file_rating ON __SCHEMA__.courses_files_ratings_single;
DROP TRIGGER __SCHEMA___courses_file_check_hash ON __SCHEMA__.courses_files_information;
DROP TRIGGER __SCHEMA___courses_remove_file ON __SCHEMA__.courses_files;
DROP TRIGGER __SCHEMA___courses_file_upload ON __SCHEMA__.courses_files;
DROP TABLE __SCHEMA__.courses_files_annotations CASCADE;
DROP TABLE __SCHEMA__.courses_files_ratings_median CASCADE;
DROP TABLE __SCHEMA__.courses_files_ratings_single CASCADE;
DROP TABLE __SCHEMA__.courses_files_ratings CASCADE;
DROP TABLE __SCHEMA__.courses_files_ratings_categories CASCADE;
DROP TABLE __SCHEMA__.courses_files_revisions CASCADE;
DROP TABLE __SCHEMA__.courses_files_information CASCADE;
DROP TABLE __SCHEMA__.courses_files_downloads CASCADE;
DROP TABLE __SCHEMA__.courses_files CASCADE;
DROP TABLE __SCHEMA__.courses_files_semesters CASCADE;
DROP TABLE __SCHEMA__.courses_files_categories CASCADE;
DROP TABLE __SCHEMA__.courses_per_study_path CASCADE;
DROP TABLE __SCHEMA__.courses_per_student CASCADE;
DROP TABLE __SCHEMA__.courses_data CASCADE;
DROP TABLE __SCHEMA__.courses CASCADE;

-- news entries
DROP TABLE __SCHEMA__.event CASCADE;
DROP TABLE __SCHEMA__.event_category CASCADE;
DROP TABLE __SCHEMA__.news CASCADE;

-- global configuration settings
DROP TRIGGER __SCHEMA___copy_global_config ON public.global_config;
DROP TABLE __SCHEMA__.global_config CASCADE;

-- forum dependecies
-- old name
DROP TABLE __SCHEMA__.user_groups_forums CASCADE;
-- new name
DROP TABLE __SCHEMA__.groups_forums CASCADE;

-- forum stuff
-- DROP TRIGGER __SCHEMA___update_counter_add_forum ON __SCHEMA__.forums;
-- DROP TRIGGER __SCHEMA___update_counter_add_thread ON __SCHEMA__.threads;
-- DROP TRIGGER __SCHEMA___update_counter_add_thread_entry ON __SCHEMA__.thread_entries;
DROP TABLE __SCHEMA__.thread_entries_attachments CASCADE;
DROP TABLE __SCHEMA__.thread_entries CASCADE;
DROP TABLE __SCHEMA__.threads CASCADE;
DROP TABLE __SCHEMA__.thread_stats CASCADE;
DROP TABLE __SCHEMA__.forum_moderator CASCADE;
DROP TABLE __SCHEMA__.forum_tag CASCADE;
DROP TABLE __SCHEMA__.forums CASCADE;
DROP TABLE __SCHEMA__.forum_default_moderator CASCADE;
DROP TABLE __SCHEMA__.category_moderator CASCADE;
DROP TABLE __SCHEMA__.categories CASCADE;

DROP TABLE __SCHEMA__.forum_abo CASCADE;
DROP TABLE __SCHEMA__.forum_thread_entries_attachments CASCADE;
DROP TABLE __SCHEMA__.forum_thread_entries CASCADE;
DROP TABLE __SCHEMA__.forum_thread_ratings CASCADE;
DROP TABLE __SCHEMA__.forum_threads CASCADE;
DROP TABLE __SCHEMA__.forum_moderator CASCADE;
DROP TABLE __SCHEMA__.forum_tag CASCADE;
DROP TABLE __SCHEMA__.forum_fora CASCADE;
DROP TABLE __SCHEMA__.forum_default_moderator CASCADE;
DROP TABLE __SCHEMA__.forum_category_moderator CASCADE;
DROP TABLE __SCHEMA__.forum_categories CASCADE;



-- entries - attachments relationship
DROP TABLE __SCHEMA__.blog_attachments CASCADE;
DROP TABLE __SCHEMA__.guestbook_attachments CASCADE;
DROP TABLE __SCHEMA__.news_attachments CASCADE;

-- entries' attachments
DROP TRIGGER __SCHEMA___move_old_attachments ON __SCHEMA__.attachments;
DROP TABLE __SCHEMA__.attachments CASCADE;
DROP TABLE __SCHEMA__.attachments_old CASCADE;

-- media center
DROP TABLE __SCHEMA__.media_dir CASCADE;

-- user's blog
DROP TRIGGER __SCHEMA___update_counter_blog_entry ON __SCHEMA__.blog;
DROP TABLE __SCHEMA__.blog CASCADE;

-- user site visits
DROP TABLE __SCHEMA__.site_visits CASCADE;

-- user spoken languages
DROP TABLE __SCHEMA__.user_languages CASCADE;

-- study_path and faculty
DROP TABLE __SCHEMA__.study_path_tag CASCADE;
DROP TABLE __SCHEMA__.study_path_per_student CASCADE;
DROP TABLE __SCHEMA__.study_path_per_faculty CASCADE;
DROP TABLE __SCHEMA__.study_path_per_university CASCADE;
DROP TABLE __SCHEMA__.study_path CASCADE;
DROP TABLE __SCHEMA__.faculty CASCADE;

-- users' wikis
DROP TRIGGER __SCHEMA___wiki_current ON __SCHEMA__.wiki;
DROP TABLE __SCHEMA__.wiki CASCADE;

-- tags
TRUNCATE TABLE __SCHEMA__.tag;
DROP TABLE __SCHEMA__.tag CASCADE;

-- friends
-- old name
DROP TABLE __SCHEMA__.friends CASCADE;
-- new name
DROP TABLE __SCHEMA__.user_friends CASCADE;

-- guestbook
DROP TRIGGER __SCHEMA___update_counter_gb_entry ON __SCHEMA__.guestbook;
DROP TABLE __SCHEMA__.guestbook CASCADE;

-- box config
DROP TABLE __SCHEMA__.box_shoutbox CASCADE;
DROP TABLE __SCHEMA__.box_config CASCADE;
DROP TABLE __SCHEMA__.box_type CASCADE;

-- old nicks
-- old names
DROP RULE __SCHEMA___old_nicks_upd ON __SCHEMA__.old_nicks CASCADE;
DROP RULE __SCHEMA___old_nicks_del ON __SCHEMA__.old_nicks CASCADE;
DROP TABLE __SCHEMA__.old_nicks CASCADE;
-- new names
DROP RULE __SCHEMA___user_old_nicks_upd ON __SCHEMA__.user_old_nicks CASCADE;
DROP RULE __SCHEMA___user_old_nicks_del ON __SCHEMA__.user_old_nicks CASCADE;
DROP TABLE __SCHEMA__.user_old_nicks CASCADE;
-- email addresses
DROP TABLE __SCHEMA__.user_old_email_addresses CASCADE;

-- external users
DROP RULE __SCHEMA___external_users_upd ON __SCHEMA__.external_users;
DROP TABLE __SCHEMA__.external_users CASCADE;

-- online user
-- old name
DROP TABLE __SCHEMA__.users_online CASCADE;
-- new name
DROP TABLE __SCHEMA__.user_online CASCADE;

-- user privacy
DROP TABLE __SCHEMA__.user_privacy CASCADE;

-- user config
DROP TABLE __SCHEMA__.user_config CASCADE;

-- user data
DROP TABLE __SCHEMA__.user_extra_data CASCADE;
DROP TABLE __SCHEMA__.user_data CASCADE;
DROP TABLE __SCHEMA__.user_contact_data CASCADE;

-- rights table
DROP TRIGGER __SCHEMA___copy_rights ON public.rights;
-- user, user group and role rights tables
-- old table names
DROP TABLE __SCHEMA__.user_group_has_rights CASCADE;
DROP TABLE __SCHEMA__.user_group_rights CASCADE;
DROP TABLE __SCHEMA__.user_rights CASCADE;
DROP TABLE __SCHEMA__.role_rights CASCADE;
-- new table names
DROP TABLE __SCHEMA__.rights_group CASCADE;
DROP TABLE __SCHEMA__.rights_user_group CASCADE;
DROP TABLE __SCHEMA__.rights_user CASCADE;
DROP TABLE __SCHEMA__.rights_role CASCADE;
DROP TABLE __SCHEMA__.rights CASCADE;

-- groups table
-- old name
DROP TRIGGER __SCHEMA___copy_user_groups ON public.user_groups;
DROP TABLE __SCHEMA__.user_groups CASCADE;
-- new name
DROP TRIGGER __SCHEMA___copy_groups ON public.user_groups;
DROP TABLE __SCHEMA__.groups CASCADE;
DROP TABLE __SCHEMA__.user_group_membership CASCADE;

-- roles table
DROP TRIGGER __SCHEMA___copy_user_roles ON public.user_roles;
DROP TABLE __SCHEMA__.user_roles CASCADE;
DROP TABLE __SCHEMA__.user_role_membership CASCADE;

-- users table
DROP TABLE __SCHEMA__.user_activation CASCADE;
DROP RULE __SCHEMA___users_upd ON __SCHEMA__.users;
DROP TABLE __SCHEMA__.users CASCADE;

-- functions
DROP FUNCTION __SCHEMA__.set_user_online (BIGINT) CASCADE;
DROP FUNCTION __SCHEMA__.small_world (BIGINT, BIGINT) CASCADE;
DROP FUNCTION __SCHEMA__.get_random_user (double precision) CASCADE;
DROP FUNCTION __SCHEMA__.update_feature_slots (BIGINT, INT) CASCADE;

