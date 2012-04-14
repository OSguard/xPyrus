BEGIN;

INSERT INTO __SCHEMA__.soccer_tournaments (name, description)
    VALUES ('EM2008', 'Fußball Europameisterschaft 2008');

INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Schweiz', 'CH', 'A', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Portugal', 'PT', 'A', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Tschechische Republik', 'CZ', 'A', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Türkei', 'TR', 'A', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';

INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Österreich', 'AT', 'B', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Kroatien', 'HR', 'B', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Deutschland', 'DE', 'B', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Polen', 'PL', 'B', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';

INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Niederlande', 'NL', 'C', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Italien', 'IT', 'C', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Rumänien', 'RO', 'C', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Frankreich', 'FR', 'C', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';

INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Griechenland', 'GR', 'D', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Schweden', 'SE', 'D', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Spanien', 'ES', 'D', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_teams (name, name_short, group_name, tournament_id)
    SELECT 'Russland', 'RU', 'D', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';

INSERT INTO __SCHEMA__.soccer_stadiums (city, tournament_id)
    SELECT 'Wien', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_stadiums (city, tournament_id)
    SELECT 'Klagenfurt', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_stadiums (city, tournament_id)
    SELECT 'Salzburg', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_stadiums (city, tournament_id)
    SELECT 'Innsbruck', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_stadiums (city, tournament_id)
    SELECT 'Basel', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_stadiums (city, tournament_id)
    SELECT 'Bern', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_stadiums (city, tournament_id)
    SELECT 'Genf', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';
INSERT INTO __SCHEMA__.soccer_stadiums (city, tournament_id)
    SELECT 'Zürich', id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM2008';

COMMIT;
