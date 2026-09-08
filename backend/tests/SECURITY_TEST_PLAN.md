# Security Regression Test Plan

Run these checks against a local XAMPP/MySQL instance after importing the database migration.

## Authentication

1. Register with an arbitrary role such as `Admin`. Confirm the created `users.role` is `Member`.
2. Register with an invalid email, an empty name, or a password shorter than 8 characters. Confirm HTTP 422.
3. Log in with valid credentials. Confirm a session is created and a CSRF token is returned.
4. Log in with an invalid password. Confirm HTTP 401 and no authenticated session.
5. Call a protected endpoint without a session. Confirm HTTP 401.
6. Send an authenticated state-changing request without `X-CSRF-Token`. Confirm HTTP 403.
7. Send a valid CSRF token. Confirm the request is accepted.

## Project authorization

8. User A creates Project A. User B must not receive Project A from `get_projects.php`.
9. User B must not update Project A by sending its ID to `update_project.php`.
10. User B must not delete Project A by sending its ID to `delete_project.php`.
11. User B attempting to access Project A should receive 404/403 according to the endpoint contract.

## Task authorization

12. User A creates Task A inside Project A. User B must not receive Task A from `get_tasks.php`.
13. User B must not update or delete Task A by changing the task ID in the request.
14. User B must not create a task in Project A unless authorized for the project.
15. A task assignee must exist and must be linked to the selected project.
16. Invalid task status or priority values must return HTTP 422.
17. An authorized project member may move an authorized task between the four Kanban statuses.

## HTTP and data leakage

18. GET requests to delete/remove endpoints must return HTTP 405 and must not change data.
19. A database connection failure must return a generic 500 response and must not expose PDO/SQL details.
20. Review browser responses and confirm passwords are never returned.
21. Confirm dashboard data contains only projects/tasks/activities visible to the logged-in user.

## Frontend regression

22. Signup still completes successfully.
23. Login still opens the dashboard.
24. Logout still clears the PHP session.
25. Existing project/task/Kanban/team/calendar/report navigation continues to work.
26. Failed API updates show an error and do not leave stale success state in the UI.
