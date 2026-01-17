import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function Settings() {
    const [settings, setSettings] = useState({});
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('general');
    const [uploading, setUploading] = useState(false);

    const tabs = [
        { id: 'general', label: 'Genel Ayarlar', icon: '⚙️' },
        { id: 'contact', label: 'İletişim', icon: '📞' },
        { id: 'social', label: 'Sosyal Medya', icon: '🌐' },
        { id: 'style', label: 'Renk & Stil', icon: '🎨' },
        { id: 'hero', label: 'Hero Ayarları', icon: '✨' },
        { id: 'ai', label: 'AI Ayarları', icon: '🤖' },
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

    const handleFileUpload = async (e, key) => {
        const file = e.target.files[0];
        if (!file) return;

        setUploading(true);
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'upload');

        try {
            const response = await api.post('/media.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (response.data.success) {
                // Update setting with public URL
                handleChange(key, response.data.data.public_url);
                Swal.fire({
                    title: 'Yüklendi',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } catch (error) {
            Swal.fire('Hata', 'Yükleme başarısız', 'error');
        } finally {
            setUploading(false);
        }
    };

    const handleSave = async () => {
        try {
            await api.post('/settings.php', { settings });
            Swal.fire('Başarılı', 'Ayarlar kaydedildi', 'success');
        } catch (error) {
            Swal.fire('Hata', 'Kaydetme başarısız', 'error');
        }
    };

    const renderField = (key, label, type = 'text', placeholder = '') => {
        // Image Upload Field
        if (type === 'image') {
            return (
                <div className="mb-6">
                    <label className="block text-sm font-bold text-gray-700 mb-2">{label}</label>
                    <div className="flex items-start gap-4 p-4 border border-gray-100 rounded-2xl bg-gray-50/50">
                        <div className="w-24 h-24 bg-white rounded-xl border border-gray-200 flex items-center justify-center p-2 shadow-sm overflow-hidden">
                            {settings[key] ? (
                                <img src={settings[key]} alt={label} className="max-w-full max-h-full object-contain" />
                            ) : (
                                <span className="text-2xl text-gray-300">🖼️</span>
                            )}
                        </div>
                        <div className="flex-1">
                            <input
                                type="text"
                                value={settings[key] || ''}
                                onChange={(e) => handleChange(key, e.target.value)}
                                className="w-full px-4 py-2 rounded-xl border border-gray-200 text-sm mb-3 text-gray-500 focus:text-gray-800 focus:border-blue-500 outline-none transition-all"
                                placeholder="https://..."
                            />
                            <div className="relative">
                                <label className={`inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 cursor-pointer shadow-sm transition-all ${uploading ? 'opacity-50 pointer-events-none' : ''}`}>
                                    <span>{uploading ? '⌛' : '📤'}</span>
                                    {uploading ? 'Yükleniyor...' : 'Görsel Yükle'}
                                    <input type="file" className="hidden" onChange={(e) => handleFileUpload(e, key)} accept="image/*" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }

        // Color Picker Field
        if (type === 'color') {
            return (
                <div className="mb-6">
                    <label className="block text-sm font-bold text-gray-700 mb-2">{label}</label>
                    <div className="flex items-center gap-3 p-2 border border-gray-200 rounded-2xl bg-white focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all">
                        <div className="relative w-12 h-12 rounded-xl border border-gray-100 overflow-hidden shadow-inner flex-shrink-0">
                            <input
                                type="color"
                                value={settings[key] || '#000000'}
                                onChange={(e) => handleChange(key, e.target.value)}
                                className="absolute -top-1/2 -left-1/2 w-[200%] h-[200%] cursor-pointer p-0 border-0"
                            />
                        </div>
                        <div className="flex-1">
                            <input
                                type="text"
                                value={settings[key] || ''}
                                onChange={(e) => handleChange(key, e.target.value)}
                                className="w-full text-sm font-mono font-bold text-gray-600 outline-none uppercase tracking-wider"
                                placeholder="#000000"
                                maxLength={7}
                            />
                        </div>
                    </div>
                </div>
            );
        }

        // Standard Text/Textarea
        return (
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
    };

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
                            {renderField('logo_url', 'Logo', 'image')}
                            {renderField('favicon_url', 'Favicon', 'image')}
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
                            {renderField('primary_color', 'Ana Renk (Primary)', 'color')}
                            {renderField('secondary_color', 'İkincil Renk (Secondary)', 'color')}

                            <div className="col-span-full mt-4 p-4 bg-gray-50 rounded-2xl">
                                <p className="text-sm text-gray-500">Not: Renk değişikliklerinin sitede aktif olması için CSS değişkenlerinin ayarlanmış olması gerekir.</p>
                            </div>
                        </div>
                    )}

                    {activeTab === 'hero' && (
                        <div className="space-y-8">
                            <div>
                                <h3 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <span className="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">1</span>
                                    Dönen Metin Ayarları (Animated Text)
                                </h3>
                                <div className="grid md:grid-cols-2 gap-6 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                    <div className="space-y-1">
                                        {renderField('hero_text_variants_1', '1. Kelime Varyasyonları (Beyaz)', 'textarea', 'Örn: Mekanınızı,Otelinizi,Restoranınızı')}
                                        <p className="text-[11px] text-gray-400 font-medium px-1 italic">Virgülle ayırarak yazın. Örn: Mekanınızı, Otelinizi, Villanızı</p>
                                    </div>
                                    <div className="space-y-1">
                                        {renderField('hero_text_variants_2', '2. Kelime Varyasyonları (Mavi/İtalik)', 'textarea', 'Örn: Sanata,Markaya,Satışa')}
                                        <p className="text-[11px] text-gray-400 font-medium px-1 italic">Virgülle ayırarak yazın. Örn: Sanata, Markaya, Hikayeye</p>
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 bg-amber-50 rounded-2xl border border-amber-100 flex gap-4 items-start">
                                <span className="text-xl">💡</span>
                                <div className="text-sm text-amber-800 leading-relaxed">
                                    <strong>İpucu:</strong> Her iki kutudaki kelime sayısının eşit olması (örn: ikisinde de 5 kelime) animasyonun daha senkronize çalışmasını sağlar.
                                    Metinler her 4 saniyede bir sırayla değişecektir.
                                </div>
                            </div>
                        </div>
                    )}

                    {activeTab === 'ai' && (
                        <div className="space-y-8">
                            <div className="bg-purple-50 p-6 rounded-3xl border border-purple-100 mb-6">
                                <div className="flex gap-4 items-start">
                                    <span className="text-2xl mt-1">✨</span>
                                    <div>
                                        <h3 className="font-bold text-purple-900 mb-1">Yapay Zeka Entegrasyonu</h3>
                                        <p className="text-sm text-purple-800/80 leading-relaxed">
                                            İçerik üretiminde OpenAI teknolojisini kullanıyoruz. Blog yazıları, SEO açıklamaları ve sayfa içerikleri bu API üzerinden profesyonelce üretilmektedir.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="grid md:grid-cols-2 gap-6">
                                <div className="space-y-1">
                                    {renderField('openai_api_key', 'OpenAI API Anahtarı', 'password', 'sk-proj-...')}
                                    <p className="text-[10px] text-gray-400 px-1">Anahtarınız güvenli bir şekilde saklanır ve sadece sunucu tarafında kullanılır.</p>
                                </div>
                                <div className="space-y-1">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">OpenAI Model</label>
                                    <select
                                        value={settings['openai_model'] || 'gpt-4o-mini'}
                                        onChange={(e) => handleChange('openai_model', e.target.value)}
                                        className="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition-all"
                                    >
                                        <option value="gpt-4o-mini">GPT-4o Mini (Hızlı ve Ekonomik - Önerilen)</option>
                                        <option value="gpt-4o">GPT-4o (En Yüksek Kalite)</option>
                                    </select>
                                    <p className="text-[10px] text-gray-400 px-1">Özellikle blog yazıları için 4o-mini hem kaliteli hem de ekonomiktir.</p>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
