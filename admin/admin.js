/**
 * Admin Panel JavaScript
 * Manages SEO content for mekanfotografcisi.tr
 * Uses PHP API endpoints with PostgreSQL database
 */

// API base URL
const API_BASE = '/api';

// Global state
let currentUser = null;
let currentTab = 'provinces';
const itemsPerPage = 20;

// Pagination state per tab
const paginationState = {
    provinces: { page: 1, total: 0 },
    districts: { page: 1, total: 0 },
    'seo-pages': { page: 1, total: 0 },
    media: { page: 1, total: 0 }
};

// DOM elements
const loginForm = document.getElementById('loginForm');
const adminPanel = document.getElementById('adminPanel');
const authForm = document.getElementById('authForm');
const authError = document.getElementById('authError');
const logoutBtn = document.getElementById('logoutBtn');

// Initialize app
document.addEventListener('DOMContentLoaded', async () => {
    // Check if user is already logged in
    try {
        const response = await fetch(`${API_BASE}/admin-auth.php?action=check`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error:', response.status, errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.authenticated) {
            currentUser = data.user;
            showAdminPanel();
        } else {
            showLoginForm();
        }
    } catch (error) {
        console.error('Error checking session:', error);
        // Show login form even on error
        showLoginForm();
    }

    setupEventListeners();
});

// Event listeners
function setupEventListeners() {
    // Auth form
    authForm.addEventListener('submit', handleLogin);
    logoutBtn.addEventListener('click', handleLogout);

    // Tab switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', (e) => {
            switchTab(e.target.dataset.tab);
        });
    });

    // Search and filter inputs
    document.getElementById('provinceSearch').addEventListener('input', debounce(loadProvinces, 300));
    document.getElementById('districtSearch').addEventListener('input', debounce(loadDistricts, 300));
    document.getElementById('provinceFilter').addEventListener('change', loadDistricts);
    document.getElementById('seoPageSearch').addEventListener('input', debounce(loadSeoPages, 300));
    document.getElementById('seoPageTypeFilter').addEventListener('change', loadSeoPages);
    document.getElementById('mediaSearch').addEventListener('input', debounce(loadMedia, 300));

    // Media upload
    document.getElementById('uploadMediaBtn').addEventListener('click', toggleUploadArea);
    document.getElementById('fileInput').addEventListener('change', handleFileSelect);

    // Drag and drop
    const dropZone = document.getElementById('dropZone');
    if (dropZone) {
        dropZone.addEventListener('dragover', handleDragOver);
        dropZone.addEventListener('drop', handleDrop);
        dropZone.addEventListener('click', () => document.getElementById('fileInput').click());
    }

    // Bulk actions
    document.getElementById('generateAllSeoPages').addEventListener('click', generateAllSeoPages);
    document.getElementById('activateSelectedProvinces').addEventListener('click', activateSelectedProvinces);
    document.getElementById('activateSelectedDistricts').addEventListener('click', activateSelectedDistricts);
    document.getElementById('publishSelectedPages').addEventListener('click', publishSelectedPages);

    // Select all checkboxes
    document.getElementById('selectAllProvinces').addEventListener('change', toggleSelectAllProvinces);
    document.getElementById('selectAllDistricts').addEventListener('change', toggleSelectAllDistricts);
    document.getElementById('selectAllSeoPages').addEventListener('change', toggleSelectAllSeoPages);
}

