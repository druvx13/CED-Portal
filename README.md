# CED Portal - FluxBB Architecture Edition

> **⚠️ Major Update:** This repository has been completely redesigned based on the [FluxBB forum CMS](https://github.com/druvx13/FluxBB_by_Visman_fork) architecture for improved performance, security, and maintainability.

A robust PHP-based educational portal for managing lab programs, manuals, homework, notes, and reminders for Computer Engineering students.

## 🎯 What's New in v2.0

- **FluxBB Architecture**: Complete migration to proven forum CMS patterns
- **Page Controller Pattern**: Each page is a standalone PHP script
- **Multi-Database Support**: MySQL, PostgreSQL, and SQLite compatible
- **Theme System**: 3 professional themes (Air, Oxygen, Mercury)
- **Enhanced Security**: CSRF protection, HMAC validation, secure cookies
- **PHP Caching**: Fast configuration and data caching
- **Web Installer**: Easy setup via browser

## 🚀 Quick Start

### Installation (Web-based)

1. **Download/Clone:**
   ```bash
   git clone https://github.com/druvx13/CED-Portal.git
   cd CED-Portal
   ```

2. **Set Permissions:**
   ```bash
   chmod 777 cache/
   chmod 777 include/
   chmod 777 img/avatars/
   ```

3. **Run Installer:**
   - Navigate to `http://your-domain.com/install.php`
   - Fill in database details and admin credentials
   - Click "Install CED Portal"

4. **Security:**
   - Delete `install.php` after installation
   - Change default admin password immediately

### Manual Installation

See [README_FLUXBB.md](README_FLUXBB.md) for detailed manual installation instructions.

## 📁 Architecture

This version uses **FluxBB's Page Controller pattern** instead of traditional MVC:

```
CED-Portal/
├── include/           # Core libraries (common.php, functions.php, etc.)
├── style/             # CSS themes (Air, Oxygen, Mercury)
├── js/                # JavaScript (jQuery, common.js)
├── lang/              # Language packs
├── cache/             # PHP file cache (auto-generated)
├── header.php         # Page header template
├── footer.php         # Page footer template
├── index.php          # Home page
├── login.php          # Login page
├── lab_programs.php   # Lab programs page
├── admin_index.php    # Admin dashboard
└── install.php        # Web installer
```

### Key Components

- **Bootstrap (`include/common.php`)**: Loaded by every page, initializes app
- **Database Layer (`include/dblayer/`)**: Abstraction supporting multiple DBs
- **Template System**: Lightweight substitution-based templating
- **Authentication**: Cookie-based with HMAC validation
- **Caching**: PHP files in `cache/` for fast config access

## 🎨 Features

### For Students

✅ **Lab Programs**: Store and manage programming assignments  
✅ **Lab Manuals**: Upload and access PDF manuals  
✅ **Homework**: Track assignments with due dates  
✅ **Notes**: Personal note-taking system  
✅ **Reminders**: Schedule important dates

### For Administrators

🔧 **User Management**: Add, edit, remove users  
🔧 **Subject Management**: Manage course subjects  
🔧 **Statistics**: View portal usage stats  
🔧 **Configuration**: Customize portal settings  
🔧 **Permissions**: Group-based access control

## 🔐 Security

- **CSRF Protection**: Token-based form protection
- **SQL Injection Prevention**: Prepared statements and escaping
- **XSS Protection**: HTML encoding on all output
- **Secure Passwords**: Bcrypt password hashing
- **Cookie Security**: HMAC validation and HTTPOnly flags
- **Bad Character Filtering**: UTF-8 security checks

## 🛠 Technology Stack

- **Backend**: PHP 7.2+ (procedural + OOP)
- **Database**: MySQL 5.5+ / PostgreSQL 7+ / SQLite 3
- **Frontend**: HTML5, CSS3, JavaScript (jQuery 1.12.4)
- **Template Engine**: Custom lightweight system
- **Authentication**: Cookie-based sessions

## 📚 Documentation

- [FluxBB Architecture Guide](README_FLUXBB.md) - Comprehensive architecture documentation
- [Installation Guide](README_FLUXBB.md#-installation) - Detailed setup instructions
- [Database Schema](schema_fluxbb.sql) - Complete database structure
- [Configuration Reference](README_FLUXBB.md#-configuration) - Configuration options

## 🔄 Migration from v1.0

If you're migrating from the previous MVC version:

1. Backup your current database
2. Export existing data
3. Install v2.0 (FluxBB architecture)
4. Import your data into new schema
5. Update file references

**Note**: The two versions use different architectures and are not directly compatible.

## 🧪 Development

### Adding a New Page

```php
<?php
define('CED_ROOT', './');
require CED_ROOT.'include/common.php';

$page_title = 'My Page';
$page = 'mypage';

require CED_ROOT.'header.php';
?>

<!-- Your content here -->

<?php require CED_ROOT.'footer.php'; ?>
```

### Debug Mode

Enable in `include/config.php`:
```php
define('CED_SHOW_QUERIES', 1);
```

## 📊 Database Schema

Core tables with `ced_` prefix:

- `users` - User accounts and profiles
- `groups` - User groups and permissions
- `config` - Site configuration
- `subjects` - Course subjects
- `lab_programs` - Lab assignments
- `homework` - Homework tracking
- `notes` - Personal notes
- `reminders` - Scheduled reminders

## 🎨 Themes

Choose from 3 included themes:

- **Air** (Default) - Clean, minimal, 37KB
- **Oxygen** - Professional blue, 23KB
- **Mercury** - Classic forum style, 23KB

## 🤝 Contributing

Contributions welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Test your changes
4. Submit a pull request

## 📝 License

This project is based on FluxBB (GPL v2 or higher).

## 🙏 Credits

- **FluxBB Team** - Original forum software
- **Visman** - FluxBB fork enhancements
- **CED Portal Team** - Educational adaptation

## 📞 Support

- GitHub Issues: [Report a bug](https://github.com/druvx13/CED-Portal/issues)
- Documentation: [README_FLUXBB.md](README_FLUXBB.md)

---

**Version**: 2.0.0 (FluxBB Architecture)  
**Previous Version**: 1.0.0 (Custom MVC) - See [old README](README.old.md)
