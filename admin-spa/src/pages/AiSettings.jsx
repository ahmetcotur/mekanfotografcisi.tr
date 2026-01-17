import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion } from 'framer-motion';

export default function AiSettings() {
    const [settings, setSettings] = useState({
        openai_api_key: '',
        openai_model: 'gpt-4o-mini'
    });
    const [loading, setLoading] = useState(true);

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
                setSettings({
                    openai_api_key: flatSettings.openai_api_key || '',
                    openai_model: flatSettings.openai_model || 'gpt-4o-mini'
                });
            }
        } catch (error) {
            console.error('Failed to load settings', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSave = async () => {
        try {
            await api.post('/settings.php', { settings });
            Swal.fire({
                title: 'Başarılı',
                text: 'AI Ayarları kaydedildi.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } catch (error) {
            Swal.fire('Hata', 'Kaydetme başarısız', 'error');
        }
    };

    if (loading) return <div className="p-12 text-center text-gray-500">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="max-w-4xl mx-auto space-y-6"
        >
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800 tracking-tight">AI Ayarları</h1>
                    <p className="text-gray-500 text-sm">Yapay zeka içerik üretim motorunu buradan yapılandırın.</p>
                </div>
                <button
                    onClick={handleSave}
                    className="px-8 py-3 bg-purple-600 text-white rounded-2xl font-bold shadow-xl shadow-purple-500/20 hover:bg-purple-700 hover:scale-105 transition-all active:scale-95"
                >
                    Kaydet ✨
                </button>
            </div>

            <div className="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden p-10">
                <div className="bg-purple-50 p-8 rounded-3xl border border-purple-100 mb-10 flex gap-6 items-start">
                    <div className="w-16 h-16 bg-purple-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-purple-500/20 shrink-0">🤖</div>
                    <div>
                        <h3 className="text-xl font-bold text-purple-900 mb-2">OpenAI Entegrasyonu</h3>
                        <p className="text-purple-800/70 leading-relaxed font-medium">
                            Blog yazıları ve sayfa içerikleri OpenAI 4o-mini modeli ile saniyeler içinde üretilir.
                            Başlamak için OpenAI platformundan aldığınız API key'i aşağıya girin.
                        </p>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div className="space-y-3">
                        <label className="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">OpenAI API Key</label>
                        <input
                            type="password"
                            value={settings.openai_api_key}
                            onChange={(e) => setSettings({ ...settings, openai_api_key: e.target.value })}
                            className="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all outline-none font-mono text-sm"
                            placeholder="sk-proj-..."
                        />
                        <p className="text-[10px] text-gray-400 px-1 font-medium">Key'iniz sunucu tarafında güvenle saklanır.</p>
                    </div>

                    <div className="space-y-3">
                        <label className="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Model Seçimi</label>
                        <select
                            value={settings.openai_model}
                            onChange={(e) => setSettings({ ...settings, openai_model: e.target.value })}
                            className="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all outline-none font-bold text-gray-800"
                        >
                            <option value="gpt-4o-mini">GPT-4o Mini (Hızlı & Ekonomik - Önerilen)</option>
                            <option value="gpt-4o">GPT-4o (En Yüksek Kalite)</option>
                        </select>
                        <p className="text-[10px] text-gray-400 px-1 font-medium">Blog yazıları için 4o-mini mükemmel sonuç verir.</p>
                    </div>
                </div>

                <div className="mt-12 pt-8 border-t border-gray-50 flex items-center gap-4 text-gray-400 text-sm italic">
                    <span>💡</span>
                    <p>Hala key'iniz yoksa <a href="https://platform.openai.com/api-keys" target="_blank" className="text-purple-600 font-bold hover:underline">OpenAI Dashboard</a> üzerinden hemen alabilirsiniz.</p>
                </div>
            </div>
        </motion.div>
    );
}
