const API_URL = "http://localhost/taskflow/backend/api.php";

export type TaskFlowState = {
  users?: unknown[];
  members?: unknown[];
  projects?: unknown[];
  tasks?: unknown[];
  activities?: unknown[];
  settings?: Record<string, unknown>;
};

async function request(action: string, options: RequestInit = {}) {
  const response = await fetch(`${API_URL}?action=${action}`, {
    headers: { "Content-Type": "application/json", ...(options.headers || {}) },
    ...options
  });
  const result = await response.json();
  if (!response.ok || !result.success) {
    throw new Error(result.message || "TaskFlow API request failed");
  }
  return result.data;
}

export async function loadStateFromMySQL(): Promise<TaskFlowState | null> {
  return request("get_state");
}

export async function saveStateToMySQL(state: TaskFlowState): Promise<void> {
  await request("save_state", {
    method: "POST",
    body: JSON.stringify(state)
  });
}
