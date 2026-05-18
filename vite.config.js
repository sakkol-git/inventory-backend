import { defineConfig } from 'vite';

export default defineConfig({
  // Backend-only API configuration
  // No frontend assets to build
  build: {
    // Disable the build since this is a backend API
    rollupOptions: {
      input: [],
    },
    emptyOutDir: false,
  },
});
