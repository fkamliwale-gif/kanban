// Base URL for the PHP backend running under XAMPP.
// Adjust the folder name if you place the backend somewhere other than
// C:\xampp\htdocs\kanban-backend\backend
export const API_BASE = "http://localhost/kanban-backend/backend/api";

type ApiResponse<T> = { success: boolean; message: string; data: T };

export async function apiRequest<T = unknown>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const response = await fetch(`${API_BASE}${path}`, {
    credentials: "include", // send the PHP session cookie
    headers: { "Content-Type": "application/json", ...(options.headers || {}) },
    ...options
  });

  let result: ApiResponse<T>;
  try {
    result = await response.json();
  } catch {
    throw new Error(`Server returned an unexpected response (status ${response.status}).`);
  }

  if (!result.success) {
    throw new Error(result.message || "Request failed");
  }
  return result.data;
}

export function apiPost<T = unknown>(path: string, body: unknown): Promise<T> {
  return apiRequest<T>(path, { method: "POST", body: JSON.stringify(body) });
}
