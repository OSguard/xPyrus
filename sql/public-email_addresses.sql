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
-- contains regexp for email addresses
--
-- $Id: public-email_addresses.sql 5863 2008-05-03 09:23:07Z trehn $
--

-- comments:
--
-- use should be: we display a distinct(displayed_domain_part) to the user
-- user selects one domain part and types in his username part
-- now we concat the userpart and the domainpart and go through the
-- table again, matching every email regexp with the concatenated string
-- the first hit gives the person type (and the domain part)


-- ###########################################################################
-- email address regexp table
CREATE TABLE public.email_regexp (
  id                      INT                      NOT NULL
                                                   UNIQUE
                                                   PRIMARY KEY
                                                   CHECK (id > 0),
  uni                     INT                      NOT NULL
                                                   REFERENCES public.uni(id),
  name                    VARCHAR(200)             NOT NULL
                                                   DEFAULT '',
  person_type             INT                      NOT NULL
                                                   REFERENCES public.person_types(id),
  needs_validating        BOOLEAN                  NOT NULL
                                                   DEFAULT FALSE,
  email_regexp            TEXT                     NOT NULL
                                                   CHECK(LENGTH(email_regexp) > 5),
  displayed_domain_part   TEXT                     NOT NULL
                                                   CHECK(LENGTH(displayed_domain_part) > 1),
  validate_regexp         TEXT                     NOT NULL
                                                   DEFAULT '',
  position                INT                      NOT NULL
) WITHOUT OIDS;
COMMENT ON TABLE public.email_regexp IS 'contains regexp for validating user email addresses for a specific location';
COMMENT ON COLUMN public.email_regexp.uni IS 'reference to the university';
COMMENT ON COLUMN public.email_regexp.name IS 'a name for the entry';
COMMENT ON COLUMN public.email_regexp.person_type IS 'if match, the user would be join this person group by default';
COMMENT ON COLUMN public.email_regexp.needs_validating IS 'defines if the email address needs extra validation by an administrator';
COMMENT ON COLUMN public.email_regexp.email_regexp IS 'the regular expression';
COMMENT ON COLUMN public.email_regexp.displayed_domain_part IS 'the email domain part we will show to the user';
COMMENT ON COLUMN public.email_regexp.position IS 'the position for ordered display';


/*INSERT INTO public.email_regexp (id, uni, name, person_type, needs_validating, email_regexp, displayed_domain_part, position)
     VALUES ((SELECT public.max_id('public', 'email_regexp') + 1),
             (SELECT id FROM public.uni WHERE name='Otto-von-Guericke-Universität Magdeburg'),
             'Unihelp Team',
             (SELECT id FROM public.person_types WHERE name='Unihelp Mitarbeiter'),
             FALSE,
             '/^[a-zA-Z0-9_\\-]+\\@unihelp\\.de$/',
             'unihelp.de', 9999);*/

INSERT INTO public.email_regexp (id, uni, name, person_type, needs_validating, email_regexp, displayed_domain_part, validate_regexp, position)
     VALUES ((SELECT public.max_id('public', 'email_regexp') + 1),
             (SELECT id FROM public.uni WHERE name='University of Springfield'),
             'Studenten Springfield',
             (SELECT id FROM public.person_types WHERE name='Student'),
             FALSE,
             '/^[^\\@]+\\@student\\.university-of-springfield.example$/i',
             'student.university-of-springfield.example',
             '^__NAME__@(student.university-of-springfield.example)$',
             2);

GRANT SELECT ON TABLE public.email_regexp TO GROUP __DB_NORMAL_GROUP__;
