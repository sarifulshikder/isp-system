#!/bin/bash
# ============================================================
#  ISP Management System - Docker Setup Script
# ============================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}"
echo "╔══════════════════════════════════════╗"
echo "║   ISP Management System - Docker    ║"
echo "╚══════════════════════════════════════╝"
echo -e "${NC}"

# ── Step 1: Check Docker ──────────────────────────────────
echo -e "${YELLOW}[1/5] Docker check করা হচ্ছে...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker পাওয়া যায়নি! Docker install করুন।${NC}"
    exit 1
fi
if ! command -v docker compose &> /dev/null; then
    echo -e "${RED}Docker Compose পাওয়া যায়নি!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker OK${NC}"

# ── Step 2: Copy config.php ───────────────────────────────
echo -e "${YELLOW}[2/5] config.php copy করা হচ্ছে...${NC}"
cp config.php ISP-PLATFORM-AS/config.php
echo -e "${GREEN}✓ config.php ready${NC}"

# ── Step 3: Stop old containers ───────────────────────────
echo -e "${YELLOW}[3/5] পুরনো container বন্ধ করা হচ্ছে...${NC}"
docker compose down 2>/dev/null || true
echo -e "${GREEN}✓ Done${NC}"

# ── Step 4: Build & Start ─────────────────────────────────
echo -e "${YELLOW}[4/5] Docker build ও start করা হচ্ছে...${NC}"
echo "    (প্রথমবার ৩-৫ মিনিট লাগতে পারে)"
docker compose up -d --build

# ── Step 5: Wait for DB ───────────────────────────────────
echo -e "${YELLOW}[5/5] Database ready হওয়ার জন্য অপেক্ষা করা হচ্ছে...${NC}"
attempt=0
until docker exec isp_db mysqladmin ping -h localhost -u root -prootpass123 --silent 2>/dev/null; do
    attempt=$((attempt+1))
    if [ $attempt -gt 30 ]; then
        echo -e "${RED}Database timeout! লগ দেখুন: docker logs isp_db${NC}"
        exit 1
    fi
    echo "    অপেক্ষা করুন... ($attempt/30)"
    sleep 3
done

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║         ✅ Setup সম্পন্ন হয়েছে!             ║${NC}"
echo -e "${GREEN}╠══════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║  🌐 ISP System:  http://localhost            ║${NC}"
echo -e "${GREEN}║  🗄️  phpMyAdmin: http://localhost:8080        ║${NC}"
echo -e "${GREEN}║                                              ║${NC}"
echo -e "${GREEN}║  👤 Username: admin                          ║${NC}"
echo -e "${GREEN}║  🔑 Password: admin123                       ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo "Container status:"
docker compose ps