// Authentication
async function handleLogin(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch(`${API_BASE}/admin-auth.php?action=login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Login failed');
        }

        currentUser = data.user;
        showAdminPanel();

    } catch (error) {
        showAuthError(error.message);
    }
}

async function handleLogout() {
    try {
        await fetch(`${API_BASE}/admin-auth.php?action=logout`);
    } catch (error) {
        console.error('Logout error:', error);
    }

    currentUser = null;
    showLoginForm();
}

function showLoginForm() {
    loginForm.style.display = 'block';
    adminPanel.style.display = 'none';
    authError.style.display = 'none';
}

function showAdminPanel() {
    loginForm.style.display = 'none';
    adminPanel.style.display = 'block';
    loadDashboardStats();
    loadProvinces();
    loadProvinceFilter();
}

function showAuthError(message) {
    authError.textContent = message;
    authError.style.display = 'block';
}

// Tab management
function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

    // Update tab content
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    document.getElementById(tabName).classList.add('active');

    currentTab = tabName;
    // Reset page for this tab if needed, or keep it. 
    // Let's reset to 1 when switching for simplicity.
    if (paginationState[tabName]) {
        paginationState[tabName].page = 1;
    }

    // Load content for active tab
    switch (tabName) {
        case 'provinces':
            loadProvinces();
            break;
        case 'districts':
            loadDistricts();
            break;
        case 'seo-pages':
            loadSeoPages();
            break;
        case 'services':
            loadServices();
            break;
        case 'media':
            loadMedia();
            break;
    }
}

// Dashboard stats
async function loadDashboardStats() {
    try {
        const response = await fetch(`${API_BASE}/admin-data.php?resource=stats`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const stats = await response.json();

        // Update UI
        document.getElementById('totalProvinces').textContent = stats.totalProvinces || 0;
        document.getElementById('activeProvinces').textContent = stats.activeProvinces || 0;
        document.getElementById('totalDistricts').textContent = stats.totalDistricts || 0;
        document.getElementById('activeDistricts').textContent = stats.activeDistricts || 0;
        document.getElementById('publishedPages').textContent = stats.publishedPages || 0;

    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

// Provinces management
async function loadProvinces() {
    const loading = document.getElementById('provincesLoading');
    const content = document.getElementById('provincesContent');
    const table = document.getElementById('provincesTable');
    const search = document.getElementById('provinceSearch').value;

    loading.style.display = 'block';
    content.style.display = 'none';

    try {
        const offset = (paginationState.provinces.page - 1) * itemsPerPage;
        let url = `${API_BASE}/admin-data.php?resource=provinces&limit=${itemsPerPage}&offset=${offset}`;

        const response = await fetch(url);
        const result = await response.json();
        let provinces = result.data || [];
        paginationState.provinces.total = result.total || 0;

        // Filter by search
        if (search) {
            provinces = provinces.filter(p =>
                p.name.toLowerCase().includes(search.toLowerCase())
            );
        }

        // Render provinces table
        table.innerHTML = provinces.map(province => `
            <tr>
                <td><input type="checkbox" class="province-checkbox" value="${province.id}"></td>
                <td><strong>${province.name}</strong></td>
                <td>${province.region_name}</td>
                <td>${province.plate_code}</td>
                <td>
                    <label class="toggle-switch">
                        <input type="checkbox" ${province.is_active ? 'checked' : ''} 
                               onchange="toggleProvinceActive('${province.id}', this.checked)">
                        <span class="slider"></span>
                    </label>
                </td>
                <td>
                    ${province.seo_pages?.length > 0 ?
                `<span style="color: var(--success-color);">✓ Oluşturuldu</span>` :
                `<span style="color: var(--danger-color);">✗ Yok</span>`
            }
                </td>
                <td>
                    <button class="btn btn-sm" onclick="editProvinceContent('${province.id}')">
                        İçerik Düzenle
                    </button>
                    <button class="btn btn-sm" onclick="generateProvinceSeoPage('${province.id}')" style="margin-left: 5px;">
                        SEO Sayfası
                    </button>
                    ${province.seo_pages?.length > 0 ?
                `<a href="/locations/${province.slug}" target="_blank" class="btn btn-sm" style="margin-left: 5px;">Önizle</a>` :
                ''
            }
                </td>
            </tr>
        `).join('');

        loading.style.display = 'none';
        content.style.display = 'block';

        renderPagination('provincesPagination', 'provinces', loadProvinces);

    } catch (error) {
        console.error('Error loading provinces:', error);
        showAlert('İller yüklenirken hata oluştu: ' + error.message, 'danger');
        loading.style.display = 'none';
    }
}

async function toggleProvinceActive(provinceId, isActive) {
    try {
        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                table: 'locations_province',
                id: provinceId,
                data: { is_active: isActive }
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Update failed');
        }

        loadDashboardStats();
        showAlert(`İl ${isActive ? 'aktifleştirildi' : 'pasifleştirildi'}`, 'success');

    } catch (error) {
        console.error('Error toggling province:', error);
        showAlert('İl durumu güncellenirken hata oluştu: ' + error.message, 'danger');
    }
}

async function generateProvinceSeoPage(provinceId) {
    try {
        showAlert('SEO sayfası oluşturuluyor...', 'info');

        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'generate-seo-page',
                type: 'province',
                id: provinceId
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Failed to create SEO page');
        }

        showAlert(result.message || 'SEO sayfası başarıyla oluşturuldu', 'success');
        loadProvinces();
        loadSeoPages();
        loadDashboardStats();

    } catch (error) {
        console.error('Error generating SEO page:', error);
        showAlert('SEO sayfası oluşturulurken hata oluştu: ' + error.message, 'danger');
    }
}

// Districts management
async function loadDistricts() {
    const loading = document.getElementById('districtsLoading');
    const content = document.getElementById('districtsContent');
    const table = document.getElementById('districtsTable');
    const search = document.getElementById('districtSearch').value;
    const provinceFilter = document.getElementById('provinceFilter').value;

    loading.style.display = 'block';
    content.style.display = 'none';

    try {
        const offset = (paginationState.districts.page - 1) * itemsPerPage;
        let url = `${API_BASE}/admin-data.php?resource=districts&limit=${itemsPerPage}&offset=${offset}`;
        if (provinceFilter) {
            url += `&province_id=${provinceFilter}`;
        }

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();
        let districts = result.data || [];
        paginationState.districts.total = result.total || 0;

        // Filter by search
        if (search) {
            districts = districts.filter(d =>
                d.name.toLowerCase().includes(search.toLowerCase())
            );
        }

        // Render districts table
        table.innerHTML = districts.map(district => `
            <tr>
                <td><input type="checkbox" class="district-checkbox" value="${district.id}"></td>
                <td><strong>${district.name}</strong></td>
                <td>${district.province_name || ''}</td>
                <td>
                    <label class="toggle-switch">
                        <input type="checkbox" ${district.is_active ? 'checked' : ''} 
                               onchange="toggleDistrictActive('${district.id}', this.checked)">
                        <span class="slider"></span>
                    </label>
                </td>
                <td>
                    <span style="color: var(--success-color);">✓</span>
                </td>
                <td>
                    <input type="text" value="${district.local_notes || ''}" 
                           placeholder="Yerel notlar..." 
                           onchange="updateDistrictNotes('${district.id}', this.value)"
                           style="width: 200px; padding: 4px;">
                </td>
                <td>
                    <button class="btn btn-sm" onclick="editDistrictContent('${district.id}')">
                        İçerik Düzenle
                    </button>
                    <button class="btn btn-sm" onclick="generateDistrictSeoPage('${district.id}')" style="margin-left: 5px;">
                        SEO Sayfası
                    </button>
                </td>
            </tr>
        `).join('');

        loading.style.display = 'none';
        content.style.display = 'block';

        renderPagination('districtsPagination', 'districts', loadDistricts);

    } catch (error) {
        console.error('Error loading districts:', error);
        showAlert('İlçeler yüklenirken hata oluştu: ' + error.message, 'danger');
        loading.style.display = 'none';
    }
}

async function loadProvinceFilter() {
    try {
        const response = await fetch(`${API_BASE}/admin-data.php?resource=provinces`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const provinces = await response.json();

        const select = document.getElementById('provinceFilter');
        select.innerHTML = '<option value="">Tüm İller</option>' +
            provinces.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

    } catch (error) {
        console.error('Error loading province filter:', error);
    }
}

async function toggleDistrictActive(districtId, isActive) {
    try {
        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                table: 'locations_district',
                id: districtId,
                data: { is_active: isActive }
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Update failed');
        }

        loadDashboardStats();
        showAlert(`İlçe ${isActive ? 'aktifleştirildi' : 'pasifleştirildi'}`, 'success');

    } catch (error) {
        console.error('Error toggling district:', error);
        showAlert('İlçe durumu güncellenirken hata oluştu: ' + error.message, 'danger');
    }
}

async function updateDistrictNotes(districtId, notes) {
    try {
        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                table: 'locations_district',
                id: districtId,
                data: { local_notes: notes }
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Update failed');
        }

        showAlert('Yerel notlar güncellendi', 'success');

    } catch (error) {
        console.error('Error updating district notes:', error);
        showAlert('Yerel notlar güncellenirken hata oluştu: ' + error.message, 'danger');
    }
}

async function generateDistrictSeoPage(districtId) {
    try {
        showAlert('SEO sayfası oluşturuluyor...', 'info');

        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'generate-seo-page',
                type: 'district',
                id: districtId
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Failed to create SEO page');
        }

        showAlert(result.message || 'SEO sayfası başarıyla oluşturuldu', 'success');
        loadDistricts();
        loadSeoPages();
        loadDashboardStats();

    } catch (error) {
        console.error('Error generating district SEO page:', error);
        showAlert('SEO sayfası oluşturulurken hata oluştu: ' + error.message, 'danger');
    }
}

// SEO Pages management
async function loadSeoPages() {
    const loading = document.getElementById('seoLoading');
    const content = document.getElementById('seoContent');
    const table = document.getElementById('seoTable');
    const search = document.getElementById('seoPageSearch').value;
    const typeFilter = document.getElementById('seoPageTypeFilter').value;

    loading.style.display = 'block';
    content.style.display = 'none';

    try {
        const offset = (paginationState['seo-pages'].page - 1) * itemsPerPage;
        let url = `${API_BASE}/admin-data.php?resource=seo-pages&limit=${itemsPerPage}&offset=${offset}`;

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();
        let pages = result.data || [];
        paginationState['seo-pages'].total = result.total || 0;

        // Filter by search
        if (search) {
            pages = pages.filter(p =>
                p.title.toLowerCase().includes(search.toLowerCase())
            );
        }

        // Filter by type
        if (typeFilter) {
            pages = pages.filter(p => p.type === typeFilter);
        }

        // Render SEO pages table
        if (pages.length === 0) {
            table.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                        <p style="font-size: 16px; margin-bottom: 10px;">Henüz SEO sayfası oluşturulmamış</p>
                        <p style="font-size: 14px; color: #999;">İller veya İlçeler sekmesinden "SEO Sayfası" butonuna tıklayarak sayfa oluşturabilirsiniz.</p>
                    </td>
                </tr>
            `;
        } else {
            table.innerHTML = pages.map(page => `
                <tr>
                    <td><input type="checkbox" class="seo-page-checkbox" value="${page.id}"></td>
                    <td><strong>${page.title || 'Başlıksız'}</strong></td>
                    <td>
                        <span class="badge ${getTypeColor(page.type)}">${getTypeLabel(page.type)}</span>
                    </td>
                    <td><code>${page.slug || ''}</code></td>
                    <td>
                        <label class="toggle-switch">
                            <input type="checkbox" ${page.published ? 'checked' : ''} 
                                   onchange="togglePagePublished('${page.id}', this.checked)">
                            <span class="slider"></span>
                        </label>
                    </td>
                    <td>${formatDate(page.updated_at)}</td>
                    <td>
                        ${page.slug ? `<a href="${page.slug}" target="_blank" class="btn btn-sm">Önizle</a>` : ''}
                        <button class="btn btn-sm btn-danger" onclick="deleteSeoPage('${page.id}')" style="margin-left: 5px;">Sil</button>
                    </td>
                </tr>
            `).join('');
        }

        loading.style.display = 'none';
        content.style.display = 'block';

        renderPagination('seoPagination', 'seo-pages', loadSeoPages);

    } catch (error) {
        console.error('Error loading SEO pages:', error);
        showAlert('SEO sayfaları yüklenirken hata oluştu: ' + error.message, 'danger');
        loading.style.display = 'none';
    }
}

