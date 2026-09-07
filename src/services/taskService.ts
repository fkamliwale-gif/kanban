import { apiRequest, apiPost } from "./api";

export interface TaskRow {
  id: number;
  project_id: number;
  user_id: number;
  assigned_to: number | null;
  title: string;
  description: string;
  status: "To Do" | "In Progress" | "Review" | "Completed";
  priority: "Low" | "Medium" | "High" | "Critical";
  start_date: string | null;
  due_date: string | null;
  created_at: string;
}

export function getTasks() {
  return apiRequest<TaskRow[]>("/tasks/get_tasks.php");
}

export function createTask(payload: {
  title: string; description: string; projectId: number; assignedTo: string;
  priority: string; status: string; startDate: string; dueDate: string;
}) {
  return apiPost<{ id: number }>("/tasks/create_task.php", payload);
}

export function updateTask(id: number, payload: {
  title: string; description: string; projectId: number; assignedTo: string;
  priority: string; status: string; startDate: string; dueDate: string;
}) {
  return apiPost<null>("/tasks/update_task.php", { id, ...payload });
}

export function updateTaskStatus(id: number, status: string) {
  return apiPost<null>("/tasks/update_task_status.php", { id, status });
}

export function deleteTask(id: number) {
  return apiPost<null>("/tasks/delete_task.php", { id });
}
