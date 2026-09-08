// ========================================
// Kanban Tracker API Configuration
// ========================================


// PHP backend URL running through XAMPP

export const API_BASE =
  "http://localhost/kanban/backend/api";


// ========================================
// API RESPONSE TYPE
// ========================================

type ApiResponse<T> = {

  success: boolean;

  message: string;

  data: T;

};


// ========================================
// MAIN API REQUEST FUNCTION
// ========================================

export async function apiRequest<T = unknown>(

  path: string,

  options: RequestInit = {}

): Promise<T> {


  const response = await fetch(

    `${API_BASE}${path}`,

    {

      ...options,


      // Required for PHP sessions
      credentials: "include",


      headers: {

        "Content-Type": "application/json",

        ...(options.headers || {})

      }

    }

  );


  let result: ApiResponse<T>;


  try {

    result = await response.json();

  }

  catch {

    throw new Error(

      `Server returned an unexpected response (status ${response.status}).`

    );

  }


  // Handle API errors

  if (!response.ok || !result.success) {

    throw new Error(

      result.message || "Request failed"

    );

  }


  return result.data;

}


// ========================================
// POST REQUEST FUNCTION
// ========================================

export function apiPost<T = unknown>(

  path: string,

  body: unknown

): Promise<T> {


  return apiRequest<T>(

    path,

    {

      method: "POST",

      body: JSON.stringify(body)

    }

  );

}