-- Create subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_subject (branch, semester, subject_code)
);

-- Insert sample subjects
INSERT INTO subjects (branch, semester, subject_name, subject_code) VALUES
('Computer Science', 1, 'Programming Fundamentals', 'CS101'),
('Computer Science', 1, 'Digital Logic', 'CS102'),
('Computer Science', 2, 'Data Structures', 'CS201'),
('Information Technology', 1, 'Introduction to IT', 'IT101');

-- Add new columns to existing subjects table
ALTER TABLE subjects
ADD COLUMN branch VARCHAR(50) AFTER id,
ADD COLUMN semester INT AFTER branch,
ADD COLUMN subject_code VARCHAR(20) AFTER subject_name,
ADD UNIQUE KEY unique_subject (branch, semester, subject_code);

-- Update existing records with default values
UPDATE subjects SET 
branch = 'Computer Science',
semester = 1,
subject_code = CONCAT('CS', LPAD(id, 3, '0'))
WHERE branch IS NULL;

-- Insert additional sample subjects
INSERT INTO subjects (branch, semester, subject_name, subject_code, description, category) VALUES
('Computer Science', 1, 'Programming Fundamentals', 'CS101', 'Introduction to programming concepts and basic algorithms', 'Programming'),
('Computer Science', 1, 'Digital Logic', 'CS102', 'Study of digital circuits and logic design', 'Hardware'),
('Computer Science', 2, 'Data Structures', 'CS201', 'Implementation and analysis of various data structures', 'Programming'),
('Information Technology', 1, 'Introduction to IT', 'IT101', 'Overview of information technology concepts and applications', 'General'); 