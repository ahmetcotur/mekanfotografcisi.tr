import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion } from 'framer-motion';

export default function SeoPages() {
    const [pages, setPages] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');

    useEffect(() => {
        loadSeoPages();
    }, []);

    const loadSeoPages = async () => {
        try {
            const response = await api.get('/admin-update.php?table=posts&action=list&post_type=seo_page');
            if (response.data.success) {
                setPages(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load SEO pages');
        } finally {
            setLoading(false);
        }
    };

    const togglePublished = async (id, currentStatus) => {
        const newStatus = currentStatus === 'publish' ? 'draft' : 'publish';
        try {
            await api.post('/admin-update.php', {
                action: 'update',
                table: 'posts',
                id,
                data: { post_status: newStatus }
            });
            loadSeoPages();
        } catch (error) {
            Swal.fire('Hata', 'Durum güncellenemedi', 'error');
        }
    };

    const [editingPage, setEditingPage] = useState(null);

    const handleEditClick = (page) => {
        setEditingPage({ ...page }); // Copy object to avoid direct mutation
    };

    const handleSave = async () => {
        try {
            await api.post('/admin-update.php', {
                action: 'save-post',
                ...editingPage
            });
            Swal.fire('Kaydedildi', 'Sayfa içeriği güncellendi', 'success');
            setEditingPage(null);
            loadSeoPages();
        } catch (error) {
            Swal.fire('Hata', 'Kaydetme başarısız', 'error');
        }
    };

    const filteredPages = pages.filter(p =>
        p.title?.toLowerCase().includes(search.toLowerCase()) ||
        p.slug?.toLowerCase().includes(search.toLowerCase())
    );

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-6"
        >
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Dinamik SEO Sayfaları</h1>
                    <p className="text-gray-500 text-sm">Lokasyon ve hizmet kombinasyonlarından oluşan sayfalar</p>
                </div>
            </div>

            <div className="flex gap-4 items-center bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                <span className="text-gray-400 ml-2">🔍</span>
                <input
                    type="text"
                    placeholder="Sayfa veya URL ara..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="flex-1 bg-transparent border-none focus:ring-0 text-sm font-medium outline-none"
                />
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Sayfa / Başlık</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">URL (Slug)</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Tip</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Durum</th>
                            <th className="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">İşlem</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {filteredPages.map((page) => (
                            <tr key={page.id} className="hover:bg-gray-50/50 transition-colors">
                                <td className="px-6 py-4">
                                    <div className="text-sm font-bold text-gray-800 line-clamp-1">{page.title}</div>
                                    <div className="text-[10px] text-gray-400 mt-0.5 line-clamp-1">{page.excerpt}</div>
                                </td>
                                <td className="px-6 py-4">
                                    <div className="flex items-center gap-2">
                                        <div className="text-xs font-mono text-blue-500">/{page.slug}</div>
                                        <a
                                            href={`/${page.slug}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-gray-400 hover:text-blue-600 transition-colors"
                                            title="Sayfayı Görüntüle"
                                        >
                                            🔗
                                        </a>
                                    </div>
                                </td>
                                <td className="px-6 py-4">
                                    <span className="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-black uppercase text-gray-500 tracking-tighter">
                                        {page.post_type}
                                    </span>
                                </td>
                                <td className="px-6 py-4">
                                    <span className={`px-2 py-1 rounded text-[10px] font-bold ${page.post_status === 'publish' ? 'text-green-600 bg-green-50' : 'text-gray-500 bg-gray-100'}`}>
                                        {page.post_status === 'publish' ? 'YAYINDA' : 'TASLAK'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-right">
                                    <div className="flex justify-end gap-2">
                                        <button
                                            onClick={() => handleEditClick(page)}
                                            className="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors"
                                        >
                                            Düzenle ✏️
                                        </button>
                                        <button
                                            onClick={() => togglePublished(page.id, page.post_status)}
                                            className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-colors ${page.post_status === 'publish'
                                                ? 'bg-amber-50 text-amber-600 hover:bg-amber-100'
                                                : 'bg-green-50 text-green-600 hover:bg-green-100'}`}
                                        >
                                            {page.post_status === 'publish' ? 'Pasifleştir' : 'Yayınla'}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Edit Modal */}
            {editingPage && (
                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95 }}
                        animate={{ opacity: 1, scale: 1 }}
                        className="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden"
                    >
                        <div className="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h2 className="text-xl font-bold text-gray-800">Sayfa Düzenle</h2>
                            <button onClick={() => setEditingPage(null)} className="text-gray-400 hover:text-gray-600">
                                ✕
                            </button>
                        </div>

                        <div className="p-6 overflow-y-auto custom-scrollbar space-y-6">
                            <div className="grid grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Sayfa Başlığı (H1)</label>
                                    <input
                                        type="text"
                                        value={editingPage.title}
                                        onChange={(e) => setEditingPage({ ...editingPage, title: e.target.value })}
                                        className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none font-bold text-gray-800"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">URL (Slug)</label>
                                    <input
                                        type="text"
                                        value={editingPage.slug}
                                        onChange={(e) => setEditingPage({ ...editingPage, slug: e.target.value })}
                                        className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none font-mono text-sm text-gray-600"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">İçerik (HTML)</label>
                                <textarea
                                    value={editingPage.content || ''}
                                    onChange={(e) => setEditingPage({ ...editingPage, content: e.target.value })}
                                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none font-mono text-sm h-64"
                                    placeholder="<p>Sayfa içeriği...</p>"
                                />
                                <p className="text-[10px] text-gray-400 mt-1">HTML etiketleri kullanabilirsiniz.</p>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Meta Açıklaması (Description)</label>
                                <textarea
                                    value={editingPage.excerpt || ''}
                                    onChange={(e) => setEditingPage({ ...editingPage, excerpt: e.target.value })}
                                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm h-24"
                                    placeholder="Google arama sonuçlarında görünecek kısa açıklama..."
                                />
                            </div>
                        </div>

                        <div className="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                            <button
                                onClick={() => setEditingPage(null)}
                                className="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors"
                            >
                                İptal
                            </button>
                            <button
                                onClick={handleSave}
                                className="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all"
                            >
                                Değişiklikleri Kaydet
                            </button>
                        </div>
                    </motion.div>
                </div>
            )}
        </motion.div>
    );
}
