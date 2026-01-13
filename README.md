# Mekan Fotoğrafçısı SEO Extension System

A comprehensive SEO extension system for mekanfotografcisi.tr that adds location-based and service-based SEO pages without affecting the existing homepage structure.

## 🎯 Project Overview

This system extends the existing photography website with:
- **81 Turkish provinces** and **973 districts** location pages
- **Service-specific** SEO pages
- **Portfolio project** pages
- **Automated content generation** with variation blocks
- **Admin panel** for content management
- **Supabase backend** with Row Level Security

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
- **Admin panel** with authentication
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
mekanfotografcisi-seo-extension/
├── admin/                          # Admin panel
│   ├── index.html                 # Admin interface
│   └── admin.js                   # Admin functionality
├── api/                           # API endpoints
│   └── seo-page.php              # SEO page data API
├── assets/                        # Existing assets (preserved)
│   ├── css/styles.css            # Original styles
│   ├── js/main.js                # Original JavaScript
│   └── images/                   # Original images
├── data/                          # Seed data
│   └── turkey-locations.json     # Complete Turkey location data
├── scripts/                       # Automation scripts
│   └── seed-locations.js         # Database seeding script
├── supabase/                      # Database migrations
│   └── migrations/
│       ├── 001_initial_schema.sql # Database schema
│       └── 002_row_level_security.sql # RLS policies
├── index.html                     # Original homepage (unchanged)
├── seo-page-template.html         # Template for SEO pages
├── sitemap.php                    # Dynamic sitemap generator
├── robots.txt                     # Updated robots.txt
└── save-form.php                  # Original form handler (preserved)
```

## 🛠️ Installation & Setup

### 1. Prerequisites
- **Node.js** 16+ and npm
- **PHP** 7.4+ (for API endpoints)
- **Supabase** account and project

### 2. Environment Setup
```bash
# Clone or download the project files
# Copy environment template
cp .env.example .env

# Edit .env with your Supabase credentials
nano .env
```

### 3. Install Dependencies
```bash
npm install
```

### 4. Database Setup
```bash
# Run Supabase migrations (via Supabase CLI or Dashboard)
# Apply files in supabase/migrations/ in order

# Seed location data
npm run seed
```

### 5. Admin User Setup
Create an admin user in Supabase Auth with `role: 'admin'` in user metadata.

## 📊 Database Schema

### Core Tables
- **`locations_province`** - 81 Turkish provinces
- **`locations_district`** - 973 Turkish districts  
- **`services`** - Photography services
- **`seo_pages`** - Generated SEO pages
- **`seo_variation_blocks`** - Content variations
- **`media`** - Media assets (Supabase Storage)
- **`portfolio_projects`** - Portfolio items

### Key Features
- **Row Level Security (RLS)** for public/admin access
- **Automated triggers** for updated_at timestamps
- **UUID primary keys** for scalability
- **JSONB fields** for flexible FAQ and rules storage

## 🎛️ Admin Panel Usage

### Access
Navigate to `/admin/` and login with admin credentials.

### Key Functions

#### 1. Province Management
- **View all 81 provinces** with activation status
- **Bulk activate** multiple provinces
- **Generate SEO pages** automatically
- **Preview generated pages**

#### 2. District Management  
- **Filter by province** or search by name
- **Add local notes** for content differentiation
- **Activate districts** individually or in bulk
- **Generate district-specific SEO pages**

#### 3. SEO Page Management
- **View all generated pages** with publish status
- **Bulk publish/unpublish** pages
- **Preview pages** before publishing
- **Delete unwanted pages**

#### 4. Services Management
- **Manage photography services**
- **Generate service-specific SEO pages**
- **Control service activation**

## 🔧 Content Generation System

### Variation Blocks
The system uses **deterministic content variation** to prevent duplicate content:

- **5 block types**: intro, process, benefits, faq, cta
- **Multiple variations** per block type
- **Hash-based selection** ensures consistent output
- **Minimum 10 variations** per block for uniqueness

### Template System
```sql
-- Example: Generate province page
SELECT upsert_seo_page(
    'province',
    province_id_param := 'uuid-here'
);
```

### Local Differentiation
- **`local_notes`** field for human-written content
- **Province/district-specific** information
- **Regional specializations** and local landmarks

## 🌐 URL Structure

### New Routes (Additive Only)
```
/services                          # Services overview
/services/{service-slug}           # Individual service pages
/locations                         # Locations overview  
/locations/{province-slug}         # Province pages
/locations/{province-slug}/{district-slug}  # District pages
/portfolio                         # Portfolio overview
/portfolio/{project-slug}          # Individual portfolio pages
```

### Preserved Routes
```
/                                  # Homepage (unchanged)
/#hizmetler                       # Services section (unchanged)
/#portfolio                       # Portfolio section (unchanged)
/#hakkimizda                      # About section (unchanged)
/#bolge-uzmanligi                 # Regions section (unchanged)
/#iletisim                        # Contact section (unchanged)
```

## 🔍 SEO Implementation

### Meta Tags
```html
<title>Antalya Mekan Fotoğrafçısı | Profesyonel Mimari ve İç Mekan Fotoğrafçılığı</title>
<meta name="description" content="Antalya'da profesyonel mekan fotoğrafçılığı hizmetleri...">
<link rel="canonical" href="https://mekanfotografcisi.tr/locations/antalya">
```

### Structured Data
```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Mekan Fotoğrafçısı",
  "serviceArea": {
    "@type": "State", 
    "name": "Antalya"
  }
}
```

### Internal Linking
- **Breadcrumb navigation** on all SEO pages
- **Cross-linking** between related locations
- **Service page links** from location pages
- **Homepage contact links** preserved

## 📈 Performance Considerations

### Optimization Features
- **Lazy loading** for images
- **Minimal JavaScript** for SEO pages
- **Cached API responses** where possible
- **Optimized database queries** with indexes
- **CDN-ready** asset structure

### Core Web Vitals
- **Fast LCP** with optimized images
- **Minimal CLS** with proper sizing
- **Good FID** with lightweight JavaScript

## 🔒 Security Features

### Row Level Security (RLS)
```sql
-- Public users: read-only access to published content
CREATE POLICY "Public can read published seo pages" ON seo_pages
    FOR SELECT USING (published = true);

