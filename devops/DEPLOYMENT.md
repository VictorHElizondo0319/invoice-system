# Deployment Guide

Step-by-step guide for deploying the Invoice System.

## Local Development Deployment

### 1. Prerequisites Check

```bash
# Check PHP version (8.1+)
php --version

# Check Composer
composer --version

# Check Node.js (18+)
node --version

# Check npm
npm --version

# Check PostgreSQL
psql --version
```

### 2. Database Setup

```bash
# Create PostgreSQL database
createdb invoice_system

# Or using psql
psql -U postgres
CREATE DATABASE invoice_system;
CREATE USER invoiceuser WITH PASSWORD 'invoicepass';
GRANT ALL PRIVILEGES ON DATABASE invoice_system TO invoiceuser;
```

### 3. Backend Deployment

```bash
cd backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
# Edit .env with your database credentials

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start server
php artisan serve
```

Backend will be available at: http://localhost:8000

### 4. Frontend Deployment

```bash
cd frontend

# Install dependencies
npm install

# Setup environment
cp .env.example .env
# Edit VITE_API_URL if needed

# Start development server
npm run dev
```

Frontend will be available at: http://localhost:3000

## Docker Deployment (Development)

### 1. Setup

```bash
# Clone repository
git clone <repository-url>
cd <project-directory>

# Create environment files
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# Edit environment files if needed
```

### 2. Build and Start

```bash
# Build images
docker compose build

# Start all services
docker compose up -d

# Check status
docker compose ps

# View logs
docker compose logs -f
```

### 3. Verify Deployment

```bash
# Test backend health
curl http://localhost:8000/api/health

# Test frontend
curl http://localhost:3000

# Access in browser
open http://localhost:3000
```

### 4. Stop Services

```bash
# Stop all services
docker compose down

# Stop and remove volumes
docker compose down -v
```

## Production Deployment

### Option 1: Traditional Server

#### Requirements
- Ubuntu 20.04+ or similar Linux distribution
- Nginx
- PostgreSQL 14+
- PHP 8.2+ with FPM
- Node.js 18+
- Composer

#### Steps

**1. Install Dependencies**

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php8.2-fpm php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl -y

# Install PostgreSQL
sudo apt install postgresql postgresql-contrib -y

# Install Nginx
sudo apt install nginx -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**2. Setup Database**

```bash
sudo -u postgres psql
CREATE DATABASE invoice_system;
CREATE USER invoiceuser WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE invoice_system TO invoiceuser;
\q
```

**3. Deploy Backend**

```bash
# Clone repository
cd /var/www
sudo git clone <repository-url> invoice-system
cd invoice-system/backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Setup environment
cp .env.example .env
# Edit .env with production settings
nano .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**4. Deploy Frontend**

```bash
cd /var/www/invoice-system/frontend

# Install dependencies
npm ci

# Build production bundle
npm run build

# Copy build to nginx directory
sudo cp -r dist/* /var/www/html/invoice-system/
```

**5. Configure Nginx**

```bash
sudo nano /etc/nginx/sites-available/invoice-system
```

Add configuration:

```nginx
# Backend API
server {
    listen 8000;
    server_name your-domain.com;
    root /var/www/invoice-system/backend/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Frontend
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/invoice-system;
    
    index index.html;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    location /api {
        proxy_pass http://localhost:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/invoice-system /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

**6. Setup SSL (Optional but Recommended)**

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot --nginx -d your-domain.com
```

### Option 2: Docker on Production Server

**1. Install Docker**

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

**2. Deploy Application**

```bash
# Clone repository
git clone <repository-url>
cd <project-directory>

# Setup environment
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
# Edit .env files with production values

# Start services
docker compose -f docker-compose.yml up -d

# Check status
docker compose ps
```

**3. Setup Reverse Proxy**

Use Nginx or Traefik as reverse proxy for SSL termination and load balancing.

### Option 3: Cloud Platform (AWS Example)

**Services:**
- **EC2**: Application servers
- **RDS**: PostgreSQL database
- **S3**: Static assets
- **CloudFront**: CDN
- **ECS/EKS**: Container orchestration

**Basic Setup:**

1. Create RDS PostgreSQL instance
2. Launch EC2 instances
3. Deploy using Docker or traditional method
4. Configure Application Load Balancer
5. Setup CloudFront for frontend assets
6. Configure Route53 for DNS

## Post-Deployment

### 1. Verify Deployment

```bash
# Check backend health
curl https://your-domain.com/api/health

# Check frontend
curl https://your-domain.com

# Test API
curl https://your-domain.com/api/invoices
```

### 2. Setup Monitoring

```bash
# View application logs
sudo tail -f /var/www/invoice-system/backend/storage/logs/laravel.log

# View Nginx logs
sudo tail -f /var/log/nginx/error.log

# View system logs
sudo journalctl -u nginx -f
```

### 3. Setup Cron Jobs (if needed)

```bash
sudo crontab -e
```

Add Laravel scheduler:
```
* * * * * cd /var/www/invoice-system/backend && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Setup Backups

```bash
# Create backup script
sudo nano /usr/local/bin/backup-invoice-db.sh
```

Add:
```bash
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
pg_dump -U invoiceuser invoice_system > /backups/invoice_system_$TIMESTAMP.sql
find /backups -mtime +7 -delete
```

Make executable and schedule:
```bash
sudo chmod +x /usr/local/bin/backup-invoice-db.sh
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-invoice-db.sh
```

## Rollback Procedure

If deployment fails:

```bash
# Docker deployment
docker compose down
git checkout <previous-commit>
docker compose up -d

# Traditional deployment
cd /var/www/invoice-system
git checkout <previous-commit>
cd backend
php artisan migrate:rollback
composer install --no-dev
php artisan config:cache
sudo systemctl restart php8.2-fpm nginx
```

## Troubleshooting

### Issue: 500 Internal Server Error

```bash
# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# Check Laravel logs
tail -f /var/www/invoice-system/backend/storage/logs/laravel.log

# Check permissions
sudo chown -R www-data:www-data /var/www/invoice-system/backend/storage
```

### Issue: Database Connection Failed

```bash
# Test PostgreSQL connection
psql -U invoiceuser -d invoice_system -h localhost

# Check PostgreSQL logs
sudo tail -f /var/log/postgresql/postgresql-14-main.log

# Verify .env settings
cat /var/www/invoice-system/backend/.env | grep DB_
```

### Issue: Frontend Not Loading

```bash
# Check Nginx configuration
sudo nginx -t

# Check Nginx logs
sudo tail -f /var/log/nginx/error.log

# Verify build files exist
ls -la /var/www/html/invoice-system/
```

## Security Checklist

- [ ] Changed default database password
- [ ] Set APP_DEBUG=false in production
- [ ] Generated unique APP_KEY
- [ ] Enabled HTTPS/SSL
- [ ] Configured firewall (ufw)
- [ ] Setup fail2ban
- [ ] Regular security updates
- [ ] Restricted database access
- [ ] Configured CORS properly
- [ ] Setup rate limiting

## Performance Optimization

```bash
# Backend optimizations
cd backend
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload -o

# Enable OPcache
sudo nano /etc/php/8.2/fpm/php.ini
# opcache.enable=1
# opcache.memory_consumption=128

sudo systemctl restart php8.2-fpm
```

## Maintenance Mode

```bash
# Enable maintenance mode
cd /var/www/invoice-system/backend
php artisan down --message="Scheduled maintenance" --retry=60

# Disable maintenance mode
php artisan up
```


