import { apiPost, apiRequest } from "./api";

export interface SessionUser {
  id: number;
  name: string;
  email?: string;
  role: string;
}

export function register(name: string, email: string, password: string, role: string) {
  return apiPost<{ id: number }>("/auth/register.php", { name, email, password, role });
}

export function login(email: string, password: string) {
  return apiPost<SessionUser>("/auth/login.php", { email, password });
}

export function logout() {
  return apiPost<null>("/auth/logout.php", {});
}

export async function checkSession(): Promise<SessionUser | null> {
  try {
    return await apiRequest<SessionUser>("/auth/session.php");
  } catch {
    return null;
  }
}
