-- PDAO Database Schema
-- PostgreSQL 17

SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;

-- Create ENUM types
CREATE TYPE public.sex_enum AS ENUM ('Male', 'Female');
CREATE TYPE public.civil_status_enum AS ENUM ('Single', 'Married', 'Separated', 'Widow/er', 'Cohabitation');
CREATE TYPE public.application_type_enum AS ENUM ('New', 'Renewal', 'Lost ID', 'new', 'renewal', 'lost');
CREATE TYPE public.application_status_enum AS ENUM ('Pending', 'Approved', 'Denied');
CREATE TYPE public.cause_detail_enum AS ENUM ('Congenital/Inborn', 'Acquired');
CREATE TYPE public.accomplished_by_enum AS ENUM ('Applicant', 'Guardian', 'Representative');
CREATE TYPE public.employment_status_enum AS ENUM ('Employed', 'Unemployed', 'Self-employed');
CREATE TYPE public.employment_category_enum AS ENUM ('Government', 'Private', 'Others');
CREATE TYPE public.type_of_employment_enum AS ENUM ('Permanent/Regular', 'Seasonal', 'Casual', 'Emergency');
CREATE TYPE public.educational_attainment_enum AS ENUM ('None', 'Kindergarten', 'Elementary', 'Junior High School', 'Senior High School', 'College', 'Vocational', 'Post Graduate');

-- User Admin table
CREATE TABLE public.user_admin (
    username VARCHAR(50) PRIMARY KEY,
    password TEXT NOT NULL,
    role TEXT,
    is_admin BOOLEAN DEFAULT false,
    is_doctor BOOLEAN DEFAULT false
);

