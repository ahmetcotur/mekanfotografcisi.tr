<div class="media-manager-container">
    <div class="media-toolbar">
        <div class="breadcrumbs" id="media-breadcrumbs">
            <!-- Injected via JS -->
            <span>Home</span>
        </div>
        <div class="actions">
            <button class="btn btn-secondary" onclick="openNewFolderModal()"><i class="fas fa-folder-plus"></i> New
                Folder</button>
            <button class="btn btn-primary" onclick="triggerUpload()"><i class="fas fa-cloud-upload-alt"></i>
                Upload</button>
            <input type="file" id="media-upload-input" multiple style="display: none;"
                onchange="handleFileSelect(this)">
        </div>
    </div>

    <!-- Drag Drop Zone -->
    <div id="drop-zone" class="media-drop-zone">
        <div class="media-content">
            <div class="section-folders">
                <h3>Folders</h3>
                <div class="folder-grid" id="folder-list">
                    <!-- Injected -->
                </div>
            </div>

            <div class="section-files">
                <h3>Files</h3>
                <div class="file-grid" id="file-list">
                    <!-- Injected -->
                </div>
            </div>
        </div>
        <div class="drop-overlay">
            <span>Drop files here to upload</span>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    let currentFolderId = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadMedia();
        setupDragDrop();
    });

    function loadMedia(folderId = null) {
        currentFolderId = folderId;
        fetch(`/api/media.php?action=list&folder_id=${folderId || ''}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderBreadcrumbs(data.breadcrumbs);
                    renderFolders(data.folders);
                    renderFiles(data.files);
                }
            });
    }

    function renderBreadcrumbs(crumbs) {
        const container = document.getElementById('media-breadcrumbs');
        container.innerHTML = crumbs.map((c, index) => {
            if (index === crumbs.length - 1) return `<span>${c.name}</span>`;
            return `<a href="#" onclick="loadMedia('${c.id || ''}')">${c.name}</a> / `;
        }).join('');
    }

    function renderFolders(folders) {
        const container = document.getElementById('folder-list');
        if (folders.length === 0) {
            container.innerHTML = '<p class="empty-msg">No folders</p>';
            return;
        }
        container.innerHTML = folders.map(f => `
        <div class="folder-item" onclick="loadMedia('${f.id}')">
            <i class="fas fa-folder"></i>
            <span class="name">${f.name}</span>
            <div class="folder-actions">
                <span class="copy-btn" title="Kısa kodu kopyala" onclick="event.stopPropagation(); copyShortcode('${f.id}', '${f.name}')"><i class="fas fa-code"></i></span>
                <span class="delete-btn" title="Sil" onclick="event.stopPropagation(); deleteItem('folder', '${f.id}')"><i class="fas fa-times"></i></span>
            </div>
        </div>
    `).join('');
    }

    function copyShortcode(id, name) {
        const shortcode = `[gallery id="${id}" title="${name}"]`;
        navigator.clipboard.writeText(shortcode).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Kısa kod kopyalandı!',
                showConfirmButton: false,
                timer: 2000
            });
        });
    }

    function renderFiles(files) {
        const container = document.getElementById('file-list');
        if (files.length === 0) {
            container.innerHTML = '<p class="empty-msg">No files</p>';
            return;
        }
        container.innerHTML = files.map(f => `
        <div class="file-item" onclick="showFileDetails('${f.public_url}')">
            <div class="thumb" style="background-image: url('${f.public_url}')"></div>
            <span class="name">${f.alt || 'image'}</span>
            <span class="delete-btn" onclick="event.stopPropagation(); deleteItem('file', '${f.id}')"><i class="fas fa-times"></i></span>
        </div>
    `).join('');
    }

    function openNewFolderModal() {
        Swal.fire({
            title: 'New Folder',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: 'Create',
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                createFolder(result.value);
            }
        });
    }

    function createFolder(name) {
        const formData = new FormData();
        formData.append('action', 'create_folder');
        formData.append('name', name);
        if (currentFolderId) formData.append('parent_id', currentFolderId);

        fetch('/api/media.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) loadMedia(currentFolderId);
                else Swal.fire('Error', data.error, 'error');
            });
    }

    function triggerUpload() {
        document.getElementById('media-upload-input').click();
    }

    function handleFileSelect(input) {
        if (input.files.length > 0) {
            uploadFiles(input.files);
        }
    }

    function uploadFiles(files) {
        const formData = new FormData();
        formData.append('action', 'upload');
        if (currentFolderId) formData.append('folder_id', currentFolderId);

        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        Swal.fire({
            title: 'Uploading...',
            didOpen: () => Swal.showLoading()
        });

        fetch('/api/media.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    loadMedia(currentFolderId);
                    const errorCount = data.errors.length;
                    if (errorCount > 0) {
                        Swal.fire('Completed with errors', `${data.files.length} uploaded, ${errorCount} failed`, 'warning');
                    } else {
                        Swal.fire('Success', `${data.files.length} files uploaded`, 'success');
                    }
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            });
    }

    function deleteItem(type, id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('type', type);
                formData.append('id', id);

                fetch('/api/media.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) loadMedia(currentFolderId);
                        else Swal.fire('Error', data.error, 'error');
                    });
            }
        })
    }

    function showFileDetails(url) {
        Swal.fire({
            imageUrl: url,
            imageHeight: 400,
            imageAlt: 'Preview',
            html: `<input type="text" value="${url}" class="swal2-input" readonly onclick="this.select()">`,
            showConfirmButton: false,
            showCloseButton: true
        });
    }

    function setupDragDrop() {
        const dropZone = document.getElementById('drop-zone');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('highlight');
        }

        function unhighlight(e) {
            dropZone.classList.remove('highlight');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            uploadFiles(files);
        }
    }
</script>

<style>
    .media-manager-container {
        padding: 20px;
    }

    .media-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .breadcrumbs a {
        color: #007bff;
        text-decoration: none;
        cursor: pointer;
    }

    .media-drop-zone {
        border: 2px dashed #ccc;
        border-radius: 8px;
        min-height: 400px;
        position: relative;
        background: #fff;
    }

    .media-drop-zone.highlight {
        border-color: #007bff;
        background: #f0f7ff;
    }

    .media-content {
        padding: 20px;
    }

    .section-folders,
    .section-files {
        margin-bottom: 30px;
    }

    .folder-grid,
    .file-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .folder-item {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        position: relative;
        transition: all 0.2s;
    }

    .folder-item:hover {
        background: #e2e6ea;
        transform: translateY(-2px);
    }

    .folder-item i.fa-folder {
        font-size: 2.5em;
        color: #ffc107;
        display: block;
        margin-bottom: 10px;
    }

    .file-item {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 5px;
        cursor: pointer;
        position: relative;
        transition: all 0.2s;
    }

    .file-item:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .file-item .thumb {
        width: 100%;
        height: 120px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 4px;
        margin-bottom: 5px;
    }

    .file-item .name,
    .folder-item .name {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.85em;
        margin-top: 4px;
    }

    .folder-item:hover .folder-actions {
        display: flex;
    }

    .folder-actions {
        position: absolute;
        top: 5px;
        right: 5px;
        display: none;
        gap: 5px;
    }

    .delete-btn, .copy-btn {
        color: #fff;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        line-height: 24px;
        text-align: center;
        font-size: 0.8em;
        cursor: pointer;
        transition: transform 0.1s;
    }

    .delete-btn { background: #dc3545; }
    .copy-btn { background: #17a2b8; }

    .delete-btn:hover, .copy-btn:hover {
        transform: scale(1.1);
    }

    .file-item:hover .delete-btn {
        display: block;
    }

    .drop-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5em;
        color: #007bff;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .media-drop-zone.highlight .drop-overlay {
        opacity: 1;
    }

    .empty-msg {
        color: #aaa;
        font-style: italic;
    }
</style>