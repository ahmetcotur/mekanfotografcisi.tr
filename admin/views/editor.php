<?php
// Unified Content Editor

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'page'; // default new type
$post = null;

if ($id) {
    // Fetch existing post
    $result = $db->select('posts', ['id' => $id]);
    if (!empty($result)) {
        $post = $result[0];
        $type = $post['post_type'];
    }
} else {
    // New Post Defaults
    $post = [
        'id' => '',
        'title' => '',
        'slug' => '',
        'content' => '',
        'excerpt' => '',
        'post_type' => $type,
        'post_status' => 'draft',
        'menu_order' => 0,
        'gallery_folder_id' => null
    ];
}

// Fetch Medya Folders for selection
$folders = $db->select('media_folders', ['order' => 'name ASC']);

?>

<?php if (isset($_GET['saved'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Başarıyla Kaydedildi',
                text: 'İçerik başarıyla güncellendi.',
                timer: 2000,
                showConfirmButton: false
            });
        });
    </script>
<?php endif; ?>

<div class="card-grid" style="display: grid; grid-template-columns: 1fr 350px; gap: 24px;">
    <!-- Main Content Area -->
    <div class="card">
        <form action="/api/admin-update.php" method="POST" id="editorForm">
            <input type="hidden" name="action" value="save-post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($post['id']) ?>">
            <input type="hidden" name="post_type" value="<?= htmlspecialchars($post['post_type']) ?>">

            <div class="form-group">
                <label class="form-label">Başlık</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label">Slug (URL)</label>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($post['slug']) ?>"
                    placeholder="Otomatik oluşturulur">
            </div>

            <div class="form-group">
                <label class="form-label">İçerik</label>
                <textarea name="content" id="editorContent" class="form-control"
                    rows="25"><?= htmlspecialchars($post['content']) ?></textarea>
            </div>

            <div class="form-group" style="display: flex; gap: 10px; align-items: center; margin-top: 30px;">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                </button>
                <a href="/admin/?page=<?= $type === 'service' ? 'services' : 'seo-pages' ?>" class="btn btn-light btn-lg">
                    İptal
                </a>
            </div>
        </form>
    </div>

    <!-- Sidebar Area -->
    <div class="space-y-6">
        <!-- Status & Meta Card -->
        <div class="card">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-brand-500"></i> Yayın Bilgisi
            </h3>
            
            <div class="form-group">
                <label class="form-label">Yayın Durumu</label>
                <select name="post_status" form="editorForm" class="form-control">
                    <option value="publish" <?= $post['post_status'] === 'publish' ? 'selected' : '' ?>>Yayında</option>
                    <option value="draft" <?= $post['post_status'] === 'draft' ? 'selected' : '' ?>>Taslak</option>
                </select>
            </div>

            <?php if ($post['id'] && $post['slug']): ?>
                <hr class="my-4 border-slate-100">
                <a href="/<?= $post['slug'] ?>" target="_blank" class="btn btn-info btn-block">
                    <i class="fas fa-external-link-alt"></i> Sayfayı Görüntüle
                </a>
            <?php endif; ?>
        </div>

        <!-- Gallery Card -->
        <div class="card" style="border-top: 4px solid var(--brand-500);">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-images text-brand-500"></i> Galeri Ayarı
            </h3>
            <p class="text-xs text-slate-500 mb-4">Bu sayfa için hangi medya klasöründeki fotoğrafların gösterileceğini seçin.</p>
            
            <div class="form-group">
                <label class="form-label">Medya Klasörü</label>
                <select name="gallery_folder_id" form="editorForm" class="form-control" style="height: 45px;">
                    <option value="">-- Galeri Yok --</option>
                    <?php foreach($folders as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= ($post['gallery_folder_id'] ?? '') === $f['id'] ? 'selected' : '' ?>>
                            📁 <?= htmlspecialchars($f['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mt-4 pt-4 border-top border-slate-100">
                <a href="/admin/?page=media" class="text-sm font-medium text-brand-600 hover:underline">
                    <i class="fas fa-folder-plus"></i> Yeni Klasör Oluştur
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .card-grid { margin-bottom: 50px; }
    .btn-block { width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px; }
    .space-y-6 > * + * { margin-top: 1.5rem; }
    #editorContent { font-family: 'Inter', sans-serif; line-height: 1.6; }
</style>

<!-- TinyMCE Integration & AJAX Save -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize TinyMCE
        tinymce.init({
            selector: '#editorContent',
            height: 700,
            plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | charmap emoticons | fullscreen  preview save print | insertfile image media link anchor codesample',
            toolbar_sticky: true,
            image_advtab: true,
            content_style: `
                body { font-family:Inter,Helvetica,Arial,sans-serif; font-size:16px; line-height: 1.6; color: #334155; }
            `
        });

        // AJAX Form Submission
        const editorForm = document.getElementById('editorForm');
        editorForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Sync TinyMCE content to textarea
            tinymce.triggerSave();

            const formData = new FormData(editorForm);
            const submitBtn = editorForm.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;

            // Loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...';

            fetch('/api/admin-update.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarıyla Kaydedildi',
                        text: 'Tüm değişiklikler güncellendi.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    
                    // Update ID if it was a new post
                    if (data.id && !editorForm.querySelector('input[name="id"]').value) {
                        editorForm.querySelector('input[name="id"]').value = data.id;
                        window.history.replaceState(null, '', `?page=editor&id=${data.id}`);
                    }
                } else {
                    Swal.fire('Hata', data.error || 'Kaydedilirken bir sorun oluştu.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Hata', 'Sunucuya bağlanılamadı.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            });
        });
    });
</script>