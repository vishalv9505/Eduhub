# EduHub - Educational Resource Platform

EduHub is a modern educational platform designed to provide students with easy access to study materials, practical guides, previous year papers, and syllabus for various subjects.

## Features

- Comprehensive subject listings
- Study materials and resources
- Practical guides and examples
- Previous year question papers
- Subject syllabus
- Modern and responsive UI
- Search functionality
- Mobile-friendly design

## Tech Stack

- PHP 8.x
- MySQL
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- Font Awesome

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Hostinger PHP hosting plan

## Installation

1. Clone the repository to your local machine:
```bash
git clone https://github.com/yourusername/eduhub.git
```

2. Create a MySQL database and import the database structure:
```bash
mysql -u your_username -p your_database_name < database/eduhub.sql
```

3. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'eduhub_db');
     ```

4. Upload the files to your Hostinger hosting:
   - Use FTP or Hostinger's file manager
   - Upload all files to the public_html directory

5. Set up file permissions:
```bash
chmod 755 -R /path/to/eduhub
chmod 777 -R /path/to/eduhub/uploads
```

## Directory Structure

```
eduhub/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   └── database.php
├── database/
│   └── eduhub.sql
├── uploads/
│   ├── materials/
│   ├── practicals/
│   ├── papers/
│   └── syllabus/
├── index.php
├── subjects.php
├── practicals.php
├── study-materials.php
├── previous-papers.php
└── syllabus.php
```

## Google AdSense Integration

1. Sign up for Google AdSense at https://www.google.com/adsense
2. Get your AdSense code
3. Add the code to your pages where you want to display ads
4. Follow Google AdSense policies and guidelines

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email info@eduhub.com or create an issue in the repository. 
