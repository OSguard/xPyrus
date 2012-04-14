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
-- contains the countries
--
-- $Id: public-countries.sql 5743 2008-03-25 19:48:14Z ads $
--


BEGIN;


-- ###########################################################################
-- contains all countries
CREATE TABLE public.countries (
  id                      SMALLINT                 NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(60)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  nationality             VARCHAR(40)              NOT NULL
                                                   UNIQUE,
  iso_code                 VARCHAR(3)               NOT NULL
                                                   UNIQUE
  /*area_code               VARCHAR(10)              NOT NULL
                                                   UNIQUE,
  zip_code                VARCHAR(10)              NOT NULL
                                                   UNIQUE,
  zip_code_length         SMALLINT                 NOT NULL
                                                   DEFAULT 5
                                                   CHECK (zip_code_length > 3 OR zip_code_length < 7)*/
) WITHOUT OIDS;
COMMENT ON TABLE public.countries IS 'contains all known countries';
INSERT INTO public.countries (id, name,      nationality, iso_code)
                      VALUES ( 1, 'unbekannt', 'unbekannt',   '?');
CREATE RULE public_countries_prot_upd
            AS ON UPDATE TO public.countries
            WHERE old.id = 1
            DO INSTEAD nothing;
CREATE RULE public_countries_prot_del
            AS ON DELETE TO public.countries
            WHERE old.id = 1
            DO INSTEAD nothing;
GRANT SELECT ON public.countries TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.countries TO GROUP __DB_ADMIN_GROUP__;


-- Countries
/*INSERT INTO public.countries (id, name,          nationality,     area_code, zip_code)
                      VALUES (2,  'Deutschland', 'deutsch',       '49',      'D');
INSERT INTO public.countries (id, name,          nationality,     area_code, zip_code)
                      VALUES (3,  'Österreich',  'österreichisch', '43',      'A');
INSERT INTO public.countries (id, name,          nationality,     area_code, zip_code)
                      VALUES (4,  'Schweiz',     'schweizerisch', '41',      'CH');*/


INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (2,'AF','Afghanistan','afghanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (3,'EG','Ägypten','ägyptisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (4,'AX','Ålandinseln','åländisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (5,'AL','Albanien','albanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (6,'DZ','Algerien','algerisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (7,'AD','Andorra','andorranisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (8,'AO','Angola','angolanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (9,'AG','Antigua und Barbuda','antiguanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (10,'GQ','Äquatorialguinea','äquatorialguineisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (11,'AR','Argentinien','argentinisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (12,'AM','Armenien','armenisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (13,'AZ','Aserbaidschan','aserbaidschanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (14,'ET','Äthiopien','äthiopisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (15,'AU','Australien','australisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (16,'BS','Bahamas','bahamaisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (17,'BH','Bahrain','bahrainisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (18,'BD','Bangladesch','bangladeschisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (19,'BB','Barbados','barbadisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (20,'BE','Belgien','belgisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (21,'BZ','Belize','belizisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (22,'BJ','Benin','beninisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (23,'BT','Bhutan','bhutanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (24,'BO','Bolivien','bolivianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (25,'BA','Bosnien und Herzegowina','bosnisch-herzegowinisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (26,'BW','Botsuana','botsuanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (27,'BR','Brasilien','brasilianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (28,'BN','Brunei Darussalam','bruneiisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (29,'BG','Bulgarien','bulgarisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (30,'BF','Burkina Faso','burkinisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (31,'BI','Burundi','burundisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (32,'CL','Chile','chilenisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (33,'CN','China','chinesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (34,'CK','Cookinseln','der Cookinseln');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (35,'CR','Costa Rica','costa-ricanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (36,'CI','Côte d’Ivoire','ivorisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (37,'DK','Dänemark','dänisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (38,'DE','Deutschland','deutsch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (39,'DM','Dominica','dominicanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (40,'DO','die Dominikanische Republik','dominikanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (41,'DJ','Dschibuti','dschibutisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (42,'EC','Ecuador','ecuadorianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (43,'SV','El Salvador','salvadorianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (44,'ER','Eritrea','eritreisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (45,'EE','Estland','estnisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (46,'FJ','Fidschi','fidschianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (47,'FI','Finnland','finnisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (48,'FR','Frankreich','französisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (49,'GA','Gabun','gabunisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (50,'GM','Gambia','gambisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (51,'GE','Georgien','georgisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (52,'GH','Ghana','ghanaisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (53,'GD','Grenada','grenadisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (54,'GR','Griechenland','griechisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (55,'GT','Guatemala','guatemaltekisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (56,'GN','Guinea','guineisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (57,'GW','Guinea-Bissau','guinea-bissauisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (58,'GY','Guyana','guyanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (59,'HT','Haiti','haitianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (60,'HN','Honduras','honduranisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (61,'IN','Indien','indisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (62,'ID','Indonesien','indonesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (63,'IQ','Irak','irakisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (64,'IR','Iran','iranisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (65,'IE','Irland','irisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (66,'IS','Island','isländisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (67,'IL','Israel','israelisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (68,'IT','Italien','italienisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (69,'JM','Jamaika','jamaikanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (70,'JP','Japan','japanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (71,'YE','Jemen','jemenitisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (72,'JO','Jordanien','jordanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (73,'KH','Kambodscha','kambodschanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (74,'CM','Kamerun','kamerunisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (75,'CA','Kanada','kanadisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (76,'CV','Kap Verde','kap-verdisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (77,'KZ','Kasachstan','kasachisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (78,'QA','Katar','katarisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (79,'KE','Kenia','kenianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (80,'KG','Kirgisistan','kirgisisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (81,'KI','Kiribati','kiribatisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (82,'CO','Kolumbien','kolumbianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (83,'KM','Komoren','Komorer/in');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (84,'CD','die Demokratische Republik Kongo','der Demokratischen Republik Kongo');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (85,'CG','Kongo','kongolesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (86,'KP','die Demokratische Volksrepublik Korea','der Demokratischen Volksrepublik Korea');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (87,'KR','Korea','der Republik Korea');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (88,'HR','Kroatien','kroatisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (89,'CU','Kuba','kubanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (90,'KW','Kuwait','kuwaitisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (91,'LA','die Demokratische Volksrepublik Laos','laotisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (92,'LS','Lesotho','lesothisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (93,'LV','Lettland','lettisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (94,'LB','Libanon','libanesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (95,'LR','Liberia','liberianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (96,'LY','Libyen','libysch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (97,'LI','Liechtenstein','liechtensteinisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (98,'LT','Litauen','litauisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (99,'LU','Luxemburg','luxemburgisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (100,'MG','Madagaskar','madagassisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (101,'MW','Malawi','malawisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (102,'MY','Malaysia','malaysisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (103,'MV','Malediven','maledivisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (104,'ML','Mali','malisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (105,'MT','Malta','maltesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (106,'MA','Marokko','marokkanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (107,'MH','Marshallinseln','marshallisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (108,'MR','Mauretanien','mauretanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (109,'MU','Mauritius','mauritisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (110,'MK','die ehemalige jugoslawische Republik Mazedonien','mazedonisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (111,'MX','Mexiko','mexikanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (112,'FM','die Föderierten Staaten von Mikronesien','mikronesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (113,'MD','die Republik Moldau','moldauisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (114,'MC','Monaco','monegassisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (115,'MN','die Mongolei','mongolisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (116,'MZ','Mosambik','mosambikanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (117,'MM','Myanmar','myanmarisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (118,'NA','Namibia','namibisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (119,'NR','Nauru','nauruisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (120,'NP','Nepal','nepalesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (121,'NZ','Neuseeland','neuseeländisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (122,'NI','Nicaragua','nicaraguanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (123,'NL','die Niederlande','niederländisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (124,'NE','Niger','nigrisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (125,'NG','Nigeria','nigerianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (126,'NU','Niue (','niueanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (127,'NO','Norwegen','norwegisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (128,'OM','Oman','omanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (129,'AT','Österreich','österreichisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (130,'TL','Osttimor','osttimorisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (131,'PK','Pakistan','pakistanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (132,'PW','Palau','palauisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (133,'PA','Panama','panamaisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (134,'PG','Papua-Neuguinea','papua-neuguineisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (135,'PY','Paraguay','paraguayisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (136,'PE','Peru','peruanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (137,'PH','die Philippinen','philippinisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (138,'PL','Polen','polnisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (139,'PT','Portugal','portugiesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (140,'RW','Ruanda','ruandisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (141,'RO','Rumänien','rumänisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (142,'RU','die Russische Föderation','russisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (143,'SB','die Salomonen','salomonisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (144,'ZM','Sambia','sambisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (145,'WS','Samoa','samoanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (146,'SM','San Marino','san-marinesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (147,'ST','São Tomé und Príncipe','são-toméisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (148,'SA','Saudi-Arabien','saudi-arabisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (149,'SE','Schweden','schwedisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (150,'CH','die Schweiz','schweizerisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (151,'SN','Senegal','senegalesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (152,'CS','Serbien und Montenegro','serbisch-montenegrinisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (153,'SC','die Seychellen','seychellisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (154,'SL','Sierra Leone','sierra-leonisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (155,'ZW','Simbabwe','simbabwisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (156,'SG','Singapur','singapurisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (157,'SK','die Slowakei','slowakisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (158,'SI','Slowenien','slowenisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (159,'SO','Somalia','somalisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (160,'ES','Spanien','spanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (161,'LK','Sri Lanka','sri-lankisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (162,'KN','St. Kitts und Nevis','St. Kitts und Nevis');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (163,'LC','St. Lucia','lucianisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (164,'VC','St. Vincent und die Grenadinen','vincentisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (165,'ZA','Südafrika','südafrikanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (166,'SD','Sudan','sudanesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (167,'SR','Suriname','surinamisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (168,'SZ','Swasiland','swasiländisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (169,'SY','die Arabische Republik Syrien','syrisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (170,'TJ','Tadschikistan','tadschikisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (171,'TW','Taiwan','taiwanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (172,'TZ','die Vereinigte Republik Tansania','tansanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (173,'TH','Thailand','thailändisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (174,'TG','Togo','togoisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (175,'TO','Tonga','tongaisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (176,'TT','Trinidad und Tobago','Trinidad und Tobago');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (177,'TD','Tschad','tschadisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (178,'CZ','die Tschechische Republik','tschechisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (179,'TN','Tunesien','tunesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (180,'TR','die Türkei','türkisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (181,'TM','Turkmenistan','turkmenisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (182,'TV','Tuvalu','tuvaluisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (183,'UG','Uganda','ugandisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (184,'UA','die Ukraine','ukrainisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (185,'HU','Ungarn','ungarisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (186,'UY','Uruguay','uruguayisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (187,'UZ','Usbekistan','usbekisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (188,'VU','Vanuatu','vanuatuisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (189,'VA','die Vatikanstadt','vatikanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (190,'VE','Venezuela','venezolanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (191,'AE','die Vereinigten Arabischen Emirate','Vereinigte Arabischen Emirate');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (192,'US','die Vereinigten Staaten','amerikanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (193,'GB','das Vereinigte Königreich','britisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (194,'VN','Vietnam','vietnamesisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (195,'BY','Weißrussland','weißrussisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (196,'CF','die Zentralafrikanische Republik','zentralafrikanisch');
INSERT INTO public.countries (id, iso_code, name, nationality) VALUES (197,'CY','Zypern','zyprisch');

INSERT INTO public.countries (id, name, nationality, iso_code) VALUES
    ((SELECT MAX(id)+1 FROM public.countries), 'England', 'englisch', 'ENG');
INSERT INTO public.countries (id, name, nationality, iso_code) VALUES
    ((SELECT MAX(id)+1 FROM public.countries), 'Schottland', 'schottisch', 'SCO');
INSERT INTO public.countries (id, name, nationality, iso_code) VALUES
    ((SELECT MAX(id)+1 FROM public.countries), 'Wales', 'walisisch', 'WAL');

COMMIT;