async function togglePagePublished(pageId, published) {
    try {
        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                table: 'seo_pages',
                id: pageId,
                data: { published: published }
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Update failed');
        }

        loadDashboardStats();
        showAlert(`Sayfa ${published ? 'yayınlandı' : 'yayından kaldırıldı'}`, 'success');

    } catch (error) {
        console.error('Error toggling page published:', error);
        showAlert('Sayfa durumu güncellenirken hata oluştu: ' + error.message, 'danger');
    }
}

async function deleteSeoPage(pageId) {
    if (!confirm('Bu SEO sayfasını silmek istediğinizden emin misiniz?')) {
        return;
    }

    try {
        // TODO: Implement delete via API
        showAlert('Silme özelliği yakında eklenecek', 'info');
        loadSeoPages();
        loadDashboardStats();

    } catch (error) {
        console.error('Error deleting SEO page:', error);
        showAlert('SEO sayfası silinirken hata oluştu: ' + error.message, 'danger');
    }
}

// Services management
async function loadServices() {
    const loading = document.getElementById('servicesLoading');
    const content = document.getElementById('servicesContent');
    const table = document.getElementById('servicesTable');

    loading.style.display = 'block';
    content.style.display = 'none';

    try {
        const response = await fetch(`${API_BASE}/admin-data.php?resource=services`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const services = await response.json();

        // Render services table
        table.innerHTML = services.map(service => `
            <tr>
                <td><strong>${service.name}</strong></td>
                <td><code>${service.slug}</code></td>
                <td>
                    <label class="toggle-switch">
                        <input type="checkbox" ${service.is_active ? 'checked' : ''} 
                               onchange="toggleServiceActive('${service.id}', this.checked)">
                        <span class="slider"></span>
                    </label>
                </td>
                <td>
                    <button class="btn btn-sm" onclick="editServiceContent('${service.id}')">
                        İçerik Düzenle
                    </button>
                    <button class="btn btn-sm" onclick="generateServiceSeoPage('${service.id}')" style="margin-left: 5px;">
                        SEO Sayfası
                    </button>
                </td>
            </tr>
        `).join('');

        loading.style.display = 'none';
        content.style.display = 'block';

    } catch (error) {
        console.error('Error loading services:', error);
        showAlert('Hizmetler yüklenirken hata oluştu: ' + error.message, 'danger');
        loading.style.display = 'none';
    }
}

async function toggleServiceActive(serviceId, isActive) {
    try {
        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                table: 'services',
                id: serviceId,
                data: { is_active: isActive }
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Update failed');
        }

        showAlert(`Hizmet ${isActive ? 'aktifleştirildi' : 'pasifleştirildi'}`, 'success');

    } catch (error) {
        console.error('Error toggling service:', error);
        showAlert('Hizmet durumu güncellenirken hata oluştu: ' + error.message, 'danger');
    }
}

