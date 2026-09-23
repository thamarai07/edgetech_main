-- Edge Tech Solution - CRM Database Schema
-- Import this via phpMyAdmin (XAMPP) or: mysql -u root -p < schema.sql


-- Admin users for the CRM login
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Courses (replaces src/data/courses.json)
CREATE TABLE IF NOT EXISTS courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(180) NOT NULL UNIQUE,
  title VARCHAR(200) NOT NULL,
  category VARCHAR(100) NOT NULL,
  level VARCHAR(100) NOT NULL,
  duration VARCHAR(50) NOT NULL,
  projects INT DEFAULT 0,
  mentor VARCHAR(150) DEFAULT NULL,
  mentor_role VARCHAR(200) DEFAULT NULL,
  rating DECIMAL(2,1) DEFAULT 4.5,
  reviews_count INT DEFAULT 0,
  price INT NOT NULL DEFAULT 0,
  original_price INT DEFAULT 0,
  image VARCHAR(255) DEFAULT NULL,
  tools JSON DEFAULT NULL,
  description TEXT,
  curriculum JSON DEFAULT NULL,
  certificate TINYINT(1) DEFAULT 1,
  internship TINYINT(1) DEFAULT 1,
  placement_support TINYINT(1) DEFAULT 1,
  status ENUM('active','draft') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Course enquiry / enrollment leads (from individual course page forms)
CREATE TABLE IF NOT EXISTS course_enquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT DEFAULT NULL,
  course_title VARCHAR(200) NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  city VARCHAR(100) DEFAULT NULL,
  qualification VARCHAR(150) DEFAULT NULL,
  message TEXT,
  status ENUM('new','contacted','converted','not_interested') DEFAULT 'new',
  source VARCHAR(50) DEFAULT 'course_page',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- General contact form messages
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  course_interest VARCHAR(200) DEFAULT NULL,
  message TEXT NOT NULL,
  status ENUM('new','contacted','replied') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Student reviews / testimonials (Reviews page)
CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  role VARCHAR(200) DEFAULT NULL,
  course VARCHAR(200) DEFAULT NULL,
  rating TINYINT DEFAULT 5,
  image VARCHAR(255) DEFAULT NULL,
  quote TEXT NOT NULL,
  status ENUM('published','hidden') DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Mentors (About page — replaces src/data/mentors.json)
CREATE TABLE IF NOT EXISTS mentors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  role VARCHAR(200) NOT NULL,
  company VARCHAR(150) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  linkedin_url VARCHAR(255) DEFAULT NULL,
  sort_order INT DEFAULT 0,
  status ENUM('published','hidden') DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Blog posts
CREATE TABLE IF NOT EXISTS blog_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(220) NOT NULL UNIQUE,
  title VARCHAR(250) NOT NULL,
  excerpt VARCHAR(500) DEFAULT NULL,
  content LONGTEXT,
  cover_image VARCHAR(255) DEFAULT NULL,
  author VARCHAR(150) DEFAULT 'Edge Tech Solution',
  category VARCHAR(100) DEFAULT NULL,
  status ENUM('published','draft') DEFAULT 'published',
  published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Placement records (for the Placements page + stats)
CREATE TABLE IF NOT EXISTS placements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_name VARCHAR(150) NOT NULL,
  company VARCHAR(150) NOT NULL,
  role VARCHAR(150) NOT NULL,
  course VARCHAR(200) DEFAULT NULL,
  package_lpa DECIMAL(4,1) DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  placed_on DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- NOTE: The default admin user (admin / Admin@123) is created by running
-- backend/setup_admin.php once in the browser AFTER importing this schema.
-- That script uses PHP's password_hash() so the hash is guaranteed correct.

