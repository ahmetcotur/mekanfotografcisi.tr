import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import useAuthStore from '../store/authStore';

export default function Settings() {
    const [settings, setSettings] = useState({});
    const [pexelsImages, setPexelsImages] = useState([]);
    const [passwords, setPasswords] = useState({ new: '', confirm: '' });
    const [loading, setLoading] = useState(true);
    const user = useAuthStore((state) => state.user);

    useEffect(() => {
        loadSettings();
        loadPexelsImages();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await api.get('/settings.php');
            if (response.data.success) {
                const flat = {};
                Object.values(response.data.settings || {}).forEach(group => {
                    group.forEach(s => flat[s.key] = s.value);
                });
                setSettings(flat);
            }
        } catch (error) {
            console.error('Failed to load settings:', error);
        } finally {
            setLoading(false);
        }
    };

    const loadPexelsImages = async () => {
        try {
            const response = await api.get('/pexels-images.php');
            if (response.data.success) {
                setPexelsImages(response.data.images || []);
            }
        } catch (error) {
            console.error('Failed to load Pexels images:', error);
        }
    };

    const handlePasswordChange = async () => {
        if (passwords.new !== passwords.confirm) {
            Swal.fire('Hata', 'Şifreler eşleşmiyor', 'error');
            return;
        }
        if (passwords.new.length < 6) {
            Swal.fire('Hata', 'Şifre en az 6 karakter olmalı', 'error');
            return;
        }

        try {
            const response = await api.post('/admin-password.php', { new_password: passwords.new });
            if (response.data.success) {
                Swal.fire('Başarılı', 'Şifre güncellendi', 'success');
                setPasswords({ new: '', confirm: '' });
            }
        } catch (error) {
            Swal.fire('Hata', error.response?.data?.error || 'Şifre güncellenemedi', 'error');
        }
    };

    const togglePexelsImage = async (id, isVisible) => {
        try {
            await api.post('/pexels-images.php', { action: 'toggle', id, is_visible: !isVisible });
            loadPexelsImages();
        } catch (error) {
            console.error('Failed to toggle image:', error);
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <div className="space-y-6">
            <h1 className="text-3xl font-bold text-gray-800">Ayarlar</h1>

            {/* Account Section */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-xl font-bold mb-4">Hesap Bilgileri</h2>
                <div className="space-y-4">
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input
                            type="email"
                            value={user?.email || ''}
                            disabled
                            className="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-2">İsim</label>
                        <input
                            type="text"
                            value={user?.name || ''}
                            disabled
                            className="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg"
                        />
                    </div>
                </div>
            </div>

            {/* Password Change */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-xl font-bold mb-4">Şifre Değiştir</h2>
                <div className="space-y-4">
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-2">Yeni Şifre</label>
                        <input
                            type="password"
                            value={passwords.new}
                            onChange={(e) => setPasswords({ ...passwords, new: e.target.value })}
                            className="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg"
                            placeholder="En az 6 karakter"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-semibold text-gray-700 mb-2">Şifre Tekrar</label>
                        <input
                            type="password"
                            value={passwords.confirm}
                            onChange={(e) => setPasswords({ ...passwords, confirm: e.target.value })}
                            className="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg"
                            placeholder="Şifreyi tekrar girin"
                        />
                    </div>
                    <button
                        onClick={handlePasswordChange}
                        className="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium"
                    >
                        Şifreyi Güncelle
                    </button>
                </div>
            </div>

            {/* Pexels Images */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-xl font-bold mb-4">Anasayfa Görselleri (Pexels)</h2>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {pexelsImages.map((img) => (
                        <div key={img.id} className={`relative group border rounded-lg overflow-hidden ${img.is_visible ? 'border-green-500' : 'border-gray-200 opacity-50'
                            }`}>
                            <img src={img.image_url} alt="Pexels" className="w-full h-32 object-cover" />
                            <button
                                onClick={() => togglePexelsImage(img.id, img.is_visible)}
                                className={`absolute top-2 right-2 px-3 py-1 rounded text-xs font-medium ${img.is_visible ? 'bg-green-500 text-white' : 'bg-gray-500 text-white'
                                    }`}
                            >
                                {img.is_visible ? 'Görünür' : 'Gizli'}
                            </button>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
