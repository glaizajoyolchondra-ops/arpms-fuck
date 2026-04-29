CREATE DATABASE IF NOT EXISTS arpms_db;
USE arpms_db;

CREATE TABLE IF NOT EXISTS departments (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) NOT NULL
);

INSERT IGNORE INTO departments (department_id, department_name) VALUES 
(1, 'College of Science'),
(2, 'Engineering'),
(3, 'College of Arts'),
(4, 'College of Education');

CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','coordinator','researcher') NOT NULL,
    profile_photo VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive','pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
);

INSERT IGNORE INTO users (user_id, department_id, full_name, email, password, role, status) VALUES
(1, 1, 'Emily Davis', 'admin@arpms.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
(2, 1, 'Michael Chen', 'coordinator@arpms.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinator', 'active'),
(3, 1, 'Sarah Montemayor', 'sarah@arpms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'researcher', 'active'),
(4, 1, 'Michael Lee', 'researcher1@arpms.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'researcher', 'active'),
(5, 2, 'James Wilson', 'researcher2@arpms.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'researcher', 'active'),
(6, 1, 'Robert Kim', 'researcher3@arpms.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'researcher', 'active');

CREATE TABLE IF NOT EXISTS research_projects (
    project_id INT PRIMARY KEY AUTO_INCREMENT,
    proposal_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    department_id INT,
    budget DECIMAL(12,2),
    budget_personnel DECIMAL(12,2) DEFAULT 0,
    budget_equipment DECIMAL(12,2) DEFAULT 0,
    budget_materials DECIMAL(12,2) DEFAULT 0,
    budget_other DECIMAL(12,2) DEFAULT 0,
    start_date DATE,
    end_date DATE,
    progress INT DEFAULT 0,
    status ENUM('not_started','in_progress','at_risk','delayed','on_track','completed') DEFAULT 'in_progress',
    coordinator_id INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (coordinator_id) REFERENCES users(user_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

INSERT IGNORE INTO research_projects (project_id, title, description, department_id, budget, start_date, end_date, progress, status, coordinator_id, created_by) VALUES
(1, 'AI-Powered Healthcare Diagnostics', 'Developing machine learning models for early disease detection using medical imaging data.', 1, 56000, '2024-01-15', '2024-12-15', 65, 'on_track', 2, 3),
(2, 'Sustainable Energy Grid Optimization', 'Research on optimizing renewable energy distribution in smart grids using IoT sensors.', 2, 89000, '2024-03-01', '2024-11-30', 15, 'in_progress', 2, 4),
(3, 'Quantum Computing Applications', 'Exploring quantum algorithms for cryptographic security and optimization problems.', 1, 36000, '2023-09-01', '2024-08-31', 40, 'delayed', 2, 5);

CREATE TABLE IF NOT EXISTS project_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    sender_id INT,
    content TEXT,
    file_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES research_projects(project_id),
    FOREIGN KEY (sender_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS project_team (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    user_id INT,
    FOREIGN KEY (project_id) REFERENCES research_projects(project_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

INSERT IGNORE INTO project_team (project_id, user_id) VALUES
(1, 3), (1, 4),
(2, 1), (2, 5),
(3, 6);

CREATE TABLE IF NOT EXISTS weekly_checklists (
    checklist_id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    week_number INT,
    activity_name VARCHAR(255),
    is_completed TINYINT(1) DEFAULT 0,
    completed_date DATETIME NULL,
    FOREIGN KEY (project_id) REFERENCES research_projects(project_id)
);

INSERT IGNORE INTO weekly_checklists (project_id, week_number, activity_name, is_completed) VALUES
(1, 1, 'Literature Review & Research Planning', 1),
(1, 2, 'Data Collection & Initial Processing', 1),
(1, 3, 'Model Development & Training', 1),
(1, 4, 'Algorithm Optimization', 1),
(1, 5, 'Testing & Validation Phase 1', 1),
(1, 6, 'Testing & Validation Phase 2', 1),
(1, 7, 'Documentation & Report Writing', 0),
(1, 8, 'Peer Review & Revisions', 0),
(1, 9, 'Final Testing & Deployment', 0),
(1, 10, 'Project Completion & Presentation', 0);

CREATE TABLE IF NOT EXISTS comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    user_id INT,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES research_projects(project_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS documents (
    document_id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    file_size VARCHAR(50),
    uploaded_by INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES research_projects(project_id),
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS meetings (
    meeting_id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT,
    title VARCHAR(255),
    meeting_type VARCHAR(50),
    meeting_date DATE,
    meeting_time TIME,
    duration INT,
    agenda TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES research_projects(project_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS meeting_attendees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT,
    user_id INT,
    FOREIGN KEY (meeting_id) REFERENCES meetings(meeting_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
