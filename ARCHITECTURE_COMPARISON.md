# CED Portal Architecture Evolution

## Architecture Comparison: v1.0 (MVC) vs v2.0 (FluxBB)

This document explains the architectural transformation of CED Portal from a custom MVC framework to the FluxBB-based architecture.

---

## Version 1.0 - Custom MVC Architecture

### Structure
```
CED-Portal/
├── public/
│   ├── index.php          # Front Controller
│   └── assets/
├── src/
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Database.php
│   │   ├── View.php
│   │   └── Auth.php
│   ├── Controllers/       # Request handlers
│   ├── Models/            # Data layer
│   └── Utils/
└── templates/             # View files
```

### Request Flow (v1.0)
```
User Request
    ↓
public/index.php (Front Controller)
    ↓
Router → Matches route pattern
    ↓
Controller → Handles business logic
    ↓
Model → Database operations
    ↓
View → Renders template
    ↓
Response
```

### Characteristics (v1.0)
- ✅ Modern MVC pattern
- ✅ Single entry point (front controller)
- ✅ URL routing
- ✅ Template engine
- ❌ Complex for small-medium projects
- ❌ More file traversal overhead
- ❌ Learning curve for contributors

---

## Version 2.0 - FluxBB Architecture

### Structure
```
CED-Portal/
├── include/
│   ├── common.php         # Bootstrap (loaded by all pages)
│   ├── functions.php      # Helper functions
│   ├── cache.php          # Caching system
│   ├── dblayer/          # Database abstraction
│   └── template/         # Template files (.tpl)
├── style/                # CSS themes
├── js/                   # JavaScript
├── cache/                # PHP cache files
├── header.php            # Header template
├── footer.php            # Footer template
├── index.php             # Home page
├── login.php             # Login page
├── lab_programs.php      # Lab programs page
└── admin_*.php           # Admin pages
```

### Request Flow (v2.0)
```
User Request (e.g., lab_programs.php)
    ↓
include/common.php (Bootstrap)
    ↓
Database connection
    ↓
User authentication (cookie check)
    ↓
Configuration & language loading
    ↓
Page logic (in same file)
    ↓
header.php (starts output buffering)
    ↓
Page content output
    ↓
footer.php (injects into template)
    ↓
Response
```

### Characteristics (v2.0)
- ✅ Simple, proven pattern
- ✅ Direct file access (easier debugging)
- ✅ Faster execution (less abstraction)
- ✅ Lower memory footprint
- ✅ Easy to understand and maintain
- ✅ Battle-tested (FluxBB forums)
- ✅ Built-in caching
- ✅ Multi-database support

---

## Key Architectural Differences

| Aspect | v1.0 (MVC) | v2.0 (FluxBB) |
|--------|-----------|---------------|
| **Pattern** | Front Controller + MVC | Page Controller |
| **Entry Point** | Single (public/index.php) | Multiple (each page.php) |
| **Routing** | URL pattern matching | Direct file access |
| **Database** | PDO singleton | DBLayer abstraction |
| **Templates** | PHP templates in templates/ | .tpl files + output buffering |
| **Caching** | None | PHP file caching |
| **Authentication** | Session-based | Cookie-based (HMAC) |
| **Configuration** | .env file | config.php + database |
| **Themes** | Single CSS file | Multiple theme support |
| **Complexity** | Higher abstraction | Lower abstraction |
| **Performance** | Good | Better (less overhead) |
| **File Count** | More scattered | More centralized |

---

## Technical Comparison

### Database Access

**v1.0 (MVC):**
```php
// In Model
class User extends Model {
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
}

// In Controller
$user = $this->userModel->findByUsername($username);
```

**v2.0 (FluxBB):**
```php
// Direct in page
$result = $db->query('SELECT * FROM '.$db->prefix.'users WHERE username=\''.$db->escape($username).'\'');
$user = $db->fetch_assoc($result);
```

### Template Rendering

**v1.0 (MVC):**
```php
// In Controller
$this->view->render('pages/home', ['data' => $data]);

// In templates/pages/home.php
<h1><?= $pageTitle ?></h1>
```

