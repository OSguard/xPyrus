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
-- contains the cities
--
-- $Id: public-cities.sql 5858 2008-05-03 08:53:09Z trehn $
--


BEGIN;


-- ###########################################################################
-- contains all cities
CREATE TABLE public.cities (
  id                      INT                      NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  name                    VARCHAR(200)             NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(name) > 0),
  schema_name             VARCHAR(20)              NOT NULL
                                                   UNIQUE
                                                   CHECK (LENGTH(schema_name) > 0),
  public_key              TEXT                     NOT NULL
                                                   DEFAULT '',
  private_key             TEXT                     NOT NULL
                                                   DEFAULT '',
  public                  BOOLEAN                  NOT NULL
                                                   DEFAULT TRUE,
  contact                 VARCHAR(500)             NOT NULL
                                                   DEFAULT ''
) WITHOUT OIDS;
COMMENT ON TABLE public.cities IS 'contains all known xpyrus locations';
COMMENT ON COLUMN public.cities.name IS 'the location name';
COMMENT ON COLUMN public.cities.schema_name IS 'the schema name for the location';
COMMENT ON COLUMN public.cities.public IS 'defines if the location is public (or for debugging as example)';
COMMENT ON COLUMN public.cities.contact IS 'defines contact data for the location';
INSERT INTO public.cities (id, name, schema_name, public, contact, public_key, private_key)
                   VALUES (1, 'Springfield', 'springfield', FALSE, 'admin@unihelp.de', '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDPkLnFkimXSPm6ztSDSN5NXaKY
BD/gvdQ3tq2Q9a9cE0AlVW/eirJTrT7QQs5Cm48vEaJgAC9i89Zjptfkyx294rfy
v5K1gSm1mSqw1QfGqNEYMZytLoPtM7MkcG7kN0N4Tke5y5GOwhvln7SM7pCUHDKZ
BbBfQ92bIXKP6cAoFwIDAQAB
-----END PUBLIC KEY-----
', '-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-CBC,DB3A0C5C30374D66

eKsOrh5QHYWNKitUFrVDt4emtZikeGESie36DI5bduQphh2MslLqQAcxZRBfDpha
c1iItYzvfurU5qjaS6yo7r1MDETjs6kVtH10vzf8I9QBSbupOVYX+MziYWqf4LAI
pWWATd3e032cXoL+t74jx6+haXWqcpDbJ5h7qpjPKmq4gCUntmZA3EBZWkk9lZ0V
+/YQrYH0J5G+9hJilX/xDBPDpuXstRqyx4YZ31qnz+YBkC0KHwFvuwymXonF2BC2
8LmjElIJGNuJHh6EOQ67Eq41sXm0kQdJMdaY62W4rYWHH8D/V7V+bDbvehGt4/Zy
6/P7GmrSYz9yc5bQcHOjvsohiXHhy2EAaXfgdfOEKbP7GlhMT0hOJpZDUBmMXDd0
1elFSHcDXq3mzhtXz3rI8tJOYN1c1HIpEuAdn9hFV36SkYCAgFrT5HhS0pWn8jjY
wG74nDZOzv/97nfx0LM57+cmvD88PO0O9NRiD4FKyQML88WUYshP1sMmuaBf1YKN
vxzDVsxvb7rE1gfIjDnBOvU67CPafMRVcmK6/6SkqSLFzKNOW1F5aVJrYssDz5k5
d2P2VKav02HBFoeIAHUor4VyMJoizjQHzY9pGVkpG0yNtOSg5m9P7iD9oKuhNcJ5
0ywBCqdvIGIb9dNI8gPSPMoIWqu9Uy8LsyZ+wD3bKQekc3ulCMpkaIkiNtyqupjn
cpSiaxTROkvllFFcUxkWqUCPY2DBMDumhF3c3EMA1W07bKuYQ7WJs7ZuQMml6Vhc
/VWXZKARNg4zDColC1o2euz8GassZoIYvfMK0qkrU9/zjCoC+A00Hg==
-----END RSA PRIVATE KEY-----
');
GRANT SELECT ON public.cities TO GROUP __DB_NORMAL_GROUP__;
GRANT ALL ON public.cities TO GROUP __DB_ADMIN_GROUP__;

COMMIT;
