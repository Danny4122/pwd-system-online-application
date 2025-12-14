-- PDAO Seed Data

-- Admin users
INSERT INTO public.user_admin (username, password, role, is_admin, is_doctor) VALUES
    ('adminpdao', '$adminpass', NULL, false, false),
    ('admin', 'admin123', NULL, false, false),
    ('admintest', 'admin123', 'admin', true, false),
    ('doctor', 'doctor123', 'doctor', false, true)
ON CONFLICT DO NOTHING;

-- User accounts
INSERT INTO public.user_account (user_id, email, password_hash, first_name, last_name, created_at, updated_at) VALUES
    (8, 'thea.ancog@g.msuiit.edu.ph', '$2y$10$H3bXkZi32ujilnEG.ctcbO4BrXsu6swNHQ6RXsNfLg1S5flEm4m46', 'Thea', 'Ancog', '2025-07-14 15:25:42.094503', '2025-07-14 15:25:42.094503'),
    (9, 'vanbautista@gmail.com', '$2y$10$fDIynEPL8ypPSBCRvj3vqOGAsaokObBb8FiCjVzWmbzb9ogyufv8.', 'Van', 'Bautista', '2025-07-14 20:44:05.745059', '2025-07-14 20:44:05.745059')
ON CONFLICT DO NOTHING;

-- Reset sequence
SELECT setval('public.user_account_user_id_seq', 9, true);

-- Applicants
INSERT INTO public.applicant (applicant_id, pwd_number, last_name, first_name, middle_name, suffix, birthdate, sex, civil_status, house_no_street, barangay, municipality, province, region, landline_no, mobile_no, email_address, user_id, created_at, updated_at) VALUES
    (1, NULL, 'Ancog', 'Thea', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, '2025-07-29 15:48:10.123944', '2025-07-29 15:48:10.123944'),
    (2, NULL, 'Bautista', 'Van', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9, '2025-08-18 13:48:26.772338', '2025-08-18 13:48:26.772338')
ON CONFLICT DO NOTHING;

SELECT setval('public.applicant_applicant_id_seq', 2, true);

-- Applications
INSERT INTO public.application (application_id, applicant_id, application_type, application_date, status, workflow_status, remarks, pic_1x1_path, approved_by, approved_at, created_at, updated_at) VALUES
    (1, 1, 'new', '2025-07-30', 'Pending', 'submitted', NULL, NULL, NULL, NULL, '2025-07-30 12:01:46.914651', '2025-07-30 12:01:46.914651'),
    (2, 1, 'new', '2025-07-30', 'Pending', 'pdao_review', NULL, NULL, NULL, NULL, '2025-07-30 22:09:23.346109', '2025-07-30 22:09:23.346109'),
    (3, 1, 'new', '2025-07-31', 'Pending', 'cho_review', NULL, NULL, NULL, NULL, '2025-07-31 12:21:19.154504', '2025-07-31 12:21:19.154504'),
    (4, 1, 'Renewal', NULL, 'Pending', 'draft', NULL, NULL, NULL, NULL, '2025-08-08 23:44:57.802909', '2025-08-08 23:44:57.802909'),
    (5, 1, 'New', NULL, 'Pending', 'cho_review', NULL, '/uploads/1755147080_22e806c9.png', NULL, NULL, '2025-08-12 14:43:08.070373', '2025-08-14 12:51:20.030512'),
    (6, 2, 'New', NULL, 'Pending', 'submitted', NULL, NULL, NULL, NULL, '2025-08-18 13:48:28.342067', '2025-08-18 13:48:28.342067'),
    (7, 1, 'Lost ID', NULL, 'Pending', 'cho_review', NULL, NULL, NULL, NULL, '2025-10-07 14:05:24.375232', '2025-10-07 14:05:24.375232')
ON CONFLICT DO NOTHING;

SELECT setval('public.application_application_id_seq', 7, true);

