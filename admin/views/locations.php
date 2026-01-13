<?php
// Locations View (Hierarchical)

// Pagination & Filtering
$per_page = 100;
$p = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$offset = ($p - 1) * $per_page;

// Filtering params
$search_name = $_GET['q'] ?? '';
$search_plate = $_GET['plate'] ?? '';

// Build Query
$conditions = [];
if (!empty($search_name)) {
    // DatabaseClient uses simple key-value for WHERE, but for LIKE we might need raw SQL or specific support.
    // Assuming simple exact match for now if Library doesn't support LIKE comfortably via array. 
    // BUT looking at previous usages, maybe we can't do LIKE easily via select(). 
    // Let's Fetch All and Filter PHP-side for now to avoid breaking DatabaseClient absraction 
    // OR use raw query if available.
    // The DatabaseClient seems to support key => value. 
    // Let's rely on fetching reduced set if possible.
    // Actually, DatabaseClient usually iterates.
    // Let's restrict by simple WHERE if exact, or fetch all then filter. 
    // Given 81 provinces, Fetch All is cheap.
}

// 1. Fetch Provinces
// For 81 rows, fetching all is fine.
$all_provinces = $db->select('locations_province', ['order' => 'name ASC']);

// Filter in PHP
$provinces = [];
foreach ($all_provinces as $prov) {
    if (!empty($search_name) && stripos($prov['name'], $search_name) === false)
        continue;
    if (!empty($search_plate) && $prov['plate_code'] != $search_plate)
        continue;
    $provinces[] = $prov;
}

// Pagination logic on filtered result
$total_provinces = count($provinces);
$total_pages = ceil($total_provinces / $per_page);
$provinces = array_slice($provinces, $offset, $per_page);

// 2. Fetch Districts for these Provinces
// ... (rest is same)
$province_ids = array_column($provinces, 'id');
$districts_grouped = [];

if (!empty($province_ids)) {
    // ...
    $districts = $db->select('locations_district', [
        'province_id.in' => $province_ids,
        'order' => 'name ASC'
    ]);
    // ... group ...
    foreach ($districts as $district) {
        $districts_grouped[$district['province_id']][] = $district;
    }
}
?>

