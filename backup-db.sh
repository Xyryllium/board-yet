#!/bin/bash

set -e

BACKUP_DIR="/home/ubuntu/board-yet/backups"
DB_CONTAINER="board_yet_db"
DB_NAME="board_yet_production"
DB_USER="board_yet_user"
RETENTION_DAYS=7

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
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

mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="backup_${TIMESTAMP}.sql"
BACKUP_PATH="$BACKUP_DIR/$BACKUP_FILE"

print_status "Starting database backup..."

if ! docker ps | grep -q "$DB_CONTAINER"; then
    print_error "Database container '$DB_CONTAINER' is not running!"
    exit 1
fi

print_status "Creating database dump..."
if docker exec "$DB_CONTAINER" pg_dump -U "$DB_USER" -d "$DB_NAME" > "$BACKUP_PATH"; then
    print_status "Database dump created: $BACKUP_FILE"
else
    print_error "Failed to create database dump!"
    exit 1
fi

print_status "Compressing backup..."
if gzip "$BACKUP_PATH"; then
    BACKUP_FILE="${BACKUP_FILE}.gz"
    BACKUP_PATH="${BACKUP_PATH}.gz"
    print_status "Backup compressed: $BACKUP_FILE"
else
    print_error "Failed to compress backup!"
    exit 1
fi

BACKUP_SIZE=$(du -h "$BACKUP_PATH" | cut -f1)
print_status "Backup size: $BACKUP_SIZE"

print_status "Cleaning up old backups (keeping last $RETENTION_DAYS days)..."
OLD_BACKUPS=$(find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +$RETENTION_DAYS)

if [ -n "$OLD_BACKUPS" ]; then
    echo "$OLD_BACKUPS" | while read -r old_backup; do
        if [ -f "$old_backup" ]; then
            print_warning "Removing old backup: $(basename "$old_backup")"
            rm "$old_backup"
        fi
    done
else
    print_status "No old backups to remove"
fi

print_status "Backup completed successfully!"
print_status "Backup file: $BACKUP_FILE"
print_status "Backup size: $BACKUP_SIZE"
print_status "Backup location: $BACKUP_PATH"

# Optional: Upload to S3
# if command -v aws &> /dev/null; then
#     print_status "Uploading backup to S3..."
#     if aws s3 cp "$BACKUP_PATH" "s3://your-backup-bucket/database/" --storage-class STANDARD_IA; then
#         print_status "Backup uploaded to S3 successfully"
#     else
#         print_warning "Failed to upload backup to S3"
#     fi
# fi

print_status "Current backup directory usage:"
du -sh "$BACKUP_DIR"

print_status "Backup process completed!"
