import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion, AnimatePresence } from 'framer-motion';

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
            inputPlaceholder: 'Örn: Villalar, Düğün Çekimleri...',
            showCancelButton: true,
            confirmButtonText: 'Oluştur',
            cancelButtonText: 'İptal',
            customClass: {
                confirmButton: 'bg-blue-600 rounded-xl px-6 py-2.5 font-bold',
                cancelButton: 'bg-gray-100 text-gray-600 rounded-xl px-6 py-2.5 font-bold'
            }
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
            Swal.fire({
                title: 'Yüklendi',
                icon: 'success',
                timer: 1000,
                showConfirmButton: false
            });
        } catch (error) {
            Swal.fire('Hata', 'Yükleme başarısız', 'error');
        } finally {
            setUploading(false);
        }
    };

    const copyToClipboard = (text, type = 'url') => {
        navigator.clipboard.writeText(text);
        Swal.fire({
            title: type === 'url' ? 'Link Kopyalandı' : 'Kod Kopyalandı',
            text: type === 'url' ? 'Resim adresi panoya kopyalandı.' : 'Galeri kodu panoya kopyalandı.',
            icon: 'success',
            timer: 1000,
            showConfirmButton: false,
            toast: true,
            position: 'bottom-end'
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
            text: 'Klasörün içindeki her şey silinecektir (Dosyalar varsa önce onları silmelisiniz).',
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
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-6"
        >
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Medya Kütüphanesi</h1>
                    <p className="text-gray-500 text-sm">Görsellerinizi ve galeri klasörlerinizi yönetin</p>
                </div>
                <div className="flex gap-2">
                    <button
                        onClick={handleCreateFolder}
                        className="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl font-bold transition-all shadow-sm flex items-center gap-2"
                    >
                        <span>📁</span> Yeni Klasör
                    </button>
                    <label className={`px-5 py-2.5 ${uploading ? 'bg-blue-400' : 'bg-blue-600 hover:bg-blue-700'} text-white rounded-xl font-bold cursor-pointer transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2`}>
                        <span>{uploading ? '⌛' : '📤'}</span>
                        {uploading ? 'Yükleniyor...' : 'Dosya Yükle'}
                        <input type="file" className="hidden" onChange={handleUpload} disabled={uploading} accept="image/*" />
                    </label>
                </div>
            </div>

            {/* Breadcrumbs */}
            <div className="flex items-center gap-3 p-4 bg-white/50 backdrop-blur-sm rounded-2xl border border-gray-100">
                <button
                    onClick={() => setCurrentFolder(null)}
                    className={`px-3 py-1 rounded-lg text-xs font-black tracking-widest uppercase transition-colors ${!currentFolder ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-blue-500'}`}
                >
                    ROOT
                </button>
                {currentFolder && (
                    <div className="flex items-center gap-3">
                        <span className="text-gray-300">/</span>
                        <button
                            onClick={() => setCurrentFolder(parentId)}
                            className="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-blue-500 hover:bg-blue-50 transition-colors"
                        >
                            <span className="text-sm">↩</span> Geri Dön
                        </button>
                    </div>
                )}
            </div>

            {loading ? (
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6 animate-pulse">
                    {[...Array(12)].map((_, i) => (
                        <div key={i} className="aspect-square bg-gray-100 rounded-3xl" />
                    ))}
                </div>
            ) : (
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-6">
                    <AnimatePresence mode="popLayout">
                        {/* Folders */}
                        {folders.map(folder => (
                            <motion.div
                                layout
                                key={folder.id}
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                exit={{ opacity: 0, scale: 0.9 }}
                                className="group relative aspect-square bg-white rounded-3xl shadow-sm border border-gray-100 hover:border-blue-400 hover:shadow-xl hover:shadow-blue-500/5 transition-all p-4 flex flex-col items-center justify-center cursor-pointer"
                                onClick={() => setCurrentFolder(folder.id)}
                            >
                                <div className="text-5xl mb-2 drop-shadow-sm group-hover:scale-110 transition-transform">📂</div>
                                <span className="text-[11px] font-bold text-gray-700 text-center truncate w-full px-2 uppercase tracking-tighter">
                                    {folder.name}
                                </span>
                                <button
                                    onClick={(e) => { e.stopPropagation(); handleDeleteFolder(folder.id); }}
                                    className="absolute top-3 right-3 opacity-0 group-hover:opacity-100 p-1.5 text-red-500 hover:bg-red-50 rounded-xl transition-all"
                                >
                                    🗑️
                                </button>
                            </motion.div>
                        ))}

                        {/* Files */}
                        {files.map(file => (
                            <motion.div
                                layout
                                key={file.id}
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                exit={{ opacity: 0, scale: 0.9 }}
                                className="group relative aspect-square bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:border-blue-400 hover:shadow-xl hover:shadow-blue-500/5 transition-all"
                            >
                                <div className="w-full h-full relative">
                                    {file.mime_type?.startsWith('image/') ? (
                                        <img src={file.public_url} alt={file.alt} className="w-full h-full object-cover" />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center text-4xl bg-gray-50">📄</div>
                                    )}

                                    {/* Actions Overlay */}
                                    <div className="absolute inset-0 bg-blue-900/60 opacity-0 group-hover:opacity-100 transition-all flex flex-col items-center justify-center gap-3">
                                        <div className="flex gap-2">
                                            <button
                                                onClick={() => copyToClipboard(file.public_url)}
                                                className="w-10 h-10 bg-white rounded-xl text-blue-600 shadow-xl hover:scale-110 active:scale-95 transition-all flex items-center justify-center"
                                                title="Link Kopyala"
                                            >
                                                🔗
                                            </button>
                                            <button
                                                onClick={() => copyToClipboard(`[gallery id="${file.folder_id}"]`, 'shortcode')}
                                                className="w-10 h-10 bg-white rounded-xl text-green-600 shadow-xl hover:scale-110 active:scale-95 transition-all flex items-center justify-center"
                                                title="Galeri Kodu Kopyala"
                                            >
                                                🖼️
                                            </button>
                                        </div>
                                        <div className="text-[10px] text-white/80 font-black tracking-widest uppercase">Kopyala</div>
                                    </div>
                                </div>
                                <div className="absolute top-2 right-2 flex gap-1">
                                    <button
                                        onClick={() => handleDeleteFile(file.id)}
                                        className="opacity-0 group-hover:opacity-100 w-7 h-7 bg-red-500/80 backdrop-blur-md text-white rounded-lg flex items-center justify-center text-xs hover:bg-red-600 transition-all shadow-lg"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <div className="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                    <p className="text-[9px] font-bold text-white truncate uppercase tracking-tighter">{(file.file_size / 1024).toFixed(1)} KB</p>
                                </div>
                            </motion.div>
                        ))}
                    </AnimatePresence>

                    {folders.length === 0 && files.length === 0 && (
                        <div className="col-span-full py-20 flex flex-col items-center justify-center text-gray-300 border-2 border-dashed border-gray-100 rounded-[3rem] bg-gray-50/20">
                            <span className="text-6xl mb-4">🏜️</span>
                            <p className="font-black uppercase text-xs tracking-[0.2em] opacity-30">Buralar Çok Issız...</p>
                        </div>
                    )}
                </div>
            )}
        </motion.div>
    );
}
