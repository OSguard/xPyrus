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
-- Comments in PostgreSQL start with --
-- or C-Style with /* */
--
-- option for use a file in psql is -f
--
-- for every database the following commands are to
-- executed on the console:
-- createlang plpgsql <databasename>
-- (as postgres user)
--
-- database design for unihelp v2
--
-- design started 2005/06/14 (by ads)
--
-- $Id: schema-users.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all users

-- notes:
-- flag_activated: user has activated his account
-- flag_active: user is active, administration may set this to FALSE
-- flag_invisible: user data is not shown on website

CREATE TABLE __SCHEMA__.users (
  id                      BIGSERIAL                PRIMARY KEY,
  last_change             TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  insert_at               TIMESTAMPTZ              NOT NULL,
  username                VARCHAR(50)              NOT NULL
                                                   UNIQUE
                                                   CHECK(LENGTH(username) > 0),
  flag_activated          BOOLEAN                  NOT NULL
                                                   DEFAULT (FALSE),
  flag_active             BOOLEAN                  NOT NULL
                                                   DEFAULT (TRUE),
  flag_invisible          BOOLEAN                  NOT NULL
                                                   DEFAULT (FALSE),
  uni_id                  INTEGER                  NOT NULL
                                                   REFERENCES public.uni(id),
  person_type             INTEGER                  NOT NULL
                                                   REFERENCES public.person_types(id),
  nationality_id          SMALLINT                 NOT NULL
                                                   DEFAULT 1
                                                   REFERENCES public.countries(id),
  userpic_file            VARCHAR(250)             NOT NULL
                                                   DEFAULT '',
  flirt_status            VARCHAR(7)               CHECK (flirt_status IN ('3none', '0green', '1yellow', '2red')) DEFAULT '3none',
  points_sum              BIGINT                   NOT NULL
                                                   DEFAULT 0,
  points_flow             INTEGER                  NOT NULL
                                                   DEFAULT 0,
  activity_index          NUMERIC(4,3)             NOT NULL
                                                   DEFAULT 0.0,
  gender                  VARCHAR(1)               CHECK (gender IN ('', 'm', 'f')) DEFAULT '',
  birthdate               DATE                     NOT NULL
                                                   DEFAULT '0001-01-01',
  signature_raw           TEXT                     NOT NULL
                                                   DEFAULT '',
  signature_parsed        TEXT                     NOT NULL
                                                   DEFAULT '',
  password                VARCHAR(40)              NOT NULL
                                                   DEFAULT '',
  original_password       VARCHAR(40)              NOT NULL
                                                   DEFAULT '',
  first_login             TIMESTAMPTZ              DEFAULT '0001-01-01',
  last_login              TIMESTAMPTZ              DEFAULT '0001-01-01',
  rand                    DOUBLE PRECISION         NOT NULL
                                                   DEFAULT 0.0
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___users_username ON __SCHEMA__.users(username);
CREATE INDEX __SCHEMA___users_username_lower ON __SCHEMA__.users(LOWER(username));
CREATE INDEX __SCHEMA___users_flag_activated ON __SCHEMA__.users(flag_activated);
CREATE INDEX __SCHEMA___users_flag_active ON __SCHEMA__.users(flag_active);

-- for latest user
CREATE INDEX __SCHEMA___users_first_login ON __SCHEMA__.users(first_login);

-- for random userpic
CREATE INDEX __SCHEMA___users_rand ON __SCHEMA__.users(rand);

CREATE INDEX __SCHEMA___users_flag_invisible ON __SCHEMA__.users(flag_invisible);
CREATE INDEX __SCHEMA___users_nationality_id ON __SCHEMA__.users(nationality_id);
CREATE INDEX __SCHEMA___users_uni_id ON __SCHEMA__.users(uni_id);
GRANT ALL ON __SCHEMA__.users TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.users_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.users TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.users_id_seq TO GROUP __DB_ADMIN_GROUP__;
-- set the current timestamp on 'insert_at'
CREATE TRIGGER __SCHEMA___users_insert BEFORE INSERT ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.set_insert_at();
-- copy the original password
CREATE TRIGGER __SCHEMA___users_insert_password BEFORE INSERT ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.set_password_on_insert();
-- set the current timestamp on 'last_change'
CREATE TRIGGER __SCHEMA___users_update BEFORE UPDATE ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.set_last_change_at();
-- update user stats 
CREATE TRIGGER __SCHEMA___users_update_stats AFTER UPDATE ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.update_point_user_stats('__SCHEMA__');
-- set membership in default groups
CREATE TRIGGER __SCHEMA___users_insert_default_group AFTER INSERT ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.set_user_default_role('__SCHEMA__');

-- set random value for user with userpic
CREATE TRIGGER __SCHEMA___users_ins_upd_random_user BEFORE INSERT OR UPDATE ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.set_user_random('__SCHEMA__');

-- -- add into random user table
--CREATE TRIGGER __SCHEMA___users_ins_upd_random_user AFTER INSERT OR UPDATE ON __SCHEMA__.users FOR EACH ROW
--               EXECUTE PROCEDURE public.random_user_add('__SCHEMA__');
-- delete from random user table
--CREATE TRIGGER __SCHEMA___users_del_random_user BEFORE DELETE ON __SCHEMA__.users FOR EACH ROW
--               EXECUTE PROCEDURE public.random_user_del('__SCHEMA__');
-- protect against id changes
CREATE RULE __SCHEMA___users_upd
            AS ON UPDATE TO __SCHEMA__.users
            WHERE old.id != new.id
            DO INSTEAD nothing;


-- contains some values which we want in an extra table to have a small usertable
CREATE TABLE __SCHEMA__.user_extra_data (
  id                      BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  last_change             TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  title                   VARCHAR(100)             NOT NULL
                                                   DEFAULT '',
  salutation              VARCHAR(50)              NOT NULL
                                                   DEFAULT '',
  first_name              VARCHAR(30)              NOT NULL
                                                   DEFAULT '',
  second_name             VARCHAR(30)              NOT NULL
                                                   DEFAULT '',
  last_name               VARCHAR(100)             NOT NULL
                                                   DEFAULT '',
  zip_code                INTEGER                  NOT NULL
                                                   DEFAULT 0,
  location                VARCHAR(50)              NOT NULL
                                                   DEFAULT '',
  street                  VARCHAR(70)              NOT NULL
                                                   DEFAULT '',
  telephone               VARCHAR(40)              NOT NULL
                                                   DEFAULT '',
  telephone_mobil         VARCHAR(20)              NOT NULL
                                                   DEFAULT '',
  fax                     VARCHAR(40)              NOT NULL
                                                   DEFAULT '',
  public_email            VARCHAR(80)              NOT NULL
                                                   DEFAULT '',
                                                   CHECK (CHECK_EMAIL(public_email)),
  private_email           VARCHAR(80)              NOT NULL
                                                   DEFAULT ''
                                                   CHECK (CHECK_EMAIL(private_email)),
  uni_email               VARCHAR(80)              NOT NULL
                                                   DEFAULT ''
                                                   CHECK (CHECK_EMAIL(uni_email)),
  PRIMARY KEY(id)                                   
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___userid ON __SCHEMA__.user_extra_data(id);
CREATE INDEX __SCHEMA___user_extra_data_lower_uni_email ON __SCHEMA__.user_extra_data(LOWER(uni_email));
GRANT ALL ON __SCHEMA__.user_extra_data TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_extra_data TO GROUP __DB_ADMIN_GROUP__;
-- set the current timestamp on 'last_change'
-- since this table is filled with an empty entry during inserts on
-- schema.users, we don't need to add an extra insert_at field
CREATE TRIGGER __SCHEMA___user_extra_data_update BEFORE UPDATE ON __SCHEMA__.user_extra_data FOR EACH ROW
               EXECUTE PROCEDURE public.set_only_last_change_at();


-- contains user config values (all configuration data for the platform)
CREATE TABLE __SCHEMA__.user_config (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  data_name_id            INTEGER                  NOT NULL
                                                   REFERENCES public.user_config_keys(id),
  data_value              TEXT                     NOT NULL
                                                   DEFAULT '',
  UNIQUE (user_id, data_name_id)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_config_user_id ON __SCHEMA__.user_config(user_id);
CREATE INDEX __SCHEMA___user_config_data_name_id ON __SCHEMA__.user_config(data_name_id);
GRANT ALL ON __SCHEMA__.user_config TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_config_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_config TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_config_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- contains user data (a lot of counters)
CREATE TABLE __SCHEMA__.user_data (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  data_name_id            INTEGER                  NOT NULL
                                                   REFERENCES public.user_data_keys(id),
  data_value              TEXT                     NOT NULL
                                                   DEFAULT '',
  UNIQUE (user_id, data_name_id)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_data_user_id ON __SCHEMA__.user_data(user_id);
CREATE INDEX __SCHEMA___user_data_data_name_id ON __SCHEMA__.user_data(data_name_id);
GRANT ALL ON __SCHEMA__.user_data TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_data_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_data TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_data_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- contains user contact data
CREATE TABLE __SCHEMA__.user_contact_data (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  data_name_id            INTEGER                  NOT NULL
                                                   REFERENCES public.user_contact_data_keys(id),
  data_value              TEXT                     NOT NULL
                                                   DEFAULT '',
  UNIQUE (user_id, data_name_id)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_contact_data_user_id ON __SCHEMA__.user_contact_data(user_id);
CREATE INDEX __SCHEMA___user_contact_data_data_name_id ON __SCHEMA__.user_contact_data(data_name_id);
GRANT ALL ON __SCHEMA__.user_contact_data TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_contact_data_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_contact_data TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_contact_data_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- contains user data (a lot of counters)
CREATE TABLE __SCHEMA__.user_privacy (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  data_name_id            INTEGER                  NOT NULL
                                                   REFERENCES public.user_privacy_keys(id),
  data_value              INTEGER                  NOT NULL
                                                   DEFAULT 1
                                                   REFERENCES public.details_visible(id),
  UNIQUE (user_id, data_name_id)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_privacy_user_id ON __SCHEMA__.user_privacy(user_id);
CREATE INDEX __SCHEMA___user_privacy_data_name_id ON __SCHEMA__.user_privacy(data_name_id);
GRANT ALL ON __SCHEMA__.user_privacy TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_privacy_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_privacy TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_privacy_id_seq TO GROUP __DB_ADMIN_GROUP__;


-- insert some default data for each user into __SCHEMA__.users
CREATE TRIGGER __SCHEMA___copy_groups AFTER INSERT ON __SCHEMA__.users FOR EACH ROW
               EXECUTE PROCEDURE public.spread_user_data('__SCHEMA__');



-- contains user contact data
CREATE TABLE __SCHEMA__.user_activation (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  activation_string       VARCHAR(32)              NOT NULL
                                                   CHECK(LENGTH(activation_string)>0),
  UNIQUE (user_id)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___user_activation_user_id ON __SCHEMA__.user_activation(user_id);
CREATE INDEX __SCHEMA___user_activation_activation_string ON __SCHEMA__.user_activation(activation_string);
GRANT ALL ON __SCHEMA__.user_activation TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.user_activation_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.user_activation TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.user_activation_id_seq TO GROUP __DB_ADMIN_GROUP__;

COMMIT;
