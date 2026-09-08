import { apiRequest, apiPost, clearCsrfToken, setCsrfToken } from './api';

export interface SessionUser {
  id: number;
  name: string;
  email?: string;
  role: string;
  csrfToken: string;
}

// The role parameter is retained for frontend compatibility, but the backend
// deliberately ignores it for public registration and always creates Member.
export async function register(name: string, email: string, password: string, _role?: string) {
  return apiPost<{ id: number }>('/auth/register.php', { name, email, password });
}

export async function login(email: string, password: string) {
  const session = await apiPost<SessionUser>('/auth/login.php', { email, password });
  setCsrfToken(session.csrfToken);
  return session;
}

export async function logout() {
  try {
    return await apiPost<null>('/auth/logout.php', {});
  } finally {
    clearCsrfToken();
  }
}

export async function checkSession(): Promise<SessionUser> {
  const session = await apiRequest<SessionUser>('/auth/logout.php', { method: 'GET' });
  setCsrfToken(session.csrfToken);
  return session;
}
