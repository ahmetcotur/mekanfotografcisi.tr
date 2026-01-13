import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion } from 'framer-motion';

export default function Pages() {
    const [pages, setPages] = useState([]);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    useEffect(() => {
        loadPages();
    }, []);

    const loadPages = async () => {
        try {
            const response = await api.get('/admin-update.php?table=posts&action=list&post_type=page');
            if (response.data.success) {
                setPages(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load pages:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu sayfa kalıcı olarak silinecektir!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                await api.post('/admin-update.php', { action: 'delete', table: 'posts', id });
                loadPages();
                Swal.fire('Silindi!', 'Sayfa başarıyla silindi.', 'success');
            } catch (error) {
                Swal.fire('Hata', 'Silme işlemi başarısız', 'error');
            }
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-6"
        >
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Kurumsal Sayfalar</h1>
                <button
                    onClick={() => navigate('/pages/new')}
                    className="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition transform hover:-translate-y-0.5 active:scale-95"
                >
                    + Yeni Sayfa Ekle
                </button>
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Sayfa Başlığı</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">URL</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Durum</th>
                            <th className="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {pages.map((page) => (
                            <motion.tr
                                layout
                                key={page.id}
                                className="group hover:bg-blue-50/30 transition-colors"
                            >
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="text-sm font-semibold text-gray-800">{page.title}</div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="text-xs text-gray-400">/{page.slug}</div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase ${page.post_status === 'publish'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-amber-100 text-amber-700'
                                        }`}>
                                        {page.post_status === 'publish' ? 'YAYINDA' : 'TASLAK'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button
                                        onClick={() => navigate(`/pages/edit/${page.id}`)}
                                        className="text-blue-600 hover:text-blue-900 bg-blue-50 px-4 py-1.5 rounded-lg transition-colors"
                                    >
                                        Düzenle
                                    </button>
                                    <button
                                        onClick={() => handleDelete(page.id)}
                                        className="text-red-500 hover:text-red-900 px-2 py-1.5 rounded-lg opacity-40 group-hover:opacity-100 transition-opacity"
                                    >
                                        Sil
                                    </button>
                                </td>
                            </motion.tr>
                        ))}
                        {pages.length === 0 && (
                            <tr>
                                <td colSpan="4" className="px-6 py-12 text-center text-gray-400 italic">
                                    Henüz sayfa oluşturulmamış.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </motion.div>
    );
}
