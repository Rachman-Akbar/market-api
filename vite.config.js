import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import path from "node:path";
import { fileURLToPath } from "node:url";

const currentDir = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), "");
  const backendTarget =
    env.VITE_DEV_BACKEND_TARGET || "http://127.0.0.1:8000";
  const reverbTarget =
    env.VITE_DEV_REVERB_TARGET || "http://127.0.0.1:8080";

  return {
    plugins: [react(), tailwindcss()],
    resolve: {
      alias: {
        "@": path.resolve(currentDir, "src"),
      },
    },
    server: {
      host: "127.0.0.1",
      port: 5173,
      strictPort: true,
      proxy: {
        "/api": {
          target: backendTarget,
          changeOrigin: true,
          secure: false,
        },
        "/storage": {
          target: backendTarget,
          changeOrigin: true,
          secure: false,
        },
        "/app": {
          target: reverbTarget,
          changeOrigin: true,
          secure: false,
          ws: true,
        },
      },
    },
  };
});
