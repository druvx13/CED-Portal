# CED Portal

A PHP web application for Computer Engineering Department students to manage lab programs, manuals, homework, and notes.

## Architecture

This application uses a custom MVC (Model-View-Controller) architecture.

### Directory Structure

- `public/`: Web root. Contains the entry point `index.php` and static assets.
- `src/`: Application source code.
    - `Config/`: Configuration handling.
    - `Controllers/`: Handles incoming requests.
    - `Core/`: Framework core (Router, Database, View, etc.).
    - `Models/`: Data access layer.
    - `Utils/`: Helper functions.
- `templates/`: View templates (HTML).
    - `layout/`: Shared layout files (header, footer).
    - `pages/`: Page-specific templates.

## Setup

1.  Clone the repository.
2.  Copy `.env.example` to `.env` and configure your database credentials.
3.  Import `schema.sql` into your MySQL database.
4.  Configure your web server to point to the `public/` directory.
5.  Ensure `public/uploads/` is writable by the web server.

## Requirements

- PHP 7.4+
- MySQL/MariaDB
- Apache (with mod_rewrite) or Nginx
