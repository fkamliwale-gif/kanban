# TaskFlow Kanban Tracker

A React + TypeScript frontend with a PHP + MySQL backend for running locally with XAMPP.

## Technology

- React
- TypeScript
- Vite
- PHP
- MySQL
- phpMyAdmin
- XAMPP

## Backend setup

1. Copy the repository's `backend` folder to:

```text
C:\xampp\htdocs\kanban\backend
```

2. Start **Apache** and **MySQL** in XAMPP.

3. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Import:

```text
backend/database/kanban_database.sql
```

The database name is:

```text
kanban_tracker
```

> Important: If you imported an older database version before this update, create a fresh `kanban_tracker` database and import the updated SQL file so the latest table structure is applied.

## Frontend setup

Install dependencies:

```bash
npm install
```

Optional: copy `.env.example` to `.env` and change the backend URL if your XAMPP folder name is different.

Run:

```bash
npm run dev
```

Open:

```text
http://localhost:5173
```

## API URL

Default API URL:

```text
http://localhost/kanban/backend/api
```

It can be overridden with:

```text
VITE_API_BASE_URL
```

## Authentication

The app uses PHP sessions and sends cookies with:

```text
credentials: include
```

CORS and session handling are centralized in:

```text
backend/config/bootstrap.php
```

Endpoints:

- `POST /auth/register.php`
- `POST /auth/login.php`
- `GET /auth/session.php`
- `POST /auth/logout.php`

## Main functionality

- User registration and login
- PHP session authentication
- Projects stored in MySQL
- Tasks stored in MySQL
- Kanban task status updates
- Team members stored in MySQL
- Dashboard data from MySQL
- Activity history
- Data isolated by logged-in user

## Production note

This project is configured for local XAMPP development. Before deploying publicly, add stronger authorization, CSRF protection, HTTPS, environment-based database credentials, and production error logging.
