-- Create units table
CREATE TABLE IF NOT EXISTS units (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    unit_number INT NOT NULL,
    unit_title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create study_materials table
CREATE TABLE IF NOT EXISTS study_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    unit_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    type ENUM('pdf', 'ppt', 'video') NOT NULL,
    file_path VARCHAR(255),
    video_url VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);

-- Create video_lectures table
CREATE TABLE IF NOT EXISTS video_lectures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    study_material_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (study_material_id) REFERENCES study_materials(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO units (subject_id, unit_number, unit_title, description) VALUES
(1, 1, 'Introduction to Programming', 'Basic concepts of programming and algorithms'),
(1, 2, 'Control Structures', 'Decision making and loops in programming'),
(2, 1, 'Digital Logic Basics', 'Introduction to digital logic and boolean algebra'),
(2, 2, 'Combinational Circuits', 'Design and analysis of combinational circuits');

INSERT INTO study_materials (unit_id, title, type, file_path, video_url, description) VALUES
(1, 'Programming Basics PDF', 'pdf', 'uploads/materials/prog_basics.pdf', NULL, 'Comprehensive guide to programming basics'),
(1, 'Introduction to Programming PPT', 'ppt', 'uploads/materials/intro_prog.ppt', NULL, 'Presentation slides for programming introduction'),
(1, 'Programming Fundamentals Video', 'video', NULL, 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Video lecture on programming fundamentals'),
(2, 'Control Structures in Programming', 'pdf', 'uploads/materials/control_structures.pdf', NULL, 'Detailed guide on control structures');

INSERT INTO video_lectures (study_material_id, title, video_url, description) VALUES
(3, 'Introduction to Programming Concepts', 'https://www.youtube.com/watch?v=XGSX7ErtiFY', 'Basic programming concepts explained'),
(3, 'Variables and Data Types', 'https://www.youtube.com/watch?v=example2', 'Understanding variables and data types');

-- Update study_materials table structure if needed
ALTER TABLE study_materials
MODIFY COLUMN file_type ENUM('PDF', 'PPT', 'VIDEO') NOT NULL DEFAULT 'PDF',
ADD COLUMN video_url VARCHAR(255) AFTER file_path;

-- Add sample data
INSERT INTO study_materials (subject_id, title, description, file_type, created_at) VALUES
(1, 'Programming Fundamentals', 'Introduction to basic programming concepts', 'PDF', CURRENT_TIMESTAMP),
(1, 'Control Flow in Programming', 'Understanding loops and conditions', 'PDF', CURRENT_TIMESTAMP),
(2, 'Digital Logic Design', 'Basic concepts of digital logic', 'PDF', CURRENT_TIMESTAMP); 