BEGIN;

INSERT INTO __SCHEMA__.soccer_games 
    (team_1, team_2, start_time, stadium, game_type, tournament_id)
  VALUES
   ((SELECT id FROM __SCHEMA__.soccer_teams WHERE name_short = 'SUI' AND tournament_id = (SELECT id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM 2008')),
    (SELECT id FROM __SCHEMA__.soccer_teams WHERE name_short = 'CZE' AND tournament_id = (SELECT id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM 2008')),
    '2008-06-07 18:00:00',
    (SELECT id FROM __SCHEMA__.soccer_stadiums WHERE city = 'Basel' AND tournament_id = (SELECT id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM 2008')),
    1,
    (SELECT id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM 2008')
   );

INSERT INTO __SCHEMA__.soccer_games 
    (team_1, team_2, start_time, stadium, game_type, tournament_id)
  VALUES
   ((SELECT id FROM __SCHEMA__.soccer_teams WHERE name_short = 'POR' AND tournament_id = (SELECT id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM 2008')),
    (SELECT id FROM __SCHEMA__.soccer_teams WHERE name_short = 'TUR' AND tournament_id = (SELECT id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM 2008')),
    '2008-06-07 20:45:00',
    (SELECT id FROM __SCHEMA__.soccer_stadiums WHERE city = 'Genf' AND tournament_id = (SELECT id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM 2008')),
    1,
    (SELECT id FROM __SCHEMA__.soccer_tournaments WHERE name = 'EM 2008')
   );

COMMIT;

