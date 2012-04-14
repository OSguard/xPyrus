begin;

CREATE TABLE magdeburg.mrs_campus_candidates (
  id SERIAL PRIMARY KEY,
  cam_pic_id int NOT NULL default '0' UNIQUE,
  first_name varchar(60) NOT NULL default '',
  last_name varchar(60) NOT NULL default '',
  sex VARCHAR(1) NOT NULL default 'f' CHECK(sex in ('m','f')),
  study_path int NOT NULL default '0',
  telephone varchar(30) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  cam_pic_path varchar(250) NOT NULL default '',
  rating_entertainer int NOT NULL default '1',
  rating_sports int NOT NULL default '1',
  rating_sexy int NOT NULL default '1',
  rating_brain int NOT NULL default '1',
  UNIQUE (cam_pic_id)
) WITHOUT OIDS;

GRANT ALL ON magdeburg.mrs_campus_candidates TO GROUP unihelp;
GRANT ALL ON magdeburg.mrs_campus_candidates_id_seq TO GROUP unihelp;

CREATE TABLE magdeburg.mrs_campus_ratings (
  id    SERIAL PRIMARY KEY,
  user_id BIGINT NOT NULL
                 REFERENCES magdeburg.users(id),
  rating int NOT NULL,
  candidate int  NOT NULL
                 REFERENCES magdeburg.mrs_campus_candidates(id),
  UNIQUE(user_id,candidate)
) WITHOUT OIDS;

GRANT ALL ON magdeburg.mrs_campus_ratings TO GROUP unihelp;
GRANT ALL ON magdeburg.mrs_campus_ratings_id_seq TO GROUP unihelp;

commit;
