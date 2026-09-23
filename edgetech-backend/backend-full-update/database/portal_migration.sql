-- Edge Tech Solution - Student Portal migration
-- Run this ONCE against an already-imported edgetech_crm database:
--   mysql -u root edgetech_crm < database/portal_migration.sql
-- (schema.sql already contains these tables for a fresh install.)

-- Enrolled students (manually created in the CRM). student_id + register_number
-- are both unique and are the two credentials the student uses to log in.
CREATE TABLE IF NOT EXISTS portal_students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(30) NOT NULL UNIQUE,
  register_number VARCHAR(40) NOT NULL UNIQUE,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  course_id INT DEFAULT NULL,
  course_title VARCHAR(200) DEFAULT NULL,
  batch VARCHAR(100) DEFAULT NULL,
  duration VARCHAR(50) DEFAULT NULL,
  dob DATE DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  enrolled_on DATE DEFAULT NULL,
  completed_on DATE DEFAULT NULL,
  status ENUM('active','completed','dropped') DEFAULT 'active',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Certificates issued to a student. file_url may be empty - the website then
-- renders a styled placeholder ("dummy") certificate instead.
CREATE TABLE IF NOT EXISTS student_certificates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  portal_student_id INT NOT NULL,
  certificate_number VARCHAR(50) NOT NULL,
  title VARCHAR(200) NOT NULL,
  type ENUM('completion','internship','achievement') DEFAULT 'completion',
  issue_date DATE DEFAULT NULL,
  file_url VARCHAR(255) DEFAULT NULL,
  grade VARCHAR(20) DEFAULT NULL,
  status ENUM('issued','draft') DEFAULT 'issued',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (portal_student_id) REFERENCES portal_students(id) ON DELETE CASCADE
) ENGINE=InnoDB;
