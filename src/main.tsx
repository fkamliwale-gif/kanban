import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App";
import "./index.css";
import { enableBackendSync, hydrateFromMySQL } from "./backendSync";

async function startApp() {
  await hydrateFromMySQL();
  enableBackendSync();
  ReactDOM.createRoot(document.getElementById("root")!).render(
    <React.StrictMode>
      <App />
    </React.StrictMode>
  );
}

startApp();
