# CED Portal - FluxBB Architecture Edition

A complete redesign of the CED Portal using FluxBB forum CMS architecture for a robust, scalable, and modern educational platform.

## 🎯 Architecture Overview

This version has been completely redesigned based on the [FluxBB by Visman fork](https://github.com/druvx13/FluxBB_by_Visman_fork) architecture, featuring:

- **Page Controller Pattern**: Each URL corresponds to a specific PHP script
- **Database Abstraction Layer**: Support for MySQL, PostgreSQL, and SQLite
- **Template System**: Lightweight substitution-based templating
- **Cookie-based Authentication**: Secure session management
- **PHP File Caching**: Fast configuration and data caching
- **Multi-theme Support**: Air, Oxygen, and Mercury themes included

## 📁 New Directory Structure

```
CED-Portal/
├── include/                    # Core libraries and functions
│   ├── common.php             # Bootstrap file (loaded by all pages)
│   ├── functions.php          # Helper functions
│   ├── cache.php              # Caching functions
│   ├── config.php             # Database and site configuration
│   ├── dblayer/               # Database abstraction
│   │   ├── common_db.php      # DB layer loader
│   │   └── mysqli.php         # MySQL implementation
│   └── template/              # Template files
│       └── main.tpl           # Main page template
├── style/                     # CSS themes
│   ├── Air.css                # Default Air theme (37KB)
│   ├── Oxygen.css             # Oxygen theme
│   └── Mercury.css            # Mercury theme
├── js/                        # JavaScript files
│   ├── jquery-1.12.4.min.js   # jQuery library
│   └── common.js              # Common JS functions
├── lang/                      # Language files
│   └── English/
│       └── common.php         # English language pack
├── cache/                     # Cache directory (must be writable)
│   ├── cache_config.php       # Configuration cache
│   └── cache_ranks.php        # Ranks cache
├── img/                       # Static images
│   └── avatars/               # User avatars
├── header.php                 # Page header template
├── footer.php                 # Page footer template
├── index.php                  # Home page
├── login.php                  # Login page
├── register.php               # Registration page
├── lab_programs.php           # Lab programs listing
├── lab_manuals.php            # Lab manuals listing
├── homework.php               # Homework listing
├── notes.php                  # Notes listing
├── reminders.php              # Reminders listing
├── admin_index.php            # Admin dashboard
├── admin_users.php            # User management
├── admin_subjects.php         # Subject management
├── install.php                # Installation script
└── schema_fluxbb.sql          # Database schema
```

## 🚀 Installation

### Prerequisites

- PHP 7.2 or higher
- MySQL 5.5+ / MariaDB / PostgreSQL / SQLite3
- Web server (Apache/Nginx)
- PHP extensions: `mysqli`, `mbstring`, `zlib` (optional)

### Quick Install

1. **Clone the repository:**
   ```bash
   git clone https://github.com/druvx13/CED-Portal.git
   cd CED-Portal
   ```

2. **Set permissions:**
   ```bash
   chmod 777 cache/
   chmod 777 include/
   chmod 777 img/avatars/
   ```

3. **Run the installer:**
   - Navigate to `http://your-domain.com/install.php`
   - Fill in database credentials and admin account details
   - Click "Install CED Portal"

4. **Post-installation:**
   - **Delete `install.php`** for security
   - Configure the portal via Admin Panel

### Manual Install

If you prefer manual installation:

1. Create a MySQL database
2. Import `schema_fluxbb.sql` into your database
3. Copy `include/config.php.example` to `include/config.php`
4. Edit `include/config.php` with your database credentials
5. Set the `CED_BASE_URL` constant to your site URL
6. Default admin credentials: `admin` / `admin123` (change immediately!)

## 🔧 Configuration

### File-based Configuration

Edit `include/config.php`:

```php
$db_type = 'mysqli';              // Database type
$db_host = 'localhost';           // Database host
$db_name = 'ced_portal';          // Database name
$db_username = 'root';            // Database user
$db_password = '';                // Database password
$db_prefix = 'ced_';              // Table prefix

$cookie_name = 'ced_cookie';      // Cookie name
$cookie_seed = 'RANDOM_STRING';   // Change this!

define('CED_BASE_URL', 'http://localhost/CED-Portal');
```

### Database Configuration

Configuration stored in `ced_config` table:

- `o_board_title`: Portal title
- `o_board_desc`: Portal description
- `o_default_lang`: Default language
- `o_default_style`: Default theme
- `o_timeout_visit`: Session timeout
- `o_gzip`: Enable gzip compression
- `o_maintenance`: Maintenance mode
- `o_maintenance_message`: Maintenance message

## 🎨 Themes

CED Portal includes three FluxBB themes:

- **Air** (Default): Clean, minimal design
- **Oxygen**: Blue-tinted professional theme
- **Mercury**: Classic forum theme

Users can select their preferred theme from their profile settings.

## 🏗 Architecture Details

### Request Lifecycle

1. **Request**: Browser requests `lab_programs.php`
2. **Bootstrap**: Script includes `include/common.php`
3. **Initialization**:
   - Load configuration
   - Connect to database
   - Load user session
   - Load language files
4. **Processing**: Execute page-specific logic
5. **Rendering**:
   - Include `header.php` (start output buffering)
   - Output page content
   - Include `footer.php` (inject into template, send response)

### Database Abstraction

The `DBLayer` class provides database independence:

```php
// Query example
$result = $db->query('SELECT * FROM '.$db->prefix.'users');
while ($row = $db->fetch_assoc($result))
    // Process row
```

### Authentication

Cookie-based authentication with HMAC validation:

1. User logs in with username/password
2. Password verified using `password_verify()`
3. Cookie set with encrypted user ID and password hash
4. On subsequent requests, cookie validated via HMAC
5. User data loaded from database

### Caching

Configuration and frequently accessed data cached to PHP files:

- `cache/cache_config.php`: Site configuration
- `cache/cache_ranks.php`: User rank definitions

Cache regenerates automatically when configuration changes.

## 🔐 Security Features

- **CSRF Protection**: Token-based protection for forms
- **Bad Character Stripping**: Remove malicious UTF-8 characters
- **SQL Injection Prevention**: Prepared statements and escaping
- **XSS Protection**: HTML special characters encoding
- **Secure Cookies**: HTTPOnly cookies with HMAC validation
- **Password Hashing**: bcrypt password hashing

## 📚 Features

### For Students

- **Lab Programs**: Store and manage programming assignments
- **Lab Manuals**: Upload and access PDF lab manuals
- **Homework**: Track assignments with due dates
- **Notes**: Personal note-taking system
- **Reminders**: Schedule reminders for important dates

### For Administrators

- **User Management**: Add, edit, remove users
- **Subject Management**: Manage course subjects
- **Language Management**: Manage programming languages
- **Statistics Dashboard**: View portal statistics
- **Configuration**: Configure portal settings

## 🔄 Migration from Old Version

If migrating from the previous MVC version:

1. Backup your current database
2. Export data from old tables
3. Install new version
4. Import data into new schema
5. Update file paths and references

## 🧪 Development

### Adding a New Page

1. Create `yourpage.php` in root directory:
   ```php
   <?php
   define('CED_ROOT', './');
   require CED_ROOT.'include/common.php';
   
   $page_title = 'Your Page';
   $page = 'yourpage';
   
   require CED_ROOT.'header.php';
   ?>
   
   <!-- Your content here -->
   
   <?php require CED_ROOT.'footer.php'; ?>
   ```

2. Add navigation link in `header.php`
3. Style using existing CSS classes

### Debug Mode

Enable query logging in `include/config.php`:

```php
define('CED_SHOW_QUERIES', 1);
```

## 📊 Database Schema

- `ced_users`: User accounts and profiles
- `ced_groups`: User groups and permissions
- `ced_config`: Site configuration
- `ced_ranks`: User rank definitions
- `ced_subjects`: Course subjects
- `ced_programming_languages`: Programming languages
- `ced_lab_programs`: Lab program submissions
- `ced_lab_manuals`: Lab manual uploads
- `ced_homework`: Homework assignments
- `ced_notes`: User notes
- `ced_reminders`: User reminders

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 License

This project is based on FluxBB which is licensed under GPL v2 or higher.

## 🙏 Credits

- **FluxBB Team**: Original forum software
- **Visman**: FluxBB fork with enhancements
- **CED Portal Team**: Educational platform adaptation

## 📞 Support

For issues, questions, or suggestions:

- Open an issue on GitHub
- Contact: admin@cedportal.local

---

**Note**: This is a complete architectural redesign. The new version uses FluxBB's proven patterns for better performance, security, and maintainability.

