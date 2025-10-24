-- =====================================
-- SQL TABLES - SNAKE_CASE VERSION
-- Organized with proper naming conventions:
-- - Table names: lowercase, English, plural, snake_case
-- - Attributes: snake_case
-- =====================================


-- =============================================
-- TABLAS LARAVEL PARA POSTGRESQL
-- =============================================

-- 1. Tabla CACHE
CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY NOT NULL,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

-- 2. Tabla CACHE_LOCKS
CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY NOT NULL,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- 3. Tabla SESSIONS
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY NOT NULL,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions (user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions (last_activity);

-- 4. Tabla JOBS
CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

-- 5. Tabla JOB_BATCHES
CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) PRIMARY KEY NOT NULL,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);

-- 7. Tabla PASSWORD_RESET_TOKENS
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP(0) NULL
);

CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT,
    last_used_at TIMESTAMPTZ,
    expires_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);


-- Users management
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    full_name VARCHAR(100),
    dni VARCHAR(20) UNIQUE,
    document VARCHAR(20) UNIQUE,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMPTZ,
    remember_token VARCHAR(100) NULL,

    phone_number VARCHAR(20),
    address TEXT,
    birth_date DATE,
    role JSONB DEFAULT '"student"',
    password VARCHAR(255) NOT NULL,
    gender VARCHAR(10) CHECK (gender IN ('male','female','other')),
    country VARCHAR(100),
    country_location VARCHAR(100),
    timezone VARCHAR(50) DEFAULT 'America/Lima',
    profile_photo VARCHAR(500),
    status VARCHAR(20) CHECK (status IN ('active','inactive','banned')) DEFAULT 'active',
    synchronized BOOLEAN DEFAULT TRUE,
    last_access_ip VARCHAR(45),
    last_access TIMESTAMPTZ,
    last_connection TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Budget management
CREATE TABLE budgets (
    id_budget BIGSERIAL PRIMARY KEY,
    category VARCHAR(50),
    academic_period_id BIGINT,
    assigned_amount NUMERIC(15,2),
    executed_amount NUMERIC(15,2) DEFAULT 0.00,
    creation_date TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    modification_date TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    approver_user_id BIGINT,
    FOREIGN KEY (approver_user_id) REFERENCES users(id)
);

