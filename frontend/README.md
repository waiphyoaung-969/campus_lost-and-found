# Campus Loop frontend

Use Node.js 24 LTS. Start the [Laravel backend](../backend/README.md) in a separate terminal. From this directory:

```sh
npm ci
npm run dev
```

Open `http://localhost:5173`. The app defaults to `http://localhost:8000` for the API. To configure the base URL, copy `.env.example` to `.env.local` and set `VITE_API_URL`. Vite exposes this value to the browser, so keep provider credentials in the backend environment.

This scaffold checks the current session and supports sign-out. Sign-in with an eligible AIU Microsoft account is pending backend OAuth integration.

Run `npm run build` for TypeScript checking and the production bundle, or `npm run lint` for lint checks. In PowerShell environments that block `npm.ps1`, use `npm.cmd`.
