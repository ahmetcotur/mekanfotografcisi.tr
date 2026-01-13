# Homepage Preservation Report - mekanfotografcisi.tr

## CRITICAL ELEMENTS THAT MUST REMAIN UNCHANGED

### 1. URL Structure
- **Homepage URL**: `/` (index.html)
- **Canonical URL**: Must remain self-referencing

### 2. Metadata (MUST NOT CHANGE)
```html
<title>Mekan Fotoğrafçısı | Antalya ve Muğla'da Profesyonel Mimari ve İç Mekan Fotoğrafçılığı</title>
<meta name="description" content="Mekan Fotoğrafçısı - Antalya ve Muğla bölgesinde profesyonel mimari ve iç mekan fotoğrafçılığı hizmetleri. Kaş, Kalkan, Fethiye, Göcek ve tüm Akdeniz'de emlak, otel, villa ve iş yerleri için yüksek kaliteli fotoğraflar.">
<meta name="keywords" content="mekan fotoğrafçısı, Antalya fotoğrafçı, Muğla fotoğrafçı, Kaş, Kalkan, Fethiye, Bodrum, mimari fotoğrafçılık, iç mekan fotoğrafçısı, emlak fotoğrafçılığı, villa fotoğrafçılığı, profesyonel mekan fotoğrafları, iç mimari fotoğrafçılık">
```

### 3. Section Order & Anchor IDs (MUST NOT CHANGE)
1. `#hizmetler` - Hizmetler section
2. `#portfolio` - Portfolyo section  
3. `#hakkimizda` - Hakkımızda section
4. `#bolge-uzmanligi` - Hizmet Bölgelerimiz section
5. `#iletisim` - İletişim section

### 4. Heading Hierarchy (MUST NOT CHANGE)
- **H1**: "Mekan Fotoğrafçısı" (in header logo)
- **H2**: "Antalya ve Muğla'da Mekanınızı En İyi Yansıtan Profesyonel Fotoğrafçılık" (hero)
- **H2**: "Hizmetlerimiz" (services section title)
- **H3**: Service card titles (4 services)
- **H2**: "Portfolyo" (portfolio section title)
- **H2**: "Hakkımızda" (about section title)
- **H2**: "Hizmet Bölgelerimiz" (region expertise section title)
- **H3**: Region column titles (3 regions)
- **H2**: "İletişim" (contact section title)

### 5. CTA Texts & Buttons (MUST NOT CHANGE)
- Hero buttons: "Teklif Al", "Çekim Planla"
- Service section CTA: "Teklif Al"
- Portfolio section CTA: "Çekim Planla"
- About section CTA: "Bizimle Çalışın"
- Region expertise CTA: "Teklif Al"
- Contact form button: "Teklif Al"
- Contact banner buttons: "+90 507 467 75 02", "Çekim Planla"

### 6. Navigation Structure (MUST NOT CHANGE)
```html
<ul class="nav-links">
    <li><a href="#hizmetler">Hizmetler</a></li>
    <li><a href="#portfolio">Portfolyo</a></li>
    <li><a href="#hakkimizda">Hakkımızda</a></li>
    <li><a href="#bolge-uzmanligi">Hizmet Bölgelerimiz</a></li>
    <li><a href="#iletisim">İletişim</a></li>
</ul>
```

### 7. Visual Layout & CSS Classes (MUST NOT CHANGE)
- All existing CSS classes and their styling
- Grid layouts and responsive breakpoints
- Color scheme (CSS variables)
- Typography (Montserrat + Open Sans)
- Image paths and alt texts
- Form structure and field names

### 8. JavaScript Functionality (MUST NOT CHANGE)
- Mobile menu toggle
- Portfolio filtering
- Smooth scrolling
- Form submission to `save-form.php`
- Header scroll behavior

### 9. Contact Information (MUST NOT CHANGE)
- Address: "Kalkan Mah. Şehitler Cad. no 7 Kaş / Antalya"
- Phone: "+90 507 467 75 02"
- Email: "info@mekanfotografcisi.tr"

### 10. Content Copy (MUST NOT CHANGE)
- All existing text content in Turkish
- Service descriptions
- About us content
- Location lists and tags
- Footer content

## ALLOWED ADDITIONS (ADDITIVE ONLY)

### 1. New Navigation Links
- Can add new menu items for new routes (/services, /locations, /portfolio)
- Must be added AFTER existing menu items
- Must not modify existing menu structure

### 2. Internal Linking
- Can add "Detail" links within existing sections
- Must not modify existing copy
- Links should point to new route pages

### 3. Schema Markup
- Can add JSON-LD structured data
- Must not modify existing HTML structure

### 4. New Meta Tags
- Can add OpenGraph and Twitter Card meta tags
- Must not modify existing meta tags

## IMPLEMENTATION CONSTRAINTS

1. **No Rewriting**: Existing content must remain word-for-word identical
2. **No Reordering**: Section order and element hierarchy must remain unchanged  
3. **No Redesigning**: Visual layout, colors, fonts, and styling must remain identical
4. **Additive Only**: All changes must be additions, not modifications
5. **URL Preservation**: Homepage URL structure must remain unchanged
6. **Performance**: New additions must not impact page load speed
7. **Mobile Compatibility**: All additions must maintain responsive design

## VERIFICATION CHECKLIST

- [ ] Homepage title and meta description unchanged
- [ ] All section anchor IDs preserved
- [ ] Heading hierarchy (H1/H2/H3) unchanged
- [ ] CTA button texts identical
- [ ] Navigation menu structure preserved
- [ ] Contact information unchanged
- [ ] All existing copy word-for-word identical
- [ ] CSS classes and styling unchanged
- [ ] JavaScript functionality preserved
- [ ] Mobile responsiveness maintained
- [ ] Form submission to save-form.php working
- [ ] All image paths and alt texts preserved