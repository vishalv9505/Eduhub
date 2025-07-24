-- Create material_files table
CREATE TABLE IF NOT EXISTS material_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES study_materials(id) ON DELETE CASCADE
);

-- Create material_videos table
CREATE TABLE IF NOT EXISTS material_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES study_materials(id) ON DELETE CASCADE
);

-- Modify study_materials table to remove file_type, file_path, and video_url columns
ALTER TABLE study_materials
DROP COLUMN IF EXISTS file_type,
DROP COLUMN IF EXISTS file_path,
DROP COLUMN IF EXISTS video_url; 