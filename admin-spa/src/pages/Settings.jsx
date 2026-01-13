import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import useAuthStore from '../store/authStore';
import { motion } from 'framer-motion';

export default function Settings() {
    const [settings, setSettings] = useState({});
    const [passwords, setPasswords] = useState({ new: '', confirm: '' });
    const [loading, setLoading] = useState(true);
    const user = useAuthStore((state) => state.user);

    useEffect(() => {
        loadSettings();
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

    const handlePasswordChange = async () => {
        if (!passwords.new) return;
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

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="max-w-4xl mx-auto space-y-8 pb-20"
        >
            <div>
                <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Ayarlar</h1>
                <p className="text-gray-500 text-sm">Panel ve hesap tercihlerinizi yönetin</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {/* Account Section */}
                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <div className="flex items-center gap-3 mb-2">
                        <div className="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">👤</div>
                        <h2 className="text-xl font-bold text-gray-800">Hesap Bilgileri</h2>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">İsim Soyisim</label>
                            <div className="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-gray-400 font-medium">
                                {user?.name || 'Administratör'}
                            </div>
                        </div>
                        <div>
                            <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">E-Posta Adresi</label>
                            <div className="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-gray-400 font-medium">
                                {user?.email || ''}
                            </div>
                        </div>
                    </div>
                    <p className="text-[10px] text-gray-400 italic font-medium leading-relaxed">
                        * Kayıtlı bilgiler sisteme ilk kurulumda atanmıştır ve güvenlik nedeniyle panel üzerinden değiştirilemez.
                    </p>
                </div>

                {/* Password Change */}
                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                    <div className="flex items-center gap-3 mb-2">
                        <div className="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">🔐</div>
                        <h2 className="text-xl font-bold text-gray-800">Şifre Değiştir</h2>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Yeni Şifre</label>
                            <input
                                type="password"
                                value={passwords.new}
                                onChange={(e) => setPasswords({ ...passwords, new: e.target.value })}
                                className="w-full px-4 py-3 bg-gray-50 border border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 rounded-2xl text-gray-800 outline-none transition-all"
                                placeholder="••••••••"
                            />
                        </div>
                        <div>
                            <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Şifre Tekrar</label>
                            <input
                                type="password"
                                value={passwords.confirm}
                                onChange={(e) => setPasswords({ ...passwords, confirm: e.target.value })}
                                className="w-full px-4 py-3 bg-gray-50 border border-gray-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 rounded-2xl text-gray-800 outline-none transition-all"
                                placeholder="••••••••"
                            />
                        </div>
                        <button
                            onClick={handlePasswordChange}
                            disabled={!passwords.new}
                            className={`w-full py-3.5 rounded-2xl font-bold text-sm transition-all shadow-lg ${passwords.new ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-500/20' : 'bg-gray-100 text-gray-400 shadow-none cursor-not-allowed'}`}
                        >
                            Bilgileri Güncelle
                        </button>
                    </div>
                </div>
            </div>

            {/* Application Branding System Note */}
            <div className="bg-gradient-to-br from-slate-800 to-slate-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden">
                <div className="relative z-10 max-w-lg">
                    <h3 className="text-2xl font-bold mb-4">Site Ayarları Hakkında</h3>
                    <p className="text-slate-400 text-sm leading-relaxed mb-6">
                        Logo, Telefon, Adres ve Sosyal Medya gibi genel site ayarları `config.php` üzerinden yönetilmektedir. Panel üzerinden dinamik yönetim özelliği yakında eklenecektir.
                    </p>
                    <div className="flex gap-4">
                        <div className="px-4 py-2 bg-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest">v1.2.0 Stable</div>
                        <div className="px-4 py-2 bg-blue-500/20 text-blue-300 rounded-xl text-[10px] font-black uppercase tracking-widest">Premium Build</div>
                    </div>
                </div>
                {/* Decorative element */}
                <div className="absolute top-[-10%] right-[-5%] w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
                <div className="absolute bottom-[-20%] left-[-10%] w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl"></div>
                <div className="absolute right-10 top-1/2 -translate-y-1/2 text-[10rem] opacity-[0.03] select-none uppercase font-black tracking-tighter pointer-events-none">
                    MF
                </div>
            </div>
        </motion.div>
    );
}
