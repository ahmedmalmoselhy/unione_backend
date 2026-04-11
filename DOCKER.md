# Docker Setup for UniOne Backend

This project is fully containerized with Docker, providing a complete development and production environment.

## 📦 What's Included

- **Laravel 12** application running on PHP 8.3-FPM
- **Nginx** web server
- **PostgreSQL 16** database
- **Redis 7** cache/session store
- **Queue Workers** (2 workers running in parallel)
- **Supervisor** for process management

## 🚀 Quick Start

### Prerequisites

- Docker Desktop installed and running
- Docker Compose (included with Docker Desktop)

### Development Mode

1. **Start all services:**
   ```bash
   docker-compose up -d
   ```

2. **Access the application:**
   - Web Application: http://localhost:8080
   - PostgreSQL: localhost:5433 (mapped from container's 5432)
   - Redis: localhost:6380 (mapped from container's 6379)

3. **View logs:**
   ```bash
   # All services
   docker-compose logs -f
   
   # Specific service
   docker-compose logs -f app
   docker-compose logs -f db
   docker-compose logs -f worker
   ```

4. **Stop services:**
   ```bash
   docker-compose down
   ```

### Production Mode

For production deployment:

```bash
docker-compose -f docker-compose.prod.yml up -d --build
```

## 📁 Docker Files Structure

```
├── Dockerfile                      # Main application image
├── docker-compose.yml              # Development environment
├── docker-compose.prod.yml         # Production environment
├── .dockerignore                   # Files to exclude from build
└── docker/
    ├── entrypoint.sh               # Container initialization script
    ├── nginx/
    │   └── default.conf            # Nginx configuration
    └── supervisor/
        └── supervisord.conf        # Supervisor process configuration
```

## 🔧 Common Commands

### Run Artisan Commands

```bash
# Enter the app container
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan make:model User
docker-compose exec app php artisan route:list
docker-compose exec app php artisan db:seed
```

### Database Operations

```bash
# Connect to PostgreSQL
docker-compose exec db psql -U unione_user -d unione_backend

# Import database
docker-compose exec -T db psql -U unione_user -d unione_backend < backup.sql

# Export database
docker-compose exec db pg_dump -U unione_user -d unione_backend > backup.sql
```

### Queue Management

```bash
# View queue worker logs
docker-compose logs -f worker

# Restart queue workers
docker-compose restart worker

# Scale workers (run more workers)
docker-compose up -d --scale worker=4
```

### Cache Management

```bash
# Clear all caches
docker-compose exec app php artisan optimize:clear

# Rebuild caches
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## 🌐 Port Mapping

| Service     | Host Port | Container Port | Description           |
|-------------|-----------|----------------|-----------------------|
| Nginx       | 8080      | 80             | Web application       |
| PostgreSQL  | 5433      | 5432           | Database              |
| Redis       | 6380      | 6379           | Cache/Session store   |

## 🔑 Environment Variables

The following environment variables are configured in docker-compose.yml:

```yaml
DB_HOST=db
DB_PORT=5432
DB_DATABASE=unione_backend
DB_USERNAME=unione_user
DB_PASSWORD=unione_password
```

To customize these, you can:
1. Create a `.env` file in the project root
2. Override in docker-compose.yml using `${VAR:-default}` syntax
3. Use docker-compose.override.yml for local development

## 🏗️ Architecture

```
┌─────────────────────────────────────────┐
│           Docker Network                 │
│                                          │
│  ┌────────────────────┐                 │
│  │   Nginx (Port 80)  │ ← Port 8080    │
│  └────────┬───────────┘                 │
│           │                              │
│  ┌────────▼───────────┐                 │
│  │  PHP 8.3-FPM       │                 │
│  │  (Port 9000)       │                 │
│  └────────┬───────────┘                 │
│           │                              │
│  ┌────────▼───────────┐    ┌──────────┐│
│  │  Queue Workers (2) │    │  Redis   ││
│  └────────────────────┘    └──────────┘│
│           │                              │
│  ┌────────▼───────────┐                 │
│  │   PostgreSQL 16    │ ← Port 5433    │
│  └────────────────────┘                 │
└─────────────────────────────────────────┘
```

## 🔍 Health Checks

All services have health checks configured:

- **PostgreSQL**: Checks database connectivity
- **App Container**: HTTP check on port 80
- **Worker**: Process health verification

View health status:
```bash
docker-compose ps
docker inspect --format='{{.State.Health.Status}}' unione_app
```

## 🐛 Troubleshooting

### Application Not Accessible

```bash
# Check if containers are running
docker-compose ps

# View app logs
docker-compose logs app

# Restart services
docker-compose restart app
```

### Database Connection Issues

```bash
# Check if PostgreSQL is healthy
docker-compose ps db

# View PostgreSQL logs
docker-compose logs db

# Test connection from app container
docker-compose exec app php -r "new PDO('pgsql:host=db;dbname=unione_backend', 'unione_user', 'unione_password');"
```

### Permission Issues

```bash
# Fix permissions inside container
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 755 storage bootstrap/cache
```

### Rebuild from Scratch

```bash
# Stop and remove everything
docker-compose down -v --rmi all

# Rebuild and start
docker-compose up -d --build
```

## 📝 Notes

- **Migrations**: Automatically run on first startup
- **Seeding**: Add seeders to entrypoint.sh if needed
- **Volumes**: Source code is mounted for hot-reloading in development
- **Optimization**: Production mode caches configuration and routes

## 🔐 Security

For production deployment:

1. Change default database passwords
2. Use `docker-compose.prod.yml`
3. Set `APP_DEBUG=false`
4. Enable HTTPS (use reverse proxy like Traefik)
5. Don't expose database ports publicly
6. Use Docker secrets for sensitive data

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Docker Best Practices](https://laravel.com/docs/deployment)
