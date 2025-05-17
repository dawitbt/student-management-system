# Student Management System (SMS)

![PHP](https://img.shields.io/badge/PHP-8.0+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0+-purple)

A modern web application for managing student records with secure authentication.

## Features

- 🛡️ Secure user authentication (login/register)
- 👨‍🎓 Student CRUD operations
- 🔍 Search and pagination
- 📱 Responsive design
- 🔄 AJAX-powered interactions
- 🔒 CSRF protection

## Screenshots

![Screenshot](https://github.com/dawitbt/student-management-system/raw/main/screenshots/sms.png)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/student-management-system.git
   ```

2. Import database:
   ```bash
   mysql -u username -p database_name < database.sql
   ```

3. Configure settings:
   ```bash
   cp config-sample.php config.php
   nano config.php
   ```

## Requirements

- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx
- Composer (for dependencies)

## File Structure

```
sms-project/
├── assets/          # CSS/JS/Images
├── includes/        # PHP classes and functions
├── config.php       # Configuration
├── database.sql     # Database schema
├── index.php        # Main dashboard
├── login.php        # Login page
├── register.php     # Registration page
└── README.md        # This file
```

## Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

Distributed under the MIT License. See `LICENSE` for more information.

## Contact

Your Name - [Dawit Betela] - dawitbatala@gmail.com

Project Link: [https://github.com/dawitbt/student-management-system](https://github.com/your-username/student-management-system)
