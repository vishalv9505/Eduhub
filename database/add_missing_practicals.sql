-- First, let's add practicals for Chemistry (CS004)
INSERT INTO practicals (subject_id, practical_number, title, file_path) VALUES
((SELECT id FROM subjects WHERE subject_code = 'CS004'), 1, 'Introduction to Laboratory Safety', '/uploads/practicals/cs004_p1.pdf'),
((SELECT id FROM subjects WHERE subject_code = 'CS004'), 2, 'Chemical Reactions and Equations', '/uploads/practicals/cs004_p2.pdf'),
((SELECT id FROM subjects WHERE subject_code = 'CS004'), 3, 'Acid Base Titration', '/uploads/practicals/cs004_p3.pdf'),
((SELECT id FROM subjects WHERE subject_code = 'CS004'), 4, 'Salt Analysis', '/uploads/practicals/cs004_p4.pdf'),
((SELECT id FROM subjects WHERE subject_code = 'CS004'), 5, 'Organic Compound Tests', '/uploads/practicals/cs004_p5.pdf');

-- Let's also add practicals for any other first semester subjects that might be missing them
INSERT INTO practicals (subject_id, practical_number, title, file_path)
SELECT s.id, p.pnum, p.title, p.file_path
FROM subjects s
CROSS JOIN (
    VALUES 
    (1, 'Basic Laboratory Techniques', 'p1.pdf'),
    (2, 'Experimental Methods', 'p2.pdf'),
    (3, 'Advanced Concepts', 'p3.pdf')
) AS p(pnum, title, file_path)
WHERE s.semester = 1 
AND s.branch = 'Computer Science'
AND NOT EXISTS (
    SELECT 1 
    FROM practicals 
    WHERE subject_id = s.id
)
AND s.subject_code != 'CS004';  -- Skip Chemistry as we already added its practicals 