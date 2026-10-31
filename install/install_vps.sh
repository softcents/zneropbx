#!/bin/bash
set -e

# Noninteractive environment variables
export DEBIAN_FRONTEND=noninteractive
export NEEDRESTART_MODE=a

# Root check
if [ "$EUID" -ne 0 ]; then
  echo "❌ Error: এই স্ক্রিপ্টটি চালানোর জন্য root প্রিভিলেজ প্রয়োজন।"
  exit 1
fi

SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
INSTALL_DIR="/var/www/fspbx"
PUBLIC_DIR="$INSTALL_DIR/public"

echo "=========================================="
echo "   🚀 ZneroPBX VPS Installation Started   "
echo "=========================================="

# 1. Update and install prerequisite tools
echo "[1/4] বেসিক প্যাকেজ আপডেট ও ইনস্টল করা হচ্ছে..."
apt-get update -y
apt-get install -y git curl wget sudo ca-certificates lsb-release

# 2. Copy all ZneroPBX files including dotfiles to /var/www/fspbx
echo "[2/4] ZneroPBX ফাইলগুলি (সব ডটফাইলসহ) $INSTALL_DIR এ কপি করা হচ্ছে..."
mkdir -p "$INSTALL_DIR"
cp -a "$SOURCE_DIR/." "$INSTALL_DIR/"

# 3. Ensure FusionPBX core files in public directory
mkdir -p "$PUBLIC_DIR"
if [ ! -f "$PUBLIC_DIR/core/upgrade/upgrade.php" ]; then
    echo "[3/4] প্রয়োজনীয় FusionPBX কোর ফাইল ডাউনলোড ও এক্সট্রাক্ট করা হচ্ছে..."
    FUSIONPBX_VERSION=$(curl -s https://api.github.com/repos/nemerald-voip/fusionpbx/releases/latest | grep "tag_name" | cut -d '"' -f 4 || echo "")
    if [ -z "$FUSIONPBX_VERSION" ]; then
        FUSIONPBX_VERSION="v1.2.28"
    fi
    echo "FusionPBX Version: $FUSIONPBX_VERSION"
    wget -q -O "$PUBLIC_DIR/fusionpbx.tar.gz" "https://github.com/nemerald-voip/fusionpbx/archive/refs/tags/${FUSIONPBX_VERSION}.tar.gz"
    tar -xzf "$PUBLIC_DIR/fusionpbx.tar.gz" -C "$PUBLIC_DIR" --strip-components=1
    rm -f "$PUBLIC_DIR/fusionpbx.tar.gz"
else
    echo "[3/4] FusionPBX কোর ফাইল ইতিমধ্যে উপস্থিত রয়েছে।"
fi

# 4. Make scripts executable and trigger install.sh
echo "[4/4] মেইন ইনস্টলার (FreeSWITCH, Nginx, PostgreSQL, PHP) রান করা হচ্ছে..."
chmod +x "$INSTALL_DIR"/install/*.sh
cd "$INSTALL_DIR/install"
bash "$INSTALL_DIR/install/install.sh"

echo "=========================================="
echo "   ✅ ZneroPBX ইনস্টলেশন সফলভাবে সম্পন্ন হয়েছে!   "
echo "=========================================="
