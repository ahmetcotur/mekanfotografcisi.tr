#!/bin/sh
set -e

echo "Running database migrations..."

# Check if migrations directory exists
if [ ! -d "/app/migrations" ]; then
    echo "No migrations directory found, skipping..."
    exit 0
fi

# Check if database connection is available
if [ -z "$DB_HOST" ] || [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo "Database environment variables not set, skipping migrations..."
    exit 0
fi

# Construct database URL
DB_URL="postgresql://${DB_USER}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT:-5432}/${DB_NAME}"

# Run all SQL files in migrations directory
for migration in /app/migrations/*.sql; do
    if [ -f "$migration" ]; then
        echo "Running migration: $(basename $migration)"
        psql "$DB_URL" -f "$migration" || {
            echo "Migration failed: $(basename $migration)"
            exit 1
        }
        echo "✓ Migration completed: $(basename $migration)"
    fi
done

echo "All migrations completed successfully!"
