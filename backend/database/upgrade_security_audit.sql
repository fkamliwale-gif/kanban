-- Upgrade an existing kanban_tracker database created from the previous schema.
-- Do not run this after importing the already-updated kanban_database.sql.

USE kanban_tracker;

ALTER TABLE team_members
  ADD COLUMN created_by INT NULL AFTER id,
  ADD CONSTRAINT fk_team_members_created_by
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX idx_team_members_created_by ON team_members(created_by);
CREATE INDEX idx_team_members_email ON team_members(member_email);

ALTER TABLE activities
  ADD COLUMN project_id INT NULL AFTER user_id,
  ADD COLUMN task_id INT NULL AFTER project_id,
  ADD COLUMN action_type VARCHAR(50) NOT NULL DEFAULT 'other' AFTER task_id,
  ADD CONSTRAINT fk_activities_project
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_activities_task
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL;

CREATE INDEX idx_activities_project_id ON activities(project_id);
CREATE INDEX idx_activities_task_id ON activities(task_id);
CREATE INDEX idx_activities_action_type ON activities(action_type);