<div class="card">
    <div class="card-header" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <h2 class="text-xl font-bold">Lokasyon Yönetimi</h2>

            <!-- Filter Form -->
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="page" value="locations">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="İl Ara..."
                    value="<?= htmlspecialchars($search_name) ?>">
                <input type="number" name="plate" class="form-control form-control-sm" placeholder="Plaka Kodu"
                    style="width: 100px;" value="<?= htmlspecialchars($search_plate) ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Ara</button>
                <?php if (!empty($search_name) || !empty($search_plate)): ?>
                    <a href="/admin/?page=locations" class="btn btn-secondary btn-sm">Temizle</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div id="bulkActions" style="display: none; margin-top: 10px;">
            <span id="selectedCount" style="margin-right: 10px; font-weight: bold;"></span>
            <button onclick="bulkUpdate(true)" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Aktif
                Yap</button>
            <button onclick="bulkUpdate(false)" class="btn btn-warning btn-sm"><i class="fas fa-ban"></i> Pasif
                Yap</button>
            <button onclick="bulkDelete()" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Seçilileri
                Sil</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="40"><input type="checkbox" id="selectAll"></th>
                    <th width="50"></th> <!-- Expand Icon -->
                    <th>Ad</th>
                    <th>Slug</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($provinces as $prov):
                    $prov_districts = $districts_grouped[$prov['id']] ?? [];
                    $has_districts = !empty($prov_districts);
                    ?>
                    <!-- PROVINCE ROW -->
                    <tr class="province-row bg-gray-50">
                        <td><input type="checkbox" class="row-checkbox" value="<?= $prov['id'] ?>"
                                data-table="locations_province"></td>
                        <td>
                            <?php if ($has_districts): ?>
                                <button class="btn btn-sm btn-light toggle-districts" data-id="<?= $prov['id'] ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($prov['name']) ?></strong> (<?= count($prov_districts) ?> İlçe)
                            <?php if (!$has_districts): ?>
                                <button onclick="fetchAndAddLocations('districts', '<?= $prov['id'] ?>')"
                                    class="btn btn-xs btn-outline-primary ml-2">
                                    <i class="fas fa-plus-circle"></i> İlçeleri Getir
                                </button>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($prov['slug']) ?></td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="status-toggle" data-id="<?= $prov['id'] ?>"
                                    data-table="locations_province" <?= ($prov['is_active'] === true || $prov['is_active'] === 't' || $prov['is_active'] === 'true') ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                            <div style="font-size: 0.8rem; color: #666; margin-top: 4px;">
                                <?= ($prov['is_active'] === true || $prov['is_active'] === 't' || $prov['is_active'] === 'true') ? 'Aktif' : 'Pasif' ?>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/?page=location-editor&type=province&id=<?= $prov['id'] ?>"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteLocation('locations_province', '<?= $prov['id'] ?>')"
                                    class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <a href="/locations/<?= $prov['slug'] ?>" target="_blank" class="btn btn-sm btn-light">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- DISTRICTS ROWS (Hidden by default) -->
                    <?php if ($has_districts): ?>
                    <tbody id="districts-<?= $prov['id'] ?>" style="display: none; background-color: #f9f9f9;">
                        <?php foreach ($prov_districts as $dist): ?>
                            <tr>
                                <td style="padding-left: 20px;">
                                    <input type="checkbox" class="row-checkbox" value="<?= $dist['id'] ?>"
                                        data-table="locations_district">
                                </td>
                                <td></td> <!-- No expand icon -->
                                <td style="padding-left: 40px; color: #555;">
                                    <i class="fas fa-map-pin" style="font-size: 0.8rem; margin-right: 5px;"></i>
                                    <?= htmlspecialchars($dist['name']) ?>

                                    <button onclick="fetchAndAddLocations('towns', '<?= $dist['id'] ?>')"
                                        class="btn btn-xs btn-outline-secondary ml-2" style="font-size: 0.7rem; padding: 2px 5px;">
                                        <i class="fas fa-plus"></i> Beldeleri Getir
                                    </button>
                                </td>
                                <td style="color: #666;"><?= htmlspecialchars($dist['slug']) ?></td>
                                <td>
                                    <label class="switch" style="transform: scale(0.8);">
                                        <input type="checkbox" class="status-toggle" data-id="<?= $dist['id'] ?>"
                                            data-table="locations_district"                    <?= ($dist['is_active'] === true || $dist['is_active'] === 't' || $dist['is_active'] === 'true') ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span
                                        style="font-size: 0.75rem; margin-left: 5px;"><?= ($dist['is_active'] === true || $dist['is_active'] === 't' || $dist['is_active'] === 'true') ? 'Aktif' : 'Pasif' ?></span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/admin/?page=location-editor&type=district&id=<?= $dist['id'] ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteLocation('locations_district', '<?= $dist['id'] ?>')"
                                            class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php $dist_slug = "locations/" . $prov['slug'] . "/" . $dist['slug']; ?>
                                        <a href="/<?= $dist_slug ?>" target="_blank" class="btn btn-sm btn-light">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endif; ?>

            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="/admin/?page=locations&p=<?= $i ?>" class="page-link <?= $i === $p ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #28a745;
    }

    input:checked+.slider:before {
        transform: translateX(16px);
    }

    .btn-xs {
        padding: 2px 6px;
        font-size: 11px;
        line-height: 1.2;
        border-radius: 3px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // 1. Expand/Collapse Logic
        document.querySelectorAll('.toggle-districts').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const target = document.getElementById('districts-' + id);
                const icon = this.querySelector('i');

                if (target.style.display === 'none') {
                    target.style.display = 'table-row-group';
                    icon.className = 'fas fa-chevron-down';
                    this.classList.add('active');
                } else {
                    target.style.display = 'none';
                    icon.className = 'fas fa-chevron-right';
                    this.classList.remove('active');
                }
            });
        });

        // 2. Status Toggle Logic (Supports table data attribute)
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const id = this.dataset.id;
                const table = this.dataset.table; // Get table dynamically
                const newStatus = this.checked;

                this.disabled = true;
                // For district rows, label is simpler
                const statusLabel = this.closest('td').querySelector('div');
                const statusSpan = this.closest('td').querySelector('span'); // for districts

                fetch('/api/admin-update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        table: table,
                        id: id,
                        data: { is_active: newStatus }
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const text = newStatus ? 'Aktif' : 'Pasif';
                            if (statusLabel) statusLabel.textContent = text;
                            if (statusSpan) statusSpan.textContent = text;
                        } else {
                            Swal.fire('Hata', data.error, 'error');
                            this.checked = !this.checked;
                        }
                    })
                    .catch(e => {
                        console.error(e);
                        this.checked = !this.checked;
                    })
                    .finally(() => this.disabled = false);
            });
        });

        // 3. Bulk Selection Logic
        const selectAll = document.getElementById('selectAll');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCountSpan = document.getElementById('selectedCount');

        function getSelectedCheckboxes() {
            return document.querySelectorAll('.row-checkbox:checked');
        }

        function updateBulkUI() {
            const checked = getSelectedCheckboxes();
            if (checked.length > 0) {
                bulkActions.style.display = 'block';
                selectedCountSpan.textContent = checked.length + ' seçildi';
            } else {
                bulkActions.style.display = 'none';
            }
        }

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
            updateBulkUI();
        });

        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkUI);
        });

        // 4. Bulk Update Logic
        window.bulkUpdate = function (status) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Seçili kayıtlar ' + (status ? 'AKTİF' : 'PASİF') + ' yapılacak.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Evet, İşlemi Yap',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const checked = getSelectedCheckboxes();
                    const payload = { locations_province: [], locations_district: [] };

                    checked.forEach(cb => {
                        const table = cb.dataset.table;
                        if (payload[table]) payload[table].push(cb.value);
                    });

                    const promises = [];
                    if (payload.locations_province.length > 0) {
                        promises.push(fetch('/api/admin-update.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'bulk-update',
                                table: 'locations_province',
                                ids: payload.locations_province,
                                data: { is_active: status }
                            })
                        }));
                    }
                    if (payload.locations_district.length > 0) {
                        promises.push(fetch('/api/admin-update.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'bulk-update',
                                table: 'locations_district',
                                ids: payload.locations_district,
                                data: { is_active: status }
                            })
                        }));
                    }

                    Promise.all(promises)
                        .then(responses => Promise.all(responses.map(r => r.json())))
                        .then(results => {
                            let success = true;
                            let count = 0;
                            results.forEach(r => {
                                if (!r.success) success = false;
                                else count += r.updated_count;
                            });

                            if (success) {
                                Swal.fire('Başarılı', count + ' kayıt güncellendi.', 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Hata', 'Bazı işlemler başarısız oldu.', 'error');
                            }
                        })
                        .catch(e => Swal.fire('Hata', e.toString(), 'error'));
                }
            });
        }

        // 5. Bulk Delete Logic
        window.bulkDelete = function () {
            Swal.fire({
                title: 'Çoklu Silme İşlemi',
                text: 'Seçili TÜM kayıtlar silinecek! Eğer il seçtiyseniz, o ile bağlı tüm alt lokasyonlar da silinir.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Evet, Hepsini Sil',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const checked = getSelectedCheckboxes();
                    const payload = { locations_province: [], locations_district: [] };

                    checked.forEach(cb => {
                        const table = cb.dataset.table;
                        if (payload[table]) payload[table].push(cb.value);
                    });

                    const promises = [];
                    if (payload.locations_province.length > 0) {
                        promises.push(fetch('/api/admin-update.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'bulk-delete',
                                table: 'locations_province',
                                ids: payload.locations_province
                            })
                        }));
                    }
                    if (payload.locations_district.length > 0) {
                        promises.push(fetch('/api/admin-update.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'bulk-delete',
                                table: 'locations_district',
                                ids: payload.locations_district
                            })
                        }));
                    }

                    Promise.all(promises)
                        .then(responses => Promise.all(responses.map(r => r.json())))
                        .then(results => {
                            let count = 0;
                            results.forEach(r => count += r.deleted_count || 0);
                            Swal.fire('Silindi', count + ' kayıt başarıyla silindi.', 'success').then(() => location.reload());
                        })
                        .catch(e => Swal.fire('Hata', e.toString(), 'error'));
                }
            });
        }

        // 6. Delete Single Logic
        window.deleteLocation = function (table, id) {
            let warningText = 'Bu kaydı silmek istediğinize emin misiniz?';
            if (table === 'locations_province') {
                warningText += ' UYARI: Bir ili silerseniz, ona bağlı TÜM alt lokasyonlar da silinir!';
            }

            Swal.fire({
                title: 'Emin misiniz?',
                text: warningText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Evet, Sil',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/api/admin-update.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'delete',
                            table: table,
                            id: id
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Silindi', 'Kayıt başarıyla silindi.', 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Hata', data.error || 'Silme işlemi başarısız.', 'error');
                            }
                        })
                        .catch(e => Swal.fire('Hata', e.toString(), 'error'));
                }
            });
        }

        // 7. Fetch and Add Locations Logic
        window.fetchAndAddLocations = function (type, parentId) {
            let action = type === 'districts' ? 'get-missing-districts' : 'get-missing-towns';
            let payloadKey = type === 'districts' ? 'province_id' : 'district_id';
            let title = type === 'districts' ? 'Eksik İlçeleri Ekle' : 'Beldeleri Ekle';

            Swal.fire({
                title: 'Veriler Getiriliyor...',
                didOpen: () => { Swal.showLoading(); }
            });

            // 1. Fetch Candidates
            fetch('/api/admin-update.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action, [payloadKey]: parentId })
            })
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    if (!data.success) {
                        Swal.fire('Hata', data.error, 'error');
                        return;
                    }
                    if (!data.missing || data.missing.length === 0) {
                        Swal.fire('Bilgi', 'Eklenecek yeni kayıt bulunamadı veya veri mevcut değil.', 'info');
                        return;
                    }

                    // 2. Show Selection Modal
                    let html = '<div style="text-align: left; max-height: 300px; overflow-y: auto;">';
                    html += '<label><input type="checkbox" id="selectAllModal"> <strong>Tümünü Seç</strong></label><hr>';
                    data.missing.forEach((item, index) => {
                        html += `<div style="margin: 5px 0;">
                    <label>
                        <input type="checkbox" class="loc-checkbox" value="${index}"> 
                        <b>${item.name}</b>
                    </label>
                </div>`;
                    });
                    html += '</div>';

                    Swal.fire({
                        title: title,
                        html: html,
                        showCancelButton: true,
                        confirmButtonText: 'Seçilileri Ekle',
                        cancelButtonText: 'İptal',
                        width: '600px',
                        didOpen: () => {
                            const selectAll = document.getElementById('selectAllModal');
                            selectAll.addEventListener('change', function () {
                                document.querySelectorAll('.loc-checkbox').forEach(cb => cb.checked = this.checked);
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const selectedIndices = [];
                            document.querySelectorAll('.loc-checkbox:checked').forEach(cb => {
                                selectedIndices.push(parseInt(cb.value));
                            });

                            if (selectedIndices.length === 0) return;

                            const selectedItems = selectedIndices.map(i => data.missing[i]);
                            let addAction = type === 'districts' ? 'add-districts' : 'add-towns';
                            let itemsKey = type === 'districts' ? 'districts' : 'towns';

                            Swal.fire({ title: 'Ekleniyor...', didOpen: () => Swal.showLoading() });

                            fetch('/api/admin-update.php', {
                                method: 'POST', headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    action: addAction,
                                    [payloadKey]: parentId,
                                    [itemsKey]: selectedItems
                                })
                            })
                                .then(res => res.json())
                                .then(resData => {
                                    if (resData.success) {
                                        Swal.fire('Başarılı', resData.added_count + ' kayıt eklendi.', 'success')
                                            .then(() => location.reload());
                                    } else {
                                        Swal.fire('Hata', resData.error, 'error');
                                    }
                                });
                        }
                    });
                })
                .catch(e => Swal.fire('Hata', e.toString(), 'error'));
        }
    });
</script>