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
-- sample data for rights-db (public part)
--

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('USER_RIGHT_ADMIN',
        'permission to set/unset user rights',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('ROLE_ADMIN',
        'permission to add/edit/remove roles and role membership',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BANNER_ADMIN',
        'permission to add and edit all banners',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('TAG_ADMIN',
        'permission to add and edit all tags',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('TAG_MAP_ADMIN',
        'permission to map all tags',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('COURSE_ADMIN',
        'permission to perform administrative tasks on course and study path system',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('COURSE_FILE_ADMIN',
        'permission to perform administrative tasks on uploaded course files',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FEATURE_ADMIN',
        'permission to add and edit all (level) features',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('POINT_SOURCE_ADMIN',
        'permission to edit all point sources',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('SMILEY_ADMIN',
        'permission to edit all available smileys',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GLOBAL_SETTINGS_ADMIN',
        'permission to edit certain (locally) global config settings',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PROFILE_ADMIN',
        'permission to modify profiles of other users',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('SOCCER_BET_ADMIN',
        'permission to perform administrative tasks on soccer betting system',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('USER_CREATE',
        'permission to add users without validation process',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('USER_DELETE',
        'permission to delete users',
        false);

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GROUP_ADMIN',
        'permission to add/edit/remove groups and group membership',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed,is_group_specific) 
    VALUES ('GROUP_OWN_ADMIN',
        'permission to perform administrative tasks on own group',
        false, true);
INSERT INTO public.rights 
        (name, description,
         default_allowed,is_group_specific) 
    VALUES ('GROUP_INFOPAGE_EDIT',
        'permission to edit own group info page',
        false, true);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GROUP_INFOPAGE_ADMIN',
        'permission to perform administrative tasks on group infopages',
        false);


INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('SERVER_STATS',
        'permission to observe server statistics',
        false);

--
-- end of administrative rights

-- gb, blog, forum, news, course

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ENTRY_ADD',
        'permission to create/add a guestbook entry',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ENTRY_EDIT',
        'permission to modify an existing, self-written guestbook entry',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ENTRY_EDIT_WITHOUT_NOTICE',
        'permission to allow modification without visible notice, e.g. via "last edited by"',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ENTRY_ADMIN',
        'permission to allow all modifications on guestbook entries',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ENTRY_DELETE',
        'permission to delete an existing, self-written guestbook entry',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ENTRY_GIVE_MULTIPLE_POINTS',
        'permission to give more than one +/-point for a guestbook entry',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ENTRY_COMMENT',
        'permission to comment on a guestbook entry in the own guestbook',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ENTRY_QUOTE',
        'permission to quote a guestbook entry in the own guestbook',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_FILTER',
        'permission to filter _own_ guestbook display',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ADVANCED_STATS',
        'permission to view advanced guestbook statistics',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('GB_ADVANCED_STATS_ALL',
        'permission to view advanced guestbook statistics of all user',
        false);

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_ENTRY_ADD',
        'permission to create/add a blog entry',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_ENTRY_EDIT',
        'permission to modify an existing, own blog entry',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_ENTRY_EDIT_WITHOUT_NOTICE',
        'permission to allow modification without visible notice, e.g. via "last edited by"',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_ENTRY_ADMIN',
        'permission to allow all modifications on blog entries',
                false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_ENTRY_DELETE',
        'permission to delete an existing, own blog entry',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_FILTER',
        'permission to filter blogs display (own and other)',
        false);

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_ADVANCED_CREATE',
        'permission to create an own blog on unihelp',
            false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_ADVANCED_OWN_ADMIN',
        'permission to perform administrative tasks (add,delete,edit) in _own_ blog',
            false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('BLOG_ADVANCED_ADMIN',
        'permission to perform administrative tasks (add,delete,edit) in _all_ blogs',
            false);
INSERT INTO public.rights 
        (name, description,
         default_allowed,
         is_group_specific) 
    VALUES ('BLOG_ADVANCED_GROUP_OWN_ADMIN',
        'permission to perform administrative tasks (add,delete,edit) in group''s blog',
            false, true);

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FORUM_THREAD_ENTRY_ADD',
        'permission to create/add a thread entry in a forum',
        false);

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FORUM_THREAD_ENTRY_EDIT',
        'permission to edit a thread entry in a forum',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed,is_group_specific) 
    VALUES ('FORUM_GROUP_THREAD_ENTRY_ADD',
        'permission to create/add a thread entry in a forum as a group',
        false,true);

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FORUM_THREAD_RATING',
        'permission to rate in a thread',
        false);

INSERT INTO public.rights 
        (name, description,
         default_allowed,
                 is_group_specific) 
    VALUES ('NEWS_ENTRY_ADD',
        'permission to create/add a news entry',
        false, true);
INSERT INTO public.rights 
        (name, description,
         default_allowed,
                 is_group_specific) 
    VALUES ('NEWS_ENTRY_STICKY',
        'permission to operate on stickyness of news entries',
        false, true);
INSERT INTO public.rights 
        (name, description,
         default_allowed,
                 is_group_specific) 
    VALUES ('NEWS_ENTRY_EDIT',
        'permission to edit a news entry',
        false, true);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('NEWS_ENTRY_ADMIN',
        'permission to perform administrative tasks on news entries',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FORUM_CATEGORY_ADMIN',
        'permission to administrate the categories of the forum',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FORUM_POINT_ADMIN',
        'permission to toggle points generation flag in forum',
        false);

--
-- PM

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_READ_MESSAGES',
        'permission to read a pm',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_ADD_USER_MESSAGES',
        'permission to send a user pm',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_DEL_USER_MESSAGES',
        'permission to delete a user pm',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_ADD_ATTACHMENT',
        'permission to add an attachment to a pm',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_SENDTO_FRIENDS',
        'permission to send a user pm to his friendlist',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_SENDTO_ALL',
        'permission to send a user pm to all user',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_SENDTO_GROUP',
        'permission to send a user pm',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_SENDTO_COURSE',
        'permission to send a user pm',
        false);

--
-- course


INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('COURSES_FILE_UPLOAD',
        'permission to upload a course related file',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('COURSES_FILE_DOWNLOAD',
        'permission to download a course related file',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('COURSES_FILE_RATING',
        'permission to rate a downloaded course related file',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('COURSES_FILE_SEARCH',
        'permission to search for a uploaded course file',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('COURSES_FILE_EDIT',
        'permission to change an own uploaded course related file',
        false);

--
-- features

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('USER_SEARCH_ADVANCED',
        'permission to start an advanced user search',
        true);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FRIENDLIST_MODIFY',
        'permission to add/remove friend from own friendlist',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FRIENDLIST_EXTENDED_CATEGORIES',
        'permission to categorize friends in own friendlist',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FEATURE_SELECT',
        'permission to select level features',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FEATURE_SMALLWORLD',
        'permission to run a smallworld query',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FEATURE_REVERSE_FRIENDLIST',
        'permission to run a reverse friendlist query',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('FEATURE_BOX_REARRANGEMENT',
        'permission to rearrange boxes in the left and right column',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PROFILE_MODIFY',
        'permission to modify own user profile',
        false);

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('LOGIN',
        'permission to login',
        false);

INSERT INTO public.rights 
        (name, description,
         default_allowed, is_group_specific) 
    VALUES ('FORUM_GROUP_MODERATOR',
        'permission to be moderator in own group forum',
        false, true);

INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('PM_SEND_AS_SYSTEM',
        'permission to send a system pm',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('USER_WARNING_ADD',
        'permission to add a warning to a user',
        false);
INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('ACCESS_STATS',
        'permission to view page stats',
        false);

--right for calendar
INSERT INTO public.rights 
        (name, description,
         default_allowed,is_group_specific) 
    VALUES ('CALENDAR_EVENT_GLOBAL_ADD',
        'permission to add event to general calendar',
        false, true);
INSERT INTO public.rights 
        (name, description,
         default_allowed,is_group_specific) 
    VALUES ('CALENDAR_EVENT_GLOBAL_ADD_USER',
        'permission to add event to general calendar as user',
        false, false);
INSERT INTO public.rights 
        (name, description,
         default_allowed,is_group_specific) 
    VALUES ('CALENDAR_EVENT_ADMIN',
        'permission to ba admin of event to general calendar',
        false, false);


INSERT INTO public.rights 
        (name, description,
         default_allowed) 
    VALUES ('POST_SHOUTBOX',
        'permission to post in shoutbox',
        false);
