# Goblin

A Matrix-themed AI text adventure game built with Laravel 12, Livewire 4, and Google Gemini. Choose a character, make choices, and navigate a cinematic narrative through the Matrix universe — all powered by an AI game master.

## Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- A [Google Gemini API key](https://aistudio.google.com/apikey)

## Local Setup

```bash
# Clone the repo
git clone <repo-url> goblin
cd goblin

# Install dependencies, generate app key, run migrations, and build assets
composer setup

# Add your Gemini API key to .env
# GEMINI_API_KEY=your-key-here

# Start the dev server (serves app, queue worker, logs, and Vite)
composer run dev
```

The `composer setup` script handles `composer install`, `.env` creation, `php artisan key:generate`, `php artisan migrate`, `npm install`, and `npm run build` in one step.

Visit [http://goblin.test](http://goblin.test) (Laravel Herd) or [http://localhost:8000](http://localhost:8000).

## Deployment

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set `GEMINI_API_KEY` in your production environment. Run a queue worker to process AI requests:

```bash
php artisan queue:work
```

## Testing

```bash
php artisan test
```
