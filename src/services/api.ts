export const API_BASE =
  import.meta.env.VITE_API_BASE_URL ||
  "http://localhost/kanban/backend/api";

export type ApiResponse<T> = {
  success: boolean;
  message: string;
  data: T;
};

export async function apiRequest<T = unknown>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    credentials: "include",
    headers: {
      "Content-Type": "application/json",
      ...(options.headers || {})
    }
  });

  const contentType = response.headers.get("content-type") || "";
  if (!contentType.includes("application/json")) {
    throw new Error(`Server returned an unexpected response (status ${response.status}).`);
  }

  const result: ApiResponse<T> = await response.json();

  if (!response.ok || !result.success) {
    throw new Error(result.message || "Request failed");
  }

  return result.data;
}

export function apiPost<T = unknown>(path: string, body: unknown): Promise<T> {
  return apiRequest<T>(path, {
    method: "POST",
    body: JSON.stringify(body)
  });
}
