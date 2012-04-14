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
-- used for our stored procedure
INSERT INTO __SCHEMA__.forum_categories VALUES (1, 'Fächer', 'Rund um die Fächer Deines Studiums', 'Rund um die Fächer Deines Studiums', 4, 0, 0, 'default.tpl', 'course');
-- a quasi virtual category without fora
INSERT INTO __SCHEMA__.forum_categories VALUES (2, 'Organisationen', 'Alles von und mit studentischen Organisationen', 'Alles von und mit studentischen Organisationen', 5, 0, 0, 'default.tpl', 'group');

INSERT INTO __SCHEMA__.forum_categories VALUES (3, 'Studentenleben', 'Hier ist das Forum zu allen Themen was dich in deinem Leben als Student bewegt.', 'Hier ist das Forum zu allen Themen was dich in deinem Leben als Student bewegt.', 1, 11, 0, 'default.tpl', 'default');
INSERT INTO __SCHEMA__.forum_categories VALUES (4, 'Marktplatz', 'Hier wird alles gehandelt.', 'Hier wird alles gehandelt.', 2, 6, 0, 'black_board.tpl', 'market');
-- legacy category
INSERT INTO __SCHEMA__.forum_categories VALUES (5, 'Studiengang', 'die Studiengangsforen aus UniHelp 1.0', 'die Studiengangsforen aus UniHelp 1.0', 3, 0, 0, 'default.tpl', 'course_old');

SELECT setval('__SCHEMA__.forum_categories_id_seq', 5);

INSERT INTO __SCHEMA__.forum_fora VALUES (1, 3, NULL, 'Tagespolitik', 'News und Politik aus der ganzen Welt.', 'News und Politik aus der ganzen Welt.', 1, true, 1, true, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (2, 3, NULL, 'Hochschulpolitik', 'Alles was an Deiner Uni los ist.', 'Alles was an Deiner Uni los ist.', 2, true, 1, true, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (15, 3, NULL, 'Organisationen', 'Alles zu den studentischen Organisationen an Deiner Uni', 'Alles zu den studentischen Organisationen an Deiner Uni', 3, true, 1, true, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (3, 3, NULL, 'Veranstaltungen', 'Was ist los in Deiner Stadt', 'Was ist los in Deiner Stadt', 4, false, 1, true, true, false, true, false, false, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (4, 3, 3, 'Partys', 'Wo wird am besten gefeiert?', 'Wo wird am besten gefeiert?', 5, true, 1, true, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (5, 3, 3, 'kullturelle Veranstaltungen', 'Was ist sonst noch so los?', 'Was ist sonst noch so los?', 6, true, 1, true, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (6, 3, NULL, 'Flirtforum', 'flirten bis der Arzt kommt ;)', 'flirten bis der Arzt kommt <img alt=";&#41;" src="/images/smileys/zwinker.gif" />', 6, false, 2, true, true, false, true, false, true, 0, 0, 'single.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (7, 3, NULL, 'Sport', 'Hier kannst du dich über deine Lieblingssport austauschen.', 'Hier kannst du dich über deine Lieblingssport austauschen.', 5, true, 1, true, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (8, 3, NULL, 'Sonstiges', 'Ein Forum für alles was dir auf dem Herzen liegt.', 'Ein Forum für alles was dir auf dem Herzen liegt.', 7, true, 1, true, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (16, 3, 8, 'Anregung und Kritik an UniHelp.de', 'Lob und Kritik an unserer Web-Plattform', 'Lob und Kritik an unserer Web-Plattform', 8, true, 1, true, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (17, 3, 8, 'Flameboard', 'Zwerge mit Flammenwerfern', 'Zwerge mit Flammenwerfern', 9, true, 1, false, true, false, true, false, true, 0, 0, 'default.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (9, 4, NULL, 'Wohnung &amp; WG', 'für Vermieter und Wohnungs- & Zimmersucher', 'für Vermieter und Wohnungs- &amp; Zimmersucher', 1, false, 1, true, true, false, true, false, true, 0, 0, 'black_board.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (10, 4, NULL, 'Jobs &amp; Arbeit', 'Wer hat Arbeit? Wer sucht ein Job?', 'Wer hat Arbeit? Wer sucht ein Job?', 2, false, 1, true, true, false, true, false, true, 0, 0, 'black_board.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (11, 4, NULL, 'Mitfahrzentrale', 'Wer kann mich mitnehmen? Ich habe noch Platz', 'Wer kann mich mitnehmen? Ich habe noch Platz', 3, false, 1, true, true, false, true, false, true, 0, 0, 'black_board.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (12, 4, NULL, 'Kaufen', 'ich bin auf der suche nach ..', 'ich bin auf der suche nach ..', 4, false, 1, true, true, false, true, false, true, 0, 0, 'black_board.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (13, 4, NULL, 'Verkaufen', 'ich möchte etwas gerne verkaufen ...', 'ich möchte etwas gerne verkaufen ...', 5, false, 1, true, true, false, true, false, true, 0, 0, 'black_board.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (14, 4, NULL, 'Verschenken', 'möchte ich gerne weiter geben ..', 'möchte ich gerne weiter geben ..', 6, false, 1, true, true, false, true, false, true, 0, 0, 'black_board.tpl', NULL);
INSERT INTO __SCHEMA__.forum_fora VALUES (18, 5, NULL, 'alte Studiengangsforen', 'Hier sind die Foren aus dem UniHelp 1.0 Studiengängen', 'Hier sind die Foren aus dem UniHelp 1.0 Studiengängen', 1, false, 1, true, true, false, true, false, false, 0, 0, 'default.tpl', NULL);

SELECT setval('__SCHEMA__.forum_fora_id_seq', 18);
