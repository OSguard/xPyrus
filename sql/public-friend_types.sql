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
-- contains the friends_types
--
-- $Id: public-friend_types.sql 5743 2008-03-25 19:48:14Z ads $
--

BEGIN;


-- ###########################################################################
-- contains all friend types

CREATE TABLE public.friend_types (
  id                      INTEGER                  PRIMARY KEY
                                                   CHECK (id > 0),
  type_name               VARCHAR(30)              NOT NULL
                                                   UNIQUE,
  is_type_friend          BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  is_normal               BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE
) WITHOUT OIDS;
CREATE INDEX public_friend_types ON public.friend_types(type_name);

GRANT SELECT ON public.friend_types TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.friend_types TO GROUP __DB_ADMIN_GROUP__;


INSERT INTO public.friend_types (id, type_name, is_type_friend, is_normal)
                         VALUES (1, 'Normal', TRUE, TRUE);
INSERT INTO public.friend_types (id, type_name, is_type_friend, is_normal)
                         VALUES (2, 'Ignore', FALSE, TRUE);
INSERT INTO public.friend_types (id, type_name, is_type_friend, is_normal)
                         VALUES (3, 'Love', TRUE, FALSE);
INSERT INTO public.friend_types (id, type_name, is_type_friend, is_normal)
                         VALUES (4, 'Family', TRUE, FALSE);
INSERT INTO public.friend_types (id, type_name, is_type_friend, is_normal)
                         VALUES (5, 'Friend', TRUE, FALSE);

COMMIT;
