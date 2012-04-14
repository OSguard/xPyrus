INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             userpic_file,
             flirt_status,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('HomerJaySimpson',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             '/userfiles/users/1/a0a59113b097ea98____big___homer_simpson_in_cerne_abbans.jpg',
             '2red',
             'm',
             '1968-12-05',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '12245',
             '12245',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Homer',
       second_name='Jay',
       last_name='Simpson',
       zip_code='12345',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='HomerJaySimpson');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             userpic_file,
             flirt_status,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Marge_Simpson',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             '/userfiles/users/2/4b182442927da3d5____big___13605980_ab1fcc16ff_o.jpg',
             '2red',
             'f',
             '1970-12-06',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '12245',
             '12245',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Marge',
       last_name='Simpson',
       zip_code='12345',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Marge_Simpson');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             userpic_file,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('Bart_Simpson',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             '/userfiles/users/3/ca3c8248bfcc31e6____big___2212688238_404dbdb8f8_b.jpg',
             'm',
             '2001-09-11',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Bart',
       last_name='Simpson',
       zip_code='56789',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Bart_Simpson');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             userpic_file,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('Lisa_Simpson',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             '/userfiles/users/4/9573609c73a71bac____big___2127762652_2b9754f040_b.jpg',
             'f',
             '2000-12-05',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Lisa',
       last_name='Simpson',
       zip_code='91011',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Lisa_Simpson');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('Meggy_Simpson',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'f',
             '2007-03-12',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Meggy',
       last_name='Simpson',
       zip_code='39116',
       location='Springfield',
       fax=' ',
       public_email=' ',
       private_email=' ',
       uni_email='meggy.simpson@student.uni-Springfield.de'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Meggy_Simpson');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             userpic_file,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Milhouse',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             '/userfiles/users/6/4c70f112c2596c80____big___68971077_a58159a004_b.jpg',
             'm',
             '1976-04-06',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '5432',
             '5432',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Mussolini',
       last_name='Milhouse von Houten',
       zip_code='39218',
       location='Springfield',
       uni_email='milhouse@student.et.hs-Springfield.de'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Milhouse');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Mr_Burns',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '1984-12-05',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '42000000',
             '42000000',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET zip_code='12345',
       location='Springfield',
       public_email='chef@kraftwerk-springfield..tv',
       uni_email='Mr_Burns@student.uni-Springfield.de'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Mr_Burns');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Carl',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '1979-01-23',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '5432',
             '5432',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Carl',
       second_name=' ',
       last_name='Carlson ',
       zip_code='12345',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Carl');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             flirt_status,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Krusty',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             '2red',
             'm',
             '1983-12-13',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '5432',
             '5432',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Krusty',
       zip_code='12345',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Krusty');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Larry',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '1982-10-06',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '5432',
             '5432',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Larry',
       zip_code='12345',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Larry');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Moe',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '1983-05-16',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '5432',
             '5432',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Moe',
       last_name='Szyslak',
       zip_code='12345',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Moe');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Hausmeister',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '1978-10-30',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '5432',
             '5432',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='William',
       last_name='MacMoran',
       zip_code='12345',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Hausmeister');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('Rektor',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '1983-11-18',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             '1332',
             '1332',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Tobias',
       last_name='Kalbitz',
       zip_code='39000',
       location='Springfield'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Rektor');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('kaiser',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '2004-12-05',
             '43a6e0785487a75e82ea08ef269456544bba2cff',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Franz',
       last_name='Beckenbauer',
       zip_code='84527',
       location='Starnberg'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='kaiser');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('wolfgang_buck',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '2004-12-05',
             'ef65de1c7be0aa837fe7b25ba9a7739905af6a55',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Wolfgang',
       last_name='Buck',
       zip_code='91731',
       location='Fï¿œd'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='wolfgang_buck');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('Claudia',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'f',
             '2004-12-05',
             '568095ee7b98b0afceb32540a1ca5540eaa72666',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Claudia',
       last_name='Hund',
       zip_code='52341',
       location='Arzthausen'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Claudia');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('kanzlerin_in_berlin',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Dozent'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'f',
             '2004-12-05',
             'affd33dec31b0844c378d4ba0506c22f4c3f1ce9',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Angela',
       last_name='Merkel',
       zip_code='10001',
       location='Berlin'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='kanzlerin_in_berlin');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('trinity',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'f',
             '2004-12-05',
             '66b9283dcf8a7d913f04ead72e559c727d9f1d82',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Trinity',
       last_name='Maier',
       zip_code='57489',
       location='Matrix'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='trinity');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('architect',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Dozent'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             'm',
             '2004-12-05',
             'bf2f749e80c970f50552e9d5f3e8434e78b88d35',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='Chef',
       last_name='Architekt',
       zip_code='57489',
       location='Matrix'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='architect');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             flirt_status,
             gender,
             birthdate,
             password,
             first_login,
             last_login)
     VALUES ('sunburner',
             true,
             true,
             false,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='amerikanisch'),
             '1yellow',
             'm',
             '2006-09-08',
             '791d3c1742b0e1aace12fd53b5ef1db76966e469',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET first_name='El',
       last_name='Cheffe',
       zip_code='39108',
       location='Springfield',
       uni_email='arno.nym@student.uni-Springfield.de'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='sunburner');


