<?php
// Services List View

// Pagination
$per_page = 20;
$p = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$offset = ($p - 1) * $per_page;

// Fetch Services
$services = $db->select('posts', [
    'post_type' => 'service',
    'limit' => $per_page,
    'offset' => $offset,
    'order' => 'menu_order ASC, title ASC'
]);

$total_services = count($db->select('posts', ['post_type' => 'service']));
$total_pages = ceil($total_services / $per_page);
?>

<div class="page-subheader" style="margin-bottom: 20px;">
    <a href="/admin/?page=editor&type=service" class="btn btn-success">
        <i class="fas fa-plus"></i> Yeni Hizmet Ekle
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Hizmet Adı</th>
                    <th>Slug</th>
                    <th>Görsel</th>
                    <th>Durum</th>
                    <th width="150">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service):
                    // Fetch thumbnail if available (from metadata)
                    $thumb_meta = $db->select('post_meta', ['post_id' => $service['id'], 'meta_key' => 'thumbnail_url']);
                    $thumb = !empty($thumb_meta) ? $thumb_meta[0]['meta_value'] : null;
                    if ($thumb)
                        $thumb = json_decode($thumb, true); // It's stored as JSON string "url"
                    ?>
                    <tr>
                        <td>
                            <?= $service['menu_order'] ?>
                        </td>
                        <td>
                            <strong>
                                <?= htmlspecialchars($service['title']) ?>
                            </strong>
                        </td>
                        <td>/
                            <?= htmlspecialchars($service['slug']) ?>
                        </td>
                        <td>
                            <?php if ($thumb): ?>
                                <img src="<?= htmlspecialchars($thumb) ?>" alt="" style="height: 40px; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color: #ccc;"><i class="fas fa-image"></i> No Image</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="service-status-toggle" data-id="<?= $service['id'] ?>"
                                    <?= $service['post_status'] === 'publish' ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                            <div style="font-size: 0.8rem; color: #666; margin-top: 4px;">
                                <?= $service['post_status'] === 'publish' ? 'Yayında' : 'Taslak' ?>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/?page=editor&id=<?= $service['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/<?= $service['slug'] ?>" target="_blank" class="btn btn-sm btn-light">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="/admin/?page=services&p=<?= $i ?>" class="page-link <?= $i === $p ? 'active' : '' ?>">
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

    input:focus+.slider {
        box-shadow: 0 0 1px #28a745;
    }

    input:checked+.slider:before {
        transform: translateX(16px);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggles = document.querySelectorAll('.service-status-toggle');

        toggles.forEach(toggle => {
            toggle.addEventListener('change', function () {
                const serviceId = this.dataset.id;
                const newStatus = this.checked ? 'publish' : 'draft';

                // Disable to prevent multiple clicks
                this.disabled = true;

                // Find the status text element (sibling div)
                const statusLabel = this.closest('td').querySelector('div');

                fetch('/api/admin-update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        table: 'posts', // We are updating posts table directly since refactor
                        id: serviceId,
                        data: {
                            post_status: newStatus
                        }
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update label text
                            if (statusLabel) {
                                statusLabel.textContent = newStatus === 'publish' ? 'Yayında' : 'Taslak';
                            }
                            console.log('Status updated');
                        } else {
                            alert('Güncelleme hatası: ' + (data.error || 'Bilinmiyor'));
                            // Revert
                            this.checked = !this.checked;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Bir hata oluştu.');
                        this.checked = !this.checked;
                    })
                    .finally(() => {
                        this.disabled = false;
                    });
            });
        });
    });
</script>