-- Application drafts
INSERT INTO public.application_draft (draft_id, application_id, step, data, created_at, updated_at) VALUES
    (1, 1, 1, '{"sex": "Female", "cause": "Congenital/Inborn", "region": "X", "suffix": "", "barangay": "Hinaplanon", "province": "Lanao Del Norte", "birthdate": "2000-08-20", "last_name": "Anc", "mobile_no": "23424242", "first_name": "Anna", "landline_no": "221-0001", "middle_name": "Tan", "civil_status": "Single", "date_applied": "2025-03-07", "municipality": "", "applicantType": "new", "email_address": "thea.ancog@g.msuiit.edu.ph", "disability_type": "Intellectual Disability", "house_no_street": "2", "cause_description": "ADHD"}', '2025-07-30 12:02:45.69716', '2025-07-30 13:35:40.344811'),
    (4, 1, 2, '{"sss_no": "4646", "tel_no": "adsad", "gsis_no": "46464", "occupation": "Managers", "pagibig_no": "45646", "philhealth_no": "4546", "contact_person": "adad", "office_address": "adsad", "accomplished_by": "Guardian", "father_last_name": "jhb", "mother_last_name": "adasdad", "employment_status": "Employed", "father_first_name": "afdaf", "mother_first_name": "Roy", "occupation_others": "", "father_middle_name": "Acero", "guardian_last_name": "Ancog", "mother_middle_name": "Acero", "type_of_employment": "Permanent/Regular", "employment_category": "Government", "guardian_first_name": "Rose", "guardian_middle_name": "Acero", "acc_last_name_guardian": "asdsad", "educational_attainment": "Kindergarten", "acc_first_name_guardian": "adsad", "organization_affiliated": "sdad", "acc_middle_name_guardian": "adad"}', '2025-07-30 12:19:33.326925', '2025-07-30 13:58:43.490241'),
    (11, 1, 3, '{"encoder": "", "control_no": "", "license_no": "", "reporting_unit": "", "approving_officer": "", "contact_person_no": "123456790", "processing_officer": "", "contact_person_name": "ansdgsg", "certifying_physician": ""}', '2025-07-30 13:58:45.795193', '2025-07-30 14:03:14.356919'),
    (31, 3, 1, '{"sex": "Female", "cause": "Congenital/Inborn", "region": "X", "suffix": "", "barangay": "Hinaplanon", "province": "Lanao Del Norte", "birthdate": "2025-07-31", "last_name": "Ancog", "mobile_no": "52655", "first_name": "Rose", "landline_no": "221-0001", "middle_name": "Acero", "civil_status": "Single", "date_applied": "2025-03-07", "municipality": "", "applicantType": "renew", "email_address": "thea.ancog@g.msuiit.edu.ph", "disability_type": "Learning Disability", "house_no_street": "2", "cause_description": "ADHD"}', '2025-07-31 12:21:23.7833', '2025-07-31 15:50:23.77755'),
    (66, 3, 2, '{"sss_no": "4646", "tel_no": "adsad", "gsis_no": "46464", "occupation": "Managers", "pagibig_no": "45646", "philhealth_no": "4546", "contact_person": "adad", "office_address": "adsad", "accomplished_by": "Applicant", "father_last_name": "jhb", "mother_last_name": "adasdad", "employment_status": "Employed", "father_first_name": "afdaf", "mother_first_name": "Roy", "occupation_others": "", "father_middle_name": "Acero", "guardian_last_name": "Ancog", "mother_middle_name": "Acero", "type_of_employment": "Permanent/Regular", "employment_category": "Government", "guardian_first_name": "Rose", "guardian_middle_name": "Acero", "educational_attainment": "College", "acc_last_name_applicant": "Anna", "organization_affiliated": "sdad", "acc_first_name_applicant": "Tan", "acc_middle_name_applicant": "Anc"}', '2025-07-31 12:22:10.848641', '2025-07-31 15:50:25.291643'),
    (94, 3, 3, '{"encoder": "", "control_no": "", "license_no": "", "reporting_unit": "", "approving_officer": "", "contact_person_no": "12345679", "processing_officer": "", "contact_person_name": "ansdgsg", "certifying_physician": ""}', '2025-07-31 12:22:49.15763', '2025-07-31 15:50:26.736088'),
    (132, 4, 1, '{"sex": "Male", "cause": "Acquired", "region": "X", "suffix": "sfsgsg", "barangay": "Hinaplanon", "province": "Lanao Del Norte", "birthdate": "2005-05-05", "last_name": "Ancasd", "mobile_no": "0213456789", "first_name": "sdfd", "landline_no": "221-0001", "middle_name": "sdf", "civil_status": "Single", "municipality": "", "pic_1x1_path": "", "applicantType": "renew", "email_address": "thea.ancog@g.msuiit.edu.ph", "disability_type": "Intellectual Disability", "house_no_street": "Acmac IC", "application_date": "2025-08-08", "cause_description": "Cerebral Palsy"}', '2025-08-08 23:45:02.317294', '2025-10-07 14:05:13.994675'),
    (154, 5, 1, '{"sex": "Male", "cause": "Acquired", "region": "X", "suffix": "sfsgsg", "barangay": "Hinaplanon", "province": "Lanao Del Norte", "birthdate": "2005-05-05", "last_name": "Ancasd", "mobile_no": "0213456789", "first_name": "sdfd", "landline_no": "221-0001", "middle_name": "sdf", "civil_status": "Single", "municipality": "", "pic_1x1_path": "/uploads/1755147080_22e806c9.png", "applicantType": "new", "email_address": "thea.ancog@g.msuiit.edu.ph", "disability_type": "Intellectual Disability", "house_no_street": "Acmac IC", "application_date": "2025-08-08", "cause_description": "Cerebral Palsy"}', '2025-08-12 14:43:10.511412', '2025-11-10 10:08:49.332526'),
    (229, 5, 2, '{"sss_no": "12656", "tel_no": "jbjkbjb", "gsis_no": "45455546", "occupation": "Managers", "pagibig_no": "75756", "philhealth_no": "5645645", "contact_person": "Rose Acero Ancog", "office_address": "2", "accomplished_by": "Guardian", "father_last_name": "Anc", "mother_last_name": "Anc", "employment_status": "Unemployed", "father_first_name": "adsad", "mother_first_name": "Anna", "occupation_others": "", "father_middle_name": "Tan", "guardian_last_name": "Anc", "mother_middle_name": "Tan", "type_of_employment": "Permanent/Regular", "employment_category": "Private", "guardian_first_name": "gsfgs", "guardian_middle_name": "Tan", "acc_last_name_guardian": "akjfbkajsbfja", "educational_attainment": "Kindergarten", "acc_first_name_guardian": "Tan", "organization_affiliated": "jbkj", "acc_middle_name_guardian": "Anc"}', '2025-08-13 14:54:04.13014', '2025-11-10 10:08:51.264459'),
    (275, 5, 3, '{"nav": "back", "encoder": "", "control_no": "", "license_no": "", "reporting_unit": "", "approving_officer": "", "contact_person_no": "12345679", "processing_officer": "", "contact_person_name": "ansdgsg", "certifying_physician": ""}', '2025-08-13 16:04:49.616349', '2025-11-10 10:09:14.395836'),
    (279, 5, 4, '{"nav": "back", "bodypic_path": "/uploads/photos/1755153508_5e1eed69.pdf", "cho_cert_path": "", "medicalcert_path": "/PWD-Application-System/uploads/1762149510_Doctor-Side.pdf", "barangaycert_path": "/uploads/docs/1755153125_1da87e8c.png"}', '2025-08-14 10:13:39.344645', '2025-11-10 10:09:11.462932'),
    (398, 4, 2, '{"sss_no": "", "tel_no": "", "gsis_no": "", "pagibig_no": "", "philhealth_no": "", "contact_person": "", "office_address": "", "father_last_name": "", "mother_last_name": "", "employment_status": "", "father_first_name": "", "mother_first_name": "", "occupation_others": "", "father_middle_name": "", "guardian_last_name": "", "mother_middle_name": "", "type_of_employment": "", "employment_category": "", "guardian_first_name": "", "guardian_middle_name": "", "educational_attainment": "", "organization_affiliated": ""}', '2025-09-16 13:18:27.760503', '2025-10-07 14:05:16.926247'),
    (399, 4, 3, '{"nav": "next", "encoder": "", "control_no": "", "license_no": "", "reporting_unit": "", "approving_officer": "", "contact_person_no": "", "processing_officer": "", "contact_person_name": "", "certifying_physician": ""}', '2025-09-16 13:18:31.338657', '2025-10-07 14:05:19.57408'),
    (400, 4, 4, '{"nav": "back", "cho_cert_path": "", "old_pwd_id_path": "", "medicalcert_path": "", "barangaycert_path": ""}', '2025-09-16 13:27:18.547437', '2025-09-16 13:53:25.604165')
ON CONFLICT DO NOTHING;

SELECT setval('public.application_draft_draft_id_seq', 503, true);

-- Document requirements
INSERT INTO public.documentrequirements (document_id, application_id, bodypic_path, pic_1x1_path, medicalcert_path, barangaycert_path, old_pwd_id_path, affidavit_loss_path, cho_cert_path, created_at, updated_at) VALUES
    (1, 5, '/uploads/photos/1755153508_5e1eed69.pdf', NULL, '/PWD-Application-System/uploads/1762149510_Doctor-Side.pdf', '/uploads/docs/1755153125_1da87e8c.png', NULL, '/PWD-Application-System/uploads/1755761887_Doctor-Side.pdf', NULL, '2025-08-14 10:13:23.134755', '2025-11-10 10:09:11.467724'),
    (2, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-16 13:27:18.572423', '2025-09-16 13:53:25.622502')
ON CONFLICT DO NOTHING;

SELECT setval('public.documentrequirements_document_id_seq', 2, true);
