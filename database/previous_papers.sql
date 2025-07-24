CREATE TABLE IF NOT EXISTS previous_papers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    exam_year INT NOT NULL,
    exam_session ENUM('Summer', 'Winter') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO previous_papers (branch, semester, subject_name, subject_code, exam_year, exam_session, file_path) VALUES
('Computer Science', 1, 'Programming Fundamentals', 'CS101', 2023, 'Summer', 'uploads/papers/CS101_2023_Summer.pdf'),
('Computer Science', 1, 'Mathematics', 'MATH101', 2023, 'Summer', 'uploads/papers/MATH101_2023_Summer.pdf'),
('Information Technology', 1, 'Introduction to IT', 'IT101', 2023, 'Summer', 'uploads/papers/IT101_2023_Summer.pdf'),
('Computer Science', 2, 'Data Structures', 'CS201', 2023, 'Winter', 'uploads/papers/CS201_2023_Winter.pdf'); 