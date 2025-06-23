# Installation Guide

## System Requirements

### Minimum Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ or Nginx
- 512MB RAM
- 1GB disk space

### Recommended Requirements
- PHP 8.1+
- MySQL 8.0+ or MariaDB 10.6+
- 2GB RAM
- 5GB disk space
- SSL certificate for production

## Step-by-Step Installation

### 1. Environment Setup

#### Windows (XAMPP/WAMP)
1. Download and install XAMPP/WAMP
2. Start Apache and MySQL services
3. Open phpMyAdmin

#### Linux/Ubuntu
```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysqli php-json
sudo systemctl start apache2
sudo systemctl start mysql
```

### 2. Database Configuration

1. Create database:
```sql
CREATE DATABASE activhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Create database user:
```sql
CREATE USER 'activhub_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON activhub.* TO 'activhub_user'@'localhost';
FLUSH PRIVILEGES;
```

3. Import database schema:
```bash
mysql -u activhub_user -p activhub < db/activhub_latest.sql
```

### 3. Project Setup

1. Clone or download the project to your web root:
```bash
cd /var/www/html  # Linux
# or C:\xampp\htdocs  # Windows
git clone https://github.com/your-username/activhub.git
```

2. Install dependencies:
```bash
cd activhub
composer install
```

3. Set permissions (Linux):
```bash
sudo chown -R www-data:www-data /var/www/html/activhub
sudo chmod -R 755 /var/www/html/activhub
sudo chmod -R 777 assets/uploads
```

### 4. Configuration

1. Update database connection in `config/connect.php`:
```php
<?php
$conn = mysqli_connect("localhost", "activhub_user", "your_secure_password");
mysqli_select_db($conn, "activhub");
?>
```

2. Configure file upload settings in `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### 5. Default Accounts

After installation, use these default accounts (change passwords immediately):

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Test Teacher Account:**
- Username: `teacher001`
- Password: `teacher123`

**Test Student Account:**
- Username: `student001`
- Password: `student123`

### 6. Security Configuration

1. Change default passwords immediately
2. Update session configuration in `includes/session_check.php`
3. Set appropriate file permissions
4. Enable SSL for production

### 7. Testing the Installation

1. Navigate to `http://localhost/activhub/`
2. Login with admin credentials
3. Test basic functionality:
   - Create a new student account
   - Create a new teacher account
   - Submit a test activity form
   - Test the approval workflow

## Troubleshooting

### Common Issues

**Database Connection Error**
- Check database credentials in `config/connect.php`
- Ensure MySQL service is running
- Verify database exists and user has permissions

**File Upload Issues**
- Check `upload_max_filesize` in php.ini
- Verify `assets/uploads/` directory permissions
- Ensure web server has write access

**Session Issues**
- Check PHP session configuration
- Verify `session.save_path` is writable
- Clear browser cookies and try again

**Permission Denied**
- Set proper file permissions (755 for files, 777 for upload directories)
- Check web server user ownership

### Performance Optimization

1. Enable PHP OPcache
2. Configure MySQL query cache
3. Use gzip compression
4. Optimize images in `assets/img/`

## Production Deployment

### Additional Security Steps

1. Remove default accounts or change passwords
2. Disable PHP error display
3. Configure firewall rules
4. Set up SSL certificate
5. Enable audit logging
6. Regular database backups

### Environment Variables

Consider using environment variables for sensitive configuration:

```php
// In config/connect.php
$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'activhub_user';
$pass = $_ENV['DB_PASS'] ?? 'default_password';
$db = $_ENV['DB_NAME'] ?? 'activhub';
```

## Backup and Maintenance

### Database Backup
```bash
mysqldump -u activhub_user -p activhub > backup_$(date +%Y%m%d).sql
```

### File Backup
```bash
tar -czf activhub_backup_$(date +%Y%m%d).tar.gz /path/to/activhub/
```

### Regular Maintenance
- Weekly database backups
- Monthly security updates
- Quarterly performance reviews
- Annual password policy reviews
