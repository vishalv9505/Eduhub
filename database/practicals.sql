-- Drop existing tables if they exist
DROP TABLE IF EXISTS practical_questions;
DROP TABLE IF EXISTS practicals;

-- Create practicals table with new structure
CREATE TABLE IF NOT EXISTS `practicals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `practical_number` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `practicals_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data
INSERT INTO `practicals` (`subject_id`, `practical_number`, `title`, `file_path`) VALUES
(1, 1, 'Introduction to Programming Basics', '/uploads/practicals/cs101_practical1.pdf'),
(1, 2, 'Control Structures and Loops', '/uploads/practicals/cs101_practical2.pdf'),
(2, 1, 'Database Design Fundamentals', '/uploads/practicals/cs201_practical1.pdf'),
(2, 2, 'SQL Queries and Joins', '/uploads/practicals/cs201_practical2.pdf');

-- Create practical_questions table
CREATE TABLE IF NOT EXISTS practical_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    practical_id INT NOT NULL,
    question_number INT NOT NULL,
    question_text TEXT NOT NULL,
    description TEXT,
    code_solution TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (practical_id) REFERENCES practicals(id) ON DELETE CASCADE
);

-- Insert sample questions
INSERT INTO practical_questions (practical_id, question_number, question_text, description, code_solution) VALUES
(1, 1, 'Write a program to implement array insertion and deletion operations.', 
'This program demonstrates how to insert and delete elements in an array at any given position.', 
'#include <stdio.h>
void insert(int arr[], int *size, int pos, int value) {
    for(int i = *size; i > pos; i--) {
        arr[i] = arr[i-1];
    }
    arr[pos] = value;
    (*size)++;
}
// Rest of the code implementation...'),

(1, 2, 'Implement binary search on a sorted array.', 
'Binary search is an efficient algorithm that finds the position of a target value within a sorted array.', 
'int binarySearch(int arr[], int left, int right, int target) {
    while (left <= right) {
        int mid = left + (right - left) / 2;
        if (arr[mid] == target) return mid;
        if (arr[mid] < target) left = mid + 1;
        else right = mid - 1;
    }
    return -1;
}'); 