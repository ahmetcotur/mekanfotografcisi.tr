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
                                    <div className="text-xs font-mono text-blue-500">/{page.slug}</div>
                                </td>
                                <td className="px-6 py-4">
                                    <span className="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-black uppercase text-gray-500 tracking-tighter">
                                        {page.post_type}
                                    </span>
                                </td>
                                <td className="px-6 py-4">
                                    <button
                                        onClick={() => togglePublished(page.id, page.post_status)}
                                        className={`px-3 py-1 rounded-full text-[10px] font-black tracking-widest transition-all ${page.post_status === 'publish'
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                            : 'bg-amber-100 text-amber-700 hover:bg-amber-200'}`}
                                    >
                                        {page.post_status === 'publish' ? 'YAYINDA' : 'PASİF'}
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </motion.div>
    );
}
