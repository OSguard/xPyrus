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
-- $Id: schema-soccer.sql 6210 2008-07-25 17:29:44Z trehn $
--
-- contains tables for soccer

BEGIN;


-- one entry per tournament
-- as example: EM 2008, WM 2010
CREATE TABLE __SCHEMA__.soccer_tournaments (
  id                      SERIAL                   PRIMARY KEY,
  name                    VARCHAR(50)              NOT NULL
                                                   UNIQUE,
  description             TEXT                     NOT NULL
                                                   DEFAULT '',
  group_stage             BOOLEAN                  NOT NULL,
  points_winner           INT                      NOT NULL
                                                   CHECK(points_winner >= 0)
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___soccer_tournaments ON __SCHEMA__.soccer_tournaments(name);
GRANT ALL ON __SCHEMA__.soccer_tournaments TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.soccer_tournaments_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_tournaments TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_tournaments_id_seq TO GROUP __DB_ADMIN_GROUP__;


-- all game types for the tournament
CREATE TABLE __SCHEMA__.soccer_game_types (
  id                      INTEGER                  NOT NULL
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(50)              NOT NULL
                                                   UNIQUE
) WITHOUT OIDS;
CREATE INDEX __SCHEMA___soccer_game_types ON __SCHEMA__.soccer_game_types(name);
GRANT ALL ON __SCHEMA__.soccer_game_types TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_game_types TO GROUP __DB_ADMIN_GROUP__;

-- all game types
INSERT INTO __SCHEMA__.soccer_game_types (id, name)
     VALUES (1, 'Vorrunde');
INSERT INTO __SCHEMA__.soccer_game_types (id, name)
     VALUES (2, 'Achtelfinale');
INSERT INTO __SCHEMA__.soccer_game_types (id, name)
     VALUES (3, 'Viertelfinale');
INSERT INTO __SCHEMA__.soccer_game_types (id, name)
     VALUES (4, 'Halbfinale');
INSERT INTO __SCHEMA__.soccer_game_types (id, name)
     VALUES (5, 'Spiel um Platz 3');
INSERT INTO __SCHEMA__.soccer_game_types (id, name)
     VALUES (6, 'Endspiel');



-- all teams, per tournament
CREATE TABLE __SCHEMA__.soccer_teams (
  id                      SERIAL                   PRIMARY KEY,
  name                    VARCHAR(200)             NOT NULL,
  name_short              VARCHAR(30)              NOT NULL,
  group_name              VARCHAR(20)              NOT NULL,
  tournament_id           INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_tournaments(id),
  UNIQUE(name, tournament_id),
  UNIQUE(name_short, tournament_id)
) WITHOUT OIDS;
GRANT ALL ON __SCHEMA__.soccer_teams TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.soccer_teams_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_teams TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_teams_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___soccer_teams_tournament_id ON  __SCHEMA__.soccer_teams(tournament_id);


-- all venues, per tournament
-- stadium name is optional, hopefully we don't have two stadiums in one city
CREATE TABLE __SCHEMA__.soccer_stadiums (
  id                      SERIAL                   PRIMARY KEY,
  city                    VARCHAR(200)             NOT NULL,
  stadium_name            VARCHAR(200)             NOT NULL
                                                   DEFAULT '',
  tournament_id           INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_tournaments(id),
  UNIQUE(city, tournament_id)
) WITHOUT OIDS;
GRANT ALL ON __SCHEMA__.soccer_stadiums TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.soccer_stadiums_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_stadiums TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_stadiums_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___soccer_stadiums_tournament_id ON  __SCHEMA__.soccer_stadiums(tournament_id);

-- all games, per tournament
CREATE TABLE __SCHEMA__.soccer_games (
  id                      SERIAL                   PRIMARY KEY,
  team_1                  INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_teams(id),
  team_2                  INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_teams(id),
  goals_team_1            INTEGER                  NULL
                                                   CHECK (goals_team_1 IS NULL OR goals_team_1 >= 0),
  goals_team_2            INTEGER                  NULL
                                                   CHECK (goals_team_2 IS NULL OR goals_team_2 >= 0),
  additional_info         VARCHAR(10)              NOT NULL
                                                   DEFAULT '',
  start_time              TIMESTAMPTZ              NULL,
  stadium                 INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_stadiums(id),
  game_type               INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_game_types(id),
  tournament_id           INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_tournaments(id),
  CHECK (team_1 != team_2)
) WITHOUT OIDS;
GRANT ALL ON __SCHEMA__.soccer_games TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.soccer_games_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_games TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_games_id_seq TO GROUP __DB_ADMIN_GROUP__;

CREATE INDEX __SCHEMA___soccer_games_tournament_id ON  __SCHEMA__.soccer_games(tournament_id);


-- all user tipps, per tournament
CREATE TABLE __SCHEMA__.soccer_tipps (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  game_id                 INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_games(id),
  goals_team_1            INTEGER                  NOT NULL,
  goals_team_2            INTEGER                  NOT NULL,
  points                  INTEGER                  NOT NULL
                                                   DEFAULT 0,
  last_change             TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  UNIQUE(game_id, user_id)
) WITHOUT OIDS;
GRANT ALL ON __SCHEMA__.soccer_tipps TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.soccer_tipps_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_tipps TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_tipps_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- all winners, per tournament
CREATE TABLE __SCHEMA__.soccer_tipps_winner (
  id                      BIGSERIAL                PRIMARY KEY,
  user_id                 BIGINT                   NOT NULL
                                                   REFERENCES __SCHEMA__.users(id)
                                                   ON DELETE CASCADE,
  winner_is               INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_teams(id)
                                                   ON DELETE CASCADE,
  points                  INTEGER                  NOT NULL
                                                   DEFAULT 0,
  points_credited         INTEGER                  NOT NULL
                                                   DEFAULT 0,
  tournament_id           INTEGER                  NOT NULL
                                                   REFERENCES __SCHEMA__.soccer_tournaments(id),
  last_change             TIMESTAMPTZ              NOT NULL
                                                   DEFAULT NOW(),
  UNIQUE(winner_is, user_id),
  UNIQUE(user_id, tournament_id)
) WITHOUT OIDS;
GRANT ALL ON __SCHEMA__.soccer_tipps_winner TO GROUP __DB_NORMAL_GROUP__;
GRANT SELECT,UPDATE ON __SCHEMA__.soccer_tipps_winner_id_seq TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_tipps_winner TO GROUP __DB_ADMIN_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_tipps_winner_id_seq TO GROUP __DB_ADMIN_GROUP__;

-- helper table
-- per tournament ranking
CREATE TABLE __SCHEMA__.soccer_tipps_ranking (
    tournament_id   INT         NOT NULL
                                REFERENCES __SCHEMA__.soccer_tournaments(id),
    rank            INT         NOT NULL,
    user_id         BIGINT      NOT NULL
                                REFERENCES __SCHEMA__.users(id)
                                ON DELETE CASCADE,
    points          INT         NOT NULL,
    points_credited INTEGER     NOT NULL
                                DEFAULT 0,
    UNIQUE(tournament_id, user_id)
) WITHOUT OIDS;
GRANT ALL ON __SCHEMA__.soccer_tipps_ranking TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON __SCHEMA__.soccer_tipps_ranking TO GROUP __DB_ADMIN_GROUP__;

COMMIT;
