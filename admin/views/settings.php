<div class="settings-container">
    <div id="settings-form-container">
        <!-- Injected -->
    </div>
    <div class="form-actions" style="margin-top: 20px;">
        <button class="btn btn-primary" onclick="saveSettings()">Save Changes</button>
    </div>
</div>

<script>
    // Define available settings structure
    const settingGroups = {
        'General': [
            { key: 'site_title', label: 'Site Title', type: 'text' },
            { key: 'site_tagline', label: 'Tagline', type: 'text' },
            { key: 'logo_url', label: 'Logo URL', type: 'image' } // 'image' type could open media selector later
        ],
        'Contact': [
            { key: 'phone', label: 'Phone Number', type: 'text' },
            { key: 'email', label: 'Email Address', type: 'email' },
            { key: 'address', label: 'Address', type: 'textarea' },
            { key: 'map_coords', label: 'Map Coordinates', type: 'text', placeholder: 'lat,lng' }
        ],
        'Social': [
            { key: 'social_instagram', label: 'Instagram URL', type: 'url' },
            { key: 'social_facebook', label: 'Facebook URL', type: 'url' },
            { key: 'whatsapp_number', label: 'WhatsApp Number', type: 'text' }
        ],
        'SEO': [
            { key: 'seo_default_title', label: 'Default Meta Title', type: 'text' },
            { key: 'seo_default_desc', label: 'Default Meta Description', type: 'textarea' }
        ],
        'Design': [
            { key: 'primary_color', label: 'Primary Brand Color (Start)', type: 'color', default: '#0ea5e9' },
            { key: 'secondary_color', label: 'Secondary Brand Color (End)', type: 'color', default: '#0284c7' }
        ]
    };

    let currentSettings = {};

    document.addEventListener('DOMContentLoaded', () => {
        loadSettings();
    });

    function loadSettings() {
        fetch('/api/settings.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Flatten settings for easier lookup
                    const flat = {};
                    if (data.settings) {
                        Object.values(data.settings).forEach(group => {
                            group.forEach(s => {
                                flat[s.key] = s.value;
                            });
                        });
                    }
                    currentSettings = flat;
                    renderSettingsForm();
                }
            });
    }

    function renderSettingsForm() {
        const container = document.getElementById('settings-form-container');
        let html = '';

        for (const [groupName, fields] of Object.entries(settingGroups)) {
            html += `<div class="settings-group card">
            <div class="card-header"><h3>${groupName}</h3></div>
            <div class="card-body">`;

            fields.forEach(field => {
                const value = currentSettings[field.key] || '';
                html += `<div class="form-group">
                <label>${field.label}</label>`;

                if (field.type === 'textarea') {
                    html += `<textarea class="form-control" id="setting-${field.key}" rows="3">${value}</textarea>`;
                } else if (field.type === 'image') {
                    html += `
                    <div style="display: flex; gap: 10px;">
                        <input type="text" class="form-control" id="setting-${field.key}" value="${value}" placeholder="${field.placeholder || ''}">
                        <button class="btn btn-secondary" onclick="openMediaSelector('${field.key}')" style="white-space: nowrap;">
                            <i class="fas fa-images"></i> Seç
                        </button>
                    </div>`;
                } else if (field.type === 'color') {
                    html += `
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="color" class="form-control" id="setting-${field.key}" value="${value || field.default}" style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                        <span style="font-family: monospace; color: #666;">${value || field.default}</span>
                    </div>`;
                } else {
                    html += `<input type="${field.type}" class="form-control" id="setting-${field.key}" value="${value}" placeholder="${field.placeholder || ''}">`;
                }

                html += `</div>`;
            });

            html += `</div></div>`;
        }

        container.innerHTML = html;
    }

    function saveSettings() {
        const data = {};
        for (const [groupName, fields] of Object.entries(settingGroups)) {
            fields.forEach(field => {
                const el = document.getElementById(`setting-${field.key}`);
                if (el) {
                    data[field.key] = el.value;
                }
            });
        }

        fetch('/api/settings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ settings: data })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Settings saved successfully', 'success');
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            });
    }
</script>

