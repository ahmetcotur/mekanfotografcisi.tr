import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../api/client';
import Swal from 'sweetalert2';
import ReactQuill from 'react-quill-new';
import 'react-quill-new/dist/quill.snow.css';
import { motion, AnimatePresence } from 'framer-motion';

export default function PostEditor() {
    const { id, type } = useParams();
    const navigate = useNavigate();
    const isNew = !id;

    const [post, setPost] = useState({
        title: '',
        slug: '',
        content: '',
        excerpt: '',
        post_status: 'draft',
        post_type: type || 'page',
        gallery_folder_id: ''
    });
    const [folders, setFolders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('content');

    const modules = {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike', 'blockquote'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['link', 'image', 'code-block'],
            ['clean']
        ],
    };

    useEffect(() => {
        loadFolders();
        if (!isNew) {
            loadPost();
        } else {
            setLoading(false);
        }
    }, [id]);

    const loadPost = async () => {
        try {
            const response = await api.get(`/admin-update.php?action=get&table=posts&id=${id}`);
            if (response.data.success) {
                setPost(response.data.data);
            }
        } catch (error) {
            Swal.fire('Hata', 'Yazı yüklenemedi', 'error');
            navigate(-1);
        } finally {
            setLoading(false);
        }
    };

    const loadFolders = async () => {
        try {
            const response = await api.get('/admin-update.php?action=list&table=media_folders');
            if (response.data.success) {
                setFolders(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load folders');
        }
    };

    const handleSave = async (e) => {
        if (e) e.preventDefault();
        try {
            const response = await api.post('/admin-update.php', {
                action: 'save-post',
                ...post,
                id: isNew ? null : id
            });

            if (response.data.success) {
                Swal.fire({
                    title: 'Başarılı',
                    text: 'Değişiklikler kaydedildi.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                if (isNew) {
                    navigate(`/${post.post_type === 'service' ? 'services' : 'pages'}`);
                }
            }
        } catch (error) {
            Swal.fire('Hata', error.response?.data?.error || 'Kaydedilemedi', 'error');
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="max-w-5xl mx-auto space-y-6 pb-20"
        >
            <div className="flex justify-between items-center bg-white/50 backdrop-blur-md p-4 rounded-2xl border border-gray-100 sticky top-0 z-10">
                <div className="flex items-center gap-4">
                    <button
                        onClick={() => navigate(-1)}
                        className="p-2 hover:bg-gray-100 rounded-xl transition-colors"
                    >
                        ←
                    </button>
                    <div>
                        <h1 className="text-xl font-bold text-gray-800">
                            {isNew ? 'Yeni Ekle' : 'Düzenle'}: <span className="text-blue-600 uppercase text-sm tracking-widest ml-2">{post.post_type}</span>
                        </h1>
                        <p className="text-xs text-gray-500">{post.title || 'Başlıksız'}</p>
                    </div>
                </div>
                <div className="flex gap-2">
                    <button
                        onClick={handleSave}
                        className="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5"
                    >
                        {isNew ? 'Yayınla' : 'Değişiklikleri Kaydet'}
                    </button>
                </div>
            </div>

            {/* Tabs */}
            <div className="flex gap-1 p-1 bg-gray-200/50 rounded-2xl w-fit">
                <button
                    onClick={() => setActiveTab('content')}
                    className={`px-6 py-2 rounded-xl text-sm font-bold transition-all ${activeTab === 'content' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:bg-white/50'}`}
                >
                    İçerik
                </button>
                <button
                    onClick={() => setActiveTab('seo')}
                    className={`px-6 py-2 rounded-xl text-sm font-bold transition-all ${activeTab === 'seo' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:bg-white/50'}`}
                >
                    SEO & Ayarlar
                </button>
            </div>

            <form onSubmit={handleSave} className="space-y-6">
                <AnimatePresence mode="wait">
                    {activeTab === 'content' ? (
                        <motion.div
                            key="content"
                            initial={{ opacity: 0, x: -10 }}
                            animate={{ opacity: 1, x: 0 }}
                            exit={{ opacity: 0, x: 10 }}
                            className="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6"
                        >
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 mb-2 tracking-tight">Sayfa Başlığı</label>
                                    <input
                                        type="text"
                                        required
                                        value={post.title}
                                        onChange={(e) => setPost({ ...post, title: e.target.value })}
                                        className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-lg font-semibold"
                                        placeholder="Örn: Hakkımızda"
                                    />
                                </div>

                                <div className="quill-editor-wrapper">
                                    <label className="block text-sm font-bold text-gray-700 mb-2 tracking-tight">İçerik</label>
                                    <ReactQuill
                                        theme="snow"
                                        value={post.content}
                                        onChange={(content) => setPost({ ...post, content })}
                                        modules={modules}
                                        className="bg-white rounded-2xl overflow-hidden"
                                        style={{ height: '500px', marginBottom: '60px' }}
                                    />
                                </div>
                            </div>
                        </motion.div>
                    ) : (
                        <motion.div
                            key="seo"
                            initial={{ opacity: 0, x: 10 }}
                            animate={{ opacity: 1, x: 0 }}
                            exit={{ opacity: 0, x: -10 }}
                            className="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-8"
                        >
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div className="space-y-6">
                                    <h3 className="text-lg font-bold text-gray-800">URL Yapısı</h3>
                                    <div>
                                        <label className="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Slug (URL)</label>
                                        <input
                                            type="text"
                                            value={post.slug}
                                            onChange={(e) => setPost({ ...post, slug: e.target.value })}
                                            className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-mono text-sm"
                                            placeholder="otomatik-olusturulur"
                                        />
                                        <p className="mt-1 text-[10px] text-gray-400">Boş bırakırsanız başlığa göre oluşturulur.</p>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Durum</label>
                                        <div className="flex gap-2">
                                            {['draft', 'publish'].map(status => (
                                                <button
                                                    key={status}
                                                    type="button"
                                                    onClick={() => setPost({ ...post, post_status: status })}
                                                    className={`flex-1 py-3 rounded-xl text-xs font-bold ring-1 transition-all ${post.post_status === status ? 'bg-blue-600 text-white ring-blue-600' : 'bg-white text-gray-500 ring-gray-200 hover:bg-gray-50'}`}
                                                >
                                                    {status === 'publish' ? 'YAYINDA' : 'TASLAK'}
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-6">
                                    <h3 className="text-lg font-bold text-gray-800">SEO Meta Verileri</h3>
                                    <div>
                                        <label className="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Meta Açıklaması (Excerpt)</label>
                                        <textarea
                                            rows="4"
                                            value={post.excerpt || ''}
                                            onChange={(e) => setPost({ ...post, excerpt: e.target.value })}
                                            className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm leading-relaxed"
                                            placeholder="Arama sonuçlarında görünecek kısa açıklama..."
                                        />
                                        <p className="mt-1 text-[10px] text-gray-400">{post.excerpt?.length || 0} karakter (Önerilen 150-160)</p>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Galeri Klasörü Bağlantısı</label>
                                        <select
                                            value={post.gallery_folder_id || ''}
                                            onChange={(e) => setPost({ ...post, gallery_folder_id: e.target.value })}
                                            className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm font-semibold"
                                        >
                                            <option value="">Klasör Seçin (Opsiyonel)</option>
                                            {folders.map(f => (
                                                <option key={f.id} value={f.id}>{f.name}</option>
                                            ))}
                                        </select>
                                        <p className="mt-1 text-[10px] text-gray-400">Bu sayfada görünecek resim galerisi için bir klasör seçin.</p>
                                    </div>
                                </div>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </form>
        </motion.div>
    );
}
