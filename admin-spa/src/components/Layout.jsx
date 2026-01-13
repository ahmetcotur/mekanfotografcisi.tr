import { Outlet } from 'react-router-dom';
import Sidebar from './Sidebar';
import useAuthStore from '../store/authStore';
import api from '../api/client';
import { motion } from 'framer-motion';

export default function Layout() {
    const { user, logout } = useAuthStore();

    const handleLogout = async () => {
        try {
            await api.post('/auth.php?action=logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            logout();
            window.location.href = '/admin/';
        }
    };

    return (
        <div className="flex min-h-screen bg-gray-100">
            <Sidebar />

            <div className="flex-1 flex flex-col">
                <header className="bg-white shadow-sm px-6 py-4 flex justify-between items-center">
                    <h2 className="text-xl font-semibold text-gray-800">Admin Panel</h2>

                    <div className="flex items-center gap-4">
                        <span className="text-sm text-gray-600">{user?.name || 'Admin'}</span>
                        <button
                            onClick={handleLogout}
                            className="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium transition"
                        >
                            Çıkış
                        </button>
                    </div>
                </header>

                <motion.main
                    key={window.location.pathname}
                    initial={{ opacity: 0, x: 20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.3 }}
                    className="flex-1 p-8"
                >
                    <Outlet />
                </motion.main>
            </div>
        </div>
    );
}
