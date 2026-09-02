import React, { useState } from "react";

type Page = "login" | "signup" | "dashboard";

function App() {
  const [page, setPage] = useState<Page>("login");

  // =========================
  // TEMPORARY LOGIN DETAILS
  // =========================
  const TEMP_EMAIL = "admin@taskflow.com";
  const TEMP_PASSWORD = "123456";

  const [loginEmail, setLoginEmail] = useState("");
  const [loginPassword, setLoginPassword] = useState("");
  const [showLoginPassword, setShowLoginPassword] = useState(false);
  const [loginError, setLoginError] = useState("");

  const [fullName, setFullName] = useState("");
  const [signupEmail, setSignupEmail] = useState("");
  const [signupPassword, setSignupPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [role, setRole] = useState("");

  const [showSignupPassword, setShowSignupPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const [signupErrors, setSignupErrors] = useState({
    fullName: "",
    email: "",
    password: "",
    confirm: "",
    role: "",
  });

  const [savedUser, setSavedUser] = useState({
    name: "",
    email: "",
    role: "",
  });

  const [savedPassword, setSavedPassword] = useState("");

  // =========================
  // PASSWORD STRENGTH
  // =========================
  const passwordStrength = () => {
    let strength = 0;

    if (signupPassword.length >= 6) strength++;
    if (signupPassword.length >= 10) strength++;

    if (/[A-Z]/.test(signupPassword) && /[0-9]/.test(signupPassword)) {
      strength++;
    }

    if (/[^A-Za-z0-9]/.test(signupPassword)) {
      strength++;
    }

    if (strength >= 3) return "strong";
    if (strength >= 2) return "medium";
    if (strength >= 1) return "weak";

    return "";
  };

  const strength = passwordStrength();

  // =========================
  // LOGIN
  // =========================
const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();

    setLoginError("");

    if (!loginEmail.trim() || !loginPassword.trim()) {
      setLoginError("Please fill in both email and password.");
      return;
    }

    if (!loginEmail.includes("@") || !loginEmail.includes(".")) {
      setLoginError("Please enter a valid email address.");
      return;
    }

try {
  const response = await fetch(
    "http://localhost/TaskFlow/backend/api.php?action=login",
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "include",
      body: JSON.stringify({
        email: loginEmail,
        password: loginPassword,
      }),
    }
  );

  const data = await response.json();

  if (data.success) {
    setSavedUser({
    name: data.user.name,
      email: data.user.email,
      role: data.user.role,
    });

    setPage("dashboard");
    return;
  } else {
    setLoginError(data.message || "Invalid email or password.");
    return;
  }
} catch (error) {
  setLoginError("Cannot connect to server.");
  return;
}

    setLoginError("Invalid email or password.");
  };

  // =========================
  // SIGN UP
  // =========================
  const handleSignup = (e: React.FormEvent) => {
    e.preventDefault();

    const errors = {
      fullName: "",
      email: "",
      password: "",
      confirm: "",
      role: "",
    };

    let valid = true;

    if (!fullName.trim()) {
      errors.fullName = "Please enter your full name.";
      valid = false;
    }

    if (
      !signupEmail.trim() ||
      !signupEmail.includes("@") ||
      !signupEmail.includes(".")
    ) {
      errors.email = "Please enter a valid email.";
      valid = false;
    }

    if (!signupPassword.trim()) {
      errors.password = "Password is required.";
      valid = false;
    } else if (signupPassword.length < 6) {
      errors.password = "Password must be at least 6 characters.";
      valid = false;
    }

    if (!confirmPassword.trim()) {
      errors.confirm = "Please confirm your password.";
      valid = false;
    } else if (signupPassword !== confirmPassword) {
      errors.confirm = "Passwords do not match.";
      valid = false;
    }

    if (!role) {
      errors.role = "Please select a role.";
      valid = false;
    }

    setSignupErrors(errors);

    if (!valid) return;

    setSavedUser({
      name: fullName,
      email: signupEmail,
      role: role,
    });

    setSavedPassword(signupPassword);

    setLoginEmail(signupEmail);
    setLoginPassword("");

    setFullName("");
    setSignupEmail("");
    setSignupPassword("");
    setConfirmPassword("");
    setRole("");

    setSignupErrors({
      fullName: "",
      email: "",
      password: "",
      confirm: "",
      role: "",
    });

    setPage("login");
  };

  // =========================
  // LOGOUT
  // =========================
  const logout = () => {
    setLoginEmail("");
    setLoginPassword("");
    setLoginError("");
    setPage("login");
  };

  // =========================
  // DASHBOARD
  // =========================
  if (page === "dashboard") {
    return (
      <>
        <style>{`
          * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Inter, -apple-system, BlinkMacSystemFont,
              "Segoe UI", Roboto, sans-serif;
          }

          body {
            margin: 0;
          }

          .dashboard {
            min-height: 100vh;
            background: #f7f7ff;
            display: flex;
          }

          .sidebar {
            width: 250px;
            min-height: 100vh;
            background: linear-gradient(
              180deg,
              #3f36d5,
              #5336e6,
              #2925a8
            );
            color: white;
            padding: 28px 18px;
          }

          .dash-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 12px;
            margin-bottom: 45px;
          }

          .dash-logo-icon {
            width: 42px;
            height: 42px;
            background: white;
            color: #4f46e5;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            font-weight: 800;
          }

          .dash-logo span {
            font-size: 23px;
            font-weight: 700;
          }

          .dash-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
          }

          .dash-menu button {
            border: none;
            background: transparent;
            color: white;
            text-align: left;
            padding: 14px 15px;
            border-radius: 12px;
            font-size: 15px;
            cursor: pointer;
            opacity: 0.9;
          }

          .dash-menu button.active,
          .dash-menu button:hover {
            background: rgba(255,255,255,0.16);
            opacity: 1;
          }

          .dashboard-main {
            flex: 1;
            padding: 35px;
          }

          .dash-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
          }

          .dash-top h1 {
            color: #172033;
            font-size: 30px;
          }

          .dash-top p {
            color: #6b7280;
            margin-top: 5px;
          }

          .logout-btn {
            padding: 11px 20px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #4f46e5;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
          }

          .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 25px;
          }

          .stat-card {
            background: white;
            padding: 22px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(79,70,229,0.07);
          }

          .stat-card p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
          }

          .stat-card h2 {
            color: #172033;
            font-size: 28px;
          }

          .welcome-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(79,70,229,0.07);
          }

          .welcome-card h2 {
            color: #172033;
            margin-bottom: 10px;
          }

          .welcome-card p {
            color: #6b7280;
            line-height: 1.6;
          }

          @media (max-width: 900px) {
            .sidebar {
              width: 190px;
            }

            .cards {
              grid-template-columns: repeat(2, 1fr);
            }
          }

          @media (max-width: 650px) {
            .dashboard {
              flex-direction: column;
            }

            .sidebar {
              width: 100%;
              min-height: auto;
            }

            .dashboard-main {
              padding: 20px;
            }

            .cards {
              grid-template-columns: 1fr;
            }
          }
        `}</style>

        <div className="dashboard">

          <aside className="sidebar">

            <div className="dash-logo">
              <div className="dash-logo-icon">✓</div>

              <span>
                Task
                <span style={{ color: "#c4b5fd" }}>
                  Flow
                </span>
              </span>
            </div>

            <div className="dash-menu">
              <button className="active">▣ Dashboard</button>
              <button>▤ Projects</button>
              <button>✓ Tasks</button>
              <button>◫ Calendar</button>
              <button>♟ Team</button>
              <button>▥ Reports</button>
              <button>⚙ Settings</button>
            </div>

          </aside>

          <main className="dashboard-main">

            <div className="dash-top">

              <div>
                <h1>Dashboard</h1>

                <p>
                  Welcome back,{" "}
                  {savedUser.name || "User"} 👋
                </p>
              </div>

              <button
                className="logout-btn"
                onClick={logout}
              >
                Logout
              </button>

            </div>

            <div className="cards">

              <div className="stat-card">
                <p>Total Projects</p>
                <h2>12</h2>
              </div>

              <div className="stat-card">
                <p>Total Tasks</p>
                <h2>48</h2>
              </div>

              <div className="stat-card">
                <p>In Progress</p>
                <h2>18</h2>
              </div>

              <div className="stat-card">
                <p>Completed</p>
                <h2>30</h2>
              </div>

            </div>

            <div className="welcome-card">

              <h2>TaskFlow Dashboard</h2>

              <p>
                You are successfully logged in to TaskFlow.
                {savedUser.role &&
                  ` Your role is ${savedUser.role}.`}
                {" "}
                Use the sidebar to manage projects,
                tasks, calendar, team members and reports.
              </p>

            </div>

          </main>

        </div>
      </>
    );
  }

  // =========================
  // LOGIN + SIGNUP
  // =========================

  return (
    <>
      <style>{`
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: Inter, -apple-system, BlinkMacSystemFont,
            "Segoe UI", Roboto, sans-serif;
        }

        body {
          background: #f5f3ff;
        }

        button,
        input,
        select {
          font-family: inherit;
        }

        .auth-page {
          min-height: 100vh;
          padding: 24px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #f5f3ff;
        }

        .auth-container {
          width: 100%;
          max-width: 1440px;
          min-height: 90vh;
          background: white;
          border-radius: 48px;
          overflow: hidden;
          display: flex;
          box-shadow:
            0 30px 60px rgba(79,70,229,0.12),
            0 12px 30px rgba(0,0,0,0.05);
        }

        .left-panel {
          width: 50%;
          background: #f8f7ff;
          padding: 40px;
          position: relative;
          overflow: hidden;
          display: flex;
          flex-direction: column;
        }

        .left-panel::before {
          content: "";
          position: absolute;
          width: 320px;
          height: 320px;
          right: -100px;
          top: -80px;
          background: radial-gradient(
            circle,
            rgba(99,102,241,0.10),
            transparent 70%
          );
          border-radius: 50%;
        }

        .left-panel::after {
          content: "";
          position: absolute;
          width: 200px;
          height: 200px;
          left: -60px;
          bottom: -50px;
          background: radial-gradient(
            circle,
            rgba(124,58,237,0.08),
            transparent 70%
          );
          border-radius: 50%;
        }

        .logo {
          display: flex;
          align-items: center;
          gap: 10px;
          margin-bottom: 55px;
          position: relative;
          z-index: 2;
        }

        .logo-icon {
          width: 44px;
          height: 44px;
          background: linear-gradient(
            135deg,
            #4F46E5,
            #7C3AED
          );
          color: white;
          border-radius: 12px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 23px;
          box-shadow:
            0 6px 12px rgba(79,70,229,0.25);
        }

        .logo-text {
          font-size: 26px;
          font-weight: 700;
        }

        .logo-task {
          color: #172033;
        }

        .logo-flow {
          color: #4F46E5;
        }

        .main-heading {
          position: relative;
          z-index: 2;
        }

        .main-heading h1 {
          font-size: 40px;
          line-height: 1.2;
          color: #172033;
          letter-spacing: -1px;
        }

        .main-heading h1 span {
          color: #4F46E5;
        }

        .description {
          color: #4b5563;
          font-size: 16px;
          line-height: 1.6;
          margin-top: 15px;
          margin-bottom: 38px;
          position: relative;
          z-index: 2;
        }

        .features {
          display: flex;
          flex-direction: column;
          gap: 22px;
          position: relative;
          z-index: 2;
        }

        .feature {
          display: flex;
          align-items: center;
          gap: 14px;
        }

        .feature-icon {
          width: 34px;
          height: 34px;
          border-radius: 10px;
          background: #eef0ff;
          color: #4F46E5;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 16px;
        }

        .feature h4 {
          color: #172033;
          font-size: 16px;
          margin-bottom: 3px;
        }

        .feature p {
          color: #6b7280;
          font-size: 13px;
        }

        .mini-board {
          display: flex;
          gap: 10px;
          margin-top: 45px;
          position: relative;
          z-index: 2;
          max-width: 310px;
        }

        .mini-column {
          flex: 1;
          padding: 10px;
          border-radius: 12px;
          background: rgba(255,255,255,0.8);
        }

        .mini-column-title {
          text-align: center;
          color: #6b7280;
          font-size: 10px;
          margin-bottom: 7px;
        }

        .mini-card {
          height: 12px;
          background: #eef0ff;
          border-radius: 5px;
          margin-bottom: 6px;
        }

        .mini-card.purple {
          background: #a78bfa;
          width: 75%;
        }

        .mini-card.blue {
          background: #818cf8;
          width: 55%;
        }

        .plant {
          position: absolute;
          bottom: 30px;
          right: 35px;
          font-size: 45px;
          opacity: 0.13;
          color: #4F46E5;
        }

        .right-panel {
          width: 50%;
          background: white;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 45px;
          overflow-y: auto;
        }

        .form-card {
          width: 100%;
          max-width: 440px;
        }

        .welcome h2 {
          color: #172033;
          font-size: 30px;
          margin-bottom: 5px;
        }

        .welcome p {
          color: #6b7280;
          font-size: 15px;
          margin-bottom: 28px;
        }

        .form-group {
          margin-bottom: 17px;
        }

        .form-group label {
          display: block;
          color: #172033;
          font-size: 14px;
          font-weight: 600;
          margin-bottom: 6px;
        }

        .input-box {
          position: relative;
        }

        .input-icon {
          position: absolute;
          left: 15px;
          top: 50%;
          transform: translateY(-50%);
          color: #9ca3af;
          font-size: 17px;
          z-index: 2;
        }

        .input-box input,
        .input-box select {
          width: 100%;
          height: 50px;
          border: 1.5px solid #e5e7eb;
          border-radius: 16px;
          outline: none;
          background: #f9faff;
          color: #172033;
          font-size: 15px;
          padding: 0 45px;
          transition: 0.2s;
        }

        .input-box select {
          appearance: auto;
          padding-right: 15px;
        }

        .input-box input:focus,
        .input-box select:focus {
          border-color: #6366F1;
          background: white;
          box-shadow:
            0 0 0 4px rgba(99,102,241,0.08);
        }

        .password-button {
          position: absolute;
          right: 12px;
          top: 50%;
          transform: translateY(-50%);
          border: none;
          background: transparent;
          color: #9ca3af;
          cursor: pointer;
          font-size: 17px;
          padding: 5px;
        }

        .password-button:hover {
          color: #4F46E5;
        }

        .remember-row {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin: 12px 0 20px;
        }

        .remember {
          display: flex;
          gap: 7px;
          align-items: center;
          color: #374151;
          font-size: 14px;
        }

        .remember input {
          width: 17px;
          height: 17px;
          accent-color: #4F46E5;
        }

        .forgot {
          border: none;
          background: transparent;
          color: #4F46E5;
          cursor: pointer;
          font-size: 14px;
          font-weight: 500;
        }

        .main-button {
          width: 100%;
          height: 52px;
          border: none;
          border-radius: 40px;
          background: linear-gradient(
            135deg,
            #4F46E5,
            #7C3AED
          );
          color: white;
          font-size: 16px;
          font-weight: 600;
          cursor: pointer;
          box-shadow:
            0 8px 18px rgba(79,70,229,0.25);
          transition: 0.15s;
        }

        .main-button:hover {
          transform: scale(1.02);
          box-shadow:
            0 12px 24px rgba(79,70,229,0.30);
        }

        .main-button:active {
          transform: scale(0.98);
        }

        .error {
          color: #dc2626;
          font-size: 13px;
          margin-top: 6px;
        }

        .strength {
          display: flex;
          gap: 6px;
          margin-top: 8px;
          align-items: center;
        }

        .strength-bar {
          flex: 1;
          height: 4px;
          background: #e5e7eb;
          border-radius: 5px;
        }

        .strength-bar.active.weak {
          background: #f87171;
        }

        .strength-bar.active.medium {
          background: #fbbf24;
        }

        .strength-bar.active.strong {
          background: #34d399;
        }

        .strength-label {
          font-size: 12px;
          color: #6b7280;
          min-width: 48px;
        }

        .divider {
          display: flex;
          align-items: center;
          gap: 12px;
          color: #9ca3af;
          font-size: 13px;
          margin: 25px 0 19px;
        }

        .divider::before,
        .divider::after {
          content: "";
          height: 1px;
          background: #e5e7eb;
          flex: 1;
        }

        .social-buttons {
          display: flex;
          gap: 10px;
        }

        .social {
          flex: 1;
          height: 44px;
          border: 1.5px solid #e5e7eb;
          border-radius: 30px;
          background: #fafaff;
          cursor: pointer;
          color: #172033;
          font-weight: 500;
        }

        .social:hover {
          border-color: #4F46E5;
          background: #f5f3ff;
        }

        .switch-row {
          text-align: center;
          color: #4b5563;
          font-size: 15px;
          margin-top: 22px;
        }

        .switch-button {
          border: none;
          background: transparent;
          color: #4F46E5;
          font-weight: 700;
          cursor: pointer;
          font-size: 15px;
          margin-left: 4px;
        }

        .switch-button:hover {
          text-decoration: underline;
        }

        .demo-login {
          margin-top: 14px;
          padding: 10px 12px;
          border-radius: 12px;
          background: #f5f3ff;
          color: #4f46e5;
          font-size: 13px;
          text-align: center;
        }

        @media (max-width: 1024px) {
          .auth-container {
            flex-direction: column;
            border-radius: 32px;
          }

          .left-panel,
          .right-panel {
            width: 100%;
          }

          .left-panel {
            min-height: 500px;
          }

          .right-panel {
            padding: 40px 28px;
          }
        }

        @media (max-width: 640px) {
          .auth-page {
            padding: 10px;
          }

          .auth-container {
            border-radius: 24px;
          }

          .left-panel {
            padding: 28px 22px;
            min-height: 430px;
          }

          .right-panel {
            padding: 30px 20px;
          }

          .main-heading h1 {
            font-size: 29px;
          }

          .logo {
            margin-bottom: 35px;
          }

          .social-buttons {
            flex-direction: column;
          }
        }
      `}</style>

      <div className="auth-page">

        <div className="auth-container">

          {/* ================= LEFT PANEL ================= */}

          <div className="left-panel">

            <div className="logo">

              <div className="logo-icon">
                ✓
              </div>

              <div className="logo-text">
                <span className="logo-task">
                  Task
                </span>

                <span className="logo-flow">
                  Flow
                </span>
              </div>

            </div>

            {page === "login" ? (
              <>
                <div className="main-heading">
                  <h1>
                    Manage Tasks.
                    <br />
                    Track Progress.
                    <br />
                    <span>Achieve More.</span>
                  </h1>
                </div>

                <p className="description">
                  TaskFlow helps teams organize projects,
                  <br />
                  collaborate in real time and get work
                  <br />
                  done efficiently.
                </p>

                <div className="features">

                  <div className="feature">
                    <div className="feature-icon">
                      ♟
                    </div>

                    <div>
                      <h4>
                        Real-Time Collaboration
                      </h4>

                      <p>
                        Work together with your team in real time.
                      </p>
                    </div>
                  </div>

                  <div className="feature">
                    <div className="feature-icon">
                      ▦
                    </div>

                    <div>
                      <h4>
                        Kanban Board
                      </h4>

                      <p>
                        Visualize tasks and track progress easily.
                      </p>
                    </div>
                  </div>

                  <div className="feature">
                    <div className="feature-icon">
                      ♢
                    </div>

                    <div>
                      <h4>
                        Smart Notifications
                      </h4>

                      <p>
                        Stay updated with important updates.
                      </p>
                    </div>
                  </div>

                </div>

                <div className="mini-board">

                  <div className="mini-column">
                    <div className="mini-column-title">
                      To Do
                    </div>

                    <div className="mini-card purple"></div>
                    <div className="mini-card"></div>
                    <div className="mini-card blue"></div>
                  </div>

                  <div className="mini-column">
                    <div className="mini-column-title">
                      Progress
                    </div>

                    <div className="mini-card blue"></div>
                    <div className="mini-card purple"></div>
                    <div className="mini-card"></div>
                  </div>

                  <div className="mini-column">
                    <div className="mini-column-title">
                      Done
                    </div>

                    <div className="mini-card"></div>
                    <div className="mini-card blue"></div>
                    <div className="mini-card purple"></div>
                  </div>

                </div>
              </>
            ) : (
              <>
                <div className="main-heading">
                  <h1>
                    Create your account
                    <br />
                    <span>and get started!</span>
                  </h1>
                </div>

                <p className="description">
                  Join TaskFlow and organize your projects,
                  <br />
                  manage tasks and collaborate with your
                  <br />
                  team in real time.
                </p>

                <div className="mini-board">

                  <div className="mini-column">
                    <div className="mini-column-title">
                      To Do
                    </div>

                    <div className="mini-card purple"></div>
                    <div className="mini-card blue"></div>
                  </div>

                  <div className="mini-column">
                    <div className="mini-column-title">
                      In Progress
                    </div>

                    <div className="mini-card blue"></div>
                    <div className="mini-card purple"></div>
                  </div>

                  <div className="mini-column">
                    <div className="mini-column-title">
                      Done
                    </div>

                    <div className="mini-card purple"></div>
                    <div className="mini-card blue"></div>
                  </div>

                </div>
              </>
            )}

            <div className="plant">
              ♧
            </div>

          </div>

          {/* ================= RIGHT PANEL ================= */}

          <div className="right-panel">

            {page === "login" ? (

              <div className="form-card">

                <div className="welcome">

                  <h2>
                    Welcome Back! 👋
                  </h2>

                  <p>
                    Login to your account and continue
                  </p>

                </div>

                <form onSubmit={handleLogin}>

                  <div className="form-group">

                    <label>
                      Email Address
                    </label>

                    <div className="input-box">

                      <span className="input-icon">
                        ✉
                      </span>

                      <input
                        type="email"
                        value={loginEmail}
                        onChange={(e) => {
                          setLoginEmail(e.target.value);
                          setLoginError("");
                        }}
                        placeholder="you@example.com"
                      />

                    </div>

                  </div>

                  <div className="form-group">

                    <label>
                      Password
                    </label>

                    <div className="input-box">

                      <span className="input-icon">
                        🔒
                      </span>

                      <input
                        type={
                          showLoginPassword
                            ? "text"
                            : "password"
                        }
                        value={loginPassword}
                        onChange={(e) => {
                          setLoginPassword(e.target.value);
                          setLoginError("");
                        }}
                        placeholder="Enter your password"
                      />

                      <button
                        type="button"
                        className="password-button"
                        onClick={() =>
                          setShowLoginPassword(
                            !showLoginPassword
                          )
                        }
                      >
                        {showLoginPassword ? "◉" : "◌"}
                      </button>

                    </div>

                  </div>

                  <div className="remember-row">

                    <label className="remember">

                      <input type="checkbox" />

                      Remember me

                    </label>

                    <button
                      type="button"
                      className="forgot"
                      onClick={() =>
                        alert(
                          "Password reset feature will be added later."
                        )
                      }
                    >
                      Forgot password?
                    </button>

                  </div>

                  {loginError && (
                    <div className="error">
                      {loginError}
                    </div>
                  )}

                  <button
                    type="submit"
                    className="main-button"
                  >
                    Login
                  </button>

                </form>

                <div className="demo-login">
                  Demo Login: admin@taskflow.com / 123456
                </div>

                <div className="divider">
                  Or continue with
                </div>

                <div className="social-buttons">

                  <button className="social">
                    Google
                  </button>

                  <button className="social">
                    Microsoft
                  </button>

                  <button className="social">
                    GitHub
                  </button>

                </div>

                <div className="switch-row">

                  Don't have an account?

                  <button
                    className="switch-button"
                    onClick={() => {
                      setLoginError("");
                      setPage("signup");
                    }}
                  >
                    Register
                  </button>

                </div>

              </div>

            ) : (

              <div className="form-card">

                <div className="welcome">

                  <h2>
                    Create your account
                  </h2>

                  <p>
                    Fill in the details below to create your account
                  </p>

                </div>

                <form onSubmit={handleSignup}>

                  <div className="form-group">

                    <label>
                      Full Name
                    </label>

                    <div className="input-box">

                      <span className="input-icon">
                        ●
                      </span>

                      <input
                        type="text"
                        value={fullName}
                        onChange={(e) => {
                          setFullName(e.target.value);

                          setSignupErrors({
                            ...signupErrors,
                            fullName: "",
                          });
                        }}
                        placeholder="Enter your full name"
                      />

                    </div>

                    {signupErrors.fullName && (
                      <div className="error">
                        {signupErrors.fullName}
                      </div>
                    )}

                  </div>

                  <div className="form-group">

                    <label>
                      Email Address
                    </label>

                    <div className="input-box">

                      <span className="input-icon">
                        ✉
                      </span>

                      <input
                        type="email"
                        value={signupEmail}
                        onChange={(e) => {
                          setSignupEmail(e.target.value);

                          setSignupErrors({
                            ...signupErrors,
                            email: "",
                          });
                        }}
                        placeholder="you@example.com"
                      />

                    </div>

                    {signupErrors.email && (
                      <div className="error">
                        {signupErrors.email}
                      </div>
                    )}

                  </div>

                  <div className="form-group">

                    <label>
                      Password
                    </label>

                    <div className="input-box">

                      <span className="input-icon">
                        🔒
                      </span>

                      <input
                        type={
                          showSignupPassword
                            ? "text"
                            : "password"
                        }
                        value={signupPassword}
                        onChange={(e) => {
                          setSignupPassword(e.target.value);

                          setSignupErrors({
                            ...signupErrors,
                            password: "",
                          });
                        }}
                        placeholder="Create a strong password"
                      />

                      <button
                        type="button"
                        className="password-button"
                        onClick={() =>
                          setShowSignupPassword(
                            !showSignupPassword
                          )
                        }
                      >
                        {showSignupPassword ? "◉" : "◌"}
                      </button>

                    </div>

                    <div className="strength">

                      <span
                        className={`strength-bar ${
                          strength === "weak" ||
                          strength === "medium" ||
                          strength === "strong"
                            ? `active ${strength}`
                            : ""
                        }`}
                      ></span>

                      <span
                        className={`strength-bar ${
                          strength === "medium" ||
                          strength === "strong"
                            ? `active ${strength}`
                            : ""
                        }`}
                      ></span>

                      <span
                        className={`strength-bar ${
                          strength === "strong"
                            ? `active ${strength}`
                            : ""
                        }`}
                      ></span>

                      <span className="strength-label">
                        {strength === "strong"
                          ? "Strong"
                          : strength === "medium"
                          ? "Medium"
                          : strength === "weak"
                          ? "Weak"
                          : ""}
                      </span>

                    </div>

                    {signupErrors.password && (
                      <div className="error">
                        {signupErrors.password}
                      </div>
                    )}

                  </div>

                  <div className="form-group">

                    <label>
                      Confirm Password
                    </label>

                    <div className="input-box">

                      <span className="input-icon">
                        🔒
                      </span>

                      <input
                        type={
                          showConfirmPassword
                            ? "text"
                            : "password"
                        }
                        value={confirmPassword}
                        onChange={(e) => {
                          setConfirmPassword(e.target.value);

                          setSignupErrors({
                            ...signupErrors,
                            confirm: "",
                          });
                        }}
                        placeholder="Confirm your password"
                      />

                      <button
                        type="button"
                        className="password-button"
                        onClick={() =>
                          setShowConfirmPassword(
                            !showConfirmPassword
                          )
                        }
                      >
                        {showConfirmPassword ? "◉" : "◌"}
                      </button>

                    </div>

                    {signupErrors.confirm && (
                      <div className="error">
                        {signupErrors.confirm}
                      </div>
                    )}

                  </div>

                  <div className="form-group">

                    <label>
                      Role
                    </label>

                    <div className="input-box">

                      <span className="input-icon">
                        ♟
                      </span>

                      <select
                        value={role}
                        onChange={(e) => {
                          setRole(e.target.value);

                          setSignupErrors({
                            ...signupErrors,
                            role: "",
                          });
                        }}
                      >
                        <option value="">
                          Select your role
                        </option>

                        <option value="Admin">
                          Admin
                        </option>

                        <option value="Manager">
                          Manager
                        </option>

                        <option value="Developer">
                          Developer
                        </option>

                        <option value="Designer">
                          Designer
                        </option>

                        <option value="Team Member">
                          Team Member
                        </option>

                      </select>

                    </div>

                    {signupErrors.role && (
                      <div className="error">
                        {signupErrors.role}
                      </div>
                    )}

                  </div>

                  <button
                    type="submit"
                    className="main-button"
                  >
                    Create Account
                  </button>

                </form>

                <div className="divider">
                  Or continue with
                </div>

                <div className="social-buttons">

                  <button className="social">
                    Google
                  </button>

                  <button className="social">
                    Microsoft
                  </button>

                  <button className="social">
                    GitHub
                  </button>

                </div>

                <div className="switch-row">

                  Already have an account?

                  <button
                    className="switch-button"
                    onClick={() => {
                      setSignupErrors({
                        fullName: "",
                        email: "",
                        password: "",
                        confirm: "",
                        role: "",
                      });

                      setPage("login");
                    }}
                  >
                    Login
                  </button>

                </div>

              </div>

            )}

          </div>

        </div>

      </div>
    </>
  );
}

export default App;