-- User Account table
CREATE TABLE public.user_account (
    user_id SERIAL PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Applicant table
CREATE TABLE public.applicant (
    applicant_id SERIAL PRIMARY KEY,
    pwd_number VARCHAR(16),
    last_name VARCHAR(50),
    first_name VARCHAR(50),
    middle_name VARCHAR(50),
    suffix VARCHAR(10),
    birthdate DATE,
    sex public.sex_enum,
    civil_status public.civil_status_enum,
    house_no_street VARCHAR(100),
    barangay VARCHAR(50),
    municipality VARCHAR(50),
    province VARCHAR(50),
    region VARCHAR(50),
    landline_no VARCHAR(20),
    mobile_no VARCHAR(20),
    email_address VARCHAR(100),
    user_id INTEGER REFERENCES public.user_account(user_id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Application table
CREATE TABLE public.application (
    application_id SERIAL PRIMARY KEY,
    applicant_id INTEGER NOT NULL REFERENCES public.applicant(applicant_id) ON DELETE CASCADE,
    application_type public.application_type_enum NOT NULL,
    application_date DATE,
    status public.application_status_enum DEFAULT 'Pending',
    workflow_status VARCHAR(50) DEFAULT 'draft',
    remarks TEXT,
    pic_1x1_path VARCHAR(255),
    approved_by VARCHAR(100),
    approved_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Application Draft table
CREATE TABLE public.application_draft (
    draft_id SERIAL PRIMARY KEY,
    application_id INTEGER NOT NULL REFERENCES public.application(application_id) ON DELETE CASCADE,
    step INTEGER NOT NULL,
    data JSONB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(application_id, step)
);

-- Application Status History table (for audit trail)
CREATE TABLE public.application_status_history (
    hist_id SERIAL PRIMARY KEY,
    application_id INTEGER NOT NULL REFERENCES public.application(application_id) ON DELETE CASCADE,
    from_status VARCHAR(50),
    to_status VARCHAR(50),
    changed_by VARCHAR(100),
    role VARCHAR(50),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cause Disability table
CREATE TABLE public.causedisability (
    cause_disability_id SERIAL PRIMARY KEY,
    cause_disability VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cause Detail table
CREATE TABLE public.causedetail (
    cause_detail_id SERIAL PRIMARY KEY,
    cause_disability_id INTEGER REFERENCES public.causedisability(cause_disability_id) ON DELETE SET NULL,
    cause_detail public.cause_detail_enum NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Disability table
CREATE TABLE public.disability (
    disability_id SERIAL PRIMARY KEY,
    application_id INTEGER NOT NULL REFERENCES public.application(application_id) ON DELETE CASCADE,
    cause_detail_id INTEGER REFERENCES public.causedetail(cause_detail_id) ON DELETE SET NULL,
    disability_type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Accomplished By table
CREATE TABLE public.accomplishedby (
    accomplishment_id SERIAL PRIMARY KEY,
    application_id INTEGER NOT NULL REFERENCES public.application(application_id) ON DELETE CASCADE,
    accomplished_by public.accomplished_by_enum NOT NULL,
    last_name VARCHAR(50),
    first_name VARCHAR(50),
    middle_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Certification table
CREATE TABLE public.certification (
    certification_id SERIAL PRIMARY KEY,
    application_id INTEGER NOT NULL REFERENCES public.application(application_id) ON DELETE CASCADE,
    certifying_physician VARCHAR(100),
    license_no VARCHAR(50),
    processing_officer VARCHAR(100),
    approving_officer VARCHAR(100),
    encoder VARCHAR(100),
    reporting_unit VARCHAR(100),
    control_no VARCHAR(30),
    pwd_cert_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Family Background table
CREATE TABLE public.familybackground (
    family_id SERIAL PRIMARY KEY,
    applicant_id INTEGER NOT NULL REFERENCES public.applicant(applicant_id) ON DELETE CASCADE,
    father_last_name VARCHAR(50),
    father_first_name VARCHAR(50),
    father_middle_name VARCHAR(50),
    mother_last_name VARCHAR(50),
    mother_first_name VARCHAR(50),
    mother_middle_name VARCHAR(50),
    guardian_last_name VARCHAR(50),
    guardian_first_name VARCHAR(50),
    guardian_middle_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(applicant_id)
);

-- Affiliation table
CREATE TABLE public.affiliation (
    affiliation_id SERIAL PRIMARY KEY,
    applicant_id INTEGER NOT NULL REFERENCES public.applicant(applicant_id) ON DELETE CASCADE,
    educational_attainment public.educational_attainment_enum,
    employment_status public.employment_status_enum,
    employment_category public.employment_category_enum,
    occupation VARCHAR(100),
    type_of_employment public.type_of_employment_enum,
    organization_affiliated VARCHAR(100),
    contact_person VARCHAR(100),
    office_address VARCHAR(255),
    tel_no VARCHAR(30),
    sss_no VARCHAR(30),
    gsis_no VARCHAR(30),
    pagibig_no VARCHAR(30),
    psn_no VARCHAR(30),
    philhealth_no VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(applicant_id)
);

-- Emergency Contact table
CREATE TABLE public.emergencycontact (
    emergency_id SERIAL PRIMARY KEY,
    applicant_id INTEGER NOT NULL REFERENCES public.applicant(applicant_id) ON DELETE CASCADE,
    contact_person_name VARCHAR(100) NOT NULL,
    contact_person_no VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(applicant_id)
);

-- Document Requirements table
CREATE TABLE public.documentrequirements (
    document_id SERIAL PRIMARY KEY,
    application_id INTEGER NOT NULL REFERENCES public.application(application_id) ON DELETE CASCADE,
    bodypic_path VARCHAR(255),
    pic_1x1_path VARCHAR(255),
    medicalcert_path VARCHAR(255),
    barangaycert_path VARCHAR(255),
    old_pwd_id_path VARCHAR(255),
    affidavit_loss_path VARCHAR(255),
    cho_cert_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX idx_application_workflow ON public.application(workflow_status);
CREATE INDEX idx_application_status ON public.application(status);
CREATE INDEX idx_application_applicant ON public.application(applicant_id);
CREATE UNIQUE INDEX documentrequirements_appid_key ON public.documentrequirements(application_id);
CREATE UNIQUE INDEX uniq_active_draft_per_applicant_type ON public.application(applicant_id, application_type) WHERE (application_date IS NULL);
