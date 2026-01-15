import { Link, useLocation } from 'react-router-dom';
import { motion } from 'framer-motion';

export default function Sidebar() {
    const location = useLocation();

    const menuItems = [
        { path: '/dashboard', icon: '📊', label: 'Dashboard' },
        { path: '/pages', icon: '📄', label: 'Sayfalar' },
        { path: '/services', icon: '📷', label: 'Hizmetler' },
        { path: '/locations', icon: '📍', label: 'Lokasyonlar' },
        { path: '/seo-links', icon: '🔗', label: 'SEO Link Yönetimi' },
        { path: '/seo-pages', icon: '🔍', label: 'SEO Sayfaları' },
        { path: '/quotes', icon: '✉️', label: 'Teklif Talepleri' },
        { path: '/media', icon: '🖼️', label: 'Medya' },
        { path: '/pexels', icon: '🌄', label: 'Pexels Koleksiyon' },
        { path: '/settings', icon: '⚙️', label: 'Ayarlar' }
    ];

    return (
        <div className="w-72 bg-gradient-to-b from-slate-900 to-black text-white min-h-screen p-6 flex flex-col border-r border-white/5 shadow-2xl">
            <div className="mb-10 px-2 flex items-center gap-3">
                <div className="w-10 h-10 bg-blue-600 rounded-2xl flex items-center justify-center text-xl shadow-lg shadow-blue-500/20 rotate-3">📸</div>
                <div>
                    <h1 className="text-lg font-black tracking-tighter leading-none">MEKAN</h1>
                    <p className="text-[10px] text-blue-400 font-bold tracking-[0.2em] uppercase">Fotoğrafçısı</p>
                </div>
            </div>

            <nav className="flex-1 space-y-1.5 relative">
                {menuItems.map((item) => {
                    const isActive = location.pathname.startsWith(item.path);
                    return (
                        <Link
                            key={item.path}
                            to={item.path}
                            className={`group relative flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300 ${isActive
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20'
                                : 'text-slate-400 hover:text-white hover:bg-white/5'
                                }`}
                        >
                            {isActive && (
                                <motion.div
                                    layoutId="active-pill"
                                    className="absolute left-[-1.5rem] w-1.5 h-8 bg-blue-500 rounded-r-full"
                                />
                            )}
                            <span className={`text-lg transition-transform group-hover:scale-125 duration-300 ${isActive ? 'grayscale-0' : 'grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100'}`}>
                                {item.icon}
                            </span>
                            <span className="text-sm font-bold tracking-tight">{item.label}</span>
                        </Link>
                    );
                })}
            </nav>

            <div className="mt-auto pt-6 border-t border-white/5">
                <button
                    onClick={() => {
                        localStorage.removeItem('admin_token');
                        window.location.href = '/admin/';
                    }}
                    className="w-full flex items-center gap-4 px-4 py-3 rounded-2xl text-slate-500 hover:text-red-400 hover:bg-red-400/5 transition-all group"
                >
                    <span className="text-xl grayscale group-hover:grayscale-0 transition-all">🚪</span>
                    <span className="text-sm font-bold tracking-tight">Oturumu Kapat</span>
                </button>
            </div>
        </div>
    );
}
