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
    description TEXT
);

CREATE TABLE positions (
    id BIGSERIAL PRIMARY KEY,
    position_name VARCHAR(100) NOT NULL,
    department_id BIGINT NOT NULL,
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
    created_by_user_id BIGINT,
    members JSONB,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (related_plan_id) REFERENCES strategic_plans(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
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

-- Surveys
CREATE TABLE surveys (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255),
    target_type VARCHAR(100),
    created_by_user_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

CREATE TABLE survey_questions (
    id BIGSERIAL PRIMARY KEY,
    survey_id BIGINT NOT NULL,
    question_text TEXT,
    question_type VARCHAR(50),
    FOREIGN KEY (survey_id) REFERENCES surveys(id)
);

CREATE TABLE survey_responses (
    id BIGSERIAL PRIMARY KEY,
    survey_id BIGINT NOT NULL,
    respondent_user_id BIGINT,
    answers JSONB,
    completed_at TIMESTAMPTZ,
    FOREIGN KEY (survey_id) REFERENCES surveys(id),
    FOREIGN KEY (respondent_user_id) REFERENCES users(id)
);

-- Audits and accreditations
CREATE TABLE audits (
    id BIGSERIAL PRIMARY KEY,
    area VARCHAR(255),
    user_id BIGINT,
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
    FOREIGN KEY (report_document_version_id) REFERENCES document_versions(id)
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

CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    title VARCHAR(255),
    type VARCHAR(255) NOT NULL,

    message TEXT,
    read_at TIMESTAMPTZ NULL,
    related_type VARCHAR(100),
    related_id BIGINT,
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id)
);


-- =====================================================
-- NEW TABLES FROM GRUPO_6
-- =====================================================

-- Employability surveys
CREATE TABLE employability_questions (
    id BIGSERIAL PRIMARY KEY,
    id_question BIGINT,
    question_text VARCHAR(255),
    question_type VARCHAR(13) CHECK (question_type IN ('text', 'number', 'option', 'date'))
);

CREATE TABLE option_job_questions (
    id BIGSERIAL PRIMARY KEY,
    id_option BIGINT,
    id_question BIGINT,
    option_text VARCHAR(50),
    FOREIGN KEY (id_question) REFERENCES employability_questions(id)
);

CREATE TABLE employability_surveys (
    id BIGSERIAL PRIMARY KEY,
    id_survey BIGINT,
    id_graduates BIGINT,
    registration_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (id_graduates) REFERENCES graduates(id)
);

CREATE TABLE response_graduates (
    id BIGSERIAL PRIMARY KEY,
    id_answer BIGINT,
    id_survey BIGINT,
    id_question BIGINT,
    id_option BIGINT,
    answer_text VARCHAR(255),
    answer_number FLOAT,
    fanswer_date TIMESTAMPTZ,
    FOREIGN KEY (id_survey) REFERENCES employability_surveys(id),
    FOREIGN KEY (id_question) REFERENCES employability_questions(id),
    FOREIGN KEY (id_option) REFERENCES option_job_questions(id)
);

CREATE TABLE professional_social_impacts (
    id BIGSERIAL PRIMARY KEY,
    id_impact BIGINT,
    id_graduates BIGINT,
    description VARCHAR(255),
    registration_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (id_graduates) REFERENCES graduates(id)
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
CREATE TABLE evaluation_criteria (
    id BIGSERIAL PRIMARY KEY,
    id_evaluation_criteria BIGINT,
    criterion_name VARCHAR(255) NOT NULL,
    category TIMESTAMPTZ,
    response_type VARCHAR(13) CHECK (response_type IN ('numeric', 'text', 'option')) NOT NULL,
    percentage_weight FLOAT,
    state VARCHAR(255)
);

CREATE TABLE option_criteria (
    id BIGSERIAL PRIMARY KEY,
    id_option_criteria BIGINT,
    id_evaluation_criteria BIGINT NOT NULL,
    option_text VARCHAR(255),
    FOREIGN KEY (id_evaluation_criteria) REFERENCES evaluation_criteria(id)
);

CREATE TABLE instructor_evaluations (
    id BIGSERIAL PRIMARY KEY,
    instructor_evaluation_id BIGINT,
    student_id BIGINT,
    instructor_id BIGINT,
    course_offering_id BIGINT,
    rating NUMERIC(3,2),
    feedback TEXT,
    evaluation_period VARCHAR(255),
    evaluation_status VARCHAR(255),
    evaluation_date TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (instructor_id) REFERENCES instructors(id),
    FOREIGN KEY (course_offering_id) REFERENCES course_offerings(id)
);

CREATE TABLE detail_evaluation_criteria (
    id BIGSERIAL PRIMARY KEY,
    id_detail_evaluation_criteria BIGINT,
    id_evaluation_criteria BIGINT NOT NULL,
    id_option_criteria BIGINT,
    id_instructor_evaluation BIGINT NOT NULL,
    numeric_response FLOAT,
    response_text VARCHAR(255),
    FOREIGN KEY (id_evaluation_criteria) REFERENCES evaluation_criteria(id),
    FOREIGN KEY (id_option_criteria) REFERENCES option_criteria(id),
    FOREIGN KEY (id_instructor_evaluation) REFERENCES instructor_evaluations(id)
);

CREATE TABLE evaluation_reports (
    id BIGSERIAL PRIMARY KEY,
    id_evaluation_report BIGINT,
    id_instructor BIGINT NOT NULL,
    id_instructor_evaluation BIGINT NOT NULL,
    id_curse BIGINT NOT NULL,
    overall_average FLOAT,
    evaluation_period FLOAT,
    total_evaluations FLOAT,
    generation_date TIMESTAMPTZ,
    FOREIGN KEY (id_instructor) REFERENCES instructors(id),
    FOREIGN KEY (id_instructor_evaluation) REFERENCES instructor_evaluations(id),
    FOREIGN KEY (id_curse) REFERENCES courses(id)
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
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- NEW TABLES FROM GRUPO_7
-- =====================================================

-- Community forums
CREATE TABLE student_community_forums (
    id BIGSERIAL PRIMARY KEY,
    id_forum BIGINT,
    title VARCHAR(100),
    description VARCHAR(250),
    associated_program VARCHAR(250),
    state VARCHAR(14) CHECK (state IN ('Agendada','Realizada','Cancelada')),
    creation_date TIMESTAMPTZ
);

CREATE TABLE student_community_vinculations (
    id BIGSERIAL PRIMARY KEY,
    id_foro BIGINT NOT NULL,
    id_estudiante BIGINT NOT NULL,
    fecha_vinculacion TIMESTAMP NOT NULL,
    tipo VARCHAR(14) NOT NULL CHECK (tipo IN ('Miembro','Moderador')),
    CONSTRAINT fk_vinc_forum
        FOREIGN KEY (id_foro) REFERENCES student_community_forums(id),
    CONSTRAINT fk_vinc_student
        FOREIGN KEY (id_estudiante) REFERENCES students(id),
    CONSTRAINT uq_vinculations_forum_student
        UNIQUE (id_foro, id_estudiante)
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

CREATE TABLE teacher_profiles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL,
    professional_title VARCHAR(200) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    experience_years BIGINT DEFAULT 0 CHECK (experience_years >= 0),
    biography TEXT,
    linkedin_link VARCHAR(255),
    cover_photo VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE student_profiles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL,
    career_interest VARCHAR(100),
    work_situation VARCHAR(20) CHECK (work_situation IN ('employed','unemployed','student','other')),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE program_courses (
    id BIGSERIAL PRIMARY KEY,
    program_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    mandatory BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_program_course UNIQUE (program_id, course_id),
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE course_previous_requirements (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT NOT NULL,
    previous_course_id BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT ck_course_previous_no_self CHECK (course_id <> previous_course_id),
    CONSTRAINT uq_course_previous UNIQUE (course_id, previous_course_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (previous_course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE groups (
    id BIGSERIAL PRIMARY KEY,
    course_id BIGINT NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    minimum_enrolled BIGINT DEFAULT 1 CHECK (minimum_enrolled >= 1),
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft','approved','open','in_progress','completed','cancelled','suspended')),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE classes (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    class_name VARCHAR(100) NOT NULL,
    class_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    platform VARCHAR(50) DEFAULT 'Zoom',
    meeting_url VARCHAR(500),
    external_meeting_id VARCHAR(100),
    meeting_password VARCHAR(100),
    allow_recording BOOLEAN DEFAULT TRUE,
    recording_url VARCHAR(500),
    max_participants BIGINT DEFAULT 100 CHECK (max_participants > 0),
    class_status VARCHAR(12) DEFAULT 'SCHEDULED' CHECK (class_status IN ('SCHEDULED','IN_PROGRESS','FINISHED','CANCELLED')),
    created_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE group_participants (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role VARCHAR(10) NOT NULL CHECK (role IN ('student','teacher')),
    teacher_function VARCHAR(20) NULL CHECK (teacher_function IN ('titular','auxiliary','coordinator')),
    enrollment_status VARCHAR(12) DEFAULT 'active' CHECK (enrollment_status IN ('pending','approved','rejected','active','withdrawn','finished')),
    assignment_date TIMESTAMPTZ DEFAULT now(),
    schedule JSONB,
    CONSTRAINT uq_group_user UNIQUE (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE attendances (
    id BIGSERIAL PRIMARY KEY,
    group_participant_id BIGINT NOT NULL REFERENCES group_participants(id) ON DELETE CASCADE,
    class_id BIGINT NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    attended VARCHAR(3) DEFAULT 'NO' CHECK (attended IN ('YES','NO')),
    entry_time TIMESTAMPTZ,
    exit_time TIMESTAMPTZ,
    connected_minutes BIGINT DEFAULT 0 CHECK (connected_minutes >= 0),
    connection_ip VARCHAR(45),
    device VARCHAR(100),
    approximate_location VARCHAR(100),
    connection_quality VARCHAR(12) CHECK (connection_quality IN ('EXCELLENT','GOOD','FAIR','POOR')),
    observations VARCHAR(200),
    cloud_synchronized BOOLEAN DEFAULT FALSE,
    record_date TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_attendance UNIQUE (group_participant_id, class_id)
);

CREATE TABLE evaluations (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    evaluation_type VARCHAR(20) NOT NULL CHECK (evaluation_type IN ('Exam','Quiz','Project','Assignment','Final')),
    start_date TIMESTAMPTZ NOT NULL,
    end_date TIMESTAMPTZ NOT NULL,
    duration_minutes BIGINT NOT NULL CHECK (duration_minutes > 0),
    total_score NUMERIC(5,2) NOT NULL CHECK (total_score > 0),
    status VARCHAR(20) DEFAULT 'Active' CHECK (status IN ('Active','Inactive','Finished')),
    teacher_creator_id BIGINT REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE questions (
    id BIGSERIAL PRIMARY KEY,
    evaluation_id BIGINT NOT NULL REFERENCES evaluations(id) ON DELETE CASCADE,
    statement TEXT NOT NULL,
    question_type VARCHAR(20) NOT NULL CHECK (question_type IN ('Multiple','Essay','True_False')),
    answer_options JSONB,
    correct_answer JSONB,
    score NUMERIC(5,2) NOT NULL CHECK (score > 0)
);

CREATE TABLE attempts (
    id BIGSERIAL PRIMARY KEY,
    evaluation_id BIGINT NOT NULL REFERENCES evaluations(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- student
    start_date TIMESTAMPTZ NOT NULL,
    end_date TIMESTAMPTZ,
    answers JSONB NOT NULL,
    obtained_score NUMERIC(5,2),
    status VARCHAR(20) DEFAULT 'In_progress' CHECK (status IN ('In_progress','Completed','Abandoned'))
);

CREATE TABLE gradings (
    id BIGSERIAL PRIMARY KEY,
    attempt_id BIGINT NOT NULL,
    teacher_grader_id BIGINT,
    grading_detail JSONB NOT NULL,
    feedback TEXT,
    grading_date TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_grading_per_attempt UNIQUE (attempt_id),
    FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_grader_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE grade_configurations (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL UNIQUE REFERENCES groups(id) ON DELETE CASCADE,
    grading_system VARCHAR(50) NOT NULL,  -- e.g., 0-20
    max_grade NUMERIC(5,2) NOT NULL,
    passing_grade NUMERIC(5,2) NOT NULL,
    evaluation_weight NUMERIC(5,2) DEFAULT 100.00
);

CREATE TABLE grade_records (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- student
    evaluation_id BIGINT NOT NULL REFERENCES evaluations(id) ON DELETE CASCADE,
    group_id BIGINT NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    configuration_id BIGINT NOT NULL REFERENCES grade_configurations(id) ON DELETE CASCADE,
    obtained_grade NUMERIC(5,2) NOT NULL,
    grade_weight NUMERIC(5,2) NOT NULL,
    grade_type VARCHAR(20) NOT NULL CHECK (grade_type IN ('Partial','Final','Makeup')),
    status VARCHAR(20) DEFAULT 'Recorded' CHECK (status IN ('Recorded','Validated','Published','Observed')),
    record_date TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE final_grades (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- student
    group_id BIGINT NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    configuration_id BIGINT NOT NULL REFERENCES grade_configurations(id) ON DELETE CASCADE,
    final_grade NUMERIC(5,2) NOT NULL,
    partial_average NUMERIC(5,2),
    program_status VARCHAR(20) NOT NULL CHECK (program_status IN ('Passed','Failed','Withdrawn','In_progress')),
    certification_obtained BOOLEAN DEFAULT FALSE,
    calculation_date TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_final_user_group UNIQUE (user_id, group_id)
);

CREATE TABLE grade_changes (
    id BIGSERIAL PRIMARY KEY,
    record_id BIGINT NOT NULL REFERENCES grade_records(id) ON DELETE CASCADE,
    previous_grade NUMERIC(5,2) NOT NULL,
    new_grade NUMERIC(5,2) NOT NULL,
    reason TEXT NOT NULL,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    change_date TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE certificates (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    program_id BIGINT NOT NULL,
    issue_date DATE NOT NULL,
    status VARCHAR(20),
    verification_code VARCHAR(255) UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);

CREATE TABLE diplomas (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    program_id BIGINT NOT NULL,
    issue_date DATE NOT NULL,
    status VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);

CREATE TABLE graduate_surveys (
    id BIGSERIAL PRIMARY KEY,
    graduate_id BIGINT NOT NULL,
    date DATE NOT NULL,
    employability VARCHAR(255),
    satisfaction VARCHAR(50),
    curriculum_feedback TEXT,
    FOREIGN KEY (graduate_id) REFERENCES graduates(id) ON DELETE CASCADE
);

CREATE TABLE teacher_recruitments (
    id BIGSERIAL PRIMARY KEY,
    request_date DATE NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    required_profile TEXT,
    status VARCHAR(12) DEFAULT 'open' CHECK (status IN ('open','closed','suspended')),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE teacher_applications (
    id BIGSERIAL PRIMARY KEY,
    recruitment_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    cv VARCHAR(255) NOT NULL,
    status VARCHAR(15) DEFAULT 'received' CHECK (status IN ('received','under_review','interview','selected','rejected')),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_application UNIQUE (recruitment_id, user_id),
    FOREIGN KEY (recruitment_id) REFERENCES teacher_recruitments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE teacher_evaluations (
    id BIGSERIAL PRIMARY KEY,
    evaluator_id BIGINT NOT NULL,
    group_id BIGINT NOT NULL,
    teacher_id BIGINT NOT NULL,
    answers JSONB NOT NULL,
    score NUMERIC(5,2),
    created_at TIMESTAMPTZ DEFAULT now(),
    FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

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