<!-- Media Selector Modal -->
<div id="media-selector-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content animate-slide-up">
        <div class="modal-header">
            <h3>Medya Kütüphanesi</h3>
            <button class="close-btn" onclick="closeMediaSelector()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="ms-breadcrumbs" class="ms-breadcrumbs"></div>
            <div id="ms-grid" class="ms-grid">
                <!-- Content injected here -->
            </div>
        </div>
    </div>
</div>

<script>
    let msCurrentFolderId = null;
    let msSelectedField = null;

    function openMediaSelector(fieldKey) {
        msSelectedField = fieldKey;
        document.getElementById('media-selector-modal').style.display = 'flex';
        loadMediaSelector();
    }

    function closeMediaSelector() {
        document.getElementById('media-selector-modal').style.display = 'none';
    }

    function loadMediaSelector(folderId = null) {
        msCurrentFolderId = folderId;
        const grid = document.getElementById('ms-grid');
        grid.innerHTML = '<div class="ms-loading"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>';

        fetch(`/api/media.php?action=list&folder_id=${folderId || ''}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderMSBreadcrumbs(data.breadcrumbs);
                    renderMSContent(data.folders, data.files);
                }
            });
    }

    function renderMSBreadcrumbs(crumbs) {
        const container = document.getElementById('ms-breadcrumbs');
        container.innerHTML = crumbs.map((c, index) => {
            if (index === crumbs.length - 1) return `<span>${c.name}</span>`;
            return `<a href="javascript:void(0)" onclick="loadMediaSelector('${c.id || ''}')">${c.name}</a> / `;
        }).join('');
    }

    function renderMSContent(folders, files) {
        const grid = document.getElementById('ms-grid');
        grid.innerHTML = '';

        if (folders.length === 0 && files.length === 0) {
            grid.innerHTML = '<p class="ms-empty">Bu klasör boş.</p>';
            return;
        }

        // Folders
        folders.forEach(f => {
            const div = document.createElement('div');
            div.className = 'ms-item ms-folder';
            div.innerHTML = `<i class="fas fa-folder"></i> <span class="ms-name">${f.name}</span>`;
            div.onclick = () => loadMediaSelector(f.id);
            grid.appendChild(div);
        });

        // Files
        files.forEach(f => {
            const div = document.createElement('div');
            div.className = 'ms-item ms-file';
            div.innerHTML = `
                <div class="ms-thumb" style="background-image: url('${f.public_url}')"></div>
                <span class="ms-name">${f.alt || 'Görsel'}</span>
            `;
            div.onclick = () => selectImage(f.public_url);
            grid.appendChild(div);
        });
    }

    function selectImage(url) {
        const input = document.getElementById(`setting-${msSelectedField}`);
        if (input) {
            input.value = url;
            closeMediaSelector();
            
            // Show preview if possible or just visual feedback
            Swal.fire({
                icon: 'success',
                title: 'Görsel Seçildi',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        }
    }
</script>

<style>
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: white;
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 { margin: 0; font-size: 1.2rem; color: #334155; }
    .close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; }

    .modal-body {
        padding: 20px;
        overflow-y: auto;
        flex-grow: 1;
    }

    .ms-breadcrumbs {
        margin-bottom: 20px;
        font-size: 0.9rem;
        color: #64748b;
    }
    .ms-breadcrumbs a { color: var(--brand-600); text-decoration: none; }

    .ms-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 15px;
    }

    .ms-item {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8fafc;
    }

    .ms-item:hover {
        background: white;
        border-color: var(--brand-500);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transform: translateY(-2px);
    }

    .ms-folder i { font-size: 2rem; color: #f59e0b; margin-bottom: 8px; display: block; }
    
    .ms-thumb {
        width: 100%;
        height: 100px;
        background-size: cover;
        background-position: center;
        border-radius: 4px;
        margin-bottom: 8px;
    }

    .ms-name {
        display: block;
        font-size: 0.8rem;
        color: #475569;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ms-loading, .ms-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px;
        color: #94a3b8;
    }
    
    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

<style>
    .settings-group {
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }

    .settings-group .card-header {
        background: #f8f9fa;
        padding: 10px 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .settings-group .card-header h3 {
        margin: 0;
        font-size: 1.1em;
    }

    .settings-group .card-body {
        padding: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 1em;
    }

    .form-control:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }

    .btn-primary {
        background: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
    }

    .btn-primary:hover {
        background: #0056b3;
    }
</style>