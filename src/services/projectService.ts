import { apiRequest, apiPost } from "./api";

export interface ProjectRow {
  id: number;
  user_id: number;
  manager_id: number | null;
  project_name: string;
  description: string;
  status: string;
  priority: string;
  start_date: string | null;
  due_date: string | null;
  teamIds: number[];
}

export function getProjects() {
  return apiRequest<ProjectRow[]>("/projects/get_projects.php");
}

export function createProject(payload: {
  name: string; description: string; status: string; priority: string;
  startDate: string; dueDate: string; teamIds: number[]; managerId: number | null;
}) {
  return apiPost<{ id: number }>("/projects/create_project.php", payload);
}

export function updateProject(id: number, payload: {
  name: string; description: string; status: string; priority: string;
  startDate: string; dueDate: string; teamIds: number[]; managerId: number | null;
}) {
  return apiPost<null>("/projects/update_project.php", { id, ...payload });
}

export function deleteProject(id: number) {
  return apiPost<null>("/projects/delete_project.php", { id });
}
