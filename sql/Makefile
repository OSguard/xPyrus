# Makefile to manage database structure
#
# $Id: Makefile 5866 2008-05-03 09:58:29Z trehn $


CREATE_FILES=schema-users.sql schema-external_users.sql schema-roles.sql schema-user_role_membership.sql schema-rights.sql schema-rights_user.sql schema-rights_role.sql schema-user_old_nicks.sql schema-user_old_email_addresses.sql schema-user_online.sql schema-attachments.sql schema-user_friends.sql schema-user_languages.sql schema-guestbook.sql schema-blog.sql schema-groups.sql schema-tag.sql schema-forum.sql schema-forum-initial-data.sql schema-user_group_membership.sql schema-study-path.sql schema-study-path-initial-data.sql schema-global_config.sql schema-news.sql schema-event.sql schema-courses_files.sql schema-banner.sql schema-rights_user_group.sql schema-pm.sql schema-user_cookies.sql schema-features.sql schema-functions.sql schema-rights_role-initial-data.sql schema-blog_advanced.sql schema-box.sql schema-smileys.sql schema-smileys-initial-data.sql schema-user_stats.sql schema-random_user.sql schema-user_warnings.sql schema-user_canvass.sql schema-mail.sql schema-user_mails.sql schema-user_login_errors.sql schema-user_recycle.sql schema-point_sources.sql schema-user_expiration.sql schema-award.sql schema-sessions.sql schema-session.sql schema-soccer.sql
CREATE_LOG_FILES=schema-guestbook_log.sql schema-forum_log.sql schema-rights_user_group_log.sql schema-rights_user_log.sql
CREATE_TSEARCH_FILES=schema-forum_tsearch.sql
CREATE_VIEW_FILES=schema-views.sql
DROP_FILES=drop-schema.sql
CREATE_PUBLIC_FILES=public-functions.sql public-countries.sql public-cities.sql public-uni.sql public-person_types.sql public-email_addresses.sql public-roles.sql public-groups.sql public-rights.sql public-friend_types.sql public-user_languages.sql public-global_config.sql public-user_data_keys.sql public-user_contact_data_keys.sql public-user_config_keys.sql public-user_privacy_keys.sql public-details_visible.sql public-point_sources.sql public-rights-initial-data.sql public-study_status.sql public-mail_types.sql
DROP_PUBLIC_FILES=drop-public.sql
DROP_LOG_FILES=schema-drop-guestbook_log.sql schema-drop-forum_log.sql schema-drop-rights_user_group_log.sql schema-drop-rights_user_log.sql
DROP_VIEW_FILES=drop-views.sql
TEST_DATA=test/user_test_data.sql test/external_user_test_data.sql test/group_test_data.sql test/tag_test_data.sql test/forum_test_data.sql test/courses_test_data.sql  test/blog_test_data.sql test/gb_test_data.sql test/courses_files_test_data.sql test/news_test_data.sql test/banner_test_data.sql test/pm_test_data.sql test/features_test_data.sql test/blog_advanced_test_data.sql test/box_test_data.sql test/user_extended_test_data.sql
PUBLIC_TEST_DATA=test/public-config_test_data.sql test/public-rights_test_data.sql test/public-point_sources.sql

# no test for: test/rights_test_data.sql 

# include local config
include Makefile.local

PSQL=${BIN_PSQL} -q -U ${DB_USER} -p ${DB_PORT} -d ${DB_NAME}
PSQL_WO_DB=${BIN_PSQL} -q -U ${DB_USER} -p ${DB_PORT}
PSQL_DOC=postgresql_autodoc -u ${DB_USER} --password=${DB_PASS} -h ${DB_HOST} -p ${DB_PORT} -d ${DB_NAME}

