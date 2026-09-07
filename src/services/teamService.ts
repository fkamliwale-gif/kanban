import { apiRequest, apiPost } from "./api";

export interface MemberRow {
  id: number;
  member_name: string;
  member_email: string;
  role: string;
}

export function getTeam() {
  return apiRequest<MemberRow[]>("/teams/get_team.php");
}

export function saveMember(payload: { id?: number; name: string; email: string; role: string }) {
  return apiPost<{ id: number }>("/teams/add_member.php", payload);
}

export function removeMember(id: number) {
  return apiPost<null>("/teams/remove_member.php", { id });
}
