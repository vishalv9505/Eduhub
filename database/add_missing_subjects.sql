-- Add missing core first-year subjects if they don't exist
INSERT INTO subjects (branch, semester, subject_name, subject_code)
SELECT * FROM (
    VALUES 
    ('Computer Science', 1, 'Chemistry', 'CS004'),
    ('Computer Science', 1, 'Physics', 'CS005'),
    ('Computer Science', 1, 'Mathematics I', 'CS006'),
    ('Computer Science', 2, 'Mathematics II', 'CS007')
) AS new_subjects(branch, semester, subject_name, subject_code)
WHERE NOT EXISTS (
    SELECT 1 FROM subjects 
    WHERE subject_code = new_subjects.subject_code
); 