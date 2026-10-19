import { defineConfig } from "vite";
import path from "path";
import { fileURLToPath } from "url";
const __filename = fileURLToPath(import.meta.url);
//const __dirname = import.meta.dirname;
const __dirname = path.dirname(__filename);

const port = 5173;
const origin = process.env.DDEV_PRIMARY_URL;


export default defineConfig({
  base: "./",
  build: {
    sourcemap: true,
    manifest: true,
    rollupOptions: {
      input: {
        productComparison: path.resolve(__dirname, "./src/main.js"),
      },
      output: {
        manualChunks(id) {
          if (id.includes("node_modules/proj4")) {
            return "proj4"; // Place proj4 in its own chunk
          }
          if (id.includes("node_modules/preact")) {
            return "preact"; // Place preact in its own chunk
          }
        },

      },
      outDir: path.resolve(__dirname, "dist"), // Output to the module's dist directory
      emptyOutDir: true,
      sourcemap: true,
    },
  },
  server: {
    host: "0.0.0.0",
    port: port,
    origin: origin,
    cors: { origin },
    // ----------------
    host: '0.0.0.0',
    port: port,
    origin: `${origin}:${port}`,
    strictPort: true,
    allowedHosts: ["products-comparison.ddev.site", "localhost"],
  },

});
