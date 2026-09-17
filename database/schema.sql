SET NAMES utf8mb4;
SET time_zone = '+05:30';

CREATE TABLE IF NOT EXISTS students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(190) DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    status ENUM('Active','Inactive','Blocked') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_students_phone (phone),
    UNIQUE KEY uq_students_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    category VARCHAR(100) NOT NULL,
    duration_weeks SMALLINT UNSIGNED NOT NULL DEFAULT 24,
    rating DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    student_count INT UNSIGNED NOT NULL DEFAULT 0,
    starting_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('Published','Draft','Inactive') NOT NULL DEFAULT 'Published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_courses_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS online_degrees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    degree_name VARCHAR(160) NOT NULL,
    university VARCHAR(190) NOT NULL,
    duration VARCHAR(80) NOT NULL,
    total_fee VARCHAR(80) NOT NULL,
    eligibility VARCHAR(255) NOT NULL,
    syllabus TEXT NOT NULL,
    status ENUM('Published','Draft','Inactive') NOT NULL DEFAULT 'Published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_degrees_name_university (degree_name, university)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institutes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_name VARCHAR(190) NOT NULL,
    institute_type VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    status ENUM('Published','Draft','Inactive') NOT NULL DEFAULT 'Published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_institutes_name (institute_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institute_courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institute_id BIGINT UNSIGNED NOT NULL,
    course_name VARCHAR(180) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_institute_courses_institute FOREIGN KEY (institute_id) REFERENCES institutes(id) ON DELETE CASCADE,
    KEY idx_institute_courses_institute (institute_id),
    UNIQUE KEY uq_institute_course (institute_id, course_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(190) NOT NULL DEFAULT '',
    address VARCHAR(300) NOT NULL DEFAULT '',
    qualification VARCHAR(180) NOT NULL DEFAULT '',
    institute VARCHAR(180) NOT NULL DEFAULT '',
    interested_in VARCHAR(120) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('New','Contacted','Closed') NOT NULL DEFAULT 'New',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_contact_status_created (status, created_at),
    KEY idx_contact_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED DEFAULT NULL,
    student_name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL DEFAULT '',
    email VARCHAR(190) NOT NULL DEFAULT '',
    course_name VARCHAR(180) NOT NULL,
    access_plan VARCHAR(120) NOT NULL,
    amount VARCHAR(40) NOT NULL,
    status ENUM('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
    KEY idx_enrollment_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cbt_tests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    question_count SMALLINT UNSIGNED NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL,
    full_marks SMALLINT UNSIGNED NOT NULL,
    status ENUM('Available','Upcoming','Closed') NOT NULL DEFAULT 'Upcoming',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cbt_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO courses (title, category, duration_weeks, rating, student_count, starting_fee) VALUES
('AI & Machine Learning','Technology',24,4.9,126,299),
('Web Development','Development',24,4.8,214,299),
('Share Trading','Finance',24,4.7,98,299),
('Digital Marketing','Marketing',24,4.8,184,299),
('Content Creation & Editing','Creative',24,4.7,142,299),
('App Development','Development',24,4.9,117,299);

INSERT IGNORE INTO online_degrees (degree_name, university, duration, total_fee, eligibility, syllabus) VALUES
('Online MBA','Amity University Online','24 months','₹2,50,000','Graduation in any discipline','Management Principles, Marketing, Finance, HR and Strategic Management'),
('Online BCA','Manipal University Jaipur','36 months','₹1,35,000','10+2 or equivalent','Programming, Data Structures, Database, Web Technology and Cloud Fundamentals'),
('Online MCA','Jain University Online','24 months','₹1,80,000','Bachelor degree with Mathematics','Advanced Programming, AI, Data Science, Cyber Security and Capstone Project'),
('Online BBA','Chandigarh University Online','36 months','₹1,20,000','10+2 or equivalent','Business Communication, Accounting, Marketing, Economics and Entrepreneurship'),
('Online M.Com','Lovely Professional University','24 months','₹96,000','Bachelor degree in a relevant field','Corporate Accounting, Taxation, Business Law, Financial Management and Research');

INSERT IGNORE INTO institutes (institute_name, institute_type, image_path) VALUES
('Amity University Online','University','assets/images/admission/campus-1.webp'),
('Manipal University Jaipur','University','assets/images/admission/campus-2.webp'),
('Jain University Online','University','assets/images/admission/campus-2.webp'),
('Chandigarh University Online','University','assets/images/admission/campus-1.webp');

INSERT IGNORE INTO institute_courses (institute_id, course_name) VALUES
(1,'Online MBA'),(1,'Online BBA'),(1,'Online BCA'),(1,'Online MCA'),(1,'Online M.Com'),
(2,'Online BCA'),(2,'Online MCA'),(2,'Online MBA'),(2,'Online MA'),(2,'Online B.Com'),
(3,'Online MBA'),(3,'Online MCA'),(3,'Online M.Com'),(3,'Online BBA'),(3,'Online BCA'),
(4,'Online BBA'),(4,'Online MBA'),(4,'Online BA'),(4,'Online MA'),(4,'Online MCA');

INSERT IGNORE INTO cbt_tests (title, subject, question_count, duration_minutes, full_marks, status) VALUES
('General Aptitude Mock Test','Competitive Exam',50,60,100,'Available'),
('Computer Fundamentals Test','Computer & IT',30,30,60,'Available'),
('English Practice Test','Language Skills',25,25,50,'Upcoming');
