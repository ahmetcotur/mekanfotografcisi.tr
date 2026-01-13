# QA Checklist - Mekan Fotoğrafçısı SEO Extension

## 🏠 Homepage Preservation Verification

### ✅ Critical Elements (MUST NOT CHANGE)
- [ ] Homepage URL remains `/` (index.html)
- [ ] Page title: "Mekan Fotoğrafçısı | Antalya ve Muğla'da Profesyonel Mimari ve İç Mekan Fotoğrafçılığı"
- [ ] Meta description unchanged (word-for-word)
- [ ] All section anchor IDs preserved: `#hizmetler`, `#portfolio`, `#hakkimizda`, `#bolge-uzmanligi`, `#iletisim`
- [ ] H1 in header logo: "Mekan Fotoğrafçısı"
- [ ] Hero H2: "Antalya ve Muğla'da Mekanınızı En İyi Yansıtan Profesyonel Fotoğrafçılık"
- [ ] All CTA button texts unchanged: "Teklif Al", "Çekim Planla", "Bizimle Çalışın"
- [ ] Navigation menu structure identical
- [ ] Contact information unchanged (phone, email, address)
- [ ] All existing copy word-for-word identical
- [ ] CSS classes and styling unchanged
- [ ] JavaScript functionality preserved (mobile menu, portfolio filter, form submission)
- [ ] Form still submits to `save-form.php`

### ✅ Visual Layout Verification
- [ ] Section order unchanged: Hero → Services → Portfolio → About → Regions → Contact → Footer
- [ ] Grid layouts and responsive breakpoints working
- [ ] Color scheme identical (CSS variables)
- [ ] Typography unchanged (Montserrat + Open Sans)
- [ ] Image paths and alt texts preserved
- [ ] Mobile responsiveness maintained

## 🗄️ Database & Backend Verification

### ✅ Supabase Setup
- [ ] All 8 tables created successfully
- [ ] Row Level Security (RLS) enabled on all tables
- [ ] Public policies allow read access to published content only
- [ ] Admin policies allow full CRUD access with `role='admin'`
- [ ] Storage bucket 'media' created with proper policies
- [ ] All indexes created for performance
- [ ] Triggers for `updated_at` working correctly

### ✅ Data Seeding
- [ ] 81 provinces imported successfully
- [ ] 973+ districts imported successfully
- [ ] 4 default services created and active
- [ ] 10+ variation blocks created for each type (intro, process, benefits, faq, cta)
- [ ] All slugs are URL-friendly (Turkish characters converted)
- [ ] Province-district relationships correct
- [ ] Plate codes assigned correctly (1-81)

### ✅ Functions & Procedures
- [ ] `upsert_seo_page()` function works for all page types
- [ ] `generate_seo_slug()` creates correct URL patterns
- [ ] `get_variation_block_by_hash()` returns deterministic content
- [ ] `is_admin()` function correctly identifies admin users

## 🎛️ Admin Panel Verification

### ✅ Authentication
- [ ] Admin login form displays correctly
- [ ] Authentication with Supabase Auth works
- [ ] Only users with `role='admin'` can access
- [ ] Logout functionality works
- [ ] Session persistence across page reloads

### ✅ Dashboard Stats
- [ ] Total provinces count displays correctly
- [ ] Active provinces count updates when toggled
- [ ] Total districts count displays correctly
- [ ] Active districts count updates when toggled
- [ ] Published pages count displays correctly

### ✅ Provinces Tab
- [ ] All 81 provinces display in table
- [ ] Search functionality works
- [ ] Toggle switches activate/deactivate provinces
- [ ] "Generate SEO Page" button creates pages
- [ ] Bulk activation works for selected provinces
- [ ] "Generate All SEO Pages" processes all active locations
- [ ] Preview links work for generated pages
- [ ] Pagination works for large datasets

### ✅ Districts Tab
- [ ] Districts display with correct province relationships
- [ ] Province filter dropdown works
- [ ] Search functionality works
- [ ] Toggle switches activate/deactivate districts
- [ ] Local notes field saves correctly
- [ ] "Generate SEO Page" button creates district pages
- [ ] Bulk activation works for selected districts

### ✅ SEO Pages Tab
- [ ] All generated pages display in table
- [ ] Type filter works (province/district/service/portfolio)
- [ ] Search functionality works
- [ ] Publish/unpublish toggles work
- [ ] Preview links open correct pages
- [ ] Delete functionality works with confirmation
- [ ] Bulk publish works for selected pages

### ✅ Services Tab
- [ ] All 4 services display correctly
- [ ] Toggle switches activate/deactivate services
- [ ] "Generate SEO Page" creates service pages
- [ ] Service slugs are correct

## 🌐 Frontend SEO Pages Verification

### ✅ Page Loading
- [ ] SEO page template loads correctly
- [ ] API endpoint `/api/seo-page.php` responds correctly
- [ ] Loading state displays while fetching data
- [ ] Error state displays for 404/invalid slugs
- [ ] Page content renders after successful API call

### ✅ URL Structure
- [ ] Province pages: `/locations/{province-slug}`
- [ ] District pages: `/locations/{province-slug}/{district-slug}`
- [ ] Service pages: `/services/{service-slug}`
- [ ] Portfolio pages: `/portfolio/{project-slug}`
- [ ] All URLs are SEO-friendly (lowercase, hyphens)

