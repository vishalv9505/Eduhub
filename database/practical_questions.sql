-- Drop existing tables if they exist
DROP TABLE IF EXISTS practical_questions;
DROP TABLE IF EXISTS practicals;

-- Create practicals table
CREATE TABLE IF NOT EXISTS practicals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    practical_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create practical_questions table
CREATE TABLE IF NOT EXISTS practical_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    practical_id INT NOT NULL,
    question_number INT NOT NULL,
    question_text TEXT NOT NULL,
    description TEXT,
    code_solution TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (practical_id) REFERENCES practicals(id) ON DELETE CASCADE
);

-- Insert sample practicals for Chemistry
INSERT INTO practicals (subject_id, practical_number, title) VALUES
((SELECT id FROM subjects WHERE subject_code = 'CS004'), 1, 'Introduction to Laboratory Safety and Basic Techniques'),
((SELECT id FROM subjects WHERE subject_code = 'CS004'), 2, 'Chemical Reactions and Equations'),
((SELECT id FROM subjects WHERE subject_code = 'CS004'), 3, 'Acid Base Titration');

-- Insert sample questions for Chemistry Practical 1
INSERT INTO practical_questions (practical_id, question_number, question_text, description, code_solution) VALUES
((SELECT id FROM practicals WHERE subject_id = (SELECT id FROM subjects WHERE subject_code = 'CS004') AND practical_number = 1), 
1, 
'List and explain the basic safety rules to be followed in a chemistry laboratory.',
'Safety is paramount in a chemistry laboratory. This question covers the fundamental safety protocols that every student must know before starting any practical work.',
'1. Always wear appropriate safety gear (lab coat, safety goggles, gloves)
2. Know the location of safety equipment (fire extinguisher, eye wash, shower)
3. Never work alone in the laboratory
4. No eating, drinking, or smoking in the lab
5. Handle chemicals with care and read labels twice
6. Dispose of chemicals properly in designated containers
7. Report any accidents or spills immediately
8. Keep your workspace clean and organized'),

((SELECT id FROM practicals WHERE subject_id = (SELECT id FROM subjects WHERE subject_code = 'CS004') AND practical_number = 1),
2,
'Describe the proper handling and disposal procedures for different types of chemical waste.',
'Understanding proper waste disposal is crucial for environmental safety and regulatory compliance.',
'Chemical Waste Categories and Disposal:
1. Acid/Base Solutions: Neutralize before disposal
2. Organic Solvents: Collect in designated waste containers
3. Heavy Metals: Special disposal through authorized handlers
4. Biological Waste: Autoclave before disposal
5. Glass Waste: Separate container for broken glassware
6. Paper/Plastic: Regular waste if not contaminated');

-- Insert sample questions for Chemistry Practical 2
INSERT INTO practical_questions (practical_id, question_number, question_text, description, code_solution) VALUES
((SELECT id FROM practicals WHERE subject_id = (SELECT id FROM subjects WHERE subject_code = 'CS004') AND practical_number = 2),
1,
'Balance the following chemical equation: Fe + O2 → Fe2O3',
'Understanding how to balance chemical equations is fundamental to chemistry. This exercise demonstrates the law of conservation of mass.',
'Step-by-step balancing:
1. Initial equation: Fe + O2 → Fe2O3
2. Count atoms on each side:
   Left: Fe(1), O(2)
   Right: Fe(2), O(3)
3. Balance Fe first: 2Fe + O2 → Fe2O3
4. Balance O: 2Fe + 3/2O2 → Fe2O3
Final balanced equation: 4Fe + 3O2 → 2Fe2O3'),

((SELECT id FROM practicals WHERE subject_id = (SELECT id FROM subjects WHERE subject_code = 'CS004') AND practical_number = 2),
2,
'Identify the type of chemical reaction: CaCO3 → CaO + CO2',
'This question helps understand different types of chemical reactions and their characteristics.',
'Analysis:
1. Type of Reaction: Decomposition
2. Characteristics:
   - Single reactant breaks down
   - Heat is usually required
   - Produces multiple products
   - Common in carbonates
3. Practical Applications:
   - Limestone decomposition
   - Industrial lime production');

-- Insert sample questions for Chemistry Practical 3
INSERT INTO practical_questions (practical_id, question_number, question_text, description, code_solution) VALUES
((SELECT id FROM practicals WHERE subject_id = (SELECT id FROM subjects WHERE subject_code = 'CS004') AND practical_number = 3),
1,
'Calculate the molarity of NaOH solution used in titration against 0.1M HCl.',
'This practical demonstrates the calculation of concentration using titration data.',
'Calculation Steps:
1. Given: 
   - HCl concentration = 0.1M
   - HCl volume = 20mL
   - NaOH volume = 25mL
2. Using M1V1 = M2V2:
   (0.1M)(20mL) = (x)(25mL)
3. Solve for x:
   x = (0.1 × 20)/25 = 0.08M NaOH'),

((SELECT id FROM practicals WHERE subject_id = (SELECT id FROM subjects WHERE subject_code = 'CS004') AND practical_number = 3),
2,
'Explain the role of indicators in acid-base titration and choose the appropriate indicator for given acid-base pairs.',
'Understanding indicators is crucial for accurate titration endpoints.',
'Indicator Selection Guide:
1. Phenolphthalein:
   - pH range: 8.2-10.0
   - Color change: Colorless to pink
   - Best for: Strong acid-strong base
2. Methyl Orange:
   - pH range: 3.1-4.4
   - Color change: Red to yellow
   - Best for: Strong acid-weak base
3. Methyl Red:
   - pH range: 4.4-6.2
   - Color change: Red to yellow
   - Best for: Medium range titrations'); 