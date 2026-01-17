#!/bin/sh
set -e

echo "Starting application..."

# Run migrations before starting services
/app/scripts/run-migrations.sh

# Start the default webdevops entrypoint
exec /entrypoint supervisord
