import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion, AnimatePresence } from 'framer-motion';

export default function Pexels() {
    const [images, setImages] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadImages();
    }, []);

    const loadImages = async () => {
        try {
            const response = await api.get('/pexels-images.php');
            if (response.data.success) {
                setImages(response.data.images);
            }
        } catch (error) {
            console.error('Failed to load images', error);
            Swal.fire('Hata', 'Görseller yüklenemedi', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleToggle = async (image) => {
        try {
            const newStatus = !image.is_visible;
            // Update UI optimistically
            setImages(prev => prev.map(img =>
                img.id === image.id ? { ...img, is_visible: newStatus } : img
            ));

            await api.post('/pexels-images.php', {
                action: 'toggle',
                id: image.id,
                is_visible: newStatus
            });
        } catch (error) {
            Swal.fire('Hata', 'Durum güncellenemedi', 'error');
            loadImages(); // Revert
        }
    };

    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu görsel koleksiyondan kaldırılacak.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                await api.post('/pexels-images.php', { action: 'delete', id });
                setImages(prev => prev.filter(img => img.id !== id));
                Swal.fire('Silindi', 'Görsel kaldırıldı.', 'success');
            } catch (error) {
                Swal.fire('Hata', 'Silinemedi', 'error');
            }
        }
    };

    const handleSync = async () => {
        setLoading(true);
        try {
            const response = await api.post('/pexels-images.php', { action: 'sync' });
            if (response.data.success) {
                Swal.fire('Eşitlendi', `${response.data.synced_count} yeni görsel eklendi.`, 'success');
                loadImages();
            }
        } catch (error) {
            console.error('Sync error:', error);
            const msg = error.response?.data?.error || error.message || 'Senkronizasyon başarısız oldu.';
            const detail = error.response?.data?.file ? `\nFile: ${error.response.data.file}:${error.response.data.line}` : '';
            Swal.fire('Hata', msg + detail, 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleAdd = async () => {
        const { value: url } = await Swal.fire({
            title: 'Yeni Pexels Görseli',
            input: 'url',
            inputLabel: 'Görsel URL (Pexels src)',
            inputPlaceholder: 'https://images.pexels.com/...',
            showCancelButton: true,
            confirmButtonText: 'Ekle',
            cancelButtonText: 'İptal'
        });

        if (url) {
            try {
                await api.post('/pexels-images.php', {
                    action: 'add',
                    image_url: url,
                    photographer: 'Admin Added'
                });
                loadImages();
                Swal.fire('Başarılı', 'Görsel eklendi', 'success');
            } catch (error) {
                Swal.fire('Hata', 'Ekleme başarısız', 'error');
            }
        }
    };

    if (loading) return <div className="p-8 text-center text-gray-500">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            className="space-y-6"
        >
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Pexels Koleksiyonu</h1>
                    <p className="text-gray-500 text-sm">Sitede rastgele gösterilen arka plan görsellerini yönetin.</p>
                </div>
                <div className="flex gap-2">
                    <button
                        onClick={handleSync}
                        disabled={loading}
                        className="px-6 py-2.5 bg-gray-600 text-white rounded-xl font-bold hover:bg-gray-700 transition-all shadow-sm"
                    >
                        🔄 Senkronize Et
                    </button>
                    <button
                        onClick={handleAdd}
                        className="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:scale-105 transition-all"
                    >
                        + Yeni Ekle
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <AnimatePresence>
                    {images.map(image => (
                        <motion.div
                            layout
                            key={image.id}
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.9 }}
                            className={`group relative rounded-3xl overflow-hidden shadow-sm border-2 transition-all ${image.is_visible ? 'border-transparent shadow-md' : 'border-red-200 grayscale opacity-70'}`}
                        >
                            <div className="aspect-[3/4] relative">
                                <img
                                    src={image.image_url}
                                    alt={image.photographer}
                                    className="w-full h-full object-cover"
                                    loading="lazy"
                                />

                                {/* Overlay Actions */}
                                <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3">
                                    <button
                                        onClick={() => handleToggle(image)}
                                        className={`px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wider ${image.is_visible ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`}
                                    >
                                        {image.is_visible ? 'Pasifleştir' : 'Aktifleştir'}
                                    </button>

                                    <button
                                        onClick={() => handleDelete(image.id)}
                                        className="p-2 bg-white/20 text-white rounded-full hover:bg-red-600 transition-colors"
                                        title="Sil"
                                    >
                                        🗑️
                                    </button>
                                </div>

                                {/* Status Badge */}
                                <div className="absolute top-3 right-3">
                                    <span className={`w-3 h-3 rounded-full block shadow-sm ${image.is_visible ? 'bg-green-500' : 'bg-red-500'}`}></span>
                                </div>
                            </div>

                            <div className="p-3 bg-white text-xs text-center text-gray-500 font-medium truncate">
                                {image.photographer || 'Fotoğrafçı Bilinmiyor'}
                            </div>
                        </motion.div>
                    ))}
                </AnimatePresence>
            </div>

            {images.length === 0 && (
                <div className="text-center py-20 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200 text-gray-400">
                    Henüz görsel eklenmemiş.
                </div>
            )}
        </motion.div>
    );
}
