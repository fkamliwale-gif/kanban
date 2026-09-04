const API_URL = "http://localhost/taskflow/backend/api.php";
const KEYS = ["tf_users", "tf_members", "tf_projects", "tf_tasks", "tf_activities", "tf_notifications"];

function stateFromStorage() {
  const state: Record<string, unknown> = {};
  for (const key of KEYS) {
    const raw = localStorage.getItem(key);
    if (!raw) continue;
    const name = key.replace("tf_", "");
    try { state[name] = JSON.parse(raw); } catch { state[name] = raw; }
  }
  return state;
}

export async function hydrateFromMySQL() {
  try {
    const response = await fetch(`${API_URL}?action=get_state`);
    if (!response.ok) return false;
    const result = await response.json();
    if (!result.success || !result.data) return false;
    const data = result.data;
    for (const name of ["users", "members", "projects", "tasks", "activities"]) {
      if (Array.isArray(data[name])) localStorage.setItem(`tf_${name}`, JSON.stringify(data[name]));
    }
    if (data.settings?.notifications !== undefined) localStorage.setItem("tf_notifications", JSON.stringify(data.settings.notifications));
    return true;
  } catch (error) {
    console.warn("TaskFlow PHP/MySQL backend is not available. Using local data.", error);
    return false;
  }
}

let syncTimer: number | undefined;
export function enableBackendSync() {
  const originalSetItem = localStorage.setItem.bind(localStorage);
  localStorage.setItem = (key: string, value: string) => {
    originalSetItem(key, value);
    if (!KEYS.includes(key)) return;
    window.clearTimeout(syncTimer);
    syncTimer = window.setTimeout(async () => {
      try {
        await fetch(`${API_URL}?action=save_state`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(stateFromStorage())
        });
      } catch (error) {
        console.warn("Could not save TaskFlow data to MySQL.", error);
      }
    }, 300);
  };
}
