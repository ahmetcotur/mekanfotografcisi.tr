import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function PostEditor() {
    const { id, type } = useParams(); // type can be 'service', 'page', 'seo_page'
    const navigate = useNavigate();
    const isNew = !id;

    const [post, setPost] = useState({
        title: '',
        slug: '',
        content: '',
        post_status: 'draft',
        post_type: type || 'page',
        gallery_folder_id: ''
    });
    const [folders, setFolders] = useState([]);
    const [loading, setLoading] = useState(true);

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
        e.preventDefault();
        try {
            const response = await api.post('/admin-update.php', {
                action: 'save-post',
                ...post,
                id: isNew ? null : id
            });

            if (response.data.success) {
                Swal.fire('Başarılı', 'Kaydedildi', 'success');
                if (isNew) {
                    navigate(`/${post.post_type === 'service' ? 'services' : 'dashboard'}`);
                }
            }
        } catch (error) {
            Swal.fire('Hata', error.response?.data?.error || 'Kaydedilemedi', 'error');
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <div className="max-w-4xl mx-auto space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800">
                    {isNew ? 'Yeni Ekle' : 'Düzenle'}: {post.post_type.toUpperCase()}
                </h1>
                <button
                    onClick={() => navigate(-1)}
                    className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
                >
                    İptal
                </button>
            </div>

            <form onSubmit={handleSave} className="space-y-6 bg-white p-8 rounded-xl shadow-sm border border-gray-100">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="col-span-2">
                        <label className="block text-sm font-semibold text-gray-700 mb-2">Başlık</label>
                        <input
                            type="text"
                            required
                            value={post.title}
                            onChange={(e) => setPost({ ...post, title: e.target.value })}
                            className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition"
                            placeholder="Örn: Profesyonel Mimari Çekim"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-2">Slug (URL)</label>
                        <input
                            type="text"
                            value={post.slug}
                            onChange={(e) => setPost({ ...post, slug: e.target.value })}
                            className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition"
                            placeholder="otomatik-olusturulur"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-2">Durum</label>
                        <select
                            value={post.post_status}
                            onChange={(e) => setPost({ ...post, post_status: e.target.value })}
                            className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition"
                        >
                            <option value="draft">Taslak</option>
                            <option value="publish">Yayında</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">İçerik (Markdown)</label>
                    <textarea
                        rows="15"
                        value={post.content}
                        onChange={(e) => setPost({ ...post, content: e.target.value })}
                        className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition font-mono text-sm"
                        placeholder="# Başlık\n\nBuraya içerik yazın..."
                    ></textarea>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-100">
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-2">Galeri Klasörü (İsteğe Bağlı)</label>
                        <select
                            value={post.gallery_folder_id || ''}
                            onChange={(e) => setPost({ ...post, gallery_folder_id: e.target.value })}
                            className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition"
                        >
                            <option value="">Seçilmedi</option>
                            {folders.map(f => (
                                <option key={f.id} value={f.id}>{f.name}</option>
                            ))}
                        </select>
                        <p className="mt-2 text-xs text-gray-500">Bu post'un altında görünecek galeri için Medya klasörü seçin.</p>
                    </div>
                </div>

                <div className="flex justify-end gap-3 pt-4">
                    <button
                        type="submit"
                        className="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition transform hover:-translate-y-0.5"
                    >
                        {isNew ? 'Yayınla' : 'Değişiklikleri Kaydet'}
                    </button>
                </div>
            </form>
        </div>
    );
}