**v2.0 (FluxBB):**
```php
// In page file
require CED_ROOT.'header.php';
?>
<h1>Page Content</h1>
<?php
require CED_ROOT.'footer.php';
```

### Configuration

**v1.0 (MVC):**
```php
// .env file
DB_HOST=localhost
DB_NAME=ced_portal
BASE_URL=http://localhost

// Access via $_ENV
$host = $_ENV['DB_HOST'];
```

**v2.0 (FluxBB):**
```php
// include/config.php
$db_host = 'localhost';
$db_name = 'ced_portal';
define('CED_BASE_URL', 'http://localhost');

// Plus database-stored config
$ced_config['o_board_title'] = 'CED Portal';
```

---

## Advantages of FluxBB Architecture

### 1. **Simplicity**
- Each page is self-contained
- No need to trace through multiple files
- Easy to understand flow

### 2. **Performance**
- Less abstraction = faster execution
- PHP file caching for config
- No routing overhead
- Lower memory usage

### 3. **Maintainability**
- Easier to debug (linear flow)
- Less coupling between components
- Simpler for new contributors
- Proven in production (FluxBB forums worldwide)

### 4. **Features**
- Built-in multi-database support
- Theme system included
- Caching infrastructure
- Security best practices
- Cookie-based auth with HMAC

### 5. **Scalability**
- Handles thousands of concurrent users (proven)
- Efficient database queries
- Caching reduces DB load
- Lightweight footprint

---

## Migration Path (v1.0 → v2.0)

### What Changed

1. **Directory Structure**
   - `public/` → Root directory
   - `src/` → `include/`
   - `templates/` → Inline with header/footer

2. **Routing**
   - URL patterns → Direct file access
   - `/lab-programs` → `lab_programs.php`

3. **Controllers**
   - `LabProgramController.php` → `lab_programs.php` (page controller)

4. **Models**
   - Model classes → Direct database queries in pages

5. **Views**
   - Template files → Output buffering with header/footer

6. **Configuration**
   - `.env` → `include/config.php` + database

### Migration Steps

1. ✅ Install v2.0 fresh
2. Export data from v1.0 database
3. Import into v2.0 schema
4. Update file paths in data
5. Test all functionality
6. Switch DNS/routing

---

## Why FluxBB Architecture?

### Problem with v1.0
- Over-engineered for project size
- Unnecessary abstraction layers
- Harder to onboard new developers
- More files to maintain
- Slower execution due to routing

### Solution: FluxBB Architecture
- ✅ **Proven**: Used by thousands of forums
- ✅ **Simple**: Easy to understand and maintain
- ✅ **Fast**: Less overhead, better performance
- ✅ **Secure**: Battle-tested security practices
- ✅ **Flexible**: Easy to extend and customize
- ✅ **Documented**: Well-established patterns

---

## Performance Metrics (Estimated)

| Metric | v1.0 (MVC) | v2.0 (FluxBB) | Improvement |
|--------|-----------|---------------|-------------|
| Page Load | ~100ms | ~60ms | 40% faster |
| Memory | ~8MB | ~4MB | 50% less |
| Files Loaded | ~15 | ~8 | 47% fewer |
| Database Queries | Similar | Similar | Optimized |
| Cache Hits | None | High | Much faster |

---

## Conclusion

The migration from custom MVC (v1.0) to FluxBB architecture (v2.0) provides:

1. **Better Performance**: Faster page loads, lower memory
2. **Easier Maintenance**: Simpler code structure
3. **Proven Stability**: Battle-tested architecture
4. **Enhanced Security**: FluxBB security practices
5. **Feature Rich**: Themes, caching, multi-DB
6. **Future Ready**: Easier to extend and scale

The FluxBB architecture is better suited for CED Portal's use case: a focused educational platform that prioritizes stability, performance, and ease of maintenance over abstract architectural patterns.

---

## References

- [FluxBB Official](https://fluxbb.org/)
- [FluxBB by Visman](https://github.com/druvx13/FluxBB_by_Visman_fork)
- [Page Controller Pattern](https://martinfowler.com/eaaCatalog/pageController.html)
- [Front Controller Pattern](https://martinfowler.com/eaaCatalog/frontController.html)

