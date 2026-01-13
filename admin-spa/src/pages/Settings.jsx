import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function Settings() {
    const [settings, setSettings] = useState({});
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('general');

    const tabs = [
        { id: 'general', label: 'Genel Ayarlar', icon: '⚙️' },
        { id: 'contact', label: 'İletişim', icon: '📞' },
        { id: 'social', label: 'Sosyal Medya', icon: '🌐' },
        { id: 'style', label: 'Renk & Stil', icon: '🎨' },
    ];

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await api.get('/settings.php');
            if (response.data.success) {
                const flatSettings = {};
                Object.values(response.data.settings).forEach(group => {
                    group.forEach(item => {
                        flatSettings[item.key] = item.value;
                    });
                });
                setSettings(flatSettings);
            }
        } catch (error) {
            console.error('Failed to load settings', error);
            Swal.fire('Hata', 'Ayarlar yüklenemedi', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (key, value) => {
        setSettings(prev => ({ ...prev, [key]: value }));
    };

    const handleSave = async () => {
        try {
            await api.post('/settings.php', { settings });
            Swal.fire('Başarılı', 'Ayarlar kaydedildi', 'success');
        } catch (error) {
            Swal.fire('Hata', 'Kaydetme başarısız', 'error');
        }
    };

    const renderField = (key, label, type = 'text', placeholder = '') => (
        <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
            {type === 'textarea' ? (
                <textarea
                    value={settings[key] || ''}
                    onChange={(e) => handleChange(key, e.target.value)}
                    className="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition-all"
                    placeholder={placeholder}
                    rows="4"
                />
            ) : (
                <input
                    type={type}
                    value={settings[key] || ''}
                    onChange={(e) => handleChange(key, e.target.value)}
                    className="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition-all"
                    placeholder={placeholder}
                />
            )}
        </div>
    );

    if (loading) return <div className="p-8 text-center text-gray-500">Yükleniyor...</div>;

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Sistem Ayarları</h1>
                    <p className="text-gray-500 text-sm">Site genel yapılandırmasını buradan yönetebilirsiniz.</p>
                </div>
                <button
                    onClick={handleSave}
                    className="px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:scale-105 transition-all active:scale-95"
                >
                    Kaydet
                </button>
            </div>

            <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                {/* Tabs */}
                <div className="flex border-b border-gray-100 overflow-x-auto">
                    {tabs.map(tab => (
                        <button
                            key={tab.id}
                            onClick={() => setActiveTab(tab.id)}
                            className={`px-6 py-4 font-bold text-sm flex items-center gap-2 whitespace-nowrap transition-all ${activeTab === tab.id
                                    ? 'text-blue-600 bg-blue-50/50 border-b-2 border-blue-600'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'
                                }`}
                        >
                            <span>{tab.icon}</span> {tab.label}
                        </button>
                    ))}
                </div>

                {/* Content */}
                <div className="p-8">
                    {activeTab === 'general' && (
                        <div className="grid md:grid-cols-2 gap-6">
                            {renderField('site_title', 'Site Başlığı (Title)', 'text', 'Örn: Mekan Fotoğrafçısı')}
                            {renderField('site_description', 'Site Açıklaması (Meta Description)', 'textarea', 'Site hakkında kısa bilgi...')}
                            {renderField('site_logo', 'Logo URL', 'text', '/uploads/logo.png')}
                            {renderField('site_favicon', 'Favicon URL', 'text', '/uploads/favicon.png')}
                            {renderField('google_analytics', 'Google Analytics ID', 'text', 'UA-XXXXX-Y')}
                        </div>
                    )}

                    {activeTab === 'contact' && (
                        <div className="grid md:grid-cols-2 gap-6">
                            {renderField('phone_primary', 'Telefon 1 (Görünür)', 'text', '+90 555 ...')}
                            {renderField('phone_whatsapp', 'Whatsapp Numarası', 'text', '90555...')}
                            {renderField('email_primary', 'E-posta Adresi', 'email', 'info@site.com')}
                            {renderField('address_short', 'Kısa Adres', 'text', 'İstanbul, Türkiye')}
                            {renderField('address_full', 'Tam Adres', 'textarea', 'Açık adres bilgisi...')}
                        </div>
                    )}

                    {activeTab === 'social' && (
                        <div className="grid md:grid-cols-2 gap-6">
                            {renderField('social_instagram', 'Instagram URL', 'text', 'https://instagram.com/...')}
                            {renderField('social_facebook', 'Facebook URL', 'text', 'https://facebook.com/...')}
                            {renderField('social_twitter', 'Twitter / X URL', 'text', 'https://x.com/...')}
                            {renderField('social_linkedin', 'LinkedIn URL', 'text', 'https://linkedin.com/...')}
                            {renderField('social_youtube', 'YouTube URL', 'text', 'https://youtube.com/...')}
                        </div>
                    )}

                    {activeTab === 'style' && (
                        <div className="grid md:grid-cols-2 gap-6">
                            {renderField('color_brand_primary', 'Ana Renk (Primary)', 'color')}
                            {renderField('color_brand_secondary', 'İkincil Renk (Secondary)', 'color')}

                            <div className="col-span-full mt-4 p-4 bg-gray-50 rounded-2xl">
                                <p className="text-sm text-gray-500">Not: Renk değişikliklerinin sitede aktif olması için CSS değişkenlerinin ayarlanmış olması gerekir.</p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
