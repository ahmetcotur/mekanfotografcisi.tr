import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion } from 'framer-motion';

export default function Users() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');

    useEffect(() => {
        loadUsers();
    }, []);

    const loadUsers = async () => {
        try {
            const response = await api.get('/admin-update.php?table=users&action=list');
            if (response.data.success) {
                const sorted = (response.data.data || []).sort((a, b) =>
                    new Date(b.created_at) - new Date(a.created_at)
                );
                setUsers(sorted);
            }
        } catch (error) {
            console.error('Failed to load users:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleToggleActive = async (user) => {
        try {
            await api.post('/admin-update.php', {
                action: 'update',
                table: 'users',
                id: user.id,
                data: { is_active: !user.is_active }
            });
            loadUsers();
            Swal.fire({ title: 'Güncellendi', icon: 'success', timer: 800, showConfirmButton: false, toast: true, position: 'bottom-end' });
        } catch (error) {
            Swal.fire('Hata', 'Durum güncellenemedi', 'error');
        }
    };

    const filteredUsers = users.filter(u => filter === 'all' || u.role === filter);

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 tracking-tight">Kullanıcılar</h1>
                    <p className="text-gray-500 text-sm">Fotoğrafçı ve müşteri hesapları</p>
                </div>
                <div className="flex bg-gray-100 p-1 rounded-xl">
                    {[
                        { id: 'all', label: 'Tümü' },
                        { id: 'freelancer', label: 'Fotoğrafçılar' },
                        { id: 'client', label: 'Müşteriler' },
                    ].map(btn => (
                        <button
                            key={btn.id}
                            onClick={() => setFilter(btn.id)}
                            className={`px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${filter === btn.id ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            {btn.label} ({btn.id === 'all' ? users.length : users.filter(u => u.role === btn.id).length})
                        </button>
                    ))}
                </div>
            </div>

            <div className="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 text-gray-400 text-[10px] uppercase tracking-widest">
                        <tr>
                            <th className="text-left px-6 py-4">Ad</th>
                            <th className="text-left px-6 py-4">E-posta</th>
                            <th className="text-left px-6 py-4">Rol</th>
                            <th className="text-left px-6 py-4">Kayıt Tarihi</th>
                            <th className="text-left px-6 py-4">Durum</th>
                            <th className="text-right px-6 py-4">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredUsers.map(u => (
                            <tr key={u.id} className="border-t border-gray-50 hover:bg-gray-50/50">
                                <td className="px-6 py-4 font-bold text-gray-800">{u.name}</td>
                                <td className="px-6 py-4 text-gray-500">{u.email}</td>
                                <td className="px-6 py-4">
                                    <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase ${u.role === 'freelancer' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}`}>
                                        {u.role === 'freelancer' ? 'Fotoğrafçı' : 'Müşteri'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-gray-400">{new Date(u.created_at).toLocaleDateString('tr-TR')}</td>
                                <td className="px-6 py-4">
                                    <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase ${u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                        {u.is_active ? 'Aktif' : 'Devre Dışı'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-right">
                                    <button
                                        onClick={() => handleToggleActive(u)}
                                        className={`px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${u.is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100'}`}
                                    >
                                        {u.is_active ? 'Devre Dışı Bırak' : 'Aktifleştir'}
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {filteredUsers.length === 0 && (
                            <tr>
                                <td colSpan={6} className="text-center py-12 text-gray-400 italic">Kullanıcı bulunamadı.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </motion.div>
    );
}
