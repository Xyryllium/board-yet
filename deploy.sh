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

if [ ! -f ".env" ]; then
    print_error ".env file not found. Please create one based on production.env.example"
    exit 1
fi

if [ -d "backup" ]; then
    print_status "Creating backup of current deployment..."
    rm -rf backup
fi

if [ -d "current" ]; then
    print_status "Backing up current deployment..."
    mv current backup
fi

mkdir -p current

print_status "Building Docker image..."
docker build -f Dockerfile.production -t $DOCKER_IMAGE .

print_status "Stopping existing containers..."
docker-compose -f docker-compose.production.yml down || true

print_status "Copying production configuration..."
cp docker-compose.production.yml current/
cp production.env.example current/.env.example

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
docker-compose -f docker-compose.production.yml up -d

print_status "Waiting for services to be healthy..."
sleep 10

if ! docker-compose -f docker-compose.production.yml ps | grep -q "Up"; then
    print_error "Services failed to start. Check logs with: docker-compose -f docker-compose.production.yml logs"
    exit 1
fi

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
print_status "  - HTTP:  http://your-domain.com"
print_status "  - HTTPS: https://your-domain.com (if SSL certificates are configured)"

print_status "Useful commands:"
print_status "  - View logs: docker-compose -f docker-compose.production.yml logs -f"
print_status "  - Stop services: docker-compose -f docker-compose.production.yml down"
print_status "  - Restart services: docker-compose -f docker-compose.production.yml restart"
print_status "  - Access app container: docker-compose -f docker-compose.production.yml exec app bash"
