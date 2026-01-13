import { Link, useLocation } from 'react-router-dom';

export default function Sidebar() {
    const location = useLocation();

    const menuItems = [
        { path: '/dashboard', icon: '📊', label: 'Dashboard' },
        { path: '/locations', icon: '📍', label: 'Lokasyonlar' },
        { path: '/services', icon: '📷', label: 'Hizmetler' },
        { path: '/quotes', icon: '✉️', label: 'Teklif Talepleri' },
        { path: '/media', icon: '🖼️', label: 'Medya' },
        { path: '/settings', icon: '⚙️', label: 'Ayarlar' }
    ];

    return (
        <div className="w-64 bg-gray-900 text-white min-h-screen p-4">
            <div className="mb-8">
                <h1 className="text-xl font-bold">Mekan Fotoğrafçısı</h1>
                <p className="text-xs text-gray-400">Admin Panel</p>
            </div>

            <nav className="space-y-2">
                {menuItems.map((item) => (
                    <Link
                        key={item.path}
                        to={item.path}
                        className={`flex items-center gap-3 px-4 py-3 rounded-lg transition ${location.pathname === item.path
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-300 hover:bg-gray-800'
                            }`}
                    >
                        <span className="text-xl">{item.icon}</span>
                        <span className="font-medium">{item.label}</span>
                    </Link>
                ))}
            </nav>

            <div className="absolute bottom-4 left-4 right-4">
                <button
                    onClick={() => {
                        localStorage.removeItem('admin_token');
                        window.location.href = '/admin/';
                    }}
                    className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-gray-800 transition"
                >
                    <span className="text-xl">🚪</span>
                    <span className="font-medium">Çıkış Yap</span>
                </button>
            </div>
        </div>
    );
}
