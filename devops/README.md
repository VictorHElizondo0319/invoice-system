# DevOps Documentation

This directory contains documentation and configurations for deploying and monitoring the Invoice System.

## Contents

- Docker configurations (see root `docker-compose.yml`)
- GitHub Actions CI/CD pipeline (see `.github/workflows/ci-cd.yml`)
- Deployment guides
- Monitoring setup

## Docker Deployment

### Prerequisites

- Docker 20.10+
- Docker Compose 2.0+

### Quick Start

```bash
# Clone repository
git clone <repository-url>
cd <repository-name>

# Start all services
docker compose up -d

# Check status
docker compose ps

# View logs
docker compose logs -f

# Stop services
docker compose down
```

## CI/CD Pipeline

The project uses GitHub Actions for continuous integration and deployment.

### Pipeline Stages

1. **Backend Tests**
   - PHP 8.2 setup
   - Composer dependency installation
   - PHPUnit test execution
   - PostgreSQL service container

2. **Frontend Build**
   - Node.js 18 setup
   - npm dependency installation
   - ESLint checks
   - Production build

3. **Docker Build**
   - Multi-stage Docker builds
   - Image caching for faster builds
   - Docker Compose validation

4. **Health Checks**
   - Service startup verification
   - Backend API health check
   - Frontend accessibility check

### Required Secrets

For Docker Hub push (optional):
- `DOCKER_USERNAME`: Docker Hub username
- `DOCKER_PASSWORD`: Docker Hub password/token

## Health Monitoring

### Application Health Check

Endpoint: `GET /api/health`

Response includes:
- Overall service status
- Database connectivity
- Application version info
- Timestamp

### Container Health Checks

All containers have built-in health checks:

**Backend:**
- Command: `curl -f http://localhost:8000/api/health`
- Interval: 30s
- Timeout: 10s
- Retries: 3

**Frontend:**
- Command: `curl -f http://localhost:80`
- Interval: 30s
- Timeout: 10s
- Retries: 3

**Database:**
- Command: `pg_isready -U invoiceuser -d invoice_system`
- Interval: 10s
- Timeout: 5s
- Retries: 5

## Logging

### Backend Logs

Laravel logs are stored in:
- Container: `/var/www/storage/logs/laravel.log`
- Format: JSON (for structured logging)

View logs:
```bash
docker compose logs backend -f
```

### Frontend Logs

Nginx access and error logs:
```bash
docker compose logs frontend -f
```

## Production Deployment Recommendations

### 1. Environment Variables

Use proper secrets management:
- AWS Secrets Manager
- HashiCorp Vault
- Kubernetes Secrets

### 2. Database Backups

Set up automated backups:
```bash
# Example backup script
docker compose exec db pg_dump -U invoiceuser invoice_system > backup.sql
```

### 3. SSL/TLS

Add HTTPS support:
- Use Let's Encrypt certificates
- Configure Nginx with SSL
- Update CORS settings

### 4. Monitoring

Implement comprehensive monitoring:
- **APM**: New Relic, Datadog
- **Logs**: ELK Stack, Splunk
- **Metrics**: Prometheus + Grafana
- **Uptime**: Pingdom, UptimeRobot

### 5. Scaling

For production scale:
- Use container orchestration (Kubernetes, ECS)
- Implement load balancing
- Add Redis for caching
- Use managed database (RDS, Cloud SQL)

### 6. Security

Additional security measures:
- Enable Laravel rate limiting
- Add WAF (Web Application Firewall)
- Implement DDoS protection
- Regular security audits

## Troubleshooting

### Backend won't start

1. Check database connectivity:
```bash
docker compose logs db
```

2. Verify environment variables:
```bash
docker compose config
```

3. Check migration status:
```bash
docker compose exec backend php artisan migrate:status
```

### Frontend can't connect to backend

1. Check backend health:
```bash
curl http://localhost:8000/api/health
```

2. Verify nginx configuration:
```bash
docker compose exec frontend cat /etc/nginx/conf.d/default.conf
```

3. Check CORS settings in `backend/config/cors.php`

### Database connection issues

1. Check PostgreSQL is running:
```bash
docker compose ps db
```

2. Test connection:
```bash
docker compose exec db psql -U invoiceuser -d invoice_system
```

3. Verify credentials in `.env` file

## Performance Optimization

### Backend

1. Enable OPcache in production
2. Use Laravel caching:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. Optimize autoloader:
   ```bash
   composer dump-autoload -o
   ```

### Frontend

1. Already optimized with Vite build
2. Gzip enabled in Nginx
3. Assets cached by browser

### Database

1. Add indexes for frequently queried columns
2. Use connection pooling
3. Regular VACUUM and ANALYZE

## Maintenance

### Update Dependencies

**Backend:**
```bash
cd backend
composer update
php artisan test
```

**Frontend:**
```bash
cd frontend
npm update
npm run build
```

### Database Migrations

Create new migration:
```bash
docker compose exec backend php artisan make:migration create_new_table
```

Run migrations:
```bash
docker compose exec backend php artisan migrate
```

Rollback:
```bash
docker compose exec backend php artisan migrate:rollback
```

## Disaster Recovery

### Backup Strategy

1. **Database**: Daily automated backups
2. **Code**: Git repository
3. **Environment**: Document all configurations

### Recovery Steps

1. Restore database from backup
2. Deploy latest code from repository
3. Run migrations if needed
4. Verify health checks pass

## Cost Optimization (Cloud Deployment)

1. Use auto-scaling for backend instances
2. Implement caching to reduce database load
3. Use CDN for frontend assets
4. Schedule non-critical tasks during off-peak hours
5. Right-size database instances based on metrics


