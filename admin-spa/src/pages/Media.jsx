import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function Media() {
    const [folders, setFolders] = useState([]);
    const [files, setFiles] = useState([]);
    const [currentFolder, setCurrentFolder] = useState(null);
    const [parentId, setParentId] = useState(null);
    const [loading, setLoading] = useState(true);
    const [uploading, setUploading] = useState(false);

    useEffect(() => {
        loadMedia(currentFolder);
    }, [currentFolder]);

    const loadMedia = async (folderId) => {
        setLoading(true);
        try {
            const response = await api.get(`/media.php?action=list&folder_id=${folderId || ''}`);
            if (response.data.success) {
                setFolders(response.data.folders);
                setFiles(response.data.files);
                setParentId(response.data.parent_id);
            }
        } catch (error) {
            console.error('Failed to load media');
        } finally {
            setLoading(false);
        }
    };

    const handleCreateFolder = async () => {
        const { value: name } = await Swal.fire({
            title: 'Yeni Klasör',
            input: 'text',
            inputLabel: 'Klasör Adı',
            showCancelButton: true,
        });

        if (name) {
            try {
                await api.post('/media.php', { action: 'create-folder', name, parent_id: currentFolder });
                loadMedia(currentFolder);
            } catch (error) {
                Swal.fire('Hata', 'Klasör oluşturulamadı', 'error');
            }
        }
    };

    const handleUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        setUploading(true);
        const formData = new FormData();
        formData.append('file', file);
        formData.append('folder_id', currentFolder || '');
        formData.append('action', 'upload');

        try {
            await api.post('/media.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            loadMedia(currentFolder);
        } catch (error) {
            Swal.fire('Hata', 'Yükleme başarısız', 'error');
        } finally {
            setUploading(false);
        }
    };

    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text);
        Swal.fire({
            title: 'Kopyalandı!',
            text: 'Link panoya kopyalandı.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    };

    const handleDeleteFile = async (id) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu dosya kalıcı olarak silinecektir!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                await api.post('/media.php', { action: 'delete-file', id });
                loadMedia(currentFolder);
            } catch (error) {
                Swal.fire('Hata', 'Silinemedi', 'error');
            }
        }
    };

    const handleDeleteFolder = async (id) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Boş olmayan klasörler silinemez!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                await api.post('/media.php', { action: 'delete-folder', id });
                loadMedia(currentFolder);
            } catch (error) {
                Swal.fire('Hata', error.response?.data?.error || 'Silinemedi', 'error');
            }
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800">Medya Kütüphanesi</h1>
                <div className="flex gap-2">
                    <button
                        onClick={handleCreateFolder}
                        className="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg font-medium transition"
                    >
                        📁 Yeni Klasör
                    </button>
                    <label className="px-4 py-2 bg-blue-500 text-white hover:bg-blue-600 rounded-lg font-medium cursor-pointer transition">
                        {uploading ? 'Yükleniyor...' : '📤 Dosya Yükle'}
                        <input type="file" className="hidden" onChange={handleUpload} disabled={uploading} />
                    </label>
                </div>
            </div>

            {/* Breadcrumbs / Back button */}
            <div className="flex items-center gap-2 text-sm text-gray-500">
                <button onClick={() => setCurrentFolder(null)} className="hover:text-blue-600">Root</button>
                {currentFolder && (
                    <>
                        <span>/</span>
                        <button onClick={() => setCurrentFolder(parentId)} className="hover:text-blue-600">Geri Dön</button>
                    </>
                )}
            </div>

            {loading ? (
                <div className="text-center py-12">Yükleniyor...</div>
            ) : (
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    {/* Folders */}
                    {folders.map(folder => (
                        <div key={folder.id} className="group relative flex flex-col items-center p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-blue-200 transition">
                            <button
                                onClick={() => setCurrentFolder(folder.id)}
                                className="text-5xl mb-2"
                            >
                                📂
                            </button>
                            <span className="text-sm font-medium text-gray-700 text-center truncate w-full px-2">
                                {folder.name}
                            </span>
                            <button
                                onClick={() => handleDeleteFolder(folder.id)}
                                className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 p-1 text-red-500 hover:bg-red-50 rounded"
                            >
                                🗑️
                            </button>
                        </div>
                    ))}

                    {/* Files */}
                    {files.map(file => (
                        <div key={file.id} className="group relative flex flex-col items-center bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:border-blue-200 transition">
                            <div className="w-full h-32 bg-gray-50 relative">
                                {file.mime_type?.startsWith('image/') ? (
                                    <img src={file.public_url} alt={file.alt} className="w-full h-full object-cover" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-3xl">📄</div>
                                )}
                                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                    <button
                                        onClick={() => copyToClipboard(file.public_url)}
                                        className="p-2 bg-white rounded-lg text-blue-600 shadow-lg hover:scale-110 transition"
                                        title="Link Kopyala"
                                    >
                                        🔗
                                    </button>
                                    <button
                                        onClick={() => copyToClipboard(`[gallery id="${file.folder_id}"]`)}
                                        className="p-2 bg-white rounded-lg text-green-600 shadow-lg hover:scale-110 transition"
                                        title="Shortcode Kopyala"
                                    >
                                        📋
                                    </button>
                                </div>
                            </div>
                            <div className="p-2 w-full">
                                <p className="text-xs font-medium text-gray-700 truncate">{file.alt || 'Unnamed'}</p>
                                <p className="text-[10px] text-gray-400">{(file.file_size / 1024).toFixed(1)} KB</p>
                            </div>
                            <button
                                onClick={() => handleDeleteFile(file.id)}
                                className="absolute top-1 right-1 opacity-0 group-hover:opacity-100 p-1 bg-red-500 text-white rounded text-[10px]"
                            >
                                ✕
                            </button>
                        </div>
                    ))}

                    {folders.length === 0 && files.length === 0 && (
                        <div className="col-span-full py-12 text-center text-gray-400">
                            Bu klasör boş.
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
