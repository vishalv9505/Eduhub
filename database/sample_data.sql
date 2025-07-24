-- Insert dummy subjects for Computer Science
INSERT INTO subjects (branch, semester, subject_name, subject_code) VALUES
('Computer Science', 1, 'Programming Fundamentals', 'CS101'),
('Computer Science', 1, 'Digital Logic Design', 'CS102'),
('Computer Science', 1, 'Introduction to Computing', 'CS103'),
('Computer Science', 2, 'Object Oriented Programming', 'CS201'),
('Computer Science', 2, 'Data Structures', 'CS202'),
('Computer Science', 2, 'Computer Organization', 'CS203'),
('Computer Science', 3, 'Database Systems', 'CS301'),
('Computer Science', 3, 'Operating Systems', 'CS302'),
('Computer Science', 3, 'Software Engineering', 'CS303');

-- Insert dummy subjects for Information Technology
INSERT INTO subjects (branch, semester, subject_name, subject_code) VALUES
('Information Technology', 1, 'IT Fundamentals', 'IT101'),
('Information Technology', 1, 'Web Technologies', 'IT102'),
('Information Technology', 1, 'Computer Networks', 'IT103'),
('Information Technology', 2, 'Database Management', 'IT201'),
('Information Technology', 2, 'System Analysis', 'IT202'),
('Information Technology', 2, 'Cloud Computing', 'IT203'),
('Information Technology', 3, 'Cybersecurity', 'IT301'),
('Information Technology', 3, 'Mobile Computing', 'IT302'),
('Information Technology', 3, 'Data Mining', 'IT303');

-- Insert dummy subjects for Electronics
INSERT INTO subjects (branch, semester, subject_name, subject_code) VALUES
('Electronics', 1, 'Basic Electronics', 'EC101'),
('Electronics', 1, 'Circuit Theory', 'EC102'),
('Electronics', 1, 'Digital Electronics', 'EC103'),
('Electronics', 2, 'Analog Electronics', 'EC201'),
('Electronics', 2, 'Microprocessors', 'EC202'),
('Electronics', 2, 'Communication Systems', 'EC203'),
('Electronics', 3, 'VLSI Design', 'EC301'),
('Electronics', 3, 'Control Systems', 'EC302'),
('Electronics', 3, 'Signal Processing', 'EC303');

-- Insert dummy subjects for Mechanical
INSERT INTO subjects (branch, semester, subject_name, subject_code) VALUES
('Mechanical', 1, 'Engineering Mechanics', 'ME101'),
('Mechanical', 1, 'Thermodynamics', 'ME102'),
('Mechanical', 1, 'Manufacturing Processes', 'ME103'),
('Mechanical', 2, 'Fluid Mechanics', 'ME201'),
('Mechanical', 2, 'Machine Design', 'ME202'),
('Mechanical', 2, 'Heat Transfer', 'ME203'),
('Mechanical', 3, 'Robotics', 'ME301'),
('Mechanical', 3, 'CAD/CAM', 'ME302'),
('Mechanical', 3, 'Industrial Engineering', 'ME303');

-- Insert dummy practicals for Computer Science subjects
INSERT INTO practicals (subject_id, practical_number, title, file_path) VALUES
(1, 1, 'Introduction to C Programming', '/uploads/practicals/cs101_p1.pdf'),
(1, 2, 'Control Structures in C', '/uploads/practicals/cs101_p2.pdf'),
(1, 3, 'Arrays and Functions', '/uploads/practicals/cs101_p3.pdf'),
(2, 1, 'Boolean Algebra', '/uploads/practicals/cs102_p1.pdf'),
(2, 2, 'Logic Gates', '/uploads/practicals/cs102_p2.pdf'),
(2, 3, 'Combinational Circuits', '/uploads/practicals/cs102_p3.pdf'),
(4, 1, 'Classes and Objects in Java', '/uploads/practicals/cs201_p1.pdf'),
(4, 2, 'Inheritance and Polymorphism', '/uploads/practicals/cs201_p2.pdf'),
(4, 3, 'Exception Handling', '/uploads/practicals/cs201_p3.pdf');

-- Insert dummy practicals for IT subjects
INSERT INTO practicals (subject_id, practical_number, title, file_path) VALUES
(10, 1, 'HTML Basics', '/uploads/practicals/it101_p1.pdf'),
(10, 2, 'CSS Styling', '/uploads/practicals/it101_p2.pdf'),
(10, 3, 'JavaScript Fundamentals', '/uploads/practicals/it101_p3.pdf'),
(11, 1, 'PHP Programming', '/uploads/practicals/it102_p1.pdf'),
(11, 2, 'MySQL Database', '/uploads/practicals/it102_p2.pdf'),
(11, 3, 'AJAX and jQuery', '/uploads/practicals/it102_p3.pdf'),
(13, 1, 'SQL Queries', '/uploads/practicals/it201_p1.pdf'),
(13, 2, 'Database Design', '/uploads/practicals/it201_p2.pdf'),
(13, 3, 'Stored Procedures', '/uploads/practicals/it201_p3.pdf');

-- Insert dummy practicals for Electronics subjects
INSERT INTO practicals (subject_id, practical_number, title, file_path) VALUES
(19, 1, 'Diode Characteristics', '/uploads/practicals/ec101_p1.pdf'),
(19, 2, 'Transistor Basics', '/uploads/practicals/ec101_p2.pdf'),
(19, 3, 'Op-Amp Circuits', '/uploads/practicals/ec101_p3.pdf'),
(20, 1, 'KVL and KCL', '/uploads/practicals/ec102_p1.pdf'),
(20, 2, 'Network Theorems', '/uploads/practicals/ec102_p2.pdf'),
(20, 3, 'RC Circuits', '/uploads/practicals/ec102_p3.pdf'),
(22, 1, 'Amplifier Design', '/uploads/practicals/ec201_p1.pdf'),
(22, 2, 'Filter Circuits', '/uploads/practicals/ec201_p2.pdf'),
(22, 3, 'Oscillators', '/uploads/practicals/ec201_p3.pdf');

-- Insert dummy practicals for Mechanical subjects
INSERT INTO practicals (subject_id, practical_number, title, file_path) VALUES
(28, 1, 'Force Analysis', '/uploads/practicals/me101_p1.pdf'),
(28, 2, 'Friction Studies', '/uploads/practicals/me101_p2.pdf'),
(28, 3, 'Centroid and MOI', '/uploads/practicals/me101_p3.pdf'),
(29, 1, 'Gas Laws', '/uploads/practicals/me102_p1.pdf'),
(29, 2, 'Steam Properties', '/uploads/practicals/me102_p2.pdf'),
(29, 3, 'Heat Engines', '/uploads/practicals/me102_p3.pdf'),
(31, 1, 'Fluid Properties', '/uploads/practicals/me201_p1.pdf'),
(31, 2, 'Bernoulli\'s Experiment', '/uploads/practicals/me201_p2.pdf'),
(31, 3, 'Flow Measurement', '/uploads/practicals/me201_p3.pdf'); 