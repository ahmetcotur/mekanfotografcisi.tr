<?php
// Dashboard View

// Fetch stats
$total_services = count($db->select('posts', ['post_type' => 'service', 'post_status' => 'publish']));
$total_seo_pages = count($db->select('posts', ['post_type' => 'seo_page', 'post_status' => 'publish']));
$total_provinces = count($db->select('locations_province', ['is_active' => true]));
$total_districts = count($db->select('locations_district', ['is_active' => true]));

// Quote stats
$total_quotes = count($db->query("SELECT id FROM quotes"));
$new_quotes = count($db->query("SELECT id FROM quotes WHERE is_read = false"));

// Recent Activity (Sorted by creation date)
$recent_pages = $db->query("SELECT * FROM posts WHERE post_type IN ('service', 'seo_page', 'page') ORDER BY created_at DESC LIMIT 5");
$recent_quotes = $db->query("SELECT * FROM quotes ORDER BY created_at DESC LIMIT 5");
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $total_services ?></h3>
            <p>Aktif Hizmet</p>
        </div>
        <div class="stat-icon"><i class="fas fa-camera"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3><?= $total_seo_pages ?></h3>
            <p>SEO Sayfası</p>
        </div>
        <div class="stat-icon"><i class="fas fa-globe"></i></div>
    </div>

    <div class="stat-card brand-highlight">
        <div class="stat-info">
            <h3 class="text-brand-600"><?= $total_quotes ?></h3>
            <p>Toplam Teklif</p>
        </div>
        <div class="stat-icon"><i class="fas fa-envelope-open-text"></i></div>
    </div>

    <div class="stat-card danger-highlight">
        <div class="stat-info">
            <h3 class="<?= $new_quotes > 0 ? 'text-red-600 pulse-text' : '' ?>"><?= $new_quotes ?></h3>
            <p>Yeni Talep</p>
        </div>
        <div class="stat-icon"><i class="fas fa-bell"></i></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Quotes Section -->
    <div class="card">
        <div class="card-header flex justify-between items-center bg-slate-50 border-b p-4">
            <h2 class="font-bold text-slate-800 text-sm italic uppercase tracking-widest">Son Teklif Talepleri</h2>
            <a href="/admin/?page=quotes" class="text-xs text-brand-600 font-bold hover:underline">Tümünü Gör</a>
        </div>
        <div class="p-0 overflow-x-auto">
            <table class="admin-table text-xs">
                <thead>
                    <tr>
                        <th>Müşteri</th>
                        <th>Hizmet</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_quotes)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-8 text-slate-400">Talep bulunmuyor.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_quotes as $q): ?>
                            <tr class="<?= $q['is_read'] ? '' : 'bg-brand-50/50' ?>">
                                <td class="font-bold text-slate-700"><?= htmlspecialchars($q['name']) ?></td>
                                <td><span class="badge badge-light text-[10px]"><?= htmlspecialchars($q['service']) ?></span>
                                </td>
                                <td>
                                    <span class="text-[10px]"><?= htmlspecialchars($q['status']) ?></span>
                                </td>
                                <td class="text-slate-400"><?= date('d.m.y', strtotime($q['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Pages Section -->
    <div class="card">
        <div class="card-header flex justify-between items-center bg-slate-50 border-b p-4">
            <h2 class="font-bold text-slate-800 text-sm italic uppercase tracking-widest">Son Oluşturulan Sayfalar</h2>
            <span class="text-[10px] text-slate-400">Son 5 Kayıt</span>
        </div>
        <div class="p-0 overflow-x-auto">
            <table class="admin-table text-xs">
                <thead>
                    <tr>
                        <th>Başlık</th>
                        <th>Tip</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_pages as $page): ?>
                        <tr>
                            <td>
                                <div class="font-bold text-slate-700 line-clamp-1"><?= htmlspecialchars($page['title']) ?>
                                </div>
                                <div class="text-[10px] text-slate-400">/<?= htmlspecialchars($page['slug']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-info text-[10px]"><?= $page['post_type'] ?></span>
                            </td>
                            <td class="text-slate-400"><?= date('d.m.y', strtotime($page['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .brand-highlight {
        border-bottom: 3px solid var(--brand-500);
    }

    .danger-highlight {
        border-bottom: 3px solid #ef4444;
    }

    .pulse-text {
        animation: pulse-opacity 2s infinite;
    }

    @keyframes pulse-opacity {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }
</style>
<!-- Pexels Image Management Section -->
<div class="card mt-8">
    <div class="card-header flex justify-between items-center bg-slate-50 border-b p-4">
        <h2 class="font-bold text-slate-800 text-sm italic uppercase tracking-widest">Anasayfa Görselleri (Pexels)</h2>
        <button onclick="addNewPexelsImage()" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Yeni Görsel Ekle
        </button>
    </div>
    <div class="p-4">
        <div id="pexels-images-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- Images will be loaded here -->
        </div>
    </div>
</div>

<script>
let pexelsImages = [];
document.addEventListener('DOMContentLoaded', () => { loadPexelsImages(); });

function loadPexelsImages() {
    fetch('/api/pexels-images.php')
        .then(res => res.json())
        .then(data => { if (data.success) { pexelsImages = data.images; renderPexelsImages(); } })
        .catch(err => console.error('Failed to load Pexels images:', err));
}

function renderPexelsImages() {
    const grid = document.getElementById('pexels-images-grid');
    if (!pexelsImages || pexelsImages.length === 0) {
        grid.innerHTML = '<p class="col-span-full text-center text-slate-400 py-8">Henüz görsel eklenmemiş.</p>';
        return;
    }
    grid.innerHTML = pexelsImages.map(img => `
        <div class="relative group border rounded-lg overflow-hidden ${img.is_visible ? 'border-green-500' : 'border-slate-200 opacity-50'}">
            <img src="${img.image_url}" alt="Pexels" class="w-full h-32 object-cover">
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                <button onclick="togglePexelsVisibility(${img.id}, ${!img.is_visible})" class="btn btn-sm ${img.is_visible ? 'btn-warning' : 'btn-success'}">
                    <i class="fas fa-eye${img.is_visible ? '-slash' : ''}"></i>
                </button>
                <button onclick="deletePexelsImage(${img.id})" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </div>
            <div class="p-2 bg-white">
                <p class="text-xs text-slate-600 truncate">${img.photographer || 'Unknown'}</p>
                <span class="text-[10px] ${img.is_visible ? 'text-green-600' : 'text-slate-400'} font-bold">${img.is_visible ? 'Görünür' : 'Gizli'}</span>
            </div>
        </div>
    `).join('');
}

function togglePexelsVisibility(id, isVisible) {
    fetch('/api/pexels-images.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'toggle', id, is_visible: isVisible }) })
        .then(res => res.json())
        .then(data => { if (data.success) { loadPexelsImages(); Swal.fire({ icon: 'success', title: isVisible ? 'Görsel gösterilecek' : 'Görsel gizlendi', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }); } });
}

function deletePexelsImage(id) {
    Swal.fire({ title: 'Emin misiniz?', text: 'Bu görseli silmek istediğinizden emin misiniz?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, Sil', cancelButtonText: 'İptal' })
        .then(result => { if (result.isConfirmed) { fetch('/api/pexels-images.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'delete', id }) }).then(res => res.json()).then(data => { if (data.success) { loadPexelsImages(); Swal.fire('Silindi!', 'Görsel başarıyla silindi.', 'success'); } }); } });
}

function addNewPexelsImage() {
    Swal.fire({ title: 'Yeni Pexels Görseli Ekle', html: '<input id="pexels-url" class="swal2-input" placeholder="Görsel URL"><input id="pexels-photographer" class="swal2-input" placeholder="Fotoğrafçı (opsiyonel)">', showCancelButton: true, confirmButtonText: 'Ekle', cancelButtonText: 'İptal', preConfirm: () => { const url = document.getElementById('pexels-url').value; if (!url) { Swal.showValidationMessage('Lütfen bir URL girin'); return false; } return { url, photographer: document.getElementById('pexels-photographer').value }; } })
        .then(result => { if (result.isConfirmed) { fetch('/api/pexels-images.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'add', image_url: result.value.url, photographer: result.value.photographer }) }).then(res => res.json()).then(data => { if (data.success) { loadPexelsImages(); Swal.fire('Eklendi!', 'Yeni görsel başarıyla eklendi.', 'success'); } }); } });
}
</script>
