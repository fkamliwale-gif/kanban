import { apiRequest } from "./api";
import type { ProjectRow } from "./projectService";
import type { TaskRow } from "./taskService";
import type { MemberRow } from "./teamService";

export interface ActivityRow {
  id: number;
  user_id: number | null;
  action: string;
  created_at: string;
}

export interface DashboardData {
  projects: ProjectRow[];
  tasks: TaskRow[];
  members: MemberRow[];
  activities: ActivityRow[];
}

export function getDashboardData() {
  return apiRequest<DashboardData>("/dashboard/get_dashboard_data.php");
}
