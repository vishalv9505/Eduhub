-- Create video_lectures table
CREATE TABLE video_lectures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    study_material_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (study_material_id) REFERENCES study_materials(id) ON DELETE CASCADE
);

-- Add sample data
INSERT INTO video_lectures (study_material_id, title, video_url, description) VALUES
(1, 'Introduction to Programming Concepts', 'https://www.youtube.com/watch?v=XGSX7ErtiFY', 'Basic programming concepts explained'),
(1, 'Variables and Data Types', 'https://www.youtube.com/watch?v=example2', 'Understanding variables and data types'),
(2, 'Control Structures Overview', 'https://www.youtube.com/watch?v=example3', 'Introduction to control structures'); 