##########################################################################
# info
all:
	@@echo "targets:"
	@@echo "drop, create, test"
	@@echo "drop               drop all tables for this schema"
	@@echo "create             create all tables for this schema"
	@@echo "test               insert test data into this schema"
	@@echo "create-test-data   create user testdata from textfile"
	@@echo "create-log         create logging tables for guestbook and forum"
	@@echo "drop-log           drop logging tables for guestbook and forum"
	@@echo "create-view        create some handy views (OPTIONAL)"
	@@echo "drop-view          drop views"
	@@echo "create-tsearch     add tsearch columns for forum"
	@@echo "changeset          apply changeset given by parameter ID (e.g. make changeset ID=05)"
	@@echo ""
	@@echo "one-time-targets:"
	@@echo "drop-accounts      drop database accounts"
	@@echo "create-accounts    create database accounts"
	@@echo "drop-schema        drop the current schema"
	@@echo "create-schema      create a schema"
	@@echo "drop-public        drop all public tables and functions"
	@@echo "create-public      create all public tables and functions"
	@@echo "drop-db            drop the database"
	@@echo "create-db          create the database"
	@@echo ""
	@@echo "install:  create-accounts  create-schema  create-public"
	@@echo "drop:     create-public    drop-schema    drop-accounts"
	@@echo ""
	@@echo ""
	@@echo "s1:   shorthand for recreate all schemas"
	@@echo "s2:   shorthand for recreate current schema data"

##########################################################################
# shorthands
s1:
	${MAKE} drop
	${MAKE} drop-public
	${MAKE} create-public
	${MAKE} create
s1-t:
	${MAKE} s1
	${MAKE} test-public
	${MAKE} test

s2:
	${MAKE} drop
	${MAKE} create
s2-t:
	${MAKE} s2
	${MAKE} test

##########################################################################
# accounts
create-accounts:
	${PSQL} -c "CREATE GROUP ${DB_NORMAL_GROUP}"
	${PSQL} -c "CREATE GROUP ${DB_ADMIN_GROUP}"
	${PSQL} -c "CREATE USER ${DB_NORMAL_USER} WITH PASSWORD '${DB_NORMAL_PASS}' IN GROUP ${DB_NORMAL_GROUP}"
	${PSQL} -c "CREATE USER ${DB_ADMIN_USER} WITH PASSWORD '${DB_ADMIN_PASS}' IN GROUP ${DB_NORMAL_GROUP}, ${DB_ADMIN_GROUP}"

drop-accounts:
	${PSQL} -c "DROP GROUP ${DB_NORMAL_GROUP}"
	${PSQL} -c "DROP GROUP ${DB_ADMIN_GROUP}"
	${PSQL} -c "DROP USER ${DB_NORMAL_USER}"
	${PSQL} -c "DROP USER ${DB_ADMIN_USER}"


##########################################################################
# schema
create-schema:
	${PSQL} -c "CREATE SCHEMA ${DB_SCHEMA} AUTHORIZATION ${DB_ADMIN_USER}"
	${PSQL} -c "GRANT ALL PRIVILEGES ON SCHEMA ${DB_SCHEMA} TO GROUP ${DB_ADMIN_GROUP}"
	${PSQL} -c "GRANT USAGE ON SCHEMA ${DB_SCHEMA} TO GROUP ${DB_NORMAL_GROUP}"

drop-schema:
	${PSQL} -c "DROP SCHEMA ${DB_SCHEMA}"
	${PSQL} -f vacuum.sql


##########################################################################
# db
create-db:
	@#${PSQL_WO_DB} -d "template1" -c "CREATE DATABASE ${DB_NAME} WITH ENCODING = 'SQL_ASCII'"
	@# team meeting from 2006-05-16: we will use UTF8/Unicode
	${PSQL_WO_DB} -d "template1" -c "CREATE DATABASE ${DB_NAME} WITH ENCODING = 'UNICODE'"
	@@echo ""
	@@echo ""
	@@echo ">>--- ATTENTION ---<<"
	@@echo ""
	@@echo "don't forget to run:"
	@@echo "createlang plpgsql ${DB_NAME}"
	@@echo "on the commandline for the database user"
	@@echo ""
	@@echo ">>--- ATTENTION ---<<"
	@@echo ""

drop-db:
	${PSQL_WO_DB} -d "template1" -c "DROP DATABASE ${DB_NAME}"
	${PSQL_WO_DB} -d "template1" -f vacuum.sql


