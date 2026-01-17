import { useEffect, useState } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import api from '../api/client';
import Swal from 'sweetalert2';
import { Editor } from '@tinymce/tinymce-react';
import 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';
import 'tinymce/skins/ui/oxide/skin.min.css';
// Import plugins
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/media';
import 'tinymce/plugins/table';
import 'tinymce/plugins/wordcount';
import { motion, AnimatePresence } from 'framer-motion';

export default function PostEditor() {
    const { id, type: typeParam } = useParams();
    const navigate = useNavigate();
    const location = useLocation();
    const isNew = !id;

    // Determine initial post type from URL path
    const getInitialType = () => {
        if (typeParam) return typeParam;
        const path = location.pathname;
        if (path.includes('/blog')) return 'blog';
        if (path.includes('/services')) return 'service';
        return 'page';
    };

    const [post, setPost] = useState({
        title: '',
        slug: '',
        content: '',
        excerpt: '',
        post_status: 'draft',
        post_type: getInitialType(),
        featured_image: '',
        gallery_folder_id: ''
    });
    const [folders, setFolders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('content');
    const [generating, setGenerating] = useState(false);

    // TinyMCE configuration is handled in the component

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
                    let redirectPath = '/pages';
                    if (post.post_type === 'service') redirectPath = '/services';
                    if (post.post_type === 'blog') redirectPath = '/blog';
                    navigate(redirectPath);
                }
            }
        } catch (error) {
            Swal.fire('Hata', error.response?.data?.error || 'Kaydedilemedi', 'error');
        }
    };

    const handleAiGenerate = async () => {
        const { value: keywords } = await Swal.fire({
            title: 'AI İçerik Oluştur',
            input: 'text',
            inputLabel: post.post_type === 'blog' && !post.title ? 'Yazı Konusu veya Anahtar Kelimeler' : 'Odaklanılacak Anahtar Kelimeler (opsiyonel)',
            inputPlaceholder: 'otel çekimi, mimari fotoğrafçılık...',
            showCancelButton: true,
            confirmButtonText: 'Üret',
            cancelButtonText: 'İptal',
            icon: 'info'
        });

        if (keywords === undefined) return;

        // Fix for TinyMCE URL issues: convert absolute URLs to root-relative if they match current domain
        // (Actually TinyMCE config below takes care of most of it, but we can pre-process too if needed)

        if (!post.title && !keywords) {
            Swal.fire('Hata', 'Lütfen en azından bir konu veya başlık belirtin.', 'warning');
            return;
        }

        setGenerating(true);
        try {
            const response = await api.post('/ai.php', {
                action: 'generate-content',
                title: post.title,
                keywords,
                existing_content: post.content,
                type: post.post_type
            });

            if (response.data.success) {
                if (response.data.title) {
                    setPost(prev => ({ ...prev, title: response.data.title, content: response.data.content }));
                } else {
                    setPost(prev => ({ ...prev, content: response.data.content }));
                }
                Swal.fire({ title: 'İçerik Üretildi', icon: 'success', timer: 1500, showConfirmButton: false });
            }
        } catch (error) {
            Swal.fire('Hata', 'İçerik üretilemedi: ' + (error.response?.data?.error || error.message), 'error');
        } finally {
            setGenerating(false);
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
                                    <div className="flex justify-between items-end mb-2">
                                        <label className="block text-sm font-bold text-gray-700 tracking-tight">İçerik</label>
                                        <button
                                            type="button"
                                            onClick={handleAiGenerate}
                                            disabled={generating}
                                            className="px-4 py-1.5 bg-purple-100 text-purple-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-purple-200 transition-all flex items-center gap-2 disabled:opacity-50"
                                        >
                                            {generating ? '⌛ Üretiliyor...' : '✨ AI ile Üret'}
                                        </button>
                                    </div>
                                    <Editor
                                        init={{
                                            height: 600,
                                            menubar: true,
                                            license_key: 'gpl',
                                            promotion: false,
                                            branding: false,
                                            relative_urls: false,
                                            remove_script_host: true,
                                            document_base_url: '/',
                                            convert_urls: true,
                                            plugins: [
                                                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                                                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                                                'insertdatetime', 'media', 'table', 'code', 'wordcount'
                                            ],
                                            toolbar: 'undo redo | blocks | ' +
                                                'bold italic forecolor | alignleft aligncenter ' +
                                                'alignright alignjustify | borderless_table | bullist numlist outdent indent | ' +
                                                'removeformat',
                                            content_style: 'body { font-family:Inter,Helvetica,Arial,sans-serif; font-size:16px; line-height:1.6; padding: 20px; } h2 { font-weight: 800; color: #1e293b; } p { color: #475569; }',
                                            skin: 'oxide',
                                            content_css: 'default'
                                        }}
                                        value={post.content}
                                        onEditorChange={(content) => setPost({ ...post, content })}
                                    />
                                    <style>{`
                                        /* Hide TinyMCE setup and promotion elements */
                                        .tox-promotion, 
                                        .tox-statusbar__branding, 
                                        .tox-notification--warning {
                                            display: none !important;
                                        }
                                        /* If the 'Finish setting up' is a dialog, this might be tricky, but usually it's a notification or promo */
                                    `}</style>
                                </div>

                                <div className="space-y-6">
                                    <h3 className="text-lg font-bold text-gray-800">Kapak Görseli</h3>
                                    <p className="text-xs text-gray-400">Blog listesinde ve paylaşım kartlarında görünecek ana görsel.</p>

                                    <div className="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-4">
                                        <div className="aspect-video bg-white rounded-xl border border-gray-200 overflow-hidden flex items-center justify-center relative group">
                                            {post.featured_image ? (
                                                <>
                                                    <img src={post.featured_image} alt="Kapak" className="w-full h-full object-cover" />
                                                    <button
                                                        type="button"
                                                        onClick={() => setPost({ ...post, featured_image: '' })}
                                                        className="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                                    >✕</button>
                                                </>
                                            ) : (
                                                <div className="text-gray-300 text-center">
                                                    <span className="text-3xl block mb-2">🖼️</span>
                                                    <span className="text-[10px] font-bold uppercase tracking-widest">Görsel Yok</span>
                                                </div>
                                            )}
                                        </div>

                                        <div className="space-y-3">
                                            <input
                                                type="text"
                                                value={post.featured_image || ''}
                                                onChange={(e) => setPost({ ...post, featured_image: e.target.value })}
                                                placeholder="Görsel URL (örn: /uploads/abc.jpg)"
                                                className="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-blue-500 transition-all font-medium text-gray-600"
                                            />

                                            <label className="block w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[10px] font-black uppercase text-center cursor-pointer hover:bg-gray-50 transition-all shadow-sm border-dashed">
                                                📤 Görsel Yükle
                                                <input
                                                    type="file"
                                                    className="hidden"
                                                    accept="image/*"
                                                    onChange={async (e) => {
                                                        const file = e.target.files[0];
                                                        if (!file) return;
                                                        const formData = new FormData();
                                                        formData.append('file', file);
                                                        formData.append('action', 'upload');
                                                        try {
                                                            const res = await api.post('/media.php', formData, {
                                                                headers: { 'Content-Type': 'multipart/form-data' }
                                                            });
                                                            if (res.data.success) {
                                                                setPost({ ...post, featured_image: res.data.data.public_url });
                                                                Swal.fire({ title: 'Yüklendi', icon: 'success', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                                                            }
                                                        } catch (err) {
                                                            Swal.fire('Hata', 'Yükleme başarısız', 'error');
                                                        }
                                                    }}
                                                />
                                            </label>
                                        </div>
                                    </div>
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