async function generateServiceSeoPage(serviceId) {
    try {
        // TODO: Implement SEO page generation via API
        showAlert('SEO sayfası oluşturma özelliği yakında eklenecek', 'info');
        loadDashboardStats();

    } catch (error) {
        console.error('Error generating service SEO page:', error);
        showAlert('Hizmet SEO sayfası oluşturulurken hata oluştu: ' + error.message, 'danger');
    }
}

// Edit service content
async function editServiceContent(serviceId) {
    try {
        // Get service data
        const response = await fetch(`${API_BASE}/admin-data.php?resource=services`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const services = await response.json();
        const service = services.find(s => s.id === serviceId);

        if (!service) {
            throw new Error('Service not found');
        }

        // Parse gallery_images if it's a JSON string
        if (service.gallery_images && typeof service.gallery_images === 'string') {
            try {
                const parsed = JSON.parse(service.gallery_images);
                if (Array.isArray(parsed)) {
                    service.gallery_images = parsed;
                }
            } catch (e) {
                // Not valid JSON, keep as is
            }
        }

        // Create modal for content editing
        const modal = document.createElement('div');
        modal.className = 'content-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>${service.name} - İçerik Düzenle</h2>
                    <button class="modal-close" onclick="this.closest('.content-modal').remove()">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kısa Açıklama (Short Intro)</label>
                        <textarea id="serviceShortIntro" rows="3" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">${service.short_intro || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Detaylı Açıklama (Description)</label>
                        <textarea id="serviceDescription" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">${service.description || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label>İçerik (Markdown)</label>
                        <textarea id="serviceContent" rows="15" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace;">${service.content || ''}</textarea>
                        <small style="color: #666;">Markdown formatında yazabilirsiniz. Başlıklar için ##, listeler için - kullanın.</small>
                    </div>
                    <div class="form-group">
                        <label>Ana Görsel URL</label>
                        <input type="text" id="serviceImage" value="${service.image || ''}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" placeholder="https://images.pexels.com/...">
                    </div>
                    <div class="form-group">
                        <label>Galeri Görselleri (Pexels Linkleri)</label>
                        <textarea id="serviceGalleryImages" rows="5" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace;" placeholder="Her satıra bir Pexels linki yapıştırın:&#10;https://images.pexels.com/photos/12345/pexels-photo-12345.jpeg&#10;https://images.pexels.com/photos/67890/pexels-photo-67890.jpeg">${(() => {
                if (!service.gallery_images) return '';
                if (Array.isArray(service.gallery_images)) {
                    return service.gallery_images.filter(url => url && url.trim()).join('\n');
                }
                if (typeof service.gallery_images === 'string') {
                    try {
                        const parsed = JSON.parse(service.gallery_images);
                        if (Array.isArray(parsed)) {
                            return parsed.filter(url => url && url.trim()).join('\n');
                        }
                    } catch (e) {
                        // Not JSON, treat as plain text
                        return service.gallery_images;
                    }
                }
                return '';
            })()}</textarea>
                        <small style="color: #666;">Her satıra bir Pexels görsel linki yapıştırın. 3-4 görsel önerilir.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" onclick="saveServiceContent('${serviceId}')">Kaydet</button>
                    <button class="btn" onclick="this.closest('.content-modal').remove()">İptal</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

    } catch (error) {
        console.error('Error loading service content:', error);
        showAlert('Hizmet içeriği yüklenirken hata oluştu: ' + error.message, 'danger');
    }
}

