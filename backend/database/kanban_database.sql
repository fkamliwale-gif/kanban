-- kanban_database.sql
-- Import this file into phpMyAdmin to create the Kanban Tracker database.

CREATE DATABASE IF NOT EXISTS kanban_tracker
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE kanban_tracker;

-- ---------------------------------------------------------------
-- Users (people who can log in)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,          -- stored with PHP password_hash()
  role VARCHAR(50) NOT NULL DEFAULT 'Member',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Team members directory (reusable across projects, as in the UI)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS team_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  member_name VARCHAR(150) NOT NULL,
  member_email VARCHAR(150) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'Member',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Projects
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,                    -- logged-in user who owns/created it
  manager_id INT NULL,                     -- team_members.id shown as "Manager" in the UI
  project_name VARCHAR(200) NOT NULL,
  description TEXT,
  status VARCHAR(30) NOT NULL DEFAULT 'Not Started',
  priority VARCHAR(20) NOT NULL DEFAULT 'Medium',
  start_date DATE,
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (manager_id) REFERENCES team_members(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Many-to-many: which team members are on which project
CREATE TABLE IF NOT EXISTS project_team_members (
  project_id INT NOT NULL,
  team_member_id INT NOT NULL,
  PRIMARY KEY (project_id, team_member_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Tasks
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  user_id INT NOT NULL,                    -- who created it
  assigned_to INT NULL,                    -- team_members.id
  title VARCHAR(200) NOT NULL,
  description TEXT,
  status ENUM('To Do','In Progress','Review','Completed') NOT NULL DEFAULT 'To Do',
  priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  start_date DATE,
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_to) REFERENCES team_members(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Activity log (shown on the Activity page / dashboard)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- No demo user is seeded here on purpose: a hand-typed password hash
-- can't be verified without running PHP. After importing this file,
-- just use the app's "Sign up" form once to create your first account
-- (it calls register.php, which hashes the password correctly for you).
