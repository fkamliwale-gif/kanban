-- Run this once against an existing kanban_tracker database.
-- Safe to run in order on databases created from the previous schema.

USE kanban_tracker;

ALTER TABLE activities
  ADD COLUMN project_id INT NULL AFTER user_id,
  ADD COLUMN task_id INT NULL AFTER project_id,
  ADD COLUMN action_type VARCHAR(50) NOT NULL DEFAULT 'other' AFTER task_id;

ALTER TABLE activities
  ADD CONSTRAINT fk_activities_project
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_activities_task
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL;

CREATE INDEX idx_activities_project_id ON activities(project_id);
CREATE INDEX idx_activities_task_id ON activities(task_id);
CREATE INDEX idx_activities_action_type ON activities(action_type);
CREATE INDEX idx_project_team_member_id ON project_team_members(team_member_id);
CREATE INDEX idx_projects_user_id ON projects(user_id);
CREATE INDEX idx_tasks_project_id ON tasks(project_id);
CREATE INDEX idx_tasks_user_id ON tasks(user_id);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_team_members_email ON team_members(member_email);
CREATE INDEX idx_activities_user_id ON activities(user_id);
