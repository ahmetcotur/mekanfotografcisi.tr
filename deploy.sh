#!/bin/bash
# Production Deployment Script
# Run this on the production server after pulling latest code

echo "🚀 Deploying Admin SPA to Production"
echo "======================================"

# 1. Pull latest code
echo "📥 Pulling latest code from repository..."
git pull origin main

# 2. Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# 3. Run database migrations
echo "🗄️  Running database migrations..."
psql $DATABASE_URL -f migrations/create_settings_table.sql
psql $DATABASE_URL -f migrations/create_pexels_images_table.sql
psql $DATABASE_URL -f scripts/migrations/20260114_006_create_freelancer_applications_table.sql
psql $DATABASE_URL -f scripts/migrations/20260116_001_create_freelancer_assignment_system.sql

# 4. Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 admin/
chmod -R 755 api/
chmod 644 admin/.htaccess

# 5. Clear any PHP cache
echo "🧹 Clearing cache..."
php -r "opcache_reset();" 2>/dev/null || true

# 6. Verify admin files
echo "✅ Verifying admin SPA files..."
if [ -f "admin/index.html" ]; then
    echo "   ✓ admin/index.html exists"
else
    echo "   ✗ admin/index.html missing!"
    exit 1
fi

if [ -d "admin/assets" ]; then
    echo "   ✓ admin/assets directory exists"
else
    echo "   ✗ admin/assets directory missing!"
    exit 1
fi

echo ""
echo "✅ Deployment complete!"
echo ""
echo "📝 Next steps:"
echo "1. Visit https://mekanfotografcisi.tr/admin/"
echo "2. Login with your credentials"
echo "3. Test all pages (Dashboard, Locations, Services, Quotes, Settings)"
echo ""
echo "🔧 Troubleshooting:"
echo "- If 403 Forbidden: Check .htaccess permissions"
echo "- If 502 Bad Gateway: Check PHP-FPM logs"
echo "- If API errors: Ensure migrations ran successfully"
echo ""
echo "📂 Old admin backup: https://mekanfotografcisi.tr/admin-legacy/"
