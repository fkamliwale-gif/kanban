// ========================================
// Kanban Tracker API Configuration
// ========================================

export const API_BASE =
  import.meta.env.VITE_API_BASE_URL ||
  'http://localhost/kanban/backend/api';

type ApiResponse<T> = {
  success: boolean;
  message: string;
  data: T;
};

export function getCsrfToken(): string | null {
  return sessionStorage.getItem('kanban_csrf_token');
}

export function setCsrfToken(token: string): void {
  sessionStorage.setItem('kanban_csrf_token', token);
}

export function clearCsrfToken(): void {
  sessionStorage.removeItem('kanban_csrf_token');
}

export async function apiRequest<T = unknown>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const method = (options.method || 'GET').toUpperCase();
  const headers = new Headers(options.headers || {});
  headers.set('Content-Type', 'application/json');

  if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
    const csrfToken = getCsrfToken();
    if (csrfToken) {
      headers.set('X-CSRF-Token', csrfToken);
    }
  }

  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    method,
    credentials: 'include',
    headers,
  });

  let result: ApiResponse<T>;
  try {
    result = await response.json();
  } catch {
    throw new Error(`Server returned an unexpected response (status ${response.status}).`);
  }

  if (!response.ok || !result.success) {
    const error = new Error(result.message || 'Request failed');
    if (response.status === 401 || response.status === 403) {
      error.name = response.status === 401 ? 'UnauthorizedError' : 'ForbiddenError';
    }
    throw error;
  }

  return result.data;
}

export function apiPost<T = unknown>(path: string, body: unknown): Promise<T> {
  return apiRequest<T>(path, {
    method: 'POST',
    body: JSON.stringify(body),
  });
}