##########################################################################
# normal tables and data
create-tmp:	clean_tmp
	cat ${CREATE_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > create.tmp
create:	create-tmp
	${PSQL} -f create.tmp
	${PSQL} -f vacuum.sql
	${MAKE} clean_tmp

drop-tmp:	clean_tmp
	cat ${DROP_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > drop.tmp
drop:	drop-tmp
	${PSQL} -f drop.tmp
	${PSQL} -f vacuum.sql
	${MAKE} clean_tmp




##########################################################################
# tables and data for public schema + functions
drop-public-tmp:	clean_tmp
	cat ${DROP_PUBLIC_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > drop-public.tmp
drop-public:	drop-public-tmp
	${PSQL} -f drop-public.tmp
	${PSQL} -f vacuum.sql
	${MAKE} clean_tmp

create-public-tmp:	clean_tmp
	cat ${CREATE_PUBLIC_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > create-public.tmp
create-public:	create-public-tmp
	${PSQL} -f create-public.tmp
	${PSQL} -c "GRANT USAGE ON SCHEMA public TO GROUP ${DB_NORMAL_GROUP}"
	${PSQL} -f vacuum.sql
	${MAKE} clean_tmp


##########################################################################
# logging for create target
drop-log-tmp:	clean_tmp
	cat ${DROP_LOG_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > drop_log.tmp
drop-log:	drop-log-tmp
	${PSQL} -f drop_log.tmp
	${PSQL} -f vacuum.sql
	${MAKE} clean_tmp

create-log-tmp:	clean_tmp
	cat ${CREATE_LOG_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > create_log.tmp
create-log:	create-log-tmp
	${PSQL} -f create_log.tmp
	${PSQL} -f vacuum.sql
	${MAKE} clean_tmp


##########################################################################
# tsearch2 columns
create-tsearch-tmp:	clean_tmp
	cat ${CREATE_TSEARCH_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > create_tsearch.tmp
create-tsearch:	create-tsearch-tmp
	${PSQL} -f create_tsearch.tmp
	${PSQL} -f vacuum.sql
	${MAKE} clean_tmp

##########################################################################
# views
create-view-tmp: clean_tmp
	cat ${CREATE_VIEW_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > create_view.tmp
create-view: create-view-tmp
	${PSQL} -f create_view.tmp
	${MAKE} clean_tmp

drop-view-tmp: clean_tmp
	cat ${DROP_VIEW_FILES} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > drop_view.tmp
drop-view: drop-view-tmp
	${PSQL} -f drop_view.tmp
	${MAKE} clean_tmp


##########################################################################
# changesets
# (set default, not-existing changeset)
ID ?= not_available_changeset
create-changeset-tmp:	clean_tmp
	cat changes/${ID}*.sql | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > create.tmp
changeset:	create-changeset-tmp
	${PSQL} -f create.tmp
	${PSQL} -f vacuum.sql
	${MAKE} clean_tmp


##########################################################################
# create documentation
doc:
	#${PSQL_DOC} -t html -f ../docs/database_overview
	#${PSQL_DOC} -t xml -f ../docs/database_overview
	${PSQL_DOC} -t dot -f /tmp/database_overview

##########################################################################
# test data
test-tmp:	clean_tmp
	cat ${TEST_DATA} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > test.tmp
test:	test-tmp
	${PSQL} -f test.tmp
	${MAKE} clean_tmp

test-public-tmp:	clean_tmp
	cat ${PUBLIC_TEST_DATA} | sed -e "s/__SCHEMA__/${DB_SCHEMA}/g" \
	-e "s/__DB_NORMAL_GROUP__/${DB_NORMAL_GROUP}/g" \
	-e "s/__DB_ADMIN_GROUP__/${DB_ADMIN_GROUP}/g" \
	-e "s/__DB_NORMAL_USER__/${DB_NORMAL_USER}/g" \
	-e "s/__DB_ADMIN_USER__/${DB_ADMIN_USER}/g" > public-test.tmp
test-public:	test-public-tmp
	${PSQL} -f public-test.tmp
	${MAKE} clean_tmp

create-test-data:	clean_tmp
	./bin/create_test_data.pl -user test/user_test_data.txt > test/user_test_data.sql

##########################################################################
# helper

Makefile.local:
	@@echo ""
	@@echo "first create Makefile.local from Makefile.local.template"
	@@echo ""

console:
	@${PSQL}

clean_tmp:
# please do not indent the commentary lines due to _buggy_ win32-make
# workaround for buggy win32-make/rm
# create a dummy file that can safely be deleted
	echo a > so_nennt_keiner_seine_temp.tmp
	rm -f *.tmp

.PHONY:	clean_tmp all s1 s1-t s2 s2-t create-accounts drop-accounts create-schema drop-schema create-tmp create drop-tmp drop drop-public-tmp drop-public create-public-tmp create-public drop_log-tmp drop_log create_log-tmp create_log doc test-tmp test test-public-tmp test-public console