-- Seed courses (migrated from src/data/courses.json)
INSERT INTO courses (slug, title, category, level, duration, projects, mentor, mentor_role, rating, reviews_count, price, original_price, image, tools, description, certificate, internship, placement_support) VALUES
('full-stack-web-development', 'Full Stack Web Development', 'Web Development', 'Beginner to Advanced', '6 Months', 8, 'Ananya Rao', 'Ex-Senior Engineer, Zoho', 4.8, 412, 34999, 59999, '/images/courses/web-dev.jpg', '["HTML","CSS","JavaScript","React","Node.js","MongoDB"]', 'Go from your very first line of code to shipping full production apps. We start slow, build your fundamentals properly, then move into React and Node so you are job-ready, not just tutorial-ready.', 1, 1, 1),
('data-science-machine-learning', 'Data Science & Machine Learning', 'Data Science', 'Beginner to Advanced', '7 Months', 10, 'Karthik Subramaniam', 'Data Scientist, Ex-Accenture', 4.9, 358, 39999, 64999, '/images/courses/data-science.jpg', '["Python","Pandas","NumPy","Scikit-learn","TensorFlow","SQL"]', 'Learn to think like a data scientist, not just run notebooks. Real datasets, real business questions, and a portfolio of projects you can actually explain in an interview.', 1, 1, 1),
('ai-generative-ai-engineering', 'AI & Generative AI Engineering', 'AI', 'Intermediate to Advanced', '6 Months', 7, 'Priya Menon', 'AI Engineer, Ex-IBM', 4.9, 201, 44999, 69999, '/images/courses/ai.jpg', '["Python","PyTorch","LLMs","LangChain","Vector DBs","APIs"]', 'We will not just teach you to call an API. You will understand how models actually work, then build real AI products.', 1, 1, 1),
('cloud-devops-engineering', 'Cloud & DevOps Engineering', 'DevOps', 'Intermediate', '5 Months', 6, 'Rahul Verma', 'Cloud Architect, Ex-Wipro', 4.7, 176, 32999, 54999, '/images/courses/devops.jpg', '["AWS","Docker","Kubernetes","Jenkins","Terraform","Linux"]', 'Learn the tools real infra teams use daily. You will deploy, break, and fix your own pipelines.', 1, 1, 1),
('ui-ux-product-design', 'UI/UX & Product Design', 'UI UX', 'Beginner to Advanced', '4 Months', 6, 'Sneha Iyer', 'Product Designer, Ex-Freshworks', 4.8, 264, 27999, 44999, '/images/courses/ui-ux.jpg', '["Figma","Wireframing","Design Systems","Prototyping","User Research"]', 'Design is a way of thinking before it is a tool. You will learn to solve real user problems.', 1, 1, 1),
('cyber-security-ethical-hacking', 'Cyber Security & Ethical Hacking', 'Cyber Security', 'Intermediate to Advanced', '6 Months', 7, 'Arvind Nair', 'Security Consultant, Ex-HCL', 4.7, 152, 36999, 59999, '/images/courses/cyber-security.jpg', '["Kali Linux","Network Security","Burp Suite","OWASP","Cryptography"]', 'Learn to think like an attacker so you can defend like a professional.', 1, 1, 1),
('mobile-app-development', 'Mobile App Development (React Native)', 'App Development', 'Beginner to Advanced', '5 Months', 6, 'Deepak Chandran', 'Mobile Lead, Ex-Cognizant', 4.6, 138, 29999, 49999, '/images/courses/app-dev.jpg', '["React Native","JavaScript","Firebase","REST APIs","App Store Deployment"]', 'Build one codebase, ship on both iOS and Android.', 1, 1, 1),
('digital-marketing-mastery', 'Digital Marketing Mastery', 'Digital Marketing', 'Beginner to Advanced', '4 Months', 5, 'Meera Krishnan', 'Growth Marketer, Ex-Capgemini', 4.6, 189, 22999, 39999, '/images/courses/digital-marketing.jpg', '["SEO","Google Ads","Meta Ads","Analytics","Content Strategy"]', 'Run real campaigns with real budgets in a guided environment.', 1, 1, 1),
('software-testing-automation', 'Software Testing & QA Automation', 'Testing', 'Beginner to Intermediate', '4 Months', 5, 'Vignesh Raja', 'QA Lead, Ex-TCS', 4.6, 121, 21999, 36999, '/images/courses/testing.jpg', '["Selenium","Java","TestNG","Postman","JIRA","CI/CD"]', 'Manual and automation testing, taught the way QA teams actually work.', 1, 1, 1),
('business-analytics-power-bi', 'Business Analytics with Power BI', 'Business Analytics', 'Beginner to Intermediate', '3 Months', 4, 'Divya Prakash', 'Business Analyst, Ex-Oracle', 4.7, 97, 18999, 29999, '/images/courses/business-analytics.jpg', '["Excel","Power BI","SQL","Dashboards","Storytelling with Data"]', 'Turn spreadsheets into decisions.', 1, 0, 1)
ON DUPLICATE KEY UPDATE slug = slug;