-- Admin users: full CRUD access
CREATE POLICY "Admin full access to seo pages" ON seo_pages
    FOR ALL USING (is_admin());
```

### Access Control
- **Admin authentication** required for management
- **Public API endpoints** for published content only
- **Secure file uploads** to Supabase Storage
- **Environment variable** protection

## 🚀 Deployment

### Production Checklist
- [ ] Update Supabase URLs in all files
- [ ] Set production environment variables
- [ ] Run database migrations
- [ ] Seed location data
- [ ] Create admin user
- [ ] Test admin panel functionality
- [ ] Verify SEO page generation
- [ ] Check sitemap.xml accessibility
- [ ] Validate robots.txt
- [ ] Test Core Web Vitals
- [ ] Verify canonical URLs
- [ ] Check structured data

### Environment Variables
```bash
# Production
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-production-anon-key
SUPABASE_SERVICE_KEY=your-production-service-key
NODE_ENV=production
```

## 📊 Monitoring & Analytics

### Key Metrics to Track
- **SEO page indexation** rates
- **Organic traffic** to location pages
- **Conversion rates** from SEO pages to contact form
- **Page load speeds** (Core Web Vitals)
- **Search rankings** for target keywords

### Recommended Tools
- **Google Search Console** for indexation monitoring
- **Google Analytics 4** for traffic analysis
- **PageSpeed Insights** for performance monitoring
- **Supabase Dashboard** for database monitoring

## 🔧 Maintenance

### Regular Tasks
- **Monitor SEO page performance** monthly
- **Update local notes** for districts quarterly
- **Review and optimize** content variations
- **Check for broken links** and fix redirects
- **Update sitemap** as needed
- **Backup database** regularly

### Content Updates
- **Add new services** as business expands
- **Update portfolio projects** with new work
- **Refresh variation blocks** to maintain uniqueness
- **Optimize underperforming pages**

## 🆘 Troubleshooting

### Common Issues

#### SEO Pages Not Generating
```bash
# Check Supabase connection
node -e "console.log(process.env.SUPABASE_URL)"

# Verify admin permissions
# Check user metadata in Supabase Auth
```

#### Admin Panel Not Loading
```bash
# Check admin.js Supabase configuration
# Verify CORS settings in Supabase
# Check browser console for errors
```

#### Sitemap Not Updating
```bash
# Check sitemap.php for errors
# Verify Supabase API access
# Test with: curl https://yoursite.com/sitemap.php
```

## 📞 Support

For technical support or questions:
- **Email**: info@mekanfotografcisi.tr
- **Phone**: +90 507 467 75 02

## 📄 License

This project is proprietary software for mekanfotografcisi.tr. All rights reserved.

---

**Built with ❤️ for Turkish photography professionals**