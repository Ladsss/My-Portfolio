# Personal developer portfolio

A Vue 3, TypeScript, Vuetify, and SCSS portfolio frontend with a Laravel API for project listings and the contact form. It presents Reymart Jay DG. Epan's full-stack development and QA automation experience, including the Energy Resiliency Management System.

## Requirements

- Node.js 20.19+ or 22.12+
- PHP 8.3+ and Composer
- A mail provider only if you want the contact form to deliver email

## Run locally

Install frontend packages, then start both the Laravel API and Vite from the repository root:

```sh
npm --prefix frontend install
npm run dev
```

Install Laravel dependencies and prepare its environment once:

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
cd ..
```

The root `npm run dev` command starts Laravel on `http://127.0.0.1:8000`, waits for its health check, and then starts Vite. Press Ctrl+C to stop both servers. To use a separately started or hosted API instead, set `LARAVEL_API_URL` before running the command; in that case Laravel is not started automatically. No database is needed for the static portfolio or contact endpoint.

Optional frontend profile links can be set in `frontend/.env` after copying the example file:

```dotenv
VITE_GITHUB_URL=https://github.com/your-account
VITE_LINKEDIN_URL=https://www.linkedin.com/in/your-profile
VITE_CV_URL=/Reymart-Epan-CV.pdf
VITE_CONTACT_EMAIL=you@example.com
```

Add the CV file to `frontend/public` when ready. The profile, CV, and direct email links are only shown when configured; no profile URLs or contact details are assumed.

## Configure contact delivery

In `backend/.env`, set the recipient and Gmail SMTP credentials. Use a Google App Password (not your regular Google password), and set the sender and recipient to the Gmail account where you want to receive submissions. The API will not report success until email delivery is configured.

```dotenv
PORTFOLIO_CONTACT_EMAIL=your-gmail-address@gmail.com
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_SCHEME=smtp
MAIL_USERNAME=your-gmail-address@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_FROM_ADDRESS=your-gmail-address@gmail.com
MAIL_FROM_NAME="Your Portfolio"
```

Create the App Password in your Google Account security settings after enabling 2-Step Verification. If Google displays it in groups, remove the spaces when putting it in `.env`. Never share or commit the App Password; keep real values in the ignored `.env` file or your hosting provider's secret environment settings. After changing mail settings on a running deployment, clear Laravel's cached configuration (`php artisan config:clear` locally, or rebuild the configuration cache for production). For a separately hosted frontend and backend, set `FRONTEND_URL` in the backend to the frontend origin (for example `https://portfolio.example.com`) and `VITE_API_URL` in the frontend build to the backend origin (for example `https://api.example.com`). CORS permits the configured origin for API requests.

The contact API is `POST /api/contact` (the legacy `POST /api/v1/contact` endpoint remains available). It validates the submitted fields, rejects a filled honeypot field, and limits requests to five per minute. `GET /api/projects` returns the portfolio project listings; the project details are maintained in the API controller and do not require a database.

## Build and deploy

Build the frontend with `cd frontend && npm run build`; deploy the generated `frontend/dist` directory to any static host. Configure the host to serve `index.html` as the fallback for client-side routes. Deploy `backend` to a PHP 8.3+ Laravel host, set its production `.env` values and `APP_KEY`, and configure the mail provider and `FRONTEND_URL`. The API itself does not require MySQL or migrations.

Before publishing, configure the profile links, contact email, and CV URL in `frontend/.env`. Only include public, authorized code or demo URLs in the project listing.
