# Kanban Tracker / TaskFlow

A simple project-management and Kanban application built for a college-level project.

## Technology stack

- React 19 + TypeScript
- Vite
- PHP 8+
- PDO
- MySQL
- PHP sessions

## Features

- Signup and login
- Session-based authentication
- Projects and project details
- Kanban task workflow
- Task assignment and priorities
- Team directory
- Activity tracking
- Dashboard and reports
- Calendar view

Kanban columns are intentionally simple:

`To Do` → `In Progress` → `Review` → `Completed`

## Local setup

### 1. Frontend

Install Node.js, then from the repository root run:

```bash
npm install
npm run dev
```

Vite runs the frontend at `http://localhost:5173` by default.

### 2. Backend

Install XAMPP or another PHP/Apache/MySQL environment.

Place the repository in the Apache web root. With XAMPP this is commonly:

```text
C:\xampp\htdocs\kanban
```

The frontend expects the PHP API at:

```text
http://localhost/kanban/backend/api
```

You can override this through `VITE_API_BASE_URL`.

### 3. Database

Open phpMyAdmin and import:

```text
backend/database/kanban_database.sql
```

This creates the `kanban_tracker` database and required tables.

If you already have a database created from an older version of the project, run:

```text
backend/database/upgrade_security_audit.sql
```

Do not run the upgrade migration on a database created from the updated fresh-install schema.

### 4. Environment configuration

Copy `.env.example` to your local configuration as appropriate.

Frontend configuration uses:

```text
VITE_API_BASE_URL=http://localhost/kanban/backend/api
```

PHP reads database and CORS values from the server process environment:

```text
FRONTEND_URL=http://localhost:5173
KANBAN_DB_HOST=localhost
KANBAN_DB_NAME=kanban_tracker
KANBAN_DB_USER=root
KANBAN_DB_PASS=
```

Real credentials must not be committed.

## Authentication and security

The application uses PHP sessions with HttpOnly cookies and CSRF protection for authenticated state-changing requests.

Public signup always creates a `Member` account. Privileged roles must not be self-assigned through the signup request.

Backend authorization checks resource ownership or project membership before protected project and task operations.

Production deployments should use HTTPS so the session cookie is marked secure.

## API response format

Successful responses use:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

Errors use the same structure with an appropriate HTTP status code.

## Development checks

Run:

```bash
npm run build
npm run lint
```

The repository also includes GitHub Actions checks for the frontend build/lint and PHP syntax.

## Project structure

```text
backend/
  api/
    auth/
    dashboard/
    projects/
    tasks/
    teams/
  config/
  database/

src/
  services/
  assets/
  App.tsx
  App.css
```

The existing UI and Kanban workflow are intentionally kept simple rather than introducing unnecessary enterprise features.
