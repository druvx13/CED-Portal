# CED Portal

A PHP-based web application for managing lab programs, manuals, homework, and reminders for Computer Engineering students.

## Architecture

This project has been restructured to follow a modern MVC (Model-View-Controller) architecture.

### Directory Structure

- `public/`: The web root. Contains `index.php` (Front Controller) and static assets.
- `src/`: Application source code.
    - `Config/`: Configuration handling.
    - `Core/`: Framework core (Router, Database, View, Auth).
    - `Controllers/`: Request handlers.
    - `Models/`: Data access layer.
    - `Utils/`: Helper functions.
- `templates/`: View templates (HTML).
- `vendor/`: Composer dependencies (if applicable).

## Setup

1.  **Clone the repository.**
2.  **Configure Environment:**
    - Copy `.env.example` to `.env`.
    - Update `DB_*` and `BASE_URL` values in `.env`.
3.  **Database:**
    - Import `schema.sql` into your MySQL database.
    - The `bp_db.php` has been replaced by `src/Core/Database.php`.
4.  **Web Server:**
    - Point your web server (Apache/Nginx) document root to the `public/` directory.
    - Ensure URL rewriting is enabled to route all requests to `public/index.php`.

## Development

- **Autoloading:** A simple PSR-4 compliant autoloader is included in `src/Autoloader.php`.
- **Routing:** Routes are defined in `public/index.php`.
