# SRI AL-AMIN ActivHub 🎓

**Pusat Rekod Kokurikulum Pelajar SRI AL-AMIN WILAYAH PERSEKUTUAN**

A comprehensive web-based student co-curricular activity management system designed for SRI AL-AMIN Wilayah Persekutuan school. This system provides a centralized platform for managing student activities, events, approvals, and notifications across different user roles.

## 🌟 Features

### 👤 Multi-Role System
- **Students**: Submit activity forms, view history, manage profiles
- **Teachers**: Approve/reject activities, monitor class activities, receive notifications
- **Administrators**: Full system management, user oversight, event coordination

### 📝 Activity Management
- **Digital Forms**: Paperless co-curricular activity submission
- **Approval Workflow**: Multi-level approval system with notifications
- **Activity History**: Complete audit trail with status tracking
- **Rejection Feedback**: Detailed remarks for rejected submissions

### 🔔 Real-time Notification System
- **Role-based Notifications**: Targeted notifications for each user type
- **Activity Alerts**: Instant updates on form submissions and approvals
- **Event Reminders**: Upcoming event notifications and deadlines
- **System Monitoring**: Admin notifications for system health and pending tasks

### 📊 Event Management
- **Event Creation**: Comprehensive event planning tools
- **Registration Management**: Student registration tracking
- **Attendance Monitoring**: Digital attendance management
- **Event Calendar**: Visual event scheduling and tracking

### 🏫 Administrative Tools
- **User Management**: Add/edit students, teachers, and admins
- **Class Management**: Class creation and teacher assignment
- **Bulk Import**: Excel/CSV import for efficient data entry
- **System Reports**: Activity statistics and user monitoring

### 🎯 Co-curricular Clubs
- **Club Management**: Create and manage co-curricular groups
- **Activity Coordination**: Link activities to specific clubs
- **Member Tracking**: Monitor student participation across clubs

## 🛠️ Technology Stack

- **Backend**: PHP 8.x
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Libraries**: 
  - PHPSpreadsheet (Excel import/export)
  - Material Symbols (Icons)
- **Architecture**: MVC-inspired modular structure

## 📋 Requirements

- **Web Server**: Apache/Nginx
- **PHP**: 8.0 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Extensions**: 
  - mysqli
  - json
  - session
  - file upload support
- **Composer**: For dependency management

## ⚡ Quick Start

### 1. Clone Repository
```bash
git clone https://github.com/your-username/activhub.git
cd activhub
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
1. Create a MySQL database named `activhub`
2. Import the database schema from `/db/activhub_latest.sql`
3. Update database credentials in `/config/connect.php`

```php
$conn = mysqli_connect("localhost", "your_username", "your_password");
mysqli_select_db($conn, "activhub");
```

### 4. Configuration
1. Ensure proper file permissions for uploads directory
2. Configure session settings in `/includes/session_check.php`
3. Set up notification preferences in `/includes/NotificationService.php`

### 5. Access the System
- Navigate to your web server's document root
- Access via: `http://localhost/activhub/`
- Use default admin credentials (change immediately after setup)

## 📁 Project Structure

```
activhub/
├── admin/              # Admin panel and management
│   ├── function/       # Admin backend functions
│   └── *.php          # Admin pages
├── assets/            # Static assets
│   ├── css/           # Stylesheets
│   ├── img/           # Images and logos
│   └── uploads/       # File uploads
├── auth/              # Authentication system
├── cocurricular/      # Co-curricular management
├── config/            # Configuration files
├── db/                # Database schemas
├── events/            # Event management
├── forms/             # Activity forms and approvals
├── function/          # Shared functions
├── includes/          # Common includes
│   ├── NotificationService.php
│   ├── notifications_panel.php
│   └── session_check.php
├── student/           # Student portal
├── teacher/           # Teacher portal
└── vendor/            # Composer dependencies
```

## 🔧 Key Components

### Notification System
- **Real-time Updates**: Instant notifications across all user roles
- **Smart Routing**: Role-based notification targeting
- **Auto-marking**: Automatic read status when accessing target pages
- **Unified Panel**: Consistent notification UI across all interfaces

### Security Features
- **Session Management**: Secure session handling with timeout
- **Input Validation**: SQL injection and XSS protection
- **Role-based Access**: Strict permission controls
- **Password Hashing**: Secure password storage

### Data Management
- **Excel Import**: Bulk student data import via Excel files
- **Template Generation**: Downloadable import templates
- **Data Validation**: Comprehensive input validation
- **Audit Trails**: Complete activity history tracking

## 🎨 User Interface

- **Responsive Design**: Mobile-friendly interface
- **Bilingual Support**: Malay and Arabic text support
- **Material Design**: Modern UI with Material Symbols
- **Consistent Theming**: Unified color scheme and typography
- **Accessibility**: User-friendly navigation and clear visual hierarchy

## 🚀 Key Features in Detail

### Student Portal
- Submit co-curricular activity forms
- View submission history and status
- Receive notifications for approvals/rejections
- Manage personal profile and club memberships
- Register for school events

### Teacher Dashboard
- Review and approve/reject student activities
- Monitor class-wide activity participation
- Receive notifications for new submissions
- Access comprehensive activity history
- Manage class events and attendance

### Admin Panel
- Complete user management (students, teachers, admins)
- System-wide activity monitoring
- Event creation and management
- Bulk data import/export capabilities
- System health monitoring and notifications

## 🔐 Security Considerations

- Regular session regeneration to prevent fixation
- Prepared statements for all database queries
- Input sanitization and validation
- Role-based access control throughout
- Secure file upload handling

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- SRI AL-AMIN Wilayah Persekutuan for project inspiration
- UTM Application Development course guidance
- Material Design Icons for UI elements
- PHPOffice community for Excel functionality

## 📞 Support

For support, email your-email@example.com or create an issue in this repository.

---

**Note**: This system is designed specifically for SRI AL-AMIN Wilayah Persekutuan school's co-curricular activity management. Customize as needed for other institutions.
