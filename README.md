# ksnmusic

A modern personal music and audio streaming web platform.

## Features

- **Audio Streaming**: Stream your audio collection in browser with high performance and seamless playback.
- **Library Management**: Organize and browse music by songs, artists, albums, genres, and folders.
- **Playlists & Favorites**: Create custom playlists, smart playlists, favorites, and track play statistics.
- **Audio Transcoding**: Transcode audio formats on-the-fly for smooth playback across devices.
- **Radio & Podcasts**: Support for streaming live radio stations and podcasts.
- **Modern UI**: Fast, responsive single-page interface powered by Vue 3, Tailwind CSS, and Vite.
- **AI & Integrations**: Music metadata enrichment, lyrics, and smart playlist capabilities.

## Tech Stack

- **Backend**: [Laravel 13](https://laravel.com/) (PHP 8.3+)
- **Frontend**: [Vue 3](https://vuejs.org/), TypeScript, [Tailwind CSS](https://tailwindcss.com/), [Vite+](https://vite.dev/)
- **Database**: MySQL, PostgreSQL, SQLite, or MariaDB
- **Queue & Realtime**: Laravel Queues, WebSocket / Pusher support

## Requirements

- PHP >= 8.3 with extensions (`gd`, `fileinfo`, `exif`, `json`, `SimpleXML`)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) (>= 20.19.0 or >= 22.12.0) & [pnpm](https://pnpm.io/)
- Database server (MySQL / PostgreSQL / SQLite)
- (Optional) `ffmpeg` for on-the-fly audio transcoding

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/Seavik2142/ksnmusic.git
cd ksnmusic
```

### 2. Install dependencies

```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
pnpm install
```

### 3. Environment configuration

Copy the example environment file and configure your database and app settings:

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), application URL (`APP_URL`), and music media path (`MEDIA_PATH`).

### 4. Database Setup & Initialization

Run migrations to set up the database schema:

```bash
php artisan migrate
```

Alternatively, initialize and configure the application with the interactive setup script:

```bash
composer koel:init
```

### 5. Running the Application

To start the full development environment (Laravel web server, background queue worker, and Vite hot reload) concurrently:

```bash
composer dev
```

The application will be accessible at:
- **Web App**: `http://localhost:8000`
- **Vite Dev Server**: `http://localhost:5173`

To build the frontend assets for production:

```bash
pnpm run build
```

## Testing & Code Quality

```bash
# Run tests
composer test

# Run linter and code formatting
composer lint
composer cs:fix
```

## License

This project is open-source software licensed under the [MIT license](LICENSE.md).