// Save service content
async function saveServiceContent(serviceId) {
    try {
        const shortIntro = document.getElementById('serviceShortIntro').value;
        const description = document.getElementById('serviceDescription').value;
        const content = document.getElementById('serviceContent').value;
        const image = document.getElementById('serviceImage').value;
        const galleryImagesText = document.getElementById('serviceGalleryImages').value;

        // Parse gallery images (one per line, filter empty lines)
        const galleryImages = galleryImagesText
            .split('\n')
            .map(url => url.trim())
            .filter(url => url.length > 0 && (url.startsWith('http') || url.startsWith('https')));

        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                table: 'services',
                id: serviceId,
                data: {
                    short_intro: shortIntro,
                    description: description,
                    content: content,
                    image: image,
                    gallery_images: JSON.stringify(galleryImages)
                }
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Update failed');
        }

        showAlert('Hizmet içeriği başarıyla kaydedildi', 'success');
        document.querySelector('.content-modal').remove();
        loadServices();

    } catch (error) {
        console.error('Error saving service content:', error);
        showAlert('Hizmet içeriği kaydedilirken hata oluştu: ' + error.message, 'danger');
    }
}

// Select media for service
function selectMediaForService() {
    // Switch to media tab and show upload area
    switchTab('media');
    toggleUploadArea();
    showAlert('Medya seçtikten sonra URL\'yi kopyalayıp hizmet formuna yapıştırabilirsiniz', 'info');
}

// Bulk actions
async function generateAllSeoPages() {
    if (!confirm('Tüm aktif iller ve ilçeler için SEO sayfaları oluşturulacak. Bu işlem uzun sürebilir. Devam etmek istiyor musunuz?')) {
        return;
    }

    try {
        showAlert('SEO sayfaları oluşturuluyor... Bu işlem birkaç dakika sürebilir.', 'info');

        // Get all active provinces
        const provincesResponse = await fetch(`${API_BASE}/admin-data.php?resource=provinces`);
        const provinces = await provincesResponse.json();
        const activeProvinces = provinces.filter(p => p.is_active);

        // Get all active districts
        const districtsResponse = await fetch(`${API_BASE}/admin-data.php?resource=districts`);
        const districts = await districtsResponse.json();
        const activeDistricts = districts.filter(d => d.is_active);

        let created = 0;
        let skipped = 0;

        // Generate province pages
        for (const province of activeProvinces) {
            try {
                const response = await fetch(`${API_BASE}/admin-update.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'generate-seo-page',
                        type: 'province',
                        id: province.id
                    })
                });
                const result = await response.json();
                if (result.success) {
                    if (result.message && result.message.includes('already exists')) {
                        skipped++;
                    } else {
                        created++;
                    }
                }
            } catch (error) {
                console.error(`Error creating SEO page for province ${province.name}:`, error);
            }
        }

        // Generate district pages
        for (const district of activeDistricts) {
            try {
                const response = await fetch(`${API_BASE}/admin-update.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'generate-seo-page',
                        type: 'district',
                        id: district.id
                    })
                });
                const result = await response.json();
                if (result.success) {
                    if (result.message && result.message.includes('already exists')) {
                        skipped++;
                    } else {
                        created++;
                    }
                }
            } catch (error) {
                console.error(`Error creating SEO page for district ${district.name}:`, error);
            }
        }

        loadDashboardStats();
        loadSeoPages();
        showAlert(`${created} yeni SEO sayfası oluşturuldu, ${skipped} sayfa zaten mevcuttu`, 'success');

    } catch (error) {
        console.error('Error generating all SEO pages:', error);
        showAlert('SEO sayfaları oluşturulurken hata oluştu: ' + error.message, 'danger');
    }
}

async function activateSelectedProvinces() {
    const checkboxes = document.querySelectorAll('.province-checkbox:checked');
    const provinceIds = Array.from(checkboxes).map(cb => cb.value);

    if (provinceIds.length === 0) {
        showAlert('Lütfen aktifleştirilecek illeri seçin', 'warning');
        return;
    }

    try {
        // Update provinces via API
        for (const provinceId of provinceIds) {
            await toggleProvinceActive(provinceId, true);
        }

        loadProvinces();
        loadDashboardStats();
        showAlert(`${provinceIds.length} il aktifleştirildi ve SEO sayfaları oluşturuldu`, 'success');

    } catch (error) {
        console.error('Error activating provinces:', error);
        showAlert('İller aktifleştirilirken hata oluştu: ' + error.message, 'danger');
    }
}

async function activateSelectedDistricts() {
    const checkboxes = document.querySelectorAll('.district-checkbox:checked');
    const districtIds = Array.from(checkboxes).map(cb => cb.value);

    if (districtIds.length === 0) {
        showAlert('Lütfen aktifleştirilecek ilçeleri seçin', 'warning');
        return;
    }

    try {
        // Update districts via API
        for (const districtId of districtIds) {
            await toggleDistrictActive(districtId, true);
        }

        loadDistricts();
        loadDashboardStats();
        showAlert(`${districtIds.length} ilçe aktifleştirildi`, 'success');

    } catch (error) {
        console.error('Error activating districts:', error);
        showAlert('İlçeler aktifleştirilirken hata oluştu: ' + error.message, 'danger');
    }
}

