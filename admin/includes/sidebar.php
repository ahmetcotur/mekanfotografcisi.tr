<aside class="sidebar">
    <div class="sidebar-header">
        <span>Admin Panel</span>
    </div>
    <nav class="sidebar-nav">
        <a href="/admin/?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="/admin/?page=services" class="nav-item <?= $page === 'services' ? 'active' : '' ?>">
            <i class="fas fa-camera"></i> Hizmetler
        </a>
        <a href="/admin/?page=locations" class="nav-item <?= $page === 'locations' ? 'active' : '' ?>">
            <i class="fas fa-map-marker-alt"></i> Lokasyonlar
        </a>
        <a href="/admin/?page=seo-pages" class="nav-item <?= $page === 'seo-pages' ? 'active' : '' ?>">
            <i class="fas fa-globe"></i> SEO Sayfaları
        </a>
        <a href="/admin/?page=media" class="nav-item <?= $page === 'media' ? 'active' : '' ?>">
            <i class="fas fa-images"></i> Medya
        </a>
        <a href="/admin/?page=quotes" class="nav-item <?= $page === 'quotes' ? 'active' : '' ?>">
            <i class="fas fa-envelope-open-text"></i> Teklifler
        </a>
        <a href="/admin/?page=settings" class="nav-item <?= $page === 'settings' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Ayarlar
        </a>
    </nav>
</aside>