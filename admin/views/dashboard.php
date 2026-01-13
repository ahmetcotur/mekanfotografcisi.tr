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