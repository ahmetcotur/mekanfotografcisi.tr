<?php
// Location Editor View
// Handles both Provinces and Districts

$type = $_GET['type'] ?? ''; // 'province' or 'district'
$id = $_GET['id'] ?? null;

if (!$type || !$id) {
    echo '<div class="alert alert-danger">Geçersiz parametreler.</div>';
    return;
}

$table = ($type === 'province') ? 'locations_province' : 'locations_district';
$item = $db->select($table, ['id' => $id]);

if (empty($item)) {
    echo '<div class="alert alert-danger">Kayıt bulunamadı.</div>';
    return;
}

$item = $item[0];
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="text-xl font-bold">
            <?= $type === 'province' ? 'İl Düzenle' : 'İlçe Düzenle' ?>:
            <?= htmlspecialchars($item['name']) ?>
        </h2>
    </div>

    <div class="card-body">
        <form id="locationForm">
            <input type="hidden" name="table" value="<?= $table ?>">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">

            <div class="form-group mb-4">
                <label class="form-label font-bold mb-2 block">Ad</label>
                <input type="text" name="name" class="form-control w-full p-2 border rounded"
                    value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>

            <div class="form-group mb-4">
                <label class="form-label font-bold mb-2 block">Slug (URL)</label>
                <input type="text" name="slug" class="form-control w-full p-2 border rounded"
                    value="<?= htmlspecialchars($item['slug']) ?>" required>
                <small class="text-gray-500">URL yapısında kullanılır.</small>
            </div>

            <div class="form-group mb-6">
                <label class="form-label font-bold mb-2 block">Durum</label>
                <select name="is_active" class="form-control w-full p-2 border rounded">
                    <option value="true" <?= $item['is_active'] ? 'selected' : '' ?>>Aktif</option>
                    <option value="false" <?= !$item['is_active'] ? 'selected' : '' ?>>Pasif</option>
                </select>
            </div>

            <div class="flex gap-4">
                <button type="submit"
                    class="btn btn-success bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-save"></i> Kaydet
                </button>
                <a href="/admin/?page=locations"
                    class="btn btn-light bg-gray-200 text-gray-800 px-6 py-2 rounded hover:bg-gray-300">
                    İptal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('locationForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Collect data manually to handle hidden inputs and format
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            if (key !== 'table' && key !== 'id') {
                data[key] = value;
            }
        });

        const table = formData.get('table');
        const id = formData.get('id');

        fetch('/api/admin-update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                table: table,
                id: id,
                data: data
            })
        })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    alert('Güncelleme başarılı.');
                    window.location.href = '/admin/?page=locations';
                } else {
                    alert('Hata: ' + result.error);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Bir hata oluştu.');
            });
    });
</script>