-- Seed testimonials (migrated from src/data/testimonials.json)
INSERT INTO testimonials (name, role, course, rating, image, quote) VALUES
('Sanjana R.', 'Software Engineer at Infosys', 'Full Stack Web Development', 5, '/images/students/sanjana.jpg', 'I joined with zero coding background. My mentor never made me feel behind - we just kept building until things clicked. Six months later I had an offer letter in hand.'),
('Mohammed Faizal', 'Data Analyst at Wipro', 'Data Science & Machine Learning', 5, '/images/students/faizal.jpg', 'What stood out was how patient the mentors were with doubts. No question felt small. The projects on my resume are the exact reason I got shortlisted.'),
('Keerthana V.', 'UI/UX Designer at Freshworks', 'UI/UX & Product Design', 5, '/images/students/keerthana.jpg', 'The portfolio reviews genuinely changed how I think about design. Interviewers kept asking about my case studies.'),
('Arjun Balaji', 'Cloud Engineer at TCS', 'Cloud & DevOps Engineering', 4, '/images/students/arjun.jpg', 'Hands-on labs from day one. I broke things, fixed them, and actually understood why.'),
('Nithya Suresh', 'AI Engineer at IBM', 'AI & Generative AI Engineering', 5, '/images/students/nithya.jpg', 'I was intimidated by AI before this. The mentors broke it down step by step until I was building my own RAG chatbot.'),
('Vikram Anand', 'Mobile Developer at Cognizant', 'Mobile App Development', 5, '/images/students/vikram.jpg', 'Shipping my first real app to the Play Store felt unreal. The mentors treated us like future colleagues, not just students.')
ON DUPLICATE KEY UPDATE name = name;

-- Seed mentors (migrated from src/data/mentors.json)
INSERT INTO mentors (name, role, company, image, linkedin_url, sort_order) VALUES
('Ananya Rao', 'Full Stack Mentor', 'Ex-Zoho', '/images/mentors/ananya.jpg', NULL, 1),
('Karthik Subramaniam', 'Data Science Mentor', 'Ex-Accenture', '/images/mentors/karthik.jpg', NULL, 2),
('Priya Menon', 'AI Mentor', 'Ex-IBM', '/images/mentors/priya.jpg', NULL, 3),
('Rahul Verma', 'Cloud & DevOps Mentor', 'Ex-Wipro', '/images/mentors/rahul.jpg', NULL, 4),
('Sneha Iyer', 'Product Design Mentor', 'Ex-Freshworks', '/images/mentors/sneha.jpg', NULL, 5),
('Arvind Nair', 'Security Mentor', 'Ex-HCL', '/images/mentors/arvind.jpg', NULL, 6)
ON DUPLICATE KEY UPDATE name = name;

-- Seed a couple of blog posts so the Blog page isn't empty
INSERT INTO blog_posts (slug, title, excerpt, content, category, author) VALUES
('how-to-choose-the-right-tech-course', 'How To Choose The Right Tech Course For You', 'Confused between web development, data science, and AI? Here is a simple framework to decide.', '<p>Choosing a tech course is less about what is trending and more about matching a track to how you think and what you enjoy building. Start by asking what kind of problems excite you...</p>', 'Career Guidance', 'Edge Tech Solution'),
('placement-preparation-checklist', 'The Placement Preparation Checklist Every Student Needs', 'From resume to final interview - a practical checklist our placement team uses with every batch.', '<p>Most students under-prepare for placements not because they lack skill, but because they lack a checklist. Here is exactly what we walk our students through...</p>', 'Placements', 'Edge Tech Solution')
ON DUPLICATE KEY UPDATE slug = slug;

-- Enrolled students for the Student Portal (manually created in the CRM).
-- student_id + register_number are both unique and are the login credentials.
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
