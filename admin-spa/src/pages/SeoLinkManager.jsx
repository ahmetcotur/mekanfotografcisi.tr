import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion, AnimatePresence } from 'framer-motion';

export default function SeoLinkManager() {
    const [settings, setSettings] = useState([]);
    const [provinces, setProvinces] = useState([]);
    const [districts, setDistricts] = useState([]);
    const [towns, setTowns] = useState([]);
    const [services, setServices] = useState([]);
    const [seoPages, setSeoPages] = useState([]);

    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('settings');
    const [locationType, setLocationType] = useState('province');
    const [aiSuggestions, setAiSuggestions] = useState([]);
    const [generating, setGenerating] = useState(false);

    // Draft settings state for "no-autosave" requirement
    const [draftSettings, setDraftSettings] = useState({});

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        setLoading(true);
        try {
            const endpoints = [
                { name: 'settings', url: '/admin-update.php?table=settings&action=list' },
                { name: 'provinces', url: '/admin-update.php?table=locations_province&action=list' },
                { name: 'districts', url: '/admin-update.php?table=locations_district&action=list' },
                { name: 'towns', url: '/admin-update.php?table=locations_town&action=list' },
                { name: 'services', url: '/admin-update.php?table=services&action=list' },
                { name: 'seoPages', url: '/admin-update.php?table=posts&action=list&post_type=seo_page' }
            ];

            const results = await Promise.all(endpoints.map(ep => api.get(ep.url).catch(err => {
                console.error(`API Error on ${ep.name}:`, err);
                return { data: { success: false, data: [] } };
            })));

            const fetchedSettings = results[0].data.data || [];
            setSettings(fetchedSettings);

            // Initialize draft settings
            const drafts = {};
            fetchedSettings.forEach(s => drafts[s.key] = s.value);
            setDraftSettings(drafts);

            setProvinces(results[1].data.data || []);
            setDistricts(results[2].data.data || []);
            setTowns(results[3].data.data || []);
            setServices(results[4].data.data || []);
            setSeoPages(results[5].data.data || []);
        } catch (error) {
            console.error('Failed to load SEO data');
        } finally {
            setLoading(false);
        }
    };

    const handleUpdateDraft = (key, value) => {
        setDraftSettings(prev => ({ ...prev, [key]: value }));
    };

    const handleSaveSetting = async (key) => {
        const value = draftSettings[key];
        try {
            await api.post('/admin-update.php', {
                action: 'update',
                table: 'settings',
                id: key,
                data: { value }
            });
            // Update the main settings state too
            setSettings(prev => prev.map(s => s.key === key ? { ...s, value } : s));
            Swal.fire({ title: 'Başarıyla Kaydedildi', icon: 'success', timer: 1000, showConfirmButton: false });
        } catch (error) {
            Swal.fire('Hata', 'Kaydedilemedi', 'error');
        }
    };

    // Helpers for current settings
    const getSetting = (key, def) => draftSettings[key] || def;

    const locSuffix = getSetting('seo_location_suffix', '-mekan-fotografcisi');
    const locTitleTemplate = getSetting('seo_location_title_template', '{name} Mekan Fotoğrafçısı');

    const srvSep = getSetting('seo_service_location_sep', '-');
    const srvOrder = getSetting('seo_service_location_order', 'province-service');
    const srvTitleTemplate = getSetting('seo_service_location_title_template', '{province} {service}');

    const aiProvider = getSetting('ai_provider', 'openai');
    const aiKey = getSetting('openai_api_key', '');
    const aiModel = getSetting('openai_model', 'gpt-4o-mini');
    const srvLocMetaDesc = getSetting('seo_service_location_meta_desc_template', '');
    const locMetaDesc = getSetting('seo_location_meta_desc_template', '');

    const getLinkStatus = (slug) => {
        const page = seoPages.find(p => p.slug === slug);
        if (!page) return { label: 'Oluşturulmadı', color: 'gray', status: 'missing' };
        if (page.post_status === 'publish') return { label: 'Yayında', color: 'green', status: 'publish' };
        return { label: 'Taslak', color: 'amber', status: 'draft' };
    };

    const handleCreatePage = async (title, slug) => {
        try {
            await api.post('/admin-update.php', {
                action: 'save-post',
                title,
                slug,
                content: `<p>${title} hizmetleri hakkında detaylı bilgi.</p>`,
                excerpt: title.includes('-')
                    ? srvLocMetaDesc.replace('{name}', title).replace('{location}', title).replace('{service}', title)
                    : locMetaDesc.replace('{name}', title).replace('{location}', title),
                post_type: 'seo_page',
                post_status: 'draft'
            });
            loadData();
            Swal.fire({ title: 'Sayfa Taslak Olarak Oluşturuldu', icon: 'success', timer: 1000, showConfirmButton: false });
        } catch (error) {
            Swal.fire('Hata', 'Sayfa oluşturulamadı', 'error');
        }
    };

    const handleGetAiSuggestions = async () => {
        if (!aiKey) {
            Swal.fire('Eksik Bilgi', 'Lütfen önce Ayarlar sekmesinden OpenAI API Anahtarını girin.', 'warning');
            return;
        }
        setGenerating(true);
        try {
            const response = await api.post('/ai.php', { action: 'suggest-urls' });
            if (response.data.success) {
                setAiSuggestions(response.data.suggestions);
            }
        } catch (error) {
            Swal.fire('Hata', 'Öneri alınamadı: ' + (error.response?.data?.error || error.message), 'error');
        } finally {
            setGenerating(false);
        }
    };

    if (loading) return <div className="flex justify-center py-24"><div className="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div></div>;

    return (
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
            <div className="flex justify-between items-center bg-white/50 backdrop-blur-md p-6 rounded-[2rem] border border-white shadow-sm">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 tracking-tight">SEO Link Yönetimi</h1>
                    <p className="text-gray-500 text-sm mt-1">Gelişmiş URL yapısı ve sayfa yönetimi</p>
                </div>
                <div className="flex bg-gray-100/50 p-1 rounded-2xl border border-gray-200 shadow-inner overflow-x-auto no-scrollbar">
                    <button onClick={() => setActiveTab('settings')} className={`px-4 py-2 rounded-xl font-bold transition-all whitespace-nowrap ${activeTab === 'settings' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}>⚙️ Ayarlar</button>
                    <button onClick={() => setActiveTab('locations')} className={`px-4 py-2 rounded-xl font-bold transition-all whitespace-nowrap ${activeTab === 'locations' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}>📍 Lokasyonlar</button>
                    <button onClick={() => setActiveTab('services')} className={`px-4 py-2 rounded-xl font-bold transition-all whitespace-nowrap ${activeTab === 'services' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}>📷 Hizmetler</button>
                    <button onClick={() => setActiveTab('ai')} className={`px-4 py-2 rounded-xl font-bold transition-all whitespace-nowrap ${activeTab === 'ai' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}>✨ AI Asistanı</button>
                </div>
            </div>

            <AnimatePresence mode="wait">
                {activeTab === 'settings' ? (
                    <motion.div key="settings" initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: 20 }} className="space-y-6 pb-20">
                        {/* URL Base Definition */}
                        <div className="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-6">
                            <h2 className="text-xl font-black text-gray-900 flex items-center gap-2">
                                <span className="p-2 bg-amber-50 rounded-lg text-amber-600 text-sm">🔗</span> Genel URL Tanımı
                            </h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">Hizmet Ana URL (Prefix)</label>
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={getSetting('seo_service_base', 'hizmetlerimiz')}
                                                onChange={(e) => handleUpdateDraft('seo_service_base', e.target.value)}
                                                className="flex-1 px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none font-bold text-amber-600"
                                                placeholder="hizmetlerimiz"
                                            />
                                            <button
                                                onClick={() => handleSaveSetting('seo_service_base')}
                                                className="px-6 bg-amber-600 text-white rounded-2xl font-bold hover:bg-amber-700 transition-colors shadow-lg shadow-amber-500/20"
                                            >
                                                Kaydet
                                            </button>
                                        </div>
                                        <p className="text-[10px] text-gray-400 px-1 italic">Tüm hizmet sayfaları bu önek altında yayına girer. Örn: /hizmetlerimiz/cekim-adi</p>
                                    </div>
                                </div>
                                <div className="bg-amber-900/5 rounded-[2rem] p-6 border border-amber-200/50">
                                    <h3 className="text-xs font-black text-amber-700 uppercase tracking-widest mb-2">URL Kuralları</h3>
                                    <p className="text-xs text-amber-900/70 leading-relaxed">
                                        Sistem otomatik olarak bu öneki zorunlu kılar. Önek olmadan erişilmeye çalışılan hizmet sayfaları otomatik olarak <b>301 (Kalıcı Yönlendirme)</b> ile doğru adrese taşınır.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Location Settings */}
                        <div className="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-6">
                            <h2 className="text-xl font-black text-gray-900 flex items-center gap-2">
                                <span className="p-2 bg-blue-50 rounded-lg text-blue-600 text-sm">📍</span> Lokasyon Link Ayarları
                            </h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">URL Sonsakısı (Suffix)</label>
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={draftSettings['seo_location_suffix'] || ''}
                                                onChange={(e) => handleUpdateDraft('seo_location_suffix', e.target.value)}
                                                className="flex-1 px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none font-mono text-sm"
                                                placeholder="-mekan-fotografcisi"
                                            />
                                            <button onClick={() => handleSaveSetting('seo_location_suffix')} className="px-6 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition-colors">Kaydet</button>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">Başlık Taslağı</label>
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={draftSettings['seo_location_title_template'] || ''}
                                                onChange={(e) => handleUpdateDraft('seo_location_title_template', e.target.value)}
                                                className="flex-1 px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none font-bold"
                                            />
                                            <button onClick={() => handleSaveSetting('seo_location_title_template')} className="px-6 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition-colors">Kaydet</button>
                                        </div>
                                    </div>
                                </div>
                                <div className="bg-slate-900 rounded-[2rem] p-6 text-white space-y-3">
                                    <h3 className="text-xs font-black text-slate-500 uppercase tracking-widest">Örnek Görünüm</h3>
                                    <div className="bg-white/5 p-4 rounded-xl border border-white/10">
                                        <div className="text-blue-400 font-mono text-xs">/antalya{locSuffix}</div>
                                        <div className="font-bold text-lg mt-1">{locTitleTemplate.replace('{name}', 'Antalya')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Service Settings */}
                        <div className="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-6">
                            <h2 className="text-xl font-black text-gray-900 flex items-center gap-2">
                                <span className="p-2 bg-purple-50 rounded-lg text-purple-600 text-sm">📷</span> Hizmet & Lokasyon Link Ayarları
                            </h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">Ayraç (Separator)</label>
                                            <input
                                                type="text"
                                                value={draftSettings['seo_service_location_sep'] || ''}
                                                onChange={(e) => handleUpdateDraft('seo_service_location_sep', e.target.value)}
                                                className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 outline-none font-mono text-center"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">Sıralama (Order)</label>
                                            <select
                                                value={draftSettings['seo_service_location_order'] || ''}
                                                onChange={(e) => handleUpdateDraft('seo_service_location_order', e.target.value)}
                                                className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 outline-none font-bold appearance-none bg-no-repeat bg-[right_1.25rem_center] bg-[length:1rem_1rem]"
                                                style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E")` }}
                                            >
                                                <option value="province-service">İl-Hizmet</option>
                                                <option value="service-province">Hizmet-İl</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">Başlık Taslağı</label>
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={draftSettings['seo_service_location_title_template'] || ''}
                                                onChange={(e) => handleUpdateDraft('seo_service_location_title_template', e.target.value)}
                                                className="flex-1 px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 outline-none font-bold"
                                            />
                                            <button onClick={() => {
                                                handleSaveSetting('seo_service_location_sep');
                                                handleSaveSetting('seo_service_location_order');
                                                handleSaveSetting('seo_service_location_title_template');
                                            }} className="px-6 bg-purple-600 text-white rounded-2xl font-bold hover:bg-purple-700 transition-colors">Kaydet</button>
                                        </div>
                                    </div>
                                </div>
                                <div className="bg-slate-900 rounded-[2rem] p-6 text-white space-y-3">
                                    <h3 className="text-xs font-black text-slate-500 uppercase tracking-widest">Örnek Görünüm</h3>
                                    <div className="bg-white/5 p-4 rounded-xl border border-white/10">
                                        <div className="text-purple-400 font-mono text-xs">
                                            /{getSetting('seo_service_base', 'hizmetlerimiz')}/{srvOrder === 'service-province' ? `otel-cekimi${srvSep}antalya` : `antalya${srvSep}otel-cekimi`}
                                        </div>
                                        <div className="font-bold text-lg mt-1">{srvTitleTemplate.replace('{province}', 'Antalya').replace('{service}', 'Otel Çekimi')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* Global Meta Settings */}
                        <div className="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-6">
                            <h2 className="text-xl font-black text-gray-900 flex items-center gap-2">
                                <span className="p-2 bg-green-50 rounded-lg text-green-600 text-sm">🌍</span> Global Meta Şablonları
                            </h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div className="space-y-2">
                                    <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">İl + Hizmet Meta Description Şablonu</label>
                                    <textarea
                                        value={srvLocMetaDesc}
                                        onChange={(e) => handleUpdateSetting('seo_service_location_meta_desc_template', e.target.value)}
                                        className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 outline-none text-sm h-32"
                                        placeholder="Antalya Otel Çekimi için profesyonel çözümler..."
                                    />
                                    <p className="text-[10px] text-gray-400 px-1">Değişkenler: `{"{name}"}`, `{"{service}"}`, `{"{location}"}`</p>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">Sadece Lokasyon Meta Description Şablonu</label>
                                    <textarea
                                        value={locMetaDesc}
                                        onChange={(e) => handleUpdateSetting('seo_location_meta_desc_template', e.target.value)}
                                        className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 outline-none text-sm h-32"
                                        placeholder="Antalya bölgesinde profesyonel çekim hizmetleri..."
                                    />
                                    <p className="text-[10px] text-gray-400 px-1">Değişkenler: `{"{name}"}`, `{"{location}"}`</p>
                                </div>
                            </div>
                        </div>

                        {/* AI Settings */}
                        <div className="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-6">
                            <h2 className="text-xl font-black text-gray-900 flex items-center gap-2">
                                <span className="p-2 bg-purple-50 rounded-lg text-purple-600 text-sm">✨</span> AI Yapılandırması
                            </h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">AI Sağlayıcı</label>
                                        <select
                                            value={aiProvider}
                                            onChange={(e) => {
                                                const newProvider = e.target.value;
                                                handleUpdateSetting('ai_provider', newProvider);
                                                handleUpdateSetting('openai_model', newProvider === 'openrouter' ? 'google/gemini-2.5-flash-free' : 'gpt-4o-mini');
                                            }}
                                            className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 outline-none font-bold appearance-none bg-no-repeat bg-[right_1.25rem_center] bg-[length:1rem_1rem]"
                                            style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E")` }}
                                        >
                                            <option value="openai">OpenAI (Standart)</option>
                                            <option value="openrouter">OpenRouter (Çoklu Model / Ücretsiz Modeller)</option>
                                        </select>
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">API Key</label>
                                        <input
                                            type="password"
                                            value={aiKey}
                                            onChange={(e) => handleUpdateSetting('openai_api_key', e.target.value)}
                                            className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 outline-none font-mono text-sm"
                                            placeholder={aiProvider === 'openrouter' ? "sk-or-v1-..." : "sk-..."}
                                        />
                                    </div>
                                </div>
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <label className="text-xs font-black text-gray-400 uppercase tracking-widest px-1">Model Seçimi</label>
                                        {aiProvider === 'openrouter' ? (
                                            <input
                                                type="text"
                                                value={aiModel}
                                                onChange={(e) => handleUpdateSetting('openai_model', e.target.value)}
                                                className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 outline-none font-bold"
                                                placeholder="google/gemini-2.5-flash-free"
                                            />
                                        ) : (
                                            <select
                                                value={aiModel}
                                                onChange={(e) => handleUpdateSetting('openai_model', e.target.value)}
                                                className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 outline-none font-bold appearance-none bg-no-repeat bg-[right_1.25rem_center] bg-[length:1rem_1rem]"
                                                style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E")` }}
                                            >
                                                <option value="gpt-4o-mini">GPT-4o Mini</option>
                                                <option value="gpt-4o">GPT-4o</option>
                                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                                            </select>
                                        )}
                                    </div>
                                    <div className="flex items-center justify-center p-6 border-2 border-dashed border-gray-200 rounded-[2rem]">
                                        <div className="text-center">
                                            <div className="text-3xl mb-2">🤖</div>
                                            <p className="text-xs text-gray-500 max-w-[200px]">AI entegrasyonu ile URL önerileri ve içerik taslakları oluşturabilirsiniz.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </motion.div>
                ) : activeTab === 'locations' ? (

                    <motion.div key="locations" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} className="space-y-4">
                        <div className="flex gap-2 bg-white/50 p-2 rounded-2xl border border-gray-200 w-fit">
                            <button onClick={() => setLocationType('province')} className={`px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all ${locationType === 'province' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-500'}`}>İller</button>
                            <button onClick={() => setLocationType('district')} className={`px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all ${locationType === 'district' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-500'}`}>İlçeler</button>
                            <button onClick={() => setLocationType('town')} className={`px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all ${locationType === 'town' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-500'}`}>Mahalleler</button>
                        </div>

                        <div className="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden overflow-y-auto max-h-[calc(100vh-350px)] custom-scrollbar">
                            <table className="w-full text-left">
                                <thead className="bg-gray-50 border-b border-gray-100 sticky top-0 z-10 backdrop-blur-sm">
                                    <tr>
                                        <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Lokasyon</th>
                                        <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">URL</th>
                                        <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Durum</th>
                                        <th className="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-50">
                                    {(locationType === 'province' ? provinces : (locationType === 'district' ? districts : towns)).map(item => {
                                        const slug = `${item.slug}${locSuffix}`;
                                        const status = getLinkStatus(slug);
                                        const title = locTitleTemplate.replace('{name}', item.name);
                                        return (
                                            <tr key={item.id} className="hover:bg-gray-50/50 transition-colors group">
                                                <td className="px-6 py-4">
                                                    <div className="font-bold text-gray-800">{item.name}</div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="text-xs font-mono text-blue-500">/{slug}</div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className={`px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tighter bg-${status.color}-50 text-${status.color}-600 border border-${status.color}-100`}>
                                                        {status.label}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    {status.status === 'missing' ? (
                                                        <button onClick={() => handleCreatePage(title, slug)} className="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-600 transition-all">Oluştur</button>
                                                    ) : (
                                                        <div className="flex justify-end gap-2">
                                                            <a href={`/${slug}`} target="_blank" rel="noopener noreferrer" className="text-gray-400 hover:text-blue-500">👁️</a>
                                                        </div>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </motion.div>
                ) : activeTab === 'services' ? (
                    <motion.div key="services" initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: -20 }} className="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden overflow-y-auto max-h-[calc(100vh-300px)] custom-scrollbar">
                        <table className="w-full text-left">
                            <thead className="bg-gray-50 border-b border-gray-100 sticky top-0 z-10 backdrop-blur-sm">
                                <tr>
                                    <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">İl / Hizmet</th>
                                    <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">URL</th>
                                    <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Durum</th>
                                    <th className="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">İşlem</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {provinces.filter(p => p.is_active).map(province => (
                                    services.filter(s => s.is_active).map(service => {
                                        const slug = srvOrder === 'service-province'
                                            ? `${service.slug}${srvSep}${province.slug}`
                                            : `${province.slug}${srvSep}${service.slug}`;
                                        const status = getLinkStatus(slug);
                                        const title = srvTitleTemplate.replace('{province}', province.name).replace('{service}', service.name);
                                        return (
                                            <tr key={`${province.id}-${service.id}`} className="hover:bg-gray-50/50 transition-colors group">
                                                <td className="px-6 py-4">
                                                    <div className="font-bold text-gray-800 text-sm">{province.name} <span className="text-gray-300 mx-1">/</span> {service.name}</div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="text-xs font-mono text-purple-600">/{slug}</div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className={`px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tighter bg-${status.color}-50 text-${status.color}-600 border border-${status.color}-100`}>
                                                        {status.label}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    {status.status === 'missing' ? (
                                                        <button onClick={() => handleCreatePage(title, slug)} className="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-purple-600 transition-all">Oluştur</button>
                                                    ) : (
                                                        <div className="flex justify-end gap-2">
                                                            <a href={`/${slug}`} target="_blank" rel="noopener noreferrer" className="text-gray-400 hover:text-purple-500">👁️</a>
                                                        </div>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })
                                ))}
                            </tbody>
                        </table>
                    </motion.div>
                ) : (
                    <motion.div key="ai" initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} exit={{ opacity: 0, scale: 0.95 }} className="space-y-6">
                        <div className="bg-gradient-to-br from-purple-600 to-blue-700 rounded-[3rem] p-12 text-white relative overflow-hidden shadow-2xl shadow-purple-500/20">
                            <div className="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-48 -mt-48 blur-3xl"></div>
                            <div className="relative z-10 space-y-6 max-w-2xl">
                                <h2 className="text-4xl font-black leading-tight">Link Yapısı AI Asistanı</h2>
                                <p className="text-purple-100 text-lg">Hizmetlerimiz için en yüksek SEO potansiyeline sahip URL yapılarını ve link stratejilerini yapay zeka ile keşfedin.</p>
                                <button
                                    onClick={handleGetAiSuggestions}
                                    disabled={generating}
                                    className="px-8 py-4 bg-white text-purple-600 rounded-2xl font-black shadow-xl hover:scale-105 transition-all flex items-center gap-3 disabled:opacity-50"
                                >
                                    {generating ? (
                                        <><span className="w-5 h-5 border-2 border-purple-600 border-t-transparent rounded-full animate-spin"></span> Bekleniyor...</>
                                    ) : (
                                        <><span className="text-xl">✨</span> URL Önerileri Al</>
                                    )}
                                </button>
                            </div>
                        </div>

                        {aiSuggestions.length > 0 && (
                            <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm space-y-6">
                                <h3 className="text-lg font-black text-gray-900 px-2 tracking-tight">Önerilen URL Yapıları</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {aiSuggestions.map((slug, idx) => (
                                        <div key={idx} className="p-4 bg-gray-50 border border-gray-100 rounded-2xl group hover:border-purple-200 transition-all">
                                            <div className="flex items-center justify-between">
                                                <code className="text-purple-600 font-mono text-sm">/{slug}</code>
                                                <button
                                                    onClick={() => {
                                                        const title = slug.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                                                        handleCreatePage(title, slug);
                                                    }}
                                                    className="opacity-0 group-hover:opacity-100 p-2 bg-white rounded-lg shadow-sm text-xs hover:text-purple-600 transition-all"
                                                >
                                                    Oluştur
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </motion.div>
                        )}
                    </motion.div>
                )}
            </AnimatePresence>
        </motion.div>
    );
}
