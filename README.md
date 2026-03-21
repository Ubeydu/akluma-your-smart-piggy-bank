# Akluma — Your Smart Piggy Bank

A savings goal tracker that helps users create virtual piggy banks, set targets, and build consistent saving habits. Live at [akluma.com](https://akluma.com).

## Features

- **Scheduled piggy banks** — set a target amount and date, choose a saving frequency, and get email reminders
- **Classic piggy banks** — simple, schedule-free piggy banks for manual deposits and withdrawals
- **Multi-currency** — supports currencies with different decimal rules (EUR, TRY, XOF, XAF, etc.)
- **Multi-language** — full localization in English, Turkish, and French with translated URLs
- **Vaults** — connect a real bank account to track progress alongside your piggy bank
- **Google login** — sign in with Google or email/password
- **PWA-ready** — installable on mobile devices

## Tech Stack

- **Framework:** Laravel 12
- **Frontend:** Blade, Tailwind CSS 4, Alpine.js 3
- **Build:** Vite 6
- **Database:** MySQL
- **Hosting:** Fly.io (staging + production)
- **CI/CD:** GitHub Actions
- **Email:** Postmark

## Local Development

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your database
3. Start Docker: `./vendor/bin/sail up -d`
   - First time? Run `docker run --rm -v $(pwd):/app composer install` first
4. `./vendor/bin/sail artisan key:generate`
5. `./vendor/bin/sail artisan migrate`
6. `npm install && npm run build`
7. Access at <http://localhost>

## Testing

```bash
./vendor/bin/sail pest              # all tests
./vendor/bin/sail pest tests/Feature # feature tests only
./vendor/bin/sail pest tests/Unit    # unit tests only
```

## Contributing

Akluma is a solo project right now, but contributions are welcome! If you're interested in helping out:

1. Browse the [open issues](https://github.com/Ubeydu/akluma-your-smart-piggy-bank/issues) for something that interests you
2. Fork the repo and create a feature branch from `dev`
3. Submit a PR to `dev` — keep it focused on a single change
4. Make sure `./vendor/bin/sail pest` passes before submitting

Questions or ideas? Open an issue and let's talk.

## License

[MIT License](LICENSE)
