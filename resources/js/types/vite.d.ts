/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    readonly VITE_APP_ENV: string;
    readonly VITE_APP_URL: string;
    // Add other environment variables here
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