CREATE TABLE budget_impacts (
    id_budget_impact BIGSERIAL PRIMARY KEY,
    final_transaction_id BIGINT,
    budget_id BIGINT,
    impact_amount NUMERIC(15,2),
    date TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    user_id BIGINT,
    FOREIGN KEY (budget_id) REFERENCES budgets(id_budget),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Accounting management
CREATE TABLE accounts (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(20) UNIQUE,
    description VARCHAR(100),
    account_type VARCHAR(20) CHECK (account_type IN ('Asset','Liability','Income','Expense')),
    current_balance NUMERIC(15,2) DEFAULT 0.00
);

CREATE TABLE transactions (
    id BIGSERIAL PRIMARY KEY,
    transaction_type VARCHAR(20) CHECK (transaction_type IN ('Income','Expense')),
    amount NUMERIC(15,2),
    transaction_date TIMESTAMPTZ DEFAULT now(),
    description TEXT,
    category VARCHAR(100),
    account_id BIGINT,
    budget_id BIGINT,
    registered_by BIGINT,
    attachment VARCHAR(255),
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    FOREIGN KEY (budget_id) REFERENCES budgets(id_budget),
    FOREIGN KEY (registered_by) REFERENCES users(id)
);

-- Audit management
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    entity VARCHAR(50),
    entity_id BIGINT,
    action VARCHAR(20) CHECK (action IN ('INSERT','UPDATE','DELETE')),
    user_id BIGINT,
    timestamp TIMESTAMPTZ DEFAULT now(),
    changes JSONB,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Companies management
CREATE TABLE companies (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    industry VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(150) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Courses management
CREATE TABLE courses (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT,
    title VARCHAR(255) NOT NULL,
    name VARCHAR(200),
    description TEXT,
    level VARCHAR(20) DEFAULT 'basic' CHECK (level IN ('basic','intermediate','advanced')),
    course_image VARCHAR(255),
    video_url VARCHAR(255),
    duration NUMERIC(8,2),
    sessions BIGINT,
    selling_price NUMERIC(10,2),
    discount_price NUMERIC(10,2),
    prerequisites TEXT,
    certificate_name BOOLEAN DEFAULT FALSE,
    certificate_issuer VARCHAR(255),
    bestseller BOOLEAN DEFAULT FALSE,
    featured BOOLEAN DEFAULT FALSE,
    highest_rated BOOLEAN DEFAULT FALSE,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Academic periods
CREATE TABLE academic_periods (
    id BIGSERIAL PRIMARY KEY,
    academic_period_id BIGINT,
    name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'open',
    created_at TIMESTAMPTZ DEFAULT now()
);

-- Instructors management
CREATE TABLE instructors (
    id BIGSERIAL PRIMARY KEY,
    instructor_id BIGINT,
    user_id BIGINT NOT NULL,
    bio TEXT,
    expertise_area VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE programs (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    duration_weeks BIGINT CHECK (duration_weeks IS NULL OR duration_weeks > 0),
    max_capacity BIGINT CHECK (max_capacity IS NULL OR max_capacity > 0),
    start_date DATE,
    end_date DATE,
    price NUMERIC(10,2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'PEN',
    image_url VARCHAR(500),
    modality VARCHAR(10) DEFAULT 'virtual' CHECK (modality IN ('virtual','hybrid')),
    required_devices TEXT,
    status VARCHAR(10) DEFAULT 'active' CHECK (status IN ('active','inactive')),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Students management
CREATE TABLE students (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT,
    user_id BIGINT,
    company_id BIGINT,
    document_number VARCHAR(20),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
);

CREATE TABLE course_offerings (
    id BIGSERIAL PRIMARY KEY,
    course_offering_id BIGINT,
    course_id BIGINT NOT NULL,
    academic_period_id BIGINT NOT NULL,
    instructor_id BIGINT,
    schedule TEXT,
    delivery_method VARCHAR(50) DEFAULT 'regular',
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (academic_period_id) REFERENCES academic_periods(id),
    FOREIGN KEY (instructor_id) REFERENCES instructors(id)
);

-- Enrollments management
CREATE TABLE enrollments (
    id BIGSERIAL PRIMARY KEY,
    enrollment_id BIGINT,
    student_id BIGINT,
    academic_period_id BIGINT,
    enrollment_type VARCHAR(100) DEFAULT 'new',
    enrollment_date DATE,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (academic_period_id) REFERENCES academic_periods(id)
);

-- Subjects management
CREATE TABLE subjects (
    id BIGSERIAL PRIMARY KEY,
    subject_code VARCHAR(20),
    subject_name VARCHAR(100),
    credits BIGINT,
    status VARCHAR(20)
);

CREATE TABLE enrollment_details (
    id BIGSERIAL PRIMARY KEY,
    enrollment_id BIGINT,
    subject_id BIGINT,
    course_offering_id BIGINT,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (course_offering_id) REFERENCES course_offerings(id)
);

CREATE TABLE enrollment_payments (
    id BIGSERIAL PRIMARY KEY,
    enrollment_id BIGINT,
    student_id BIGINT,
    amount NUMERIC(10,2),
    payment_date DATE,
    payment_method VARCHAR(50),
    receipt VARCHAR(100),
    status VARCHAR(20),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Documents management
CREATE TABLE documents (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(200),
    category VARCHAR(20) CHECK (category IN ('academic','administrative','legal')),
    entity_type VARCHAR(100),
    entity_id BIGINT,
    version BIGINT,
    status VARCHAR(20) CHECK (status IN ('draft','active','archived')),
    download_count INTEGER DEFAULT 0,
    file_path VARCHAR(255),
    created_by BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE document_histories (
    id BIGSERIAL PRIMARY KEY,
    document_id BIGINT,
    version BIGINT,
    change_description TEXT,
    changed_by BIGINT,
    FOREIGN KEY (document_id) REFERENCES documents(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

CREATE TABLE document_templates (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150),
    category VARCHAR(20) CHECK (category IN ('academic','administrative','legal')),
    file_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE
);

-- Indicators management
CREATE TABLE indicators (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150),
    description TEXT,
    category VARCHAR(50) CHECK (category IN ('academic','administrative','financial','satisfaction')),
    formula VARCHAR(255),
    target_value NUMERIC(6,2),
    unit VARCHAR(20),
    responsible_user_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (responsible_user_id) REFERENCES users(id)
);

CREATE TABLE indicator_measurements (
    id BIGSERIAL PRIMARY KEY,
    indicator_id BIGINT,
    period VARCHAR(20),
    value NUMERIC(10,2),
    data_source VARCHAR(255),
    recorded_at TIMESTAMPTZ DEFAULT now(),
    recorded_by BIGINT,
    FOREIGN KEY (indicator_id) REFERENCES indicators(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

CREATE TABLE indicator_alerts (
    id BIGSERIAL PRIMARY KEY,
    indicator_id BIGINT,
    current_value NUMERIC(10,2),
    threshold NUMERIC(10,2),
    status VARCHAR(20) CHECK (status IN ('normal','alert','critical')),
    notified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (indicator_id) REFERENCES indicators(id)
);

-- Human resources management
CREATE TABLE departments (
    id BIGSERIAL PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP with TIME zone null,
    updated_at TIMESTAMP with TIME zone null

);

CREATE TABLE positions (
    id BIGSERIAL PRIMARY KEY,
    position_name VARCHAR(100) NOT NULL,
    department_id BIGINT NOT NULL,
    created_at TIMESTAMP with TIME zone null,
    updated_at TIMESTAMP with TIME zone null,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE employees (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT,
    hire_date DATE,
    position_id BIGINT NOT NULL,
    department_id BIGINT NOT NULL,
    user_id BIGINT,
    employment_status VARCHAR(20) CHECK (employment_status IN ('Active','Inactive','Terminated')),
    schedule TEXT,
    speciality VARCHAR(255),
    salary DECIMAL(10,2),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMP with TIME zone null,
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE job_vacancies (
    id BIGSERIAL PRIMARY KEY,
    position_id BIGINT NOT NULL,
    department_id BIGINT NOT NULL,
    status VARCHAR(20) CHECK (status IN ('Open', 'In Progress', 'Closed')) DEFAULT 'Open',
    posted_date DATE DEFAULT CURRENT_DATE,
    description TEXT,
    requirements TEXT,
    salary_range VARCHAR(50),
    FOREIGN KEY (position_id) REFERENCES positions(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE candidates (
    id BIGSERIAL PRIMARY KEY,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    cv_path VARCHAR(255)
);

CREATE TABLE job_applications (
    id BIGSERIAL PRIMARY KEY,
    candidate_id BIGINT,
    vacancy_id BIGINT,
    status VARCHAR(20) CHECK (status IN ('Review','Interview','Hired','Rejected')),
    application_date DATE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (vacancy_id) REFERENCES job_vacancies(id)
);

CREATE TABLE trainings (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    institution VARCHAR(200),
    provider VARCHAR(100),
    training_status VARCHAR(50) default 'Active',
    duration_hours BIGINT CHECK (duration_hours IS NULL OR duration_hours >= 0),
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE employee_trainings (
    employee_id BIGINT,
    training_id BIGINT,
    attended BOOLEAN DEFAULT FALSE,
    grade NUMERIC(5,2),
    issue_date DATE,
    expiration_date DATE,
    certificate VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    PRIMARY KEY (employee_id, training_id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (training_id) REFERENCES trainings(id)
);

CREATE TABLE performance_evaluations (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT,
    period VARCHAR(20),
    score NUMERIC(5,2),
    comments TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Revenue and billing management
CREATE TABLE revenue_sources (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100),
    description VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE invoices (
    id BIGSERIAL PRIMARY KEY,
    enrollment_id BIGINT,
    revenue_source_id BIGINT,
    invoice_number VARCHAR(50),
    issue_date DATE,
    total_amount NUMERIC(10,2),
    status VARCHAR(20) CHECK (status IN ('Pending','Paid','Cancelled')) DEFAULT 'Pending',
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id),
    FOREIGN KEY (revenue_source_id) REFERENCES revenue_sources(id)
);

CREATE TABLE payment_methods (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(50),
    description VARCHAR(150),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE payments (
    id BIGSERIAL PRIMARY KEY,
    invoice_id BIGINT,
    payment_method_id BIGINT,
    amount NUMERIC(10,2),
    payment_date DATE,
    status VARCHAR(20) CHECK (status IN ('Pending','Completed','Failed')) DEFAULT 'Pending',
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
);

CREATE TABLE payment_plans (
    id BIGSERIAL PRIMARY KEY,
    invoice_id BIGINT,
    installments BIGINT,
    installment_amount NUMERIC(10,2),
    total_amount NUMERIC(10,2),
    status VARCHAR(20) CHECK (status IN ('Active','Completed','Defaulted')) DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

CREATE TABLE financial_transactions (
    id BIGSERIAL PRIMARY KEY,
    account_id BIGINT NOT NULL,
    amount NUMERIC(12,2) NOT NULL,
    transaction_date DATE NOT NULL,
    description VARCHAR(255),
    transaction_type VARCHAR(10) CHECK (transaction_type IN ('income','expense')) NOT NULL,
    invoice_id BIGINT,
    payment_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL
);

-- =====================================================
-- NEW TABLES FROM GRUPO_3
-- =====================================================

CREATE TABLE instructor_applications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    bio TEXT,
    expertise_area VARCHAR(150),
    status VARCHAR(10) CHECK (status IN ('pending','approved','rejected')) DEFAULT 'pending',
    reviewed_at TIMESTAMPTZ,
    reviewed_by BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Course categories
CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    image VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE course_categories (
    id BIGSERIAL PRIMARY KEY,
    id_course_catg BIGINT,
    course_id BIGINT NOT NULL,
    category_id BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE course_instructors (
    id BIGSERIAL PRIMARY KEY,
    id_course_inst BIGINT,
    instructor_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    assigned_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (instructor_id) REFERENCES instructors(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE course_contents (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT NOT NULL,
    session BIGINT,
    type VARCHAR(50),
    title VARCHAR(255),
    content TEXT,
    order_number BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE graduates (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    program_id BIGINT NOT NULL,
    graduation_date DATE NOT NULL,
    final_note FLOAT,
    state VARCHAR(14) CHECK (state IN ('graduated','pending','withdrawn')),
    employability VARCHAR(255),
    feedback TEXT,
    CONSTRAINT uq_graduate UNIQUE (user_id, program_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);

-- Tickets and support
CREATE TABLE tickets (
    id BIGSERIAL PRIMARY KEY,
    ticket_id BIGINT,
    assigned_technician BIGINT,
    user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority VARCHAR(50) DEFAULT 'media',
    status VARCHAR(50) DEFAULT 'abierto',
    creation_date TIMESTAMPTZ DEFAULT now(),
    assignment_date TIMESTAMPTZ,
    resolution_date TIMESTAMPTZ,
    close_date TIMESTAMPTZ,
    category VARCHAR(100),
    notes TEXT,
    FOREIGN KEY (assigned_technician) REFERENCES employees(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE ticket_trackings (
    id BIGSERIAL PRIMARY KEY,
    ticket_tracking_id BIGINT,
    ticket_id BIGINT NOT NULL,
    comment TEXT,
    action_type VARCHAR(50),
    follow_up_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
);

CREATE TABLE escalations (
    id BIGSERIAL PRIMARY KEY,
    escalation_id BIGINT,
    ticket_id BIGINT NOT NULL,
    technician_origin_id BIGINT,
    technician_destiny_id BIGINT,
    escalation_reason VARCHAR(255),
    observations TEXT,
    escalation_date TIMESTAMPTZ DEFAULT now(),
    approved BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (technician_origin_id) REFERENCES employees(id),
    FOREIGN KEY (technician_destiny_id) REFERENCES employees(id)
);

-- Security management
CREATE TABLE security_logs (
    id BIGSERIAL PRIMARY KEY,
    id_security_log BIGINT,
    user_id BIGINT,
    event_type VARCHAR(100) NOT NULL,
    description TEXT,
    source_ip VARCHAR(45),
    event_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE blocked_ips (
    id BIGSERIAL PRIMARY KEY,
    id_blocked_ip BIGINT,
    ip_address VARCHAR(45) NOT NULL,
    reason VARCHAR(255),
    block_date TIMESTAMPTZ DEFAULT now(),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE security_alerts (
    id BIGSERIAL PRIMARY KEY,
    id_security_alert BIGINT,
    threat_type VARCHAR(100) NOT NULL,
    severity VARCHAR(50) DEFAULT 'medium',
    status VARCHAR(50) DEFAULT 'new',
    blocked_ip_id BIGINT,
    detection_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (blocked_ip_id) REFERENCES blocked_ips(id)
);

CREATE TABLE incidents (
    id BIGSERIAL PRIMARY KEY,
    id_incident BIGINT,
    alert_id BIGINT,
    responsible_id BIGINT,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'open',
    report_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (alert_id) REFERENCES security_alerts(id),
    FOREIGN KEY (responsible_id) REFERENCES employees(id)
);

CREATE TABLE active_sessions (
    id BIGSERIAL PRIMARY KEY,
    session_id BIGINT,
    user_id BIGINT NOT NULL,
    ip_address VARCHAR(45),
    device VARCHAR(255),
    start_date TIMESTAMPTZ DEFAULT now(),
    active BOOLEAN DEFAULT TRUE,
    blocked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE security_configurations (
    id BIGSERIAL PRIMARY KEY,
    id_security_configuration BIGINT,
    user_id BIGINT,
    modulo VARCHAR(100),
    parameter VARCHAR(100) NOT NULL,
    value TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Software and licenses
CREATE TABLE licenses (
    id BIGSERIAL PRIMARY KEY,
    id_license BIGINT,
    software_name VARCHAR(255) NOT NULL,
    license_key TEXT,
    license_type VARCHAR(100),
    provider VARCHAR(255),
    purchase_date DATE,
    expiration_date DATE,
    seats_total BIGINT,
    seats_used BIGINT DEFAULT 0,
    cost_annual NUMERIC(12,2),
    status VARCHAR(50) DEFAULT 'active',
    responsible_id BIGINT,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (responsible_id) REFERENCES employees(id)
);

CREATE TABLE softwares (
    id BIGSERIAL PRIMARY KEY,
    id_software BIGINT,
    software_name VARCHAR(255) NOT NULL,
    version VARCHAR(100),
    category VARCHAR(100),
    vendor VARCHAR(255),
    license_id BIGINT,
    installation_date TIMESTAMPTZ,
    last_update TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (license_id) REFERENCES licenses(id)
);

-- Chatbot management
CREATE TABLE chatbot_conversations (
    id BIGSERIAL PRIMARY KEY,
    id_conversation BIGINT,
    started_date TIMESTAMPTZ DEFAULT now(),
    ended_date TIMESTAMPTZ,
    satisfaction_rating BIGINT CHECK (satisfaction_rating BETWEEN 1 AND 5),
    feedback TEXT,
    resolved BOOLEAN DEFAULT FALSE,
    handed_to_human BOOLEAN DEFAULT FALSE
);

CREATE TABLE chatbot_faqs (
    id BIGSERIAL PRIMARY KEY,
    id_faq BIGINT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100),
    keywords JSONB,
    active BOOLEAN DEFAULT TRUE,
    usage_count BIGINT DEFAULT 0,
    created_date TIMESTAMPTZ DEFAULT now(),
    updated_date TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE chatbot_messages (
    id BIGSERIAL PRIMARY KEY,
    id_message BIGINT,
    conversation_id BIGINT NOT NULL,
    sender VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMPTZ DEFAULT now(),
    faq_matched BIGINT,
    FOREIGN KEY (conversation_id) REFERENCES chatbot_conversations(id),
    FOREIGN KEY (faq_matched) REFERENCES chatbot_faqs(id)
);

-- News and announcements
CREATE TABLE news (
    id BIGSERIAL PRIMARY KEY,
    id_news BIGINT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    summary TEXT,
    content TEXT,
    featured_image VARCHAR(500),
    author_id BIGINT,
    category VARCHAR(100),
    tags JSONB,
    status VARCHAR(50) DEFAULT 'draft',
    views BIGINT DEFAULT 0,
    published_date TIMESTAMPTZ,
    created_date TIMESTAMPTZ DEFAULT now(),
    updated_date TIMESTAMPTZ DEFAULT now(),
    seo_title VARCHAR(255),
    seo_description TEXT,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

CREATE TABLE announcements (
    id BIGSERIAL PRIMARY KEY,
    id_announcement BIGINT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(500),
    display_type VARCHAR(50),
    target_page VARCHAR(100),
    link_url VARCHAR(500),
    button_text VARCHAR(100),
    status VARCHAR(50) DEFAULT 'draft',
    start_date TIMESTAMPTZ,
    end_date TIMESTAMPTZ,
    views BIGINT DEFAULT 0,
    created_by BIGINT,
    created_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE alerts (
    id BIGSERIAL PRIMARY KEY,
    id_alert BIGINT,
    message TEXT NOT NULL,
    type VARCHAR(50),
    status VARCHAR(50) DEFAULT 'active',
    link_url VARCHAR(500),
    link_text VARCHAR(100),
    start_date TIMESTAMPTZ,
    end_date TIMESTAMPTZ,
    priority BIGINT DEFAULT 1,
    created_by BIGINT,
    created_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Contact forms
CREATE TABLE contact_forms (
    id BIGSERIAL PRIMARY KEY,
    id_contact BIGINT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    form_type VARCHAR(50),
    status VARCHAR(50) DEFAULT 'pending',
    assigned_to BIGINT,
    response TEXT,
    response_date TIMESTAMPTZ,
    submission_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (assigned_to) REFERENCES employees(id)
);

-- =====================================================
-- NEW TABLES FROM GRUPO_5
-- =====================================================

-- Teams management
CREATE TABLE teams (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    members JSONB,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Strategic planning
CREATE TABLE strategic_plans (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    status VARCHAR(50) DEFAULT 'draft',
    created_by_user_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    deleted_at TIMESTAMPTZ NULL,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

CREATE TABLE strategic_objectives (
    id BIGSERIAL PRIMARY KEY,
    plan_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    goal_value NUMERIC(12,2),
    responsible_user_id BIGINT,
    weight BIGINT DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (plan_id) REFERENCES strategic_plans(id),
    FOREIGN KEY (responsible_user_id) REFERENCES users(id)
);

CREATE TABLE kpis (
    id BIGSERIAL PRIMARY KEY,
    objective_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    target_value NUMERIC(12,2),
    unit VARCHAR(50),
    frequency VARCHAR(50),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (objective_id) REFERENCES strategic_objectives(id)
);

CREATE TABLE kpi_measurements (
    id BIGSERIAL PRIMARY KEY,
    kpi_id BIGINT NOT NULL,
    measured_at DATE NOT NULL,
    value NUMERIC(12,4),
    source VARCHAR(255),
    recorded_by_user_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (kpi_id) REFERENCES kpis(id),
    FOREIGN KEY (recorded_by_user_id) REFERENCES users(id)
);

CREATE TABLE dashboards (
    id BIGSERIAL PRIMARY KEY,
    plan_id BIGINT,
    title VARCHAR(255),
    owner_user_id BIGINT,
    widgets JSONB,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (plan_id) REFERENCES strategic_plans(id),
    FOREIGN KEY (owner_user_id) REFERENCES users(id)
);

CREATE TABLE initiatives (
    id BIGSERIAL PRIMARY KEY,
    plan_id BIGINT,
    title VARCHAR(255),
    summary TEXT,
    responsible_team_id BIGINT,
    responsible_user_id BIGINT,
    status VARCHAR(50),
    start_date DATE,
    end_date DATE,
    estimated_impact VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    deleted_at TIMESTAMPTZ NULL,
    FOREIGN KEY (plan_id) REFERENCES strategic_plans(id),
    FOREIGN KEY (responsible_team_id) REFERENCES teams(id),
    FOREIGN KEY (responsible_user_id) REFERENCES users(id)
);

CREATE TABLE initiative_evaluations (
    id BIGSERIAL PRIMARY KEY,
    initiative_id BIGINT NOT NULL,
    evaluator_user_id BIGINT,
    evaluation_date DATE,
    summary TEXT,
    score NUMERIC(5,2),
    report_document_version_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (initiative_id) REFERENCES initiatives(id),
    FOREIGN KEY (evaluator_user_id) REFERENCES users(id)
);

-- Partners and agreements
CREATE TABLE partners (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255),
    type VARCHAR(100),
    contact JSONB,
    legal_representative VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE agreements (
    id BIGSERIAL PRIMARY KEY,
    partner_id BIGINT NOT NULL,
    title VARCHAR(255),
    start_date DATE,
    end_date DATE,
    status VARCHAR(50),
    renewal_date DATE,
    electronic_signature BOOLEAN DEFAULT FALSE,
    created_by_user_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    deleted_at TIMESTAMPTZ NULL,
    FOREIGN KEY (partner_id) REFERENCES partners(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

-- Communication channels
CREATE TABLE channels (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255),
    channel_type VARCHAR(100),
    related_plan_id BIGINT,
    team_id BIGINT,
    created_by_user_id BIGINT,
    members JSONB,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (related_plan_id) REFERENCES strategic_plans(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id),
    FOREIGN KEY (team_id) REFERENCES teams(id)
);

CREATE TABLE messages (
    id BIGSERIAL PRIMARY KEY,
    channel_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    content TEXT,
    parent_id BIGINT,
    pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (channel_id) REFERENCES channels(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES messages(id)
);

-- Task management
CREATE TABLE tasks (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    channel_id BIGINT,
    initiative_id BIGINT,
    status VARCHAR(50),
    priority VARCHAR(50),
    due_date DATE,
    created_by_user_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (channel_id) REFERENCES channels(id),
    FOREIGN KEY (initiative_id) REFERENCES initiatives(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

CREATE TABLE task_assignments (
    id BIGSERIAL PRIMARY KEY,
    task_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    assigned_by_user_id BIGINT,
    assigned_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (task_id) REFERENCES tasks(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (assigned_by_user_id) REFERENCES users(id)
);

-- Document management (extends existing documents table)
CREATE TABLE document_versions (
    id BIGSERIAL PRIMARY KEY,
    document_id BIGINT NOT NULL,
    version_number BIGINT DEFAULT 1,
    file_name VARCHAR(255) NOT NULL,
    storage_path VARCHAR(1024) NOT NULL,
    mime_type VARCHAR(100),
    file_size BIGINT,
    uploaded_by_user_id BIGINT,
    uploaded_at TIMESTAMPTZ DEFAULT now(),
    checksum VARCHAR(128),
    notes TEXT,
    linked_type VARCHAR(100),
    linked_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (document_id) REFERENCES documents(id),
    FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id)
);

CREATE TABLE evidences (
    id BIGSERIAL PRIMARY KEY,
    initiative_id BIGINT,
    document_version_id BIGINT NOT NULL,
    description TEXT,
    kpi_measurement_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (document_version_id) REFERENCES document_versions(id),
    FOREIGN KEY (kpi_measurement_id) REFERENCES kpi_measurements(id),
    FOREIGN KEY (initiative_id) REFERENCES initiatives(id)
);

-- Surveys (Modified by tmr_script)
CREATE TABLE surveys (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    target_type VARCHAR(100),
    status VARCHAR(20) CHECK (status IN ('draft', 'active', 'closed')) DEFAULT 'draft',
    created_by_user_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);
COMMENT ON TABLE surveys IS 'Tabla principal para definir todas las encuestas del sistema.';

-- Survey Questions (Modified by tmr_script)
CREATE TABLE survey_questions (
    id BIGSERIAL PRIMARY KEY,
    survey_id BIGINT NOT NULL,
    question_text TEXT,
    question_type VARCHAR(50),
    "order" INTEGER DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
);
COMMENT ON TABLE survey_questions IS 'Preguntas pertenecientes a una encuesta definida en la tabla surveys.';

-- NEW Survey Options (from tmr_script)
CREATE TABLE survey_options (
    id BIGSERIAL PRIMARY KEY,
    survey_question_id BIGINT NOT NULL,
    option_text TEXT NOT NULL,
    "order" INTEGER DEFAULT 0, -- Orden de la opción
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (survey_question_id) REFERENCES survey_questions(id) ON DELETE CASCADE -- Se borra si se borra la pregunta
);
COMMENT ON TABLE survey_options IS 'Opciones para preguntas de opción múltiple de la tabla survey_questions.';

-- NEW Survey Answers (from tmr_script)
CREATE TABLE survey_answers (
    id BIGSERIAL PRIMARY KEY,
    survey_question_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL, -- Quién respondió (Enlazado a users general)
    survey_option_id BIGINT NULL, -- Respuesta si es opción múltiple (FK a survey_options)
    answer_text TEXT NULL,        -- Respuesta si es texto abierto
    answer_rating INTEGER NULL,   -- Respuesta si es calificación numérica
    answered_at TIMESTAMPTZ DEFAULT now(),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (survey_question_id) REFERENCES survey_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (survey_option_id) REFERENCES survey_options(id) ON DELETE SET NULL -- Si se borra la opción, la respuesta no se borra, solo pierde el enlace
);
COMMENT ON TABLE survey_answers IS 'Respuestas individuales de los usuarios a las preguntas de las encuestas.';

-- NEW Survey Assignments (from tmr_script)
CREATE TABLE survey_assignments (
    id BIGSERIAL PRIMARY KEY,
    survey_id BIGINT NOT NULL, -- Enlaza a la tabla principal 'surveys'
    user_id BIGINT NOT NULL, -- A quién se asigna (Enlazado a users general)
    status VARCHAR(20) CHECK (status IN ('pending', 'completed')) DEFAULT 'pending',
    assigned_at TIMESTAMPTZ DEFAULT now(),
    completed_at TIMESTAMPTZ NULL, -- Fecha en que completó la encuesta
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (survey_id, user_id) -- Evita asignar la misma encuesta dos veces a la misma persona
);
COMMENT ON TABLE survey_assignments IS 'Registra qué encuestas han sido asignadas a qué usuarios y su estado.';

-- survey_responses (JSONB version) was REMOVED by tmr_script


-- Audits and accreditations
CREATE TABLE audits (
    id BIGSERIAL PRIMARY KEY,
    area VARCHAR(255),
    user_id BIGINT,
    assigned_user_id BIGINT,
    start_date DATE,
    end_date DATE,
    summary_results TEXT,
    report_document_version_id BIGINT,
    type VARCHAR(13) CHECK (type IN ('internal','external')),
    state VARCHAR(16) CHECK (state IN ('planned','in_progress','completed','cancelled')),
    objective VARCHAR(255),
    range VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (report_document_version_id) REFERENCES document_versions(id),
    FOREIGN KEY (assigned_user_id) REFERENCES users(id)
);

CREATE TABLE accreditations (
    id BIGSERIAL PRIMARY KEY,
    entity VARCHAR(255),
    accreditation_date DATE,
    expiration_date DATE,
    result VARCHAR(255),
    document_version_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (document_version_id) REFERENCES document_versions(id)
);

-- Activity and notifications
CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT,
    action_type VARCHAR(100),
    target_type VARCHAR(100),
    target_id BIGINT,
    old_data JSONB,
    new_data JSONB,
    ip VARCHAR(50),
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Notifications (Modified by vrga_script)
CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP(0) NULL,
    created_at TIMESTAMP(0) DEFAULT now(),
    updated_at TIMESTAMP(0) NULL
);
CREATE INDEX IF NOT EXISTS notifications_notifiable_index ON notifications (notifiable_type, notifiable_id);


-- =====================================================
-- NEW TABLES FROM GRUPO_6
-- =====================================================
-- =============================================
-- NUEVO SISTEMA DE ENCUESTAS (Con prefijos)
-- =============================================

-- Tabla principal de encuestas de empleabilidad
CREATE TABLE emp_surveys (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Preguntas de las encuestas
CREATE TABLE emp_questions (
    id BIGSERIAL PRIMARY KEY,
    survey_id BIGINT NOT NULL,
    question_text TEXT NOT NULL,
    question_type VARCHAR(13) CHECK (question_type IN ('text', 'number', 'option', 'date')),
    question_order INTEGER DEFAULT 0,
    is_required BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (survey_id) REFERENCES emp_surveys(id) ON DELETE CASCADE
);

-- Opciones para preguntas de tipo opción
CREATE TABLE emp_question_options (
    id BIGSERIAL PRIMARY KEY,
    question_id BIGINT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    option_value VARCHAR(100),
    option_order INTEGER DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES emp_questions(id) ON DELETE CASCADE
);

-- Encuestas asignadas a graduados
CREATE TABLE emp_graduate_surveys (
    id BIGSERIAL PRIMARY KEY,
    graduate_id BIGINT NOT NULL,
    survey_id BIGINT NOT NULL,
    status VARCHAR(20) CHECK (status IN ('pending', 'in_progress', 'completed')) DEFAULT 'pending',
    assigned_date TIMESTAMPTZ DEFAULT now(),
    started_at TIMESTAMPTZ,
    completed_at TIMESTAMPTZ,
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE,
    FOREIGN KEY (survey_id) REFERENCES emp_surveys(id) ON DELETE CASCADE,
    UNIQUE(graduate_id, survey_id)
);

-- Respuestas de empleabilidad
CREATE TABLE emp_survey_responses (
    id BIGSERIAL PRIMARY KEY,
    graduate_survey_id BIGINT NOT NULL,
    question_id BIGINT NOT NULL,
    option_id BIGINT,
    text_response TEXT,
    number_response DECIMAL(10,2),
    date_response DATE,
    responded_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (graduate_survey_id) REFERENCES emp_graduate_surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES emp_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES emp_question_options(id) ON DELETE SET NULL,
    UNIQUE(graduate_survey_id, question_id)
);

-- Tabla de impactos sociales profesionales
CREATE TABLE emp_professional_impacts (
    id BIGSERIAL PRIMARY KEY,
    graduate_id BIGINT NOT NULL,
    description TEXT NOT NULL,
    impact_date DATE,
    evidence_url VARCHAR(500),
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE
);


-- Audit findings and reports  
CREATE TABLE findings (
    id BIGSERIAL PRIMARY KEY,
    audit_id BIGINT,
    description VARCHAR(255),
    classification VARCHAR(255),
    evidence VARCHAR(255),
    severity VARCHAR(20) CHECK (severity IN ('low','medium','high')) DEFAULT 'medium',
    discovery_date TIMESTAMPTZ,
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (audit_id) REFERENCES audits(id)
);

CREATE TABLE audit_reports (
    id BIGSERIAL PRIMARY KEY,
    audit_id BIGINT,
    version_document_id BIGINT,
    resume VARCHAR(255),
    recommendations VARCHAR(255),
    indicators VARCHAR(255),
    generation_date DATE,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (audit_id) REFERENCES audits(id),
    FOREIGN KEY (version_document_id) REFERENCES document_versions(id)
);

CREATE TABLE corrective_actions (
    id BIGSERIAL PRIMARY KEY,
    finding_id BIGINT,
    user_id BIGINT,
    description VARCHAR(255),
    status VARCHAR(20) CHECK (status IN ('pending','in_progress','completed','cancelled')) DEFAULT 'pending',
    engagement_date DATE,
    due_date DATE,
    completion_date DATE,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (finding_id) REFERENCES findings(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE document_audits (
    id BIGSERIAL PRIMARY KEY,
    audit_id BIGINT,
    version_document_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (audit_id) REFERENCES audits(id),
    FOREIGN KEY (version_document_id) REFERENCES document_versions(id)
);

-- Student-course relationships
CREATE TABLE student_courses (
    id BIGSERIAL PRIMARY KEY,
    id_student_course BIGINT,
    id_student BIGINT NOT NULL,
    id_curse BIGINT NOT NULL,
    assigned_date TIMESTAMPTZ,
    FOREIGN KEY (id_student) REFERENCES students(id),
    FOREIGN KEY (id_curse) REFERENCES courses(id)
);

-- Evaluation criteria and instructor evaluations
CREATE TABLE evaluation_sessions (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    academic_period VARCHAR(50),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'completed')),
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE evaluation_questions (
    id BIGSERIAL PRIMARY KEY,
    evaluation_session_id BIGINT REFERENCES evaluation_sessions(id),
    question_text TEXT NOT NULL,
    question_type VARCHAR(20) DEFAULT 'scale_1_5' CHECK (question_type IN ('scale_1_5', 'text')),
    question_order INT DEFAULT 0,
    is_required BOOLEAN DEFAULT true,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE evaluation_question_options (
    id BIGSERIAL PRIMARY KEY,
    question_id BIGINT NOT NULL REFERENCES evaluation_questions(id) ON DELETE CASCADE,
    option_value INT NOT NULL CHECK (option_value BETWEEN 1 AND 5),
    option_text VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE evaluation_responses (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT NOT NULL REFERENCES students(id),
    instructor_id BIGINT NOT NULL REFERENCES instructors(id),
    evaluation_session_id BIGINT NOT NULL REFERENCES evaluation_sessions(id),
    question_id BIGINT NOT NULL REFERENCES evaluation_questions(id),
    course_offering_id BIGINT REFERENCES course_offerings(id),
    rating INT CHECK (rating BETWEEN 1 AND 5),
    text_response TEXT,
    response_date TIMESTAMPTZ DEFAULT now(),
    created_at TIMESTAMPTZ DEFAULT now(),
    UNIQUE(student_id, instructor_id, question_id, evaluation_session_id)
);

CREATE TABLE instructor_evaluation_summary (
    id BIGSERIAL PRIMARY KEY,
    instructor_id BIGINT NOT NULL REFERENCES instructors(id),
    evaluation_session_id BIGINT NOT NULL REFERENCES evaluation_sessions(id),
    course_offering_id BIGINT REFERENCES course_offerings(id),
    total_questions INT DEFAULT 0,
    total_responses INT DEFAULT 0,
    average_rating DECIMAL(5,2),
    completion_rate DECIMAL(5,2),
    evaluation_period DATE,
    calculated_at TIMESTAMPTZ DEFAULT now(),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);


-- Satisfaction surveys
CREATE TABLE satisfaction_survey_categories (
    id BIGSERIAL PRIMARY KEY,
    id_category BIGINT,
    category_name VARCHAR(255) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE satisfaction_surveys (
    id BIGSERIAL PRIMARY KEY,
    id_satisfaction_survey BIGINT,
    id_category BIGINT NOT NULL,
    qualification VARCHAR(255),
    description VARCHAR(255),
    state VARCHAR(255),
    creation_date TIMESTAMPTZ,
    FOREIGN KEY (id_category) REFERENCES satisfaction_survey_categories(id)
);

CREATE TABLE satisfaction_questions (
    id BIGSERIAL PRIMARY KEY,
    id_satisfaction_question BIGINT,
    id_survey BIGINT NOT NULL,
    question_text VARCHAR(255) NOT NULL,
    type VARCHAR(255),
    FOREIGN KEY (id_survey) REFERENCES satisfaction_surveys(id)
);

CREATE TABLE satisfaction_options (
    id BIGSERIAL PRIMARY KEY,
    id_satisfaction_option BIGINT,
    id_question BIGINT NOT NULL,
    option_text VARCHAR(255),
    FOREIGN KEY (id_question) REFERENCES satisfaction_questions(id)
);

CREATE TABLE satisfaction_responses (
    id BIGSERIAL PRIMARY KEY,
    id_satisfaction_response BIGINT,
    id_student BIGINT NOT NULL,
    id_question BIGINT NOT NULL,
    id_opcion BIGINT,
    response_text VARCHAR(255),
    response_date TIMESTAMPTZ,
    FOREIGN KEY (id_question) REFERENCES satisfaction_questions(id),
    FOREIGN KEY (id_opcion) REFERENCES satisfaction_options(id),
    FOREIGN KEY (id_student) REFERENCES students(id)
);

CREATE TABLE surveys_assigned (
    id BIGSERIAL PRIMARY KEY,
    id_survey_assigned BIGINT,
    id_survey BIGINT NOT NULL,
    id_student BIGINT NOT NULL,
    state VARCHAR(255),
    creation_date TIMESTAMPTZ,
    FOREIGN KEY (id_survey) REFERENCES satisfaction_surveys(id),
    FOREIGN KEY (id_student) REFERENCES students(id)
);

CREATE TABLE survey_reports (
    id BIGSERIAL PRIMARY KEY,
    id_report BIGINT,
    id_survey BIGINT NOT NULL,
    report_type VARCHAR(255),
    file_path VARCHAR(255),
    creation_date TIMESTAMPTZ,
    FOREIGN KEY (id_survey) REFERENCES satisfaction_surveys(id)
);

-- Version document management (extends existing document management)
CREATE TABLE version_documents (
    id BIGSERIAL PRIMARY KEY,
    document_id BIGINT,
    file_id BIGINT,
    num_version VARCHAR(255),
    observations VARCHAR(255),
    user_id BIGINT,
    download_count INTEGER default 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- NEW TABLES FROM GRUPO_7
-- =====================================================

-- Community forums (versión actualizada)
CREATE TABLE public.student_community_forums (
    id BIGSERIAL,
    id_forum BIGINT,
    title VARCHAR(100),
    description VARCHAR(250),
    associated_program VARCHAR(250),
    state VARCHAR(14),
    creation_date TIMESTAMPTZ,
    creator_id BIGINT,
    CONSTRAINT forums_pkey PRIMARY KEY (id),
    CONSTRAINT forums_creator_fk
        FOREIGN KEY (creator_id) REFERENCES public.students(id)
            ON UPDATE NO ACTION ON DELETE NO ACTION,
    CONSTRAINT forums_state_check
        CHECK (state IN ('Pendiente','Activo','Finalizado','Rechazado'))
);

-- 2) Ajustar nombres para que coincidan esquema destino
ALTER SEQUENCE IF EXISTS public.student_community_forums_id_seq RENAME TO forums_id_seq;
ALTER TABLE public.student_community_forums
  ALTER COLUMN id SET DEFAULT nextval('forums_id_seq'::regclass);
ALTER SEQUENCE public.forums_id_seq OWNED BY public.student_community_forums.id;


-- Vinculations (versión actualizada)
CREATE TABLE student_community_vinculations (
    id BIGSERIAL PRIMARY KEY,
    id_foro BIGINT NOT NULL,
    id_estudiante BIGINT NOT NULL,
    fecha_vinculacion TIMESTAMP NOT NULL,
    tipo VARCHAR(14) NOT NULL,
    CONSTRAINT fk_vinc_forum
        FOREIGN KEY (id_foro) REFERENCES student_community_forums(id),
    CONSTRAINT fk_vinc_student
        FOREIGN KEY (id_estudiante) REFERENCES students(id),
    CONSTRAINT uq_vinculations_forum_student
        UNIQUE (id_foro, id_estudiante),
    CONSTRAINT vinculations_tipo_check
        CHECK (tipo IN ('Miembro','Moderador','Owner'))
);


CREATE TABLE student_community_posts(
    id BIGSERIAL PRIMARY KEY,
    id_publicacion BIGINT,
    forum_id BIGINT,
    student_id BIGINT,
    content VARCHAR(250),
    creation_date TIMESTAMPTZ,
    moderado BOOLEAN,
    FOREIGN KEY (forum_id) REFERENCES student_community_forums(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
);

CREATE TABLE student_community_moderation (
    id BIGSERIAL PRIMARY KEY,
    id_moderation BIGINT,
    publicacion_id BIGINT,
    admin_id BIGINT,
    action VARCHAR(50),
    comment VARCHAR(200),
    revision_date TIMESTAMPTZ,
    FOREIGN KEY (publicacion_id) REFERENCES student_community_posts(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

CREATE TABLE student_community_community_events (
    id BIGSERIAL PRIMARY KEY,
    id_event BIGINT,
    titulo VARCHAR(100),
    description VARCHAR(200),
    event_date TIMESTAMPTZ,
    student_id BIGINT,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

CREATE TABLE student_community_chat_messages (
    id BIGSERIAL PRIMARY KEY,
    id_message BIGINT,
    sender_id BIGINT,
    receiver_id BIGINT,
    content VARCHAR(250),
    sent_date TIMESTAMPTZ,
    seen BOOLEAN,
    FOREIGN KEY (sender_id) REFERENCES students(id),
    FOREIGN KEY (receiver_id) REFERENCES students(id)
);

-- Documentary processing
CREATE TABLE attention_students_Request_Types (
    id BIGSERIAL PRIMARY KEY,
    id_type BIGINT,
    name_type VARCHAR(80),
    description VARCHAR(200)
);

CREATE TABLE attention_students_Requests (
    id BIGSERIAL PRIMARY KEY,
    id_document BIGINT,
    student_id BIGINT,
    type_id BIGINT,
    description VARCHAR(250),
    creation_date TIMESTAMPTZ,
    update_date TIMESTAMPTZ,
    current_state VARCHAR(16) CHECK (current_state IN ('received', 'in_progress', 'completed', 'failed')),
    final_answer VARCHAR(500),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (type_id) REFERENCES attention_students_Request_Types(id)
);


CREATE TABLE attention_students_request_histories (
    id_history BIGSERIAL PRIMARY KEY,
    id_attention_students_request BIGINT,
    previous_state VARCHAR(30),
    new_state VARCHAR(30),
    comment VARCHAR(200),
    change_date TIMESTAMP,
    id_employee_responsible BIGINT,
    FOREIGN KEY (id_attention_students_request) REFERENCES attention_students_requests(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (id_employee_responsible) REFERENCES employees(id) ON UPDATE CASCADE ON DELETE SET NULL
);

-- Vocational guidance
CREATE TABLE vocational_questionnaires (
    id BIGSERIAL PRIMARY KEY,
    id_questionnaire BIGINT,
    title VARCHAR(80),
    description VARCHAR(200),
    creation_date TIMESTAMPTZ,
    activated BOOLEAN
);

CREATE TABLE vocational_questions (
    id BIGSERIAL PRIMARY KEY,
    id_question BIGINT,
    id_questionnaire BIGINT,
    text_question VARCHAR(250),
    type_response VARCHAR(250),
    FOREIGN KEY (id_questionnaire) REFERENCES vocational_questionnaires(id)
);

CREATE TABLE vocational_responses (
    id BIGSERIAL PRIMARY KEY,
    id_response BIGINT,
    id_question BIGINT,
    text_response VARCHAR(250),
    type_response VARCHAR(250),
    FOREIGN KEY (id_question) REFERENCES vocational_questions(id)
);

CREATE TABLE vocational_results (
    id BIGSERIAL PRIMARY KEY,
    id_resultado BIGINT,
    student_id BIGINT,
    questionnaire_id BIGINT,
    recommended_profile VARCHAR(100),
    completed_date TIMESTAMPTZ,
    score NUMERIC(5,2),
    recommendation VARCHAR(250),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (questionnaire_id) REFERENCES vocational_questionnaires(id)
);

CREATE TABLE vocational_answers(
	id BIGSERIAL PRIMARY KEY,
	student_id BIGINT,
	questionnaire_id BIGINT,
	question_id BIGINT,
	response_id BIGINT,
	answered_at TIMESTAMPTZ, 
	FOREIGN KEY (questionnaire_id) REFERENCES vocational_questionnaires(id),
	FOREIGN KEY (question_id) REFERENCES vocational_questions(id),
	FOREIGN KEY (response_id) REFERENCES vocational_responses(id)
);

CREATE TABLE vocational_response_courses(
	id BIGSERIAL PRIMARY KEY,
	response_id BIGINT,
	course_id BIGINT,
	rank SMALLINT DEFAULT 1,         -- orden sugerido 1..5
	weight NUMERIC(5,2) DEFAULT 1.0, -- ponderación opcional
	FOREIGN KEY (response_id) REFERENCES vocational_responses(id),
	FOREIGN KEY (course_id) REFERENCES courses(id)
);



-- Tutoring and wellness
CREATE TABLE student_wellbeing_tutorings (
    id BIGSERIAL PRIMARY KEY,
    id_tutorial BIGINT,
    estudent_id BIGINT,
    instructor_id BIGINT,
    scheduled_date TIMESTAMPTZ,
    type_tutorial VARCHAR(16) CHECK (type_tutorial IN ('Académica','Psicológica')),
    state VARCHAR(14) CHECK (state IN ('Agendada','Realizada','Cancelada')),
    FOREIGN KEY (estudent_id) REFERENCES students(id),
    FOREIGN KEY (instructor_id) REFERENCES instructors(id)
);

CREATE TABLE student_wellbeing_tutoring_assistances (
    id BIGSERIAL PRIMARY KEY,
    id_assistance BIGINT,
    tutoring_id BIGINT,
    attended BOOLEAN,
    observations VARCHAR(500),
    registration_date TIMESTAMPTZ,
    FOREIGN KEY (tutoring_id) REFERENCES student_wellbeing_tutorings(id)
);

CREATE TABLE student_wellbeing_extracurricular_activities (
    id BIGSERIAL PRIMARY KEY,
    id_activity BIGINT,
    activity_name VARCHAR(100),
    activity_type VARCHAR(16) CHECK (activity_type IN ('Deportiva','Cultural','Integración')),
    description VARCHAR(500),
    event_date TIMESTAMPTZ,
	student_creator_id BIGINT,
	FOREIGN KEY (student_creator_id) REFERENCES students
);

CREATE TABLE extracurricular_enrollments (
    id BIGSERIAL PRIMARY KEY,
    id_enrollment BIGINT,
    activity_id BIGINT NOT NULL,
    student_id BIGINT NOT NULL,
    status VARCHAR(15) CHECK (status IN ('registered','confirmed','attended','cancelled')) DEFAULT 'registered',
    role VARCHAR(16) CHECK (role IN ('participant','organizer','coach')) DEFAULT 'participant',
    attendance_at TIMESTAMPTZ,
    FOREIGN KEY (activity_id) REFERENCES student_wellbeing_extracurricular_activities(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
);



-- Claims and suggestions
CREATE TABLE claims (
    id BIGSERIAL PRIMARY KEY,
    id_claim BIGINT,
    estudent_id BIGINT,
    type VARCHAR(15) CHECK (type IN ('Reclamo','Sugerencia')),
    category VARCHAR(50),
    priority VARCHAR(10) CHECK (priority IN ('Alta','Media','Baja')),
    description VARCHAR(250),
    state VARCHAR(14) CHECK (state IN ('Agendada','Realizada','Cancelada')),
    creation_date TIMESTAMPTZ,
    FOREIGN KEY (estudent_id) REFERENCES students(id)
);

CREATE TABLE claim_assignments (
    id BIGSERIAL PRIMARY KEY,
    id_assignment BIGINT,
    claim_id BIGINT,
    responsible_id BIGINT,
    comments VARCHAR(500),
    event_date TIMESTAMPTZ,
    FOREIGN KEY (claim_id) REFERENCES claims(id),
    FOREIGN KEY (responsible_id) REFERENCES employees(id)
);

-- ===============================================
-- ACADEMIC MANAGEMENT SYSTEM (from grupo_2.sql)
-- User profiles and academic programs
-- ===============================================

-- --- Perfil del Profesor ---
CREATE TABLE teacher_profiles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL,
    professional_title VARCHAR(200) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    experience_years BIGINT DEFAULT 0,
    biography TEXT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE teacher_profiles ADD CONSTRAINT teacher_profiles_experience_years_check CHECK (experience_years >= 0);

-- --- Estructura de Cursos y Programas ---
CREATE TABLE program_courses (
    id BIGSERIAL PRIMARY KEY,
    program_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    mandatory BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_program_course UNIQUE (program_id, course_id),
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE course_previous_requirements (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT NOT NULL,
    previous_course_id BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_course_previous UNIQUE (course_id, previous_course_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (previous_course_id) REFERENCES courses(id) ON DELETE CASCADE
);

ALTER TABLE course_previous_requirements ADD CONSTRAINT ck_course_previous_no_self CHECK (course_id <> previous_course_id);

-- --- Grupos y Clases ---
CREATE TABLE groups (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'draft',
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
     
ALTER TABLE groups ADD CONSTRAINT groups_status_check CHECK (status IN ('draft','approved','open','in_progress','completed','cancelled','suspended'));

CREATE TABLE classes (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    meeting_url VARCHAR(500),
    description TEXT,
    class_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    class_status VARCHAR(12) DEFAULT 'SCHEDULED',
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);
     
ALTER TABLE classes ADD CONSTRAINT classes_class_status_check CHECK (class_status IN ('SCHEDULED','IN_PROGRESS','FINISHED','CANCELLED'));

CREATE TABLE class_materials (
    id BIGSERIAL PRIMARY KEY,             -- id del material
    class_id BIGINT NOT NULL,             -- id de la clase (llave foránea)
    material_url TEXT NOT NULL,           -- URL del material (puede ser larga)
    type VARCHAR(50) NOT NULL,            -- tipo de material (ej: 'PDF', 'Video', 'Enlace')
    created_at TIMESTAMP DEFAULT NOW(),   -- fecha de creación
    updated_at TIMESTAMP DEFAULT NOW(),   -- fecha de última actualización

    CONSTRAINT fk_class_materials_class
        FOREIGN KEY (class_id)
        REFERENCES classes (id)
        ON DELETE CASCADE                 -- elimina los materiales al borrar la clase
);

-- --- Participantes y Asistencia ---
CREATE TABLE group_participants (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role VARCHAR(10) NOT NULL, -- 'student' o 'teacher'
    enrollment_status VARCHAR(12) DEFAULT 'active',
    assignment_date TIMESTAMPTZ DEFAULT now(),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_group_user UNIQUE (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE group_participants ADD CONSTRAINT group_participants_role_check CHECK (role IN ('student','teacher'));
ALTER TABLE group_participants ADD CONSTRAINT group_participants_enrollment_status_check CHECK (enrollment_status IN ('pending','approved','rejected','active','withdrawn','finished'));

CREATE TABLE attendances (
    id BIGSERIAL PRIMARY KEY,
    group_participant_id BIGINT NOT NULL,
    class_id BIGINT NOT NULL,
    attended BOOLEAN DEFAULT FALSE,
    observations VARCHAR(200),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_attendance UNIQUE (group_participant_id, class_id),
    FOREIGN KEY (group_participant_id) REFERENCES group_participants(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- --- Sistema de Evaluaciones y Calificaciones (Simplificado) ---
CREATE TABLE evaluations (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    external_url VARCHAR(500),
    evaluation_type VARCHAR(20) NOT NULL,
    due_date TIMESTAMPTZ,
    weight NUMERIC(5,2) DEFAULT 1.00,
    teacher_creator_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_creator_id) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE evaluations ADD CONSTRAINT evaluations_evaluation_type_check CHECK (evaluation_type IN ('Exam','Quiz','Project','Assignment','Final'));
ALTER TABLE evaluations ADD CONSTRAINT evaluations_weight_check CHECK (weight > 0);

CREATE TABLE grade_records (
    id BIGSERIAL PRIMARY KEY,
    evaluation_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL, -- ID del estudiante
    obtained_grade NUMERIC(5,2) NOT NULL,
    feedback TEXT,
    record_date TIMESTAMPTZ DEFAULT now(),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_grade_record_eval_user UNIQUE (evaluation_id, user_id),
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE final_grades (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    group_id BIGINT NOT NULL,
    final_grade NUMERIC(5,2) NOT NULL,
    program_status VARCHAR(20) NOT NULL,
    calculation_date TIMESTAMPTZ DEFAULT now(),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_final_user_group UNIQUE (user_id, group_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

ALTER TABLE final_grades ADD CONSTRAINT final_grades_program_status_check CHECK (program_status IN ('Passed','Failed','Withdrawn','In_progress'));

-- --- Credenciales y Configuración ---
CREATE TABLE credentials (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID UNIQUE NOT NULL,
    user_id BIGINT NOT NULL,
    group_id BIGINT NOT NULL,
    issue_date DATE NOT NULL,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

CREATE TABLE academic_settings (
    id BIGSERIAL PRIMARY KEY,
    base_grade NUMERIC(5, 2) DEFAULT 20.00,
    min_passing_grade NUMERIC(5, 2) DEFAULT 11.00,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Crear tabla teacher_offers
CREATE TABLE teacher_offers (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    status VARCHAR(20) DEFAULT 'open',
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP
);

-- Agregar constraint CHECK para status en teacher_offers
ALTER TABLE teacher_offers 
ADD CONSTRAINT teacher_offers_status_check 
CHECK (status IN ('open', 'closed'));

-- Crear tabla teacher_applications
CREATE TABLE teacher_applications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    teacher_offer_id BIGINT NOT NULL,
    professional_title VARCHAR(200) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    experience_years BIGINT DEFAULT 0,
    biography TEXT,
    cv_path VARCHAR(500) NOT NULL,
    cover_letter TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    admin_feedback TEXT,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    
    -- Llaves foráneas
    CONSTRAINT fk_teacher_applications_user 
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
        
    CONSTRAINT fk_teacher_applications_teacher_offer 
        FOREIGN KEY (teacher_offer_id) 
        REFERENCES teacher_offers(id) 
        ON DELETE CASCADE,
    
    -- Restricción única para evitar postulaciones duplicadas
    CONSTRAINT user_offer_unique_application 
        UNIQUE (user_id, teacher_offer_id)
);

-- Agregar constraints CHECK para teacher_applications
ALTER TABLE teacher_applications 
ADD CONSTRAINT teacher_applications_status_check 
CHECK (status IN ('pending', 'accepted', 'rejected'));

ALTER TABLE teacher_applications 
ADD CONSTRAINT teacher_applications_experience_years_check 
CHECK (experience_years >= 0);

-- ====================================================
-- MARKETING AND CONTENT GENERATION SYSTEM (from grupo_4.sql)
-- Roles, locations, profiles, leads, and marketing tools
-- ====================================================

CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE locations (
    id BIGSERIAL PRIMARY KEY,
    country VARCHAR(100),
    region VARCHAR(100),
    city VARCHAR(100)
);

CREATE TABLE user_profiles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    address VARCHAR(255),
    gender VARCHAR(10) CHECK (gender IN ('male','female')),
    birth_date DATE,
    location_id BIGINT,
    bio TEXT,
    experience TEXT,
    photo VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
);

CREATE TABLE leads (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    origin VARCHAR(50),
    main_interest VARCHAR(100),
    status VARCHAR(20) CHECK (status IN ('new', 'qualified', 'contacted', 'discarded')) DEFAULT 'new',
    creation_date TIMESTAMPTZ DEFAULT now(),
    last_contact_date TIMESTAMPTZ
);

CREATE TABLE contents (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    content_type VARCHAR(20) CHECK (content_type IN ('social_post','blog','email','video_script','podcast')),
    platform VARCHAR(20) CHECK (platform IN ('facebook','instagram','twitter','web','newsletter','youtube')),
    prompt TEXT NOT NULL,
    generated_content TEXT NOT NULL,
    metadata JSONB,
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft','published','scheduled')),
    scheduled_date TIMESTAMPTZ,
    creation_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE generation_logs (
    id BIGSERIAL PRIMARY KEY,
    content_id BIGINT,
    status VARCHAR(20) CHECK (status IN ('successful','failed')),
    error_message TEXT,
    timestamp TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE
);

CREATE TABLE campaigns (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    channel VARCHAR(20) CHECK (channel IN ('whatsapp','facebook','instagram','webchat','email')) NOT NULL,
    target_audience VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active','paused','finished')),
    start_date TIMESTAMPTZ,
    end_date TIMESTAMPTZ,
    creation_date TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE bot_flows (
    id BIGSERIAL PRIMARY KEY,
    campaign_id BIGINT,
    flow_name VARCHAR(100) NOT NULL,
    trigger_keyword VARCHAR(50),
    flow_config JSONB NOT NULL,
    priority BIGINT DEFAULT 1,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
);

CREATE TABLE conversations (
    id BIGSERIAL PRIMARY KEY,
    lead_id BIGINT NOT NULL,
    campaign_id BIGINT,
    channel VARCHAR(20) NOT NULL,
    message_type VARCHAR(10) CHECK (message_type IN ('in','out')) NOT NULL,
    message_content TEXT NOT NULL,
    bot_flow_id BIGINT,
    agent_id BIGINT,
    timestamp TIMESTAMPTZ DEFAULT now(),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending','responded','closed')),
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
    FOREIGN KEY (bot_flow_id) REFERENCES bot_flows(id),
    FOREIGN KEY (agent_id) REFERENCES users(id)
);

CREATE TABLE service_configs (
    id BIGSERIAL PRIMARY KEY,
    service_type VARCHAR(30) CHECK (service_type IN ('ai_content','messaging')) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    config_data JSONB NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_update TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE campaign_metrics (
    id BIGSERIAL PRIMARY KEY,
    campaign_id BIGINT,
    date DATE DEFAULT CURRENT_DATE,
    impressions BIGINT DEFAULT 0,
    clicks BIGINT DEFAULT 0,
    conversions BIGINT DEFAULT 0,
    engagement_rate NUMERIC(5,4),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
);

CREATE TABLE content_performance (
    id BIGSERIAL PRIMARY KEY,
    content_id BIGINT,
    views BIGINT DEFAULT 0,
    likes BIGINT DEFAULT 0,
    shares BIGINT DEFAULT 0,
    click_rate NUMERIC(5,4),
    FOREIGN KEY (content_id) REFERENCES contents(id)
);

CREATE TABLE lead_course_interests (
    lead_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    interest_date TIMESTAMPTZ DEFAULT now(),
    PRIMARY KEY (lead_id, course_id),
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE exported_reports (
    id BIGSERIAL PRIMARY KEY,
    
    report_type VARCHAR(255) NOT NULL,
    format VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    report_title VARCHAR(255) NOT NULL,
    file_size BIGINT DEFAULT 0,
    filters JSONB NULL,
    description TEXT NULL,
    record_count INTEGER DEFAULT 0,
    generated_by BIGINT NOT NULL,
    access_token VARCHAR(255) UNIQUE NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT fk_exported_reports_generated_by 
        FOREIGN KEY (generated_by) 
        REFERENCES users(id) 
        ON DELETE CASCADE
);