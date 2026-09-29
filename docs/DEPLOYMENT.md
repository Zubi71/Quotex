# OTC Signal Intelligence — Production Deployment Guide

## 1. Prerequisites

- **Docker**: Engine version 24.0+ and Docker Compose v2.20+
- **Host Specifications**:
  - Minimum: 2 vCPU, 4GB RAM, 20GB SSD
  - Recommended: 4 vCPU, 8GB RAM, 50GB SSD
- **Supported Operating Systems**: Linux (Ubuntu 22.04 LTS / Debian 12 / Alpine), Windows with WSL2, or macOS.

---

## 2. Quick Start: Local Development (Docker)

1. **Clone and Navigate**:
   ```bash
   cd otc-signal-intelligence
   ```

2. **Configure Environment Variables**:
   ```bash
   cp .env.example .env
   ```

3. **Start All Services**:
   ```bash
   docker compose up -d
   ```
   This orchestrates:
   - `mysql` on port `3306`
   - `redis` on port `6379`
   - `api` (PHP 8.3 FPM)
   - `web` (Next.js 14) on port `3000`
   - `nginx` on ports `80` (Web) and `8000` (API)
   - `reverb` (WebSocket server) on port `8080`
   - `worker` (Queue processor)
   - `scheduler` (Background cron runner)

4. **Run Migrations & Seeders**:
   ```bash
   docker compose exec api php artisan migrate --seed
   ```

5. **Access Application**:
   - Web Terminal: `http://localhost:3000` or `http://localhost`
   - API Backend: `http://localhost:8000/api/v1`
   - Default Demo Login: `trader@otcsignal.local` / `password123`

---

## 3. Production Deployment

1. **Configure Production Variables**:
   Set strong passwords in `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-trading-domain.com
   DB_PASSWORD=YourStrongDatabasePassword123!
   REDIS_PASSWORD=YourStrongRedisPassword123!
   ```

2. **Build and Launch with Production Compose**:
   ```bash
   docker compose -f docker-compose.prod.yml up -d --build
   ```

3. **Optimize Laravel Caches**:
   ```bash
   docker compose -f docker-compose.prod.yml exec api php artisan config:cache
   docker compose -f docker-compose.prod.yml exec api php artisan route:cache
   docker compose -f docker-compose.prod.yml exec api php artisan view:cache
   ```

---

## 4. Background Services & Daemons

The system relies on three background worker processes:
1. **Queue Worker**:
   `php artisan queue:work redis --queue=default,signals,backtests --sleep=1 --tries=3`
2. **Scheduled Task Engine**:
   `php artisan schedule:run`
   - Runs `UpdateCandleHistoryJob` every minute.
   - Runs `ResolveSignalOutcomesJob` every 10 seconds to score expiring binary trades.
3. **Laravel Reverb WebSocket Server**:
   `php artisan reverb:start --host=0.0.0.0 --port=8080`
