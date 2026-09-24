import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion, AnimatePresence } from 'framer-motion';

export default function FreelancerApplications() {
    const [applications, setApplications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');
    const [selectedApp, setSelectedApp] = useState(null);

    useEffect(() => {
        loadApplications();
    }, []);

    const loadApplications = async () => {
        try {
            const response = await api.get('/admin-update.php?table=freelancer_applications&action=list');
            if (response.data.success) {
                const sorted = (response.data.data || []).sort((a, b) =>
                    new Date(b.created_at) - new Date(a.created_at)
                );
                setApplications(sorted);
            }
        } catch (error) {
            console.error('Failed to load applications:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleUpdateStatus = async (app, status) => {
        try {
            await api.post('/admin-update.php', {
                action: 'update',
                table: 'freelancer_applications',
                id: app.id,
                data: { status }
            });
            loadApplications();
            if (selectedApp?.id === app.id) {
                setSelectedApp({ ...app, status });
            }
            Swal.fire({ title: 'Güncellendi', icon: 'success', timer: 800, showConfirmButton: false, toast: true, position: 'bottom-end' });
        } catch (error) {
            Swal.fire('Hata', 'Durum güncellenemedi', 'error');
        }
    };

    const handleTogglePublic = async (app) => {
        try {
            await api.post('/admin-update.php', {
                action: 'update',
                table: 'freelancer_applications',
                id: app.id,
                data: { is_public: !app.is_public }
            });
            loadApplications();
            if (selectedApp?.id === app.id) {
                setSelectedApp({ ...app, is_public: !app.is_public });
            }
        } catch (error) {
            Swal.fire('Hata', 'Dizin görünürlüğü güncellenemedi', 'error');
        }
    };

    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu başvuru kalıcı olarak silinecektir!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                await api.post('/admin-update.php', { action: 'delete', table: 'freelancer_applications', id });
                loadApplications();
                setSelectedApp(null);
                Swal.fire('Silindi!', 'Başvuru başarıyla silindi.', 'success');
            } catch (error) {
                Swal.fire('Hata', 'Silme işlemi başarısız', 'error');
            }
        }
    };

    const filteredApplications = applications.filter(app => {
        if (filter === 'pending') return app.status === 'pending';
        if (filter === 'approved') return app.status === 'approved';
        if (filter === 'rejected') return app.status === 'rejected';
        return true;
    });

    const getStatusColor = (status) => {
        switch (status) {
            case 'approved': return 'green';
            case 'rejected': return 'red';
            default: return 'amber';
        }
    };

    const getStatusLabel = (status) => {
        switch (status) {
            case 'approved': return 'Onaylandı';
            case 'rejected': return 'Reddedildi';
            default: return 'Beklemede';
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-6"
        >
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 tracking-tight">Freelancer Başvuruları</h1>
                    <p className="text-gray-500 text-sm">Ekibimize katılmak isteyen fotoğrafçıların listesi</p>
                </div>
                <div className="flex bg-gray-100 p-1 rounded-xl">
                    {[
                        { id: 'all', label: 'Tümü', count: applications.length },
                        { id: 'pending', label: 'Bekleyen', count: applications.filter(a => a.status === 'pending').length },
                        { id: 'approved', label: 'Onaylanan', count: applications.filter(a => a.status === 'approved').length },
                    ].map(btn => (
                        <button
                            key={btn.id}
                            onClick={() => setFilter(btn.id)}
                            className={`px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${filter === btn.id ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            {btn.label} ({btn.count})
                        </button>
                    ))}
                </div>
            </div>

            <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
                {/* List Portion */}
                <div className={`xl:col-span-1 space-y-3 max-h-[700px] overflow-y-auto pr-2 custom-scrollbar ${selectedApp ? 'hidden xl:block' : 'block'}`}>
                    {filteredApplications.map((app) => (
                        <motion.div
                            layout
                            key={app.id}
                            onClick={() => setSelectedApp(app)}
                            className={`p-4 rounded-2xl border cursor-pointer transition-all ${selectedApp?.id === app.id
                                ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200'
                                : `bg-white hover:bg-gray-50 border-gray-100`}`}
                        >
                            <div className="flex justify-between items-start mb-1">
                                <span className={`text-[10px] font-black uppercase tracking-widest ${selectedApp?.id === app.id ? 'text-white/70' : `text-${getStatusColor(app.status)}-500`}`}>
                                    {getStatusLabel(app.status)}
                                </span>
                                <span className={`text-[10px] opacity-60`}>
                                    {new Date(app.created_at).toLocaleDateString('tr-TR')}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <div className={`font-bold ${selectedApp?.id === app.id ? 'text-white' : 'text-gray-800'}`}>{app.name}</div>
                                {app.is_public && (
                                    <span className={`text-[9px] font-black uppercase px-1.5 py-0.5 rounded ${selectedApp?.id === app.id ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-500'}`}>Dizinde</span>
                                )}
                            </div>
                            <div className={`text-xs truncate opacity-70`}>{app.city}</div>
                        </motion.div>
                    ))}
                    {filteredApplications.length === 0 && (
                        <div className="text-center py-12 text-gray-400 italic bg-white rounded-3xl border border-dashed border-gray-200">
                            Başvuru bulunamadı.
                        </div>
                    )}
                </div>

                {/* Detail Portion */}
                <div className={`xl:col-span-2 ${!selectedApp ? 'hidden xl:flex' : 'block'}`}>
                    <AnimatePresence mode="wait">
                        {selectedApp ? (
                            <motion.div
                                key={selectedApp.id}
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                exit={{ opacity: 0, x: -20 }}
                                className="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 h-full flex flex-col"
                            >
                                <div className="flex justify-between items-start mb-8">
                                    <button
                                        onClick={() => setSelectedApp(null)}
                                        className="xl:hidden p-2 hover:bg-gray-100 rounded-lg text-gray-400"
                                    >
                                        ← Geri
                                    </button>
                                    <div className="flex gap-2 flex-wrap justify-end">
                                        <div className="flex bg-gray-100 p-1 rounded-xl mr-2">
                                            <button onClick={() => handleUpdateStatus(selectedApp, 'approved')} className={`px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all ${selectedApp.status === 'approved' ? 'bg-green-500 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600'}`}>Onayla</button>
                                            <button onClick={() => handleUpdateStatus(selectedApp, 'rejected')} className={`px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all ${selectedApp.status === 'rejected' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600'}`}>Reddet</button>
                                            <button onClick={() => handleUpdateStatus(selectedApp, 'pending')} className={`px-3 py-1 rounded-lg text-[10px] font-black uppercase transition-all ${selectedApp.status === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-400 hover:text-gray-600'}`}>Beklet</button>
                                        </div>
                                        <button
                                            onClick={() => handleTogglePublic(selectedApp)}
                                            title={selectedApp.status !== 'approved' ? 'Önce başvuruyu onaylayın' : ''}
                                            disabled={selectedApp.status !== 'approved' && !selectedApp.is_public}
                                            className={`px-3 py-1.5 rounded-xl text-[10px] font-black uppercase transition-all disabled:opacity-40 disabled:cursor-not-allowed ${selectedApp.is_public ? 'bg-blue-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:text-gray-700'}`}
                                        >
                                            {selectedApp.is_public ? '✓ Dizinde Görünüyor' : 'Dizinde Yayınla'}
                                        </button>
                                        <button
                                            onClick={() => handleDelete(selectedApp.id)}
                                            className="p-2 text-red-400 hover:bg-red-50 rounded-xl transition-colors"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>

                                <div className="space-y-6 flex-1">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div>
                                            <h3 className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Başvuru Sahibi</h3>
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xs">👤</span>
                                                    <div className="text-lg font-bold text-gray-800">{selectedApp.name}</div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xs">📧</span>
                                                    <div className="text-sm text-gray-600 font-medium">{selectedApp.email}</div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xs">📞</span>
                                                    <div className="text-sm text-gray-600 font-medium">{selectedApp.phone}</div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xs">📍</span>
                                                    <div className="text-sm text-gray-600 font-medium">{selectedApp.city}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Mesleki Detaylar</h3>
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center text-xs">🏆</span>
                                                    <div>
                                                        <div className="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Deneyim</div>
                                                        <div className="text-sm font-bold text-gray-800">{selectedApp.experience} Yıl</div>
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center text-xs">🛠️</span>
                                                    <div>
                                                        <div className="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Uzmanlık Alanları</div>
                                                        <div className="flex flex-wrap gap-1 mt-1">
                                                            {JSON.parse(selectedApp.specialization || '[]').map(spec => (
                                                                <span key={spec} className="px-2 py-0.5 bg-slate-100 rounded text-[9px] font-black uppercase text-slate-600">{spec}</span>
                                                            ))}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-xs">⭐</span>
                                                    <div>
                                                        <div className="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Değerlendirme</div>
                                                        <div className="text-sm font-bold text-gray-800">
                                                            {selectedApp.rating_count > 0
                                                                ? `${parseFloat(selectedApp.rating_avg).toFixed(1)} (${selectedApp.rating_count} değerlendirme)`
                                                                : 'Henüz değerlendirme yok'}
                                                        </div>
                                                    </div>
                                                </div>
                                                {selectedApp.portfolio_url && (
                                                    <div className="flex items-center gap-3">
                                                        <span className="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xs">🔗</span>
                                                        <div>
                                                            <div className="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Portfolyo</div>
                                                            <a href={selectedApp.portfolio_url} target="_blank" rel="noopener noreferrer" className="text-sm font-bold text-blue-600 hover:underline">{selectedApp.portfolio_url}</a>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-8 pt-8 border-t border-gray-50">
                                        <h3 className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Mesaj / Hakkında</h3>
                                        <div className="bg-gray-50 p-6 rounded-2xl text-gray-700 leading-relaxed whitespace-pre-wrap italic">
                                            {selectedApp.message || 'Ek bilgi belirtilmemiş.'}
                                        </div>
                                    </div>
                                </div>
                                <p className="text-[9px] text-gray-300 mt-8 text-center uppercase tracking-widest font-bold">Başvuru ID: {selectedApp.id}</p>
                            </motion.div>
                        ) : (
                            <div className="w-full h-full flex flex-col items-center justify-center text-gray-300 border-2 border-dashed border-gray-100 rounded-3xl bg-gray-50/30">
                                <span className="text-6xl mb-4">📸</span>
                                <p className="font-bold tracking-tight uppercase text-xs opacity-50">Detayları görmek için bir başvuru seçin</p>
                            </div>
                        )}
                    </AnimatePresence>
                </div>
            </div>
        </motion.div>
    );
}
