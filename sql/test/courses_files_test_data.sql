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
-- test data for courses uplaod
-- $Id: courses_files_test_data.sql 5893 2008-05-03 15:03:47Z schnueptus $

-- the categories
INSERT INTO __SCHEMA__.courses_files_categories (name)
                         VALUES ('Übung');
INSERT INTO __SCHEMA__.courses_files_categories (name)
                         VALUES ('Klausuren');
INSERT INTO __SCHEMA__.courses_files_categories ( name)
                         VALUES ('Sonstiges');

-- the semesters
INSERT INTO __SCHEMA__.courses_files_semesters (name)
                         VALUES ('älter');
INSERT INTO __SCHEMA__.courses_files_semesters (name)
                         VALUES ('WS06/07');
INSERT INTO __SCHEMA__.courses_files_semesters (name)
                         VALUES ('SS07');
INSERT INTO __SCHEMA__.courses_files_semesters (name)
                         VALUES ('WS07/08');
INSERT INTO __SCHEMA__.courses_files_semesters (name)
                         VALUES ('SS08');

-- the files



INSERT INTO __SCHEMA__.courses_files (course_id,category_id,semester_id,author_id,description,file_name,file_size,file_type)
    VALUES (1,3,2,(SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'),
            'Physicists agree that constant-time models are an interesting new topic in the field of discrete artificial intelligence, and security experts con- cur. In fact, few futurists would disagree with the construction of operating systems, which embodies the intuitive principles of operating systems. BOX, our new heuristic for architec- ture, isthesolutiontoall oftheseobstacles.',
            'scimakelatex.3032.Homer.Bart.pdf', 13469, 'pdf');
INSERT INTO __SCHEMA__.courses_files_revisions 
        (file_id,file_size, file_type, hash, path, upload_time)
    VALUES ((SELECT MAX(id) FROM __SCHEMA__.courses_files),
            15469,'pdf',
            'ca21343e97c66584051653e4bf396baef8007420',
            '../uploads/users/1/scimakelatex.3032.Homer.Bart.pdf',
            NOW() - '1 hours 2 minutes'::interval);
INSERT INTO __SCHEMA__.courses_files_revisions 
        (file_id,file_size, file_type, hash, path, upload_time)
    VALUES ((SELECT MAX(id) FROM __SCHEMA__.courses_files),
            15469,'pdf',
            'ca21343e97c66584051653e4bf396baef8007430',
            '../uploads/users/1/scimakelatex.3032.Homer.Bart.pdf',
            NOW() - '1 hours 2 minutes'::interval);            
            
INSERT INTO __SCHEMA__.courses_files (course_id,category_id,semester_id,author_id,description,file_name,file_size,file_type)
    VALUES (1,3,2,(SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),
            'Many systems engineers would agree that, hadit not been for the improvement of SCSI disks,the improvement of simulated annealing mightnever have occurred. Given the current status of flexible archetypes, steganographers predictably desire the synthesis of expert systems. In this work we use ambimorphic methodologies to con- firm that neural networks can be made lossless, certifiable, and constant-time.',
            'scimakelatex.23907.Prof.Dr.+Dr.+med.+Brain.pdf', 13469, 'pdf');
INSERT INTO __SCHEMA__.courses_files_revisions 
        (file_id,file_size, file_type, hash, path, upload_time)
    VALUES ((SELECT MAX(id) FROM __SCHEMA__.courses_files),
            15469,'pdf',
            'ca21343e97c66584051653e4bf396baef8007421',
            '../uploads/users/1/scimakelatex.23907.Prof.Dr.+Dr.+med.+Brain.pdf',
            NOW() - '1 hours 43 minutes'::interval);            

INSERT INTO __SCHEMA__.courses_files (course_id,category_id,semester_id,author_id,description,file_name,file_size,file_type)
    VALUES (1,3,2,(SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),
            'The networking approach to multi-processors is defined not only by the improvement of e-business, but also by the unfor- tunateneedfore-business.Giventhecurrentstatusofwearable methodologies, futurists compellingly desire the refinement of the Internet. This is instrumental to the success of our work. Pinocle, our new system for amphibious configurations, is the solution to all of these issues.',
            'scimakelatex.24284.Prof.+You+Know+All.pdf', 13469, 'pdf');
INSERT INTO __SCHEMA__.courses_files_revisions 
        (file_id,file_size, file_type, hash, path, upload_time)
    VALUES ((SELECT MAX(id) FROM __SCHEMA__.courses_files),
            15469,'pdf',
            'ca21343e97c66584051653e4bf396baef8007422',
            '../uploads/users/1/scimakelatex.24284.Prof.+You+Know+All.pdf',
            NOW() - '1 hours 23 minutes'::interval);     

INSERT INTO __SCHEMA__.courses_files (course_id,category_id,semester_id,author_id,description,file_name,file_size,file_type)
    VALUES (1,3,2,(SELECT id FROM __SCHEMA__.users WHERE username='Marge_Simpson'),
            'Recent advances in omniscient theory and “smart” archetypes are based entirely on the assumption that voice- over-IP[15]and hash tables are not in conflict with write-back caches. In this work, we prove the unfortunate unification of XML and Moore’s Law. We better understand how replication can be applied to the synthesis of redundancy.',
            'scimakelatex.40681.Prof.+Itchhy.pdf', 13469, 'pdf');
INSERT INTO __SCHEMA__.courses_files_revisions 
        (file_id,file_size, file_type, hash, path, upload_time)
    VALUES ((SELECT MAX(id) FROM __SCHEMA__.courses_files),
            15469,'pdf',
            'ca21343e97c66584051653e4bf396baef8007423',
            '../uploads/users/1/scimakelatex.40681.Prof.+Itchhy.pdf',
            NOW() - '27 minutes'::interval);   

INSERT INTO __SCHEMA__.courses_files (course_id,category_id,semester_id,author_id,description,file_name,file_size,file_type)
    VALUES (1,3,2,(SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'),
            'Permutable configurations and the World Wide Web have garnered great interest from both system administrators and biologists in the last several years [11]. In fact, few hackers worldwide would disagree with the compelling unification of write-ahead logging and kernels [11]. BondWipe, our new pplicationforthesynthesisofcachecoherence,isthesolution o all of these challenges.',
            'scimakelatex.29132.Master+of+IT+180.pdf', 13469, 'pdf');
INSERT INTO __SCHEMA__.courses_files_revisions 
        (file_id,file_size, file_type, hash, path, upload_time)
    VALUES ((SELECT MAX(id) FROM __SCHEMA__.courses_files),
            15469,'pdf',
            'ca21343e97c66584051653e4bf396baef8007424',
            '../uploads/users/1/scimakelatex.29132.Master+of+IT+180.pdf',
            NOW() - '1 hours 28 minutes'::interval);  



INSERT INTO __SCHEMA__.courses_files_downloads (file_id, user_id)
    VALUES (1,(SELECT id FROM __SCHEMA__.users WHERE username='Bart_Simpson'));
INSERT INTO __SCHEMA__.courses_files_downloads (file_id, user_id)
    VALUES (1,(SELECT id FROM __SCHEMA__.users WHERE username='Marge_Simpson'));
INSERT INTO __SCHEMA__.courses_files_downloads (file_id, user_id)
    VALUES (1,(SELECT id FROM __SCHEMA__.users WHERE username='HomerJaySimpson'));
INSERT INTO __SCHEMA__.courses_files_downloads (file_id, user_id)
    VALUES (1,(SELECT id FROM __SCHEMA__.users WHERE username='Rektor'));
INSERT INTO __SCHEMA__.courses_files_downloads (file_id, user_id)
    VALUES (1,(SELECT id FROM __SCHEMA__.users WHERE username='Lisa_Simpson'));




