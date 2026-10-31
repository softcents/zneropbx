#!/bin/bash
set -e

# Ensure running as root/sudo
if [ "$EUID" -ne 0 ]; then
  echo "❌ Error: সফটওয়্যার আপডেট করার জন্য root প্রিভিলেজ প্রয়োজন।"
  echo "অনুগ্রহ করে চালান: sudo bash $0"
  exit 1
fi

INSTALL_DIR="/var/www/fspbx"
cd "$INSTALL_DIR"

echo "=========================================="
echo "   🔄 ZneroPBX Software Update Started    "
echo "=========================================="

# 1. Pull latest changes from Git
echo "[1/6] গিটহাব থেকে লেটেস্ট কোড পুল করা হচ্ছে (git pull)..."
git pull origin main

# 2. Update Composer dependencies
echo "[2/6] কম্পোজার ডিপেন্ডেন্সি চেক করা হচ্ছে..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 3. Run database migrations
echo "[3/6] ডাটাবেজ মাইগ্রেশন চেক ও আপডেট করা হচ্ছে..."
php artisan migrate --force

# 4. Clear and rebuild caches
echo "[4/6] Laravel কনফিগারেশন ও রুট ক্যাশ রিফ্রেশ করা হচ্ছে..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart background queue & services
echo "[5/6] কিউ ও ব্যাকগ্রাউন্ড সার্ভিস রিস্টার্ট করা হচ্ছে..."
php artisan queue:restart
supervisorctl restart horizon:* || true
supervisorctl restart fs-cdr-service || true

# 6. Restore ownership and permissions
echo "[6/6] ফাইল পারমিশন নিশ্চিত করা হচ্ছে..."
chown -R www-data:www-data "$INSTALL_DIR"
find "$INSTALL_DIR/storage" "$INSTALL_DIR/bootstrap/cache" -type d -exec chmod 775 {} +

echo "=========================================="
echo "   ✅ ZneroPBX সফলভাবে আপডেট সম্পন্ন হয়েছে!  "
echo "=========================================="