async function publishSelectedPages() {
    const checkboxes = document.querySelectorAll('.seo-page-checkbox:checked');
    const pageIds = Array.from(checkboxes).map(cb => cb.value);

    if (pageIds.length === 0) {
        showAlert('Lütfen yayınlanacak sayfaları seçin', 'warning');
        return;
    }

    try {
        // Update pages via API
        for (const pageId of pageIds) {
            await togglePagePublished(pageId, true);
        }

        loadSeoPages();
        loadDashboardStats();
        showAlert(`${pageIds.length} sayfa yayınlandı`, 'success');

    } catch (error) {
        console.error('Error publishing pages:', error);
        showAlert('Sayfalar yayınlanırken hata oluştu: ' + error.message, 'danger');
    }
}

// Select all functionality
function toggleSelectAllProvinces() {
    const selectAll = document.getElementById('selectAllProvinces');
    const checkboxes = document.querySelectorAll('.province-checkbox');

    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
}

function toggleSelectAllDistricts() {
    const selectAll = document.getElementById('selectAllDistricts');
    const checkboxes = document.querySelectorAll('.district-checkbox');

    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
}

function toggleSelectAllSeoPages() {
    const selectAll = document.getElementById('selectAllSeoPages');
    const checkboxes = document.querySelectorAll('.seo-page-checkbox');

    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    // Insert at top of container
    const container = document.querySelector('.container');
    container.insertBefore(alert, container.firstChild);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

function getTypeLabel(type) {
    const labels = {
        'province': 'İl',
        'district': 'İlçe',
        'service': 'Hizmet',
        'portfolio': 'Portfolyo'
    };
    return labels[type] || type;
}

function getTypeColor(type) {
    const colors = {
        'province': 'primary',
        'district': 'success',
        'service': 'warning',
        'portfolio': 'info'
    };
    return colors[type] || 'secondary';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '-';
        return date.toLocaleDateString('tr-TR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return '-';
    }
}

function renderPagination(containerId, tabKey, loadFunction) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const state = paginationState[tabKey];
    const totalPages = Math.ceil(state.total / itemsPerPage);

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <div class="pagination" style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 20px;">
            <button class="btn btn-sm" ${state.page === 1 ? 'disabled' : ''} onclick="changePage('${tabKey}', ${state.page - 1}, ${loadFunction.name})">
                &laquo; Önceki
            </button>
            <span style="font-size: 14px; font-weight: 500;">
                Sayfa ${state.page} / ${totalPages} 
                <small style="color: #666; margin-left: 5px;">(Toplam ${state.total} kayıt)</small>
            </span>
            <button class="btn btn-sm" ${state.page === totalPages ? 'disabled' : ''} onclick="changePage('${tabKey}', ${state.page + 1}, ${loadFunction.name})">
                Sonraki &raquo;
            </button>
        </div>
    `;
}

window.changePage = function (tabKey, newPage, loadFunction) {
    paginationState[tabKey].page = newPage;
    loadFunction();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Add CSS for badges
const style = document.createElement('style');
style.textContent = `
    .badge {
        display: inline-block;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 4px;
        text-transform: uppercase;
    }
    .badge.primary { background: var(--primary-color); color: white; }
    .badge.success { background: var(--success-color); color: white; }
    .badge.warning { background: var(--warning-color); color: white; }
    .badge.info { background: #17a2b8; color: white; }
    .badge.secondary { background: #6c757d; color: white; }
`;
document.head.appendChild(style);

// =============================================
// MEDIA MANAGEMENT
// =============================================

// Media state
let mediaPage = 1;
const mediaPerPage = 20;

// Toggle upload area
function toggleUploadArea() {
    const uploadArea = document.getElementById('uploadArea');
    uploadArea.style.display = uploadArea.style.display === 'none' ? 'block' : 'none';
}

// Load media gallery
async function loadMedia() {
    const loading = document.getElementById('mediaLoading');
    const content = document.getElementById('mediaContent');
    const grid = document.getElementById('mediaGrid');
    const search = document.getElementById('mediaSearch').value;

    loading.style.display = 'block';
    content.style.display = 'none';

    try {
        const offset = (paginationState.media.page - 1) * itemsPerPage;
        const response = await fetch(`${API_BASE}/admin-data.php?resource=media&limit=${itemsPerPage}&offset=${offset}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();
        let mediaItems = result.data || [];
        paginationState.media.total = result.total || 0;

        // Filter by search
        if (search) {
            mediaItems = mediaItems.filter(item =>
                (item.alt || '').toLowerCase().includes(search.toLowerCase()) ||
                (item.storage_path || '').toLowerCase().includes(search.toLowerCase())
            );
        }

        if (mediaItems.length === 0) {
            grid.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    <p style="font-size: 16px; margin-bottom: 10px;">Henüz görsel yüklenmemiş</p>
                    <p style="font-size: 14px; color: #999;">Yukarıdaki "Görsel Yükle" butonuna tıklayarak görselleri yükleyebilirsiniz.</p>
                </div>
            `;
        } else {
            // Render media grid with GLightbox
            grid.innerHTML = mediaItems.map((item, index) => `
                <div class="media-item" style="position: relative; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: white;">
                    <a href="${item.public_url}" class="glightbox" data-gallery="media-gallery" data-title="${item.alt || 'Görsel'}">
                        <img src="${item.public_url}" alt="${item.alt || ''}" 
                             style="width: 100%; height: 200px; object-fit: cover; cursor: pointer; display: block;">
                    </a>
                    <div class="media-item-info" style="padding: 12px;">
                        <p style="font-size: 12px; color: #666; margin: 4px 0; word-break: break-all;">
                            <strong>${(item.storage_path || '').split('/').pop()}</strong>
                        </p>
                        <p style="font-size: 11px; color: #999; margin: 2px 0;">
                            ${formatFileSize(item.file_size || 0)} • ${item.width || 0} × ${item.height || 0}px
                        </p>
                        <div class="media-item-actions" style="display: flex; gap: 5px; margin-top: 8px;">
                            <button class="btn btn-sm" onclick="copyMediaUrl('${item.public_url}'); return false;" 
                                    style="flex: 1; padding: 6px; font-size: 11px;">📋 URL</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteMedia('${item.id}'); return false;" 
                                    style="flex: 1; padding: 6px; font-size: 11px;">🗑️ Sil</button>
                        </div>
                    </div>
                </div>
            `).join('');

            // Initialize GLightbox
            if (typeof GLightbox !== 'undefined') {
                const lightbox = GLightbox({
                    selector: '.glightbox',
                    touchNavigation: true,
                    loop: true,
                    autoplayVideos: false
                });
            }
        }

        loading.style.display = 'none';
        content.style.display = 'block';

    } catch (error) {
        console.error('Error loading media:', error);
        showAlert('Medya yüklenirken hata oluştu: ' + error.message, 'danger');
        loading.style.display = 'none';
    }
}

// Upload media files
async function uploadMedia(files) {
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadStatus = document.getElementById('uploadStatus');

    uploadProgress.style.display = 'block';
    uploadProgressBar.style.width = '0%';

    // Validate all files first
    const validFiles = [];
    for (const file of files) {
        if (file.size > 10 * 1024 * 1024) {
            showAlert(`${file.name} çok büyük (Max 10MB)`, 'danger');
            continue;
        }
        if (!file.type.startsWith('image/')) {
            showAlert(`${file.name} geçerli bir görsel değil`, 'danger');
            continue;
        }
        validFiles.push(file);
    }

    if (validFiles.length === 0) {
        uploadProgress.style.display = 'none';
        return;
    }

    const totalFiles = validFiles.length;
    let uploaded = 0;

    try {
        uploadStatus.textContent = `${totalFiles} dosya yükleniyor...`;

        // Create FormData for all files
        const formData = new FormData();
        validFiles.forEach(file => {
            formData.append('files[]', file);
        });

        console.log('Uploading files:', validFiles.map(f => f.name));

        const response = await fetch(`${API_BASE}/admin-upload.php`, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Upload error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('Upload result:', result);

        if (!result.success) {
            throw new Error(result.error || 'Upload failed');
        }

        uploaded = result.uploaded || 0;
        uploadProgressBar.style.width = '100%';
        uploadStatus.textContent = `${uploaded}/${totalFiles} dosya başarıyla yüklendi`;

        if (result.errors && result.errors.length > 0) {
            result.errors.forEach(error => {
                showAlert(error, 'warning');
            });
        }

        if (uploaded > 0) {
            showAlert(`${uploaded} görsel başarıyla yüklendi`, 'success');
        }

    } catch (error) {
        console.error('Error uploading files:', error);
        showAlert('Görsel yüklenirken hata: ' + error.message, 'danger');
    }

    // Reload media gallery
    setTimeout(() => {
        loadMedia();
        uploadProgress.style.display = 'none';
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.value = '';
        }
    }, 1000);
}

// Handle file select
function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
        uploadMedia(files);
    }
}

// Handle drag and drop
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.style.borderColor = '#3a5e7c'; // primary-color
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.style.borderColor = '#dee2e6'; // border-color

    const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
    if (files.length > 0) {
        uploadMedia(files);
    } else {
        showAlert('Lütfen geçerli görsel dosyaları seçin', 'warning');
    }
}

// Preview media
function previewMedia(url) {
    // Create preview modal
    let preview = document.getElementById('mediaPreview');
    if (!preview) {
        preview = document.createElement('div');
        preview.id = 'mediaPreview';
        preview.className = 'media-preview';
        preview.innerHTML = `
            <button class="media-preview-close" onclick="closeMediaPreview()">×</button>
            <img src="${url}" alt="Preview">
        `;
        document.body.appendChild(preview);
    } else {
        preview.querySelector('img').src = url;
    }
    preview.classList.add('active');
}

function closeMediaPreview() {
    const preview = document.getElementById('mediaPreview');
    if (preview) {
        preview.classList.remove('active');
    }
}

// Copy media URL
async function copyMediaUrl(url) {
    try {
        await navigator.clipboard.writeText(url);
        showAlert('URL kopyalandı', 'success');

        // If there's a selected input, fill it automatically
        if (window.selectedMediaInputId) {
            const input = document.getElementById(window.selectedMediaInputId);
            if (input) {
                input.value = url;
                window.selectedMediaInputId = null;
            }
        }
    } catch (error) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('URL kopyalandı', 'success');

        // If there's a selected input, fill it automatically
        if (window.selectedMediaInputId) {
            const input = document.getElementById(window.selectedMediaInputId);
            if (input) {
                input.value = url;
                window.selectedMediaInputId = null;
            }
        }
    }
}

// Delete media
async function deleteMedia(mediaId) {
    if (!confirm('Bu görseli silmek istediğinizden emin misiniz?')) {
        return;
    }

    try {
        // TODO: Implement media deletion via API
        showAlert('Görsel silme özelliği yakında eklenecek', 'info');
        return;

        showAlert('Görsel silindi', 'success');
        loadMedia();

    } catch (error) {
        console.error('Error deleting media:', error);
        showAlert('Görsel silinirken hata oluştu: ' + error.message, 'danger');
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// =============================================
// LOCATION CONTENT EDITING
// =============================================

// Edit province content
async function editProvinceContent(provinceId) {
    try {
        const response = await fetch(`${API_BASE}/admin-data.php?resource=provinces`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const provinces = await response.json();
        const province = provinces.find(p => p.id === provinceId);

        if (!province) {
            throw new Error('Province not found');
        }

        const modal = document.createElement('div');
        modal.className = 'content-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>${province.name} - İçerik Düzenle</h2>
                    <button class="modal-close" onclick="this.closest('.content-modal').remove()">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kısa Açıklama (Description)</label>
                        <textarea id="provinceDescription" rows="3" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">${province.description || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label>İçerik (Markdown)</label>
                        <textarea id="provinceContent" rows="15" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace;">${province.content || ''}</textarea>
                        <small style="color: #666;">Markdown formatında yazabilirsiniz.</small>
                    </div>
                    <div class="form-group">
                        <label>Görsel URL</label>
                        <input type="text" id="provinceImage" value="${province.image || ''}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" placeholder="/assets/images/province-image.jpg">
                        <button type="button" class="btn btn-sm" onclick="selectMediaForLocation('provinceImage')" style="margin-top: 5px;">Medya Galeriden Seç</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" onclick="saveProvinceContent('${provinceId}')">Kaydet</button>
                    <button class="btn" onclick="this.closest('.content-modal').remove()">İptal</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

    } catch (error) {
        console.error('Error loading province content:', error);
        showAlert('İl içeriği yüklenirken hata oluştu: ' + error.message, 'danger');
    }
}

// Save province content
async function saveProvinceContent(provinceId) {
    try {
        const description = document.getElementById('provinceDescription').value;
        const content = document.getElementById('provinceContent').value;
        const image = document.getElementById('provinceImage').value;

        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                table: 'locations_province',
                id: provinceId,
                data: {
                    description: description,
                    content: content,
                    image: image
                }
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Update failed');
        }

        showAlert('İl içeriği başarıyla kaydedildi', 'success');
        document.querySelector('.content-modal').remove();
        loadProvinces();

    } catch (error) {
        console.error('Error saving province content:', error);
        showAlert('İl içeriği kaydedilirken hata oluştu: ' + error.message, 'danger');
    }
}

// Edit district content
async function editDistrictContent(districtId) {
    try {
        // Get district data
        const response = await fetch(`${API_BASE}/admin-data.php?resource=districts`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const districts = await response.json();
        const district = districts.find(d => d.id === districtId);

        if (!district) {
            throw new Error('District not found');
        }

        // Get province name
        const provinceResponse = await fetch(`${API_BASE}/admin-data.php?resource=provinces`);
        const provinces = await provinceResponse.json();
        const province = provinces.find(p => p.id === district.province_id);

        const modal = document.createElement('div');
        modal.className = 'content-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>${district.name} - ${province?.name || ''} - İçerik Düzenle</h2>
                    <button class="modal-close" onclick="this.closest('.content-modal').remove()">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kısa Açıklama (Description)</label>
                        <textarea id="districtDescription" rows="3" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">${district.description || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label>İçerik (Markdown)</label>
                        <textarea id="districtContent" rows="15" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace;">${district.content || ''}</textarea>
                        <small style="color: #666;">Markdown formatında yazabilirsiniz.</small>
                    </div>
                    <div class="form-group">
                        <label>Görsel URL</label>
                        <input type="text" id="districtImage" value="${district.image || ''}" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;" placeholder="/assets/images/district-image.jpg">
                        <button type="button" class="btn btn-sm" onclick="selectMediaForLocation('districtImage')" style="margin-top: 5px;">Medya Galeriden Seç</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" onclick="saveDistrictContent('${districtId}')">Kaydet</button>
                    <button class="btn" onclick="this.closest('.content-modal').remove()">İptal</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

    } catch (error) {
        console.error('Error loading district content:', error);
        showAlert('İlçe içeriği yüklenirken hata oluştu: ' + error.message, 'danger');
    }
}

// Save district content
async function saveDistrictContent(districtId) {
    try {
        const description = document.getElementById('districtDescription').value;
        const content = document.getElementById('districtContent').value;
        const image = document.getElementById('districtImage').value;

        const response = await fetch(`${API_BASE}/admin-update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                table: 'locations_district',
                id: districtId,
                data: {
                    description: description,
                    content: content,
                    image: image
                }
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Update failed');
        }

        showAlert('İlçe içeriği başarıyla kaydedildi', 'success');
        document.querySelector('.content-modal').remove();
        loadDistricts();

    } catch (error) {
        console.error('Error saving district content:', error);
        showAlert('İlçe içeriği kaydedilirken hata oluştu: ' + error.message, 'danger');
    }
}

// Select media for location
function selectMediaForLocation(inputId) {
    // Switch to media tab and show upload area
    switchTab('media');
    toggleUploadArea();
    showAlert('Medya seçtikten sonra URL\'yi kopyalayıp form alanına yapıştırabilirsiniz. Input ID: ' + inputId, 'info');

    // Store the input ID for later use
    window.selectedMediaInputId = inputId;
}