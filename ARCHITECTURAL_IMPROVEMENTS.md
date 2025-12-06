# Architectural Improvements Proposal

## 1. Executive Summary

The current application is a functional but monolithic PHP application where all business logic, routing, and presentation are contained within a single file (`index.php`). While this approach is simple for small scripts, it becomes difficult to maintain, test, and scale as the application grows.

This document proposes a refactoring to a standard **Model-View-Controller (MVC)** architecture. This separation of concerns will improve code organization, enhance security, and facilitate future development.

## 2. Current Architecture Analysis

*   **Entry Point:** `index.php` serves as the Router, Controller, and View.
*   **Database:** `bp_db.php` handles connection. `schema.sql` defines the structure.
*   **Routing:** Custom `parse_url` logic with `if/else` blocks inside `index.php`.
*   **Views:** HTML is embedded directly within PHP logic blocks.
*   **Dependencies:** Minimal (PDO for database). No package manager (Composer) is currently used.
*   **Security:** Basic checks exist (SQL injection protection via prepared statements), but having logic mixed with views increases the risk of XSS if output escaping is missed (though `h()` helper is used).

### Identified Issues

1.  **Maintainability:** `index.php` is over 1000 lines long. Finding specific logic is time-consuming.
2.  **Scalability:** Adding new features requires modifying the core file, increasing complexity and risk of regression.
3.  **Testability:** It is nearly impossible to unit test individual components (like controllers or models) because they are tightly coupled.
4.  **Code Duplication:** Similar logic (e.g., pagination, error handling) might be repeated.
5.  **Security Audit:** It is harder to audit access control when it is scattered across a large procedural file.

## 3. Proposed MVC Architecture

We recommend transitioning to a structured MVC framework.

### 3.1 Directory Structure

```text
/
├── public/              # Web root (only this folder is accessible via web)
│   ├── index.php        # Front Controller (Entry point)
│   ├── assets/          # CSS, JS, Images
│   └── uploads/         # User uploads (served directly or via script)
├── src/                 # Application Source Code
│   ├── Config/          # Configuration (DB credentials, constants)
│   ├── Core/            # Framework Core (Router, Database, View)
│   ├── Controllers/     # Controllers (Handle user requests)
│   ├── Models/          # Models (Data access layer)
│   └── Views/           # Templates (HTML files)
├── composer.json        # Dependency Manager (Optional but recommended)
└── .htaccess            # Rewrite rules to route everything to public/index.php
```

### 3.2 Key Components

*   **Front Controller (`public/index.php`):** Initializes the application, loads configuration, and dispatches the request to the Router.
*   **Router (`src/Core/Router.php`):** Maps URLs (e.g., `/lab-programs`) to specific Controller methods (e.g., `LabProgramController::index`).
*   **Controllers (`src/Controllers/*`):** Handle input, interact with Models, and return Views.
    *   `AuthController`: Login, logout.
    *   `DashboardController`: Homepage logic.
    *   `LabProgramController`: CRUD for lab programs.
    *   `ManualController`: CRUD for manuals.
    *   `HomeworkController`: CRUD for homework.
    *   `AdminController`: User and master data management.
*   **Models (`src/Models/*`):** Encapsulate database interaction.
    *   `User`: specific user queries.
    *   `LabProgram`: fetch, create, update programs.
*   **Views (`src/Views/*`):** Pure HTML files with minimal PHP for outputting variables. A layout system (header/footer) should be used.

## 4. Implementation Roadmap

### Phase 1: Preparation
1.  Set up the directory structure.
2.  Move `assets` to `public/assets`.
3.  Create a basic `public/index.php` and `.htaccess` to handle routing.

### Phase 2: Core Components
1.  Implement a simple **Autoloader** (or use Composer).
2.  Create a **Database** class (singleton or dependency injection) based on `bp_db.php`.
3.  Create a **Router** class to replace the `if/else` routing blocks.

### Phase 3: Migration (Iterative)
1.  **Auth & Users:** Migrate Login/Logout logic to `AuthController`. Move User queries to `UserModel`.
2.  **Dashboard:** Move dashboard logic to `DashboardController`.
3.  **Features:** One by one, migrate Lab Programs, Manuals, Homework, and Admin sections.
4.  **Views:** Extract HTML into separate `.php` template files in `src/Views`.

### Phase 4: Cleanup
1.  Remove the old `index.php` once all functionality is migrated.
2.  Standardize error handling and input validation.

## 5. Benefits

*   **Organization:** Code is easy to find.
*   **Collaboration:** Multiple developers can work on different files simultaneously.
*   **Security:** `public` folder isolates application code from the web root.
*   **Future Proofing:** Easier to upgrade to a full framework (like Laravel or Symfony) later if needed, as the concepts are the same.