### ✅ Meta Tags & SEO
- [ ] Page title updates correctly for each page
- [ ] Meta description is unique per page
- [ ] Canonical URL is self-referencing
- [ ] OpenGraph tags populated correctly
- [ ] Twitter Card tags populated correctly
- [ ] JSON-LD structured data is valid
- [ ] Breadcrumbs display correct hierarchy

### ✅ Content Generation
- [ ] H1 displays correctly for each page type
- [ ] Content varies between pages (no duplicates)
- [ ] Variation blocks selected deterministically
- [ ] FAQ section displays when available
- [ ] Districts grid shows for province pages
- [ ] Local notes appear in district content
- [ ] CTA section links to homepage contact

### ✅ Navigation & UX
- [ ] Header navigation identical to homepage
- [ ] Breadcrumbs link correctly
- [ ] Footer identical to homepage
- [ ] Mobile responsiveness works
- [ ] FAQ accordion functionality works
- [ ] All internal links work correctly

## 🔍 SEO Technical Verification

### ✅ Crawlability
- [ ] `robots.txt` allows crawling of SEO pages
- [ ] `robots.txt` disallows admin and sensitive directories
- [ ] Sitemap.xml generates dynamically
- [ ] Sitemap includes all published pages
- [ ] Sitemap excludes unpublished pages
- [ ] Sitemap has correct priorities and change frequencies

### ✅ Page Speed & Performance
- [ ] Largest Contentful Paint (LCP) < 2.5s
- [ ] First Input Delay (FID) < 100ms
- [ ] Cumulative Layout Shift (CLS) < 0.1
- [ ] Images have width/height attributes
- [ ] CSS and JS are minified for production
- [ ] No render-blocking resources

### ✅ Content Quality
- [ ] No duplicate content between pages
- [ ] Each page has unique title and meta description
- [ ] Content length is substantial (300+ words)
- [ ] Internal linking structure is logical
- [ ] No broken links or 404 errors
- [ ] All images have proper alt text

## 🔒 Security Verification

### ✅ Access Control
- [ ] Admin panel requires authentication
- [ ] Public API endpoints only return published content
- [ ] Unpublished pages return 404 to public users
- [ ] SQL injection protection in place
- [ ] XSS protection in place
- [ ] CSRF protection for admin actions

### ✅ Data Protection
- [ ] Environment variables not exposed to client
- [ ] Database credentials secure
- [ ] Admin passwords are strong
- [ ] File upload restrictions in place
- [ ] No sensitive data in client-side code

## 📊 Analytics & Tracking Verification

### ✅ Google Search Console
- [ ] Sitemap submitted successfully
- [ ] Pages being indexed correctly
- [ ] No crawl errors reported
- [ ] Mobile usability passes
- [ ] Core Web Vitals are good

### ✅ Google Analytics (if implemented)
- [ ] Tracking code on all SEO pages
- [ ] Goals set up for contact form submissions
- [ ] Enhanced ecommerce tracking (if applicable)
- [ ] Custom events for CTA clicks

## 🚀 Deployment Verification

### ✅ Production Environment
- [ ] All environment variables set correctly
- [ ] Database migrations applied
- [ ] Seed data imported
- [ ] Admin user created with proper role
- [ ] SSL certificate active
- [ ] CDN configured (if applicable)

### ✅ Monitoring Setup
- [ ] Error logging configured
- [ ] Performance monitoring active
- [ ] Uptime monitoring in place
- [ ] Database backup scheduled
- [ ] Alert notifications configured

## 📱 Cross-Browser & Device Testing

### ✅ Desktop Browsers
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### ✅ Mobile Devices
- [ ] iOS Safari
- [ ] Android Chrome
- [ ] Responsive design works on all screen sizes
- [ ] Touch interactions work properly

## 🔄 Regression Testing

### ✅ Original Functionality
- [ ] Homepage loads correctly
- [ ] Contact form submission works
- [ ] Portfolio filtering works
- [ ] Mobile menu works
- [ ] Smooth scrolling works
- [ ] All original links work
- [ ] Email notifications work (if applicable)

### ✅ New Functionality
- [ ] SEO pages load without affecting homepage
- [ ] Admin panel doesn't interfere with public site
- [ ] Database operations don't slow down homepage
- [ ] New routes don't conflict with existing ones

## ✅ Final Checklist

### Pre-Launch
- [ ] All QA items above completed
- [ ] Stakeholder approval received
- [ ] Backup of current site created
- [ ] Rollback plan prepared
- [ ] Launch timeline confirmed

### Post-Launch
- [ ] Monitor error logs for 24 hours
- [ ] Check Google Search Console for crawl errors
- [ ] Verify analytics tracking
- [ ] Test admin panel functionality
- [ ] Monitor site performance
- [ ] Check for any broken links
- [ ] Verify SEO page indexation

## 🆘 Issue Tracking

### Critical Issues (Must Fix Before Launch)
- [ ] Issue 1: [Description]
- [ ] Issue 2: [Description]

### Minor Issues (Can Fix Post-Launch)
- [ ] Issue 1: [Description]
- [ ] Issue 2: [Description]

### Notes
- Date of QA: ___________
- QA Performed by: ___________
- Environment Tested: ___________
- Browser Versions: ___________

---

**This checklist ensures zero regression to existing functionality while validating all new SEO features work correctly.**