INSERT INTO __SCHEMA__.users
            (username,
             flag_activated,
             flag_active,
             flag_invisible,
             uni_id,
             person_type,
             nationality_id,
             flirt_status,
             gender,
             birthdate,
             password,
             points_sum,
             points_flow,
             first_login,
             last_login)
     VALUES ('UniHelp-System',
             true,
             false,
             true,
             (SELECT id
                FROM public.uni
               WHERE name='University of Springfield'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='Springfield')),
             (SELECT id
                FROM public.person_types
               WHERE name='Student'),
             (SELECT id
                FROM public.countries
               WHERE nationality='unbekannt'),
             '3none',
             'f',
             '1999-01-01',
             'ef191d368776bae152d28f0b6f9f735e737b5cac',
             '23',
             '23',
             NOW(),
             NOW());

UPDATE __SCHEMA__.user_extra_data
   SET title=' ',
       salutation=' ',
       first_name=' ',
       second_name=' ',
       last_name=' ',
       zip_code=' ',
       location=' ',
       street=' ',
       telephone=' ',
       telephone_mobil=' ',
       fax=' ',
       public_email='team@unihelp.de',
       private_email='team@unihelp.de',
       uni_email='team@unihelp.de'
 WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='UniHelp-System');



INSERT INTO __SCHEMA__.user_friends
    (user_id, friend_id, friend_type)
  VALUES
    ((SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Meggy_Simpson'),
     (SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
     (SELECT id::INTEGER FROM public.friend_types WHERE type_name='Love'));

INSERT INTO __SCHEMA__.user_friends
    (user_id, friend_id, friend_type)
  VALUES
    ((SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Meggy_Simpson'),
     (SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Marge_Simpson'),
     (SELECT id::INTEGER FROM public.friend_types WHERE type_name='Normal'));

INSERT INTO __SCHEMA__.user_friends
    (user_id, friend_id, friend_type)
  VALUES
    ((SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Meggy_Simpson'),
     (SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),
     (SELECT id::INTEGER FROM public.friend_types WHERE type_name='Normal'));

INSERT INTO __SCHEMA__.user_friends
    (user_id, friend_id, friend_type)
  VALUES
    ((SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Milhouse'),
     (SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
     (SELECT id::INTEGER FROM public.friend_types WHERE type_name='Normal'));


INSERT INTO __SCHEMA__.user_role_membership
		(user_id, role_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Mr_Burns'),
            (SELECT id FROM __SCHEMA__.user_roles WHERE name='blog_owners') );

INSERT INTO __SCHEMA__.user_role_membership
		(user_id, role_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'),
            (SELECT id FROM __SCHEMA__.user_roles WHERE name='blog_owners') );


INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	SELECT (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'), id, true FROM __SCHEMA__.rights;

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	SELECT (SELECT id FROM __SCHEMA__.users WHERE username='Marge_Simpson'), id, true FROM __SCHEMA__.rights;

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	SELECT (SELECT id FROM __SCHEMA__.users WHERE username='Milhouse'), id, true FROM __SCHEMA__.rights;

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	SELECT (SELECT id FROM __SCHEMA__.users WHERE username='Mr_Burns'), id, true FROM __SCHEMA__.rights;

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='BANNER_ADMIN'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='FORUM_CATEGORY_ADMIN'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='USER_RIGHT_ADMIN'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='ROLE_ADMIN'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='GROUP_ADMIN'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='NEWS_ENTRY_ADD'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Rektor'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='NEWS_ENTRY_EDIT'),
            true );

INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	SELECT (SELECT id FROM __SCHEMA__.users WHERE username='sunburner'), id, true FROM __SCHEMA__.rights;


INSERT INTO __SCHEMA__.study_path_per_student
		(user_id, study_path_id, primary_course)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'),
            (SELECT id FROM __SCHEMA__.study_path WHERE name='Mathematik'), true );

INSERT INTO __SCHEMA__.study_path_per_student
		(user_id, study_path_id, primary_course)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='Marge_Simpson'),
            (SELECT id FROM __SCHEMA__.study_path WHERE name='Informatik'), true );


