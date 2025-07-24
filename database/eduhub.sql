-- Create database
CREATE DATABASE IF NOT EXISTS eduhub_db;
USE eduhub_db;

-- Create subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create study_materials table
CREATE TABLE IF NOT EXISTS study_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    file_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create practicals table
CREATE TABLE IF NOT EXISTS practicals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    steps TEXT,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create previous_papers table
CREATE TABLE IF NOT EXISTS previous_papers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT,
    year INT,
    semester INT,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create syllabus table
CREATE TABLE IF NOT EXISTS syllabus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Insert sample subjects
INSERT INTO subjects (subject_name, description, category) VALUES
('Computer Science', 'Introduction to computer science fundamentals and programming concepts', 'Programming'),
('Mathematics', 'Advanced mathematics including calculus, algebra, and statistics', 'Science'),
('Physics', 'Classical and modern physics concepts and applications', 'Science'),
('Chemistry', 'Organic and inorganic chemistry principles and laboratory work', 'Science'),
('English Literature', 'Study of classic and contemporary literature', 'Humanities');

-- Insert sample study materials
INSERT INTO study_materials (subject_id, title, description, file_type) VALUES
(1, 'Introduction to Programming', 'Basic programming concepts and examples', 'PDF'),
(1, 'Data Structures Guide', 'Comprehensive guide to data structures', 'PDF'),
(2, 'Calculus Notes', 'Detailed notes on calculus topics', 'PDF');

-- Insert sample practicals
INSERT INTO practicals (subject_id, title, description, steps) VALUES
(1, 'Basic Program Structure', 'Learn about program structure and syntax', '1. Setup development environment\n2. Write first program\n3. Compile and run'),
(3, 'Basic Physics Experiments', 'Introduction to physics laboratory work', '1. Safety guidelines\n2. Equipment setup\n3. Conduct experiment');

-- Insert sample previous papers
INSERT INTO previous_papers (subject_id, year, semester) VALUES
(1, 2023, 1),
(1, 2023, 2),
(2, 2023, 1);

-- Insert sample syllabus
INSERT INTO syllabus (subject_id, title, description) VALUES
(1, 'Computer Science Syllabus', 'Complete curriculum for computer science course'),
(2, 'Mathematics Syllabus', 'Comprehensive mathematics course outline'); 