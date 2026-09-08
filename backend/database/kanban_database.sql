-- Kanban Tracker database schema
-- Fresh-install schema for the React + PHP + MySQL application.

CREATE DATABASE IF NOT EXISTS kanban_tracker
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE kanban_tracker;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'Member',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS team_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_by INT NULL,
  member_name VARCHAR(150) NOT NULL,
  member_email VARCHAR(150) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'Member',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_team_members_created_by (created_by),
  INDEX idx_team_members_email (member_email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  manager_id INT NULL,
  project_name VARCHAR(200) NOT NULL,
  description TEXT,
  status VARCHAR(30) NOT NULL DEFAULT 'Not Started',
  priority VARCHAR(20) NOT NULL DEFAULT 'Medium',
  start_date DATE,
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (manager_id) REFERENCES team_members(id) ON DELETE SET NULL,
  INDEX idx_projects_user_id (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS project_team_members (
  project_id INT NOT NULL,
  team_member_id INT NOT NULL,
  PRIMARY KEY (project_id, team_member_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE,
  INDEX idx_project_team_member_id (team_member_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  user_id INT NOT NULL,
  assigned_to INT NULL,
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
  FOREIGN KEY (assigned_to) REFERENCES team_members(id) ON DELETE SET NULL,
  INDEX idx_tasks_project_id (project_id),
  INDEX idx_tasks_user_id (user_id),
  INDEX idx_tasks_assigned_to (assigned_to)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  project_id INT NULL,
  task_id INT NULL,
  action_type VARCHAR(50) NOT NULL DEFAULT 'other',
  action VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL,
  INDEX idx_activities_user_id (user_id),
  INDEX idx_activities_project_id (project_id),
  INDEX idx_activities_task_id (task_id),
  INDEX idx_activities_action_type (action_type)
) ENGINE=InnoDB;

-- No demo user is seeded. Public signup always creates Member.
