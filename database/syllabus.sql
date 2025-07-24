-- Create syllabus table
CREATE TABLE IF NOT EXISTS syllabus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some sample data
INSERT INTO syllabus (branch, semester, subject_name, subject_code, academic_year, file_path) VALUES
('Computer Science', 1, 'Programming Fundamentals', 'CS101', '2023-24', 'uploads/syllabus/CS101_2023-24.pdf'),
('Computer Science', 1, 'Mathematics', 'MATH101', '2023-24', 'uploads/syllabus/MATH101_2023-24.pdf'),
('Information Technology', 1, 'Introduction to IT', 'IT101', '2023-24', 'uploads/syllabus/IT101_2023-24.pdf'),
('Computer Science', 2, 'Data Structures', 'CS201', '2023-24', 'uploads/syllabus/CS201_2023-24.pdf'); 