#!/bin/bash
set -e

ENVIRONMENT=${1:-production}
PROJECT_NAME="board-yet"
DOCKER_IMAGE="${PROJECT_NAME}:latest"

echo "🚀 Starting deployment to ${ENVIRONMENT}..."

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

if [[ $EUID -eq 0 ]]; then
    print_warning "Running as root. Consider using a non-root user with Docker permissions."
fi

if ! docker info > /dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker and try again."
    exit 1
fi

# Check available disk space (minimum 2GB required)
AVAILABLE_SPACE=$(df / | awk 'NR==2 {print $4}')
if [ "$AVAILABLE_SPACE" -lt 2097152 ]; then
    print_error "Insufficient disk space. At least 2GB required, but only $(($AVAILABLE_SPACE / 1024))MB available."
    exit 1
fi
print_status "Disk space check passed: $(($AVAILABLE_SPACE / 1024 / 1024))GB available"

if [ ! -f ".env" ]; then
    print_warning ".env file not found. Creating one from production.env.example..."
    cp production.env.example .env
    print_status "Created .env file from production.env.example"
    print_warning "Please update .env with your production values before running again"
    print_warning "Key values to update: APP_KEY, DB_PASSWORD, MAIL_USERNAME, MAIL_PASSWORD"
    exit 1
fi

if [ -d "current" ]; then
    print_status "Backing up current deployment..."
    if [ -d "backup" ]; then
        print_status "Removing old backup directory..."
        sudo rm -rf backup
    fi
    mv current backup
fi

mkdir -p current

print_status "Building Docker image..."
docker build -f Dockerfile.production -t $DOCKER_IMAGE .

print_status "Stopping existing containers..."
docker-compose -f docker-compose.production.yml down || true

print_status "Copying production configuration..."
cp docker-compose.production.yml current/
cp -r docker current/
cp .env current/.env

# Copy the entire application to current directory
print_status "Copying application files..."
cp -r app current/
cp -r bootstrap current/
cp -r config current/
cp -r database current/
cp -r public current/
cp -r resources current/
cp -r routes current/
cp -r storage current/
cp -r tests current/
cp artisan current/
cp composer.json current/
cp composer.lock current/
cp package.json current/
cp phpunit.xml current/
cp vite.config.js current/
cp .gitignore current/

print_status "Installing dependencies..."

cd current
print_status "Cleaning up existing dependencies..."
sudo rm -rf vendor composer.lock

print_status "Installing Composer dependencies..."
for attempt in {1..3}; do
    print_status "Composer install attempt $attempt/3..."
    if docker run --rm \
        -v $(pwd):/var/www \
        -v ~/.composer/cache:/tmp/composer-cache \
        --workdir /var/www \
        board-yet:latest \
        composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-suggest; then
        print_status "Dependencies installed successfully!"
        break
    else
        print_warning "Composer install attempt $attempt failed"
        if [ $attempt -eq 3 ]; then
            print_error "All Composer install attempts failed. Check disk space and network connectivity."
            print_error "Current disk usage:"
            df -h
            exit 1
        fi
        print_status "Retrying in 5 seconds..."
        sleep 5
    fi
done
cd ..

if [ ! -f "current/.env" ]; then
    print_error "Failed to copy .env file to current directory"
    exit 1
fi

if ! grep -q "DB_DATABASE=" current/.env; then
    print_error ".env file is missing DB_DATABASE variable"
    exit 1
fi

print_status "Environment file copied successfully"

mkdir -p ssl

if [ ! -f "ssl/cert.pem" ] || [ ! -f "ssl/key.pem" ]; then
    print_warning "SSL certificates not found in ssl/ directory."
    print_warning "Please add your SSL certificates:"
    print_warning "  - ssl/cert.pem (your SSL certificate)"
    print_warning "  - ssl/key.pem (your private key)"
    print_warning "Continuing without SSL (HTTP only)..."
    
    if [ ! -f "ssl/cert.pem" ]; then
        print_status "Creating self-signed certificate for development..."
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -keyout ssl/key.pem \
            -out ssl/cert.pem \
            -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
    fi
fi

print_status "Starting services..."
cd current
if [ ! -f ".env" ]; then
    print_error ".env file not found in current directory. This is required for docker-compose."
    exit 1
fi
docker-compose -f docker-compose.production.yml up -d

print_status "Waiting for services to be healthy..."
sleep 10

if ! docker-compose -f docker-compose.production.yml ps | grep -q "Up"; then
    print_error "Services failed to start. Check logs with: docker-compose -f docker-compose.production.yml logs"
    exit 1
fi

print_status "Checking database connectivity..."
for i in {1..30}; do
    if docker-compose -f docker-compose.production.yml exec -T database pg_isready -U ${DB_USERNAME:-board_yet_user} -d ${DB_DATABASE:-board_yet_production} > /dev/null 2>&1; then
        print_status "Database is ready!"
        break
    fi
    if [ $i -eq 30 ]; then
        print_error "Database connection timeout after 30 attempts. Check database container logs."
        docker-compose -f docker-compose.production.yml logs database
        print_error "Current container status:"
        docker-compose -f docker-compose.production.yml ps
        exit 1
    fi
    print_status "Waiting for database connection... (attempt $i/30)"
    sleep 2
done

print_status "Running database migrations..."
docker-compose -f docker-compose.production.yml exec -T app php artisan migrate --force

print_status "Clearing application caches..."
docker-compose -f docker-compose.production.yml exec -T app php artisan cache:clear
docker-compose -f docker-compose.production.yml exec -T app php artisan config:clear
docker-compose -f docker-compose.production.yml exec -T app php artisan route:clear

print_status "Optimizing application for production..."
docker-compose -f docker-compose.production.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.production.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.production.yml exec -T app php artisan view:cache

print_status "Setting proper permissions..."
docker-compose -f docker-compose.production.yml exec -T app chown -R www-data:www-data /var/www/storage
docker-compose -f docker-compose.production.yml exec -T app chown -R www-data:www-data /var/www/bootstrap/cache

print_status "Performing health check..."
if curl -f http://localhost/api/health > /dev/null 2>&1; then
    print_status "✅ Application is healthy and responding!"
else
    print_warning "⚠️  Health check failed. Application may not be fully ready yet."
fi

print_status "🎉 Deployment completed successfully!"
print_status "Your application should be available at:"
print_status "  - HTTP:  http://api.boardyet.com"
print_status "  - HTTPS: https://api.boardyet.com (if SSL certificates are configured)"

print_status "Useful commands:"
print_status "  - View logs: docker-compose -f docker-compose.production.yml logs -f"
print_status "  - Stop services: docker-compose -f docker-compose.production.yml down"
print_status "  - Restart services: docker-compose -f docker-compose.production.yml restart"
print_status "  - Access app container: docker-compose -f docker-compose.production.yml exec app bash"
