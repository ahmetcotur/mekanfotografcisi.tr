# Mekan Fotoğrafçısı SEO Extension System

A comprehensive SEO extension system for mekanfotografcisi.tr that adds location-based and service-based SEO pages without affecting the existing homepage structure.

## 🎯 Project Overview

This system extends the existing photography website with:
- **81 Turkish provinces** and **973 districts** location pages
- **Service-specific** SEO pages
- **Portfolio project** pages
- **Automated content generation** with variation blocks
- **Admin panel** for content management
- **Direct PostgreSQL backend** for reliable and fast operations

## 🚀 Key Features

### ✅ Homepage Preservation
- **Zero modifications** to existing homepage structure
- **Preserved SEO rankings** and URL structure
- **Additive-only** approach with new routes

### 🗺️ Location-Based SEO
- Complete Turkey location database (81 provinces, 973 districts)
- Automated SEO page generation for activated locations
- Deterministic content variation to prevent duplicate content
- Local notes system for human-written differentiators

### 🛠️ Content Management
- **Admin panel** with authentication (Local PHP Session)
- **Bulk activation** of provinces/districts
- **SEO page generation** with one-click
- **Publish/unpublish** controls
- **Preview functionality**

### 🔍 SEO Optimization
- **Unique titles and meta descriptions** per page
- **JSON-LD structured data** (LocalBusiness, ProfessionalService)
- **Dynamic sitemap generation**
- **Canonical URLs** and OpenGraph tags
- **Breadcrumb navigation**

## 📁 Project Structure

```
mekanfotografcisi-tr/
├── admin/                          # Admin panel
│   ├── index.html                 # Admin interface
│   └── admin.js                   # Admin functionality (AJAX to PHP API)
├── api/                           # API endpoints
│   ├── admin-auth.php            # Admin authentication
│   ├── admin-data.php            # Admin data fetcher
│   ├── admin-update.php          # Admin content updater
│   └── admin-upload.php          # Local media uploader
├── data/                          # Seed data
│   └── turkey-locations.json     # Complete Turkey location data
├── includes/                      # Core logic
│   ├── database.php              # Direct PostgreSQL Client
│   ├── helpers.php               # SEO and utility helpers
│   └── config.php                # Environment loader
├── uploads/                       # Local media storage
│   └── media/                    # Uploaded photos
├── scripts/                       # Management scripts
│   └── seed-locations.php        # PHP-based database seeding script
├── database_schema.sql            # PostgreSQL database schema
├── index.php                      # Main entry point (router handler)
├── router.php                     # Unified request router
├── sitemap.php                    # Dynamic sitemap generator
└── robots.txt                     # Updated robots.txt
```

## 🛠️ Installation & Setup

### 1. Prerequisites
- **PHP** 8.1+ (with `pdo_pgsql` extension)
- **PostgreSQL** 14+ database
- **Nginx/Apache** or PHP built-in server for dev

### 2. Environment Setup
```bash
# Clone or download the project files
# Copy environment template
cp .env.example .env

# Edit .env with your PostgreSQL credentials
nano .env
```

### 3. Database Setup
```bash
# Import the schema to your Postgres database
psql -h YOUR_HOST -U YOUR_USER -d YOUR_DB -f database_schema.sql

# Seed location data
php scripts/seed-locations.php
```

### 4. Admin User Setup
The system uses the `admin_users` table for authentication. You can add a user directly via SQL or a registration script (not included for security).

## 🎛️ Admin Panel Usage

### Access
Navigate to `/admin/` and login with your credentials.

### Key Functions
- **Province/District Management**: Bulk activate locations and generate SEO pages.
- **Media Library**: Upload and manage project photos directly to the server.
- **SEO Pages**: Edit, publish, or delete generated SEO content.

## 🌐 URL Structure

### New Routes (Additive Only)
```
/locations                         # Locations overview  
/locations/{province-slug}         # Province pages
/locations/{province-slug}/{district-slug}  # District pages
/services/{service-slug}           # Individual service pages
/admin                             # Content management panel
```

## 🚀 Deployment (Coolify/Docker)

Using the provided `Dockerfile`, the application can be deployed instantly.

### Required Environment Variables:
- `DB_HOST`: Postgres host
- `DB_PORT`: `5432`
- `DB_NAME`: `postgres`
- `DB_USER`: `postgres`
- `DB_PASSWORD`: Your secret password

## 📄 License

This project is proprietary software for mekanfotografcisi.tr. All rights reserved.

---

**Built with ❤️ for Turkish photography professionals**