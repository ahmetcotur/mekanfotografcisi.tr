import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion } from 'framer-motion';

export default function Services() {
    const [services, setServices] = useState([]);
    const [settings, setSettings] = useState([]);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        try {
            const [srvRes, setRes] = await Promise.all([
                api.get('/admin-update.php?table=posts&action=list&post_type=service&post_status=publish'),
                api.get('/admin-update.php?table=settings&action=list')
            ]);

            if (srvRes.data.success) {
                setServices(srvRes.data.data || []);
            }
            if (setRes.data.success) {
                setSettings(setRes.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load data:', error);
        } finally {
            setLoading(false);
        }
    };

    const getSetting = (key, def) => settings.find(s => s.key === key)?.value || def;
    const serviceBase = getSetting('seo_service_base', 'hizmetlerimiz');

    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu hizmet kalıcı olarak silinecektir!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                await api.post('/admin-update.php', { action: 'delete', table: 'posts', id });
                loadServices();
                Swal.fire('Silindi!', 'Hizmet başarıyla silindi.', 'success');
            } catch (error) {
                Swal.fire('Hata', 'Silme işlemi başarısız', 'error');
            }
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, scale: 0.98 }}
            animate={{ opacity: 1, scale: 1 }}
            className="space-y-6"
        >
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Hizmetler</h1>
                <button
                    onClick={() => navigate('/services/new')}
                    className="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition transform hover:-translate-y-0.5 active:scale-95"
                >
                    + Yeni Hizmet Ekle
                </button>
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50/50 border-b border-gray-100">
                        <tr>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Hizmet Adı</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">URL (Slug)</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Durum</th>
                            <th className="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {services.map((service) => (
                            <tr key={service.id} className="group hover:bg-blue-50/30 transition-colors">
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="text-sm font-semibold text-gray-800">{service.title}</div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="text-xs text-blue-500 font-mono">/{serviceBase}/{service.slug}</div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase ${service.post_status === 'publish'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-amber-100 text-amber-700'
                                        }`}>
                                        {service.post_status === 'publish' ? 'YAYINDA' : 'TASLAK'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button
                                        onClick={() => navigate(`/services/edit/${service.id}`)}
                                        className="text-blue-600 hover:text-blue-900 bg-blue-50 px-4 py-1.5 rounded-lg transition-colors"
                                    >
                                        Düzenle
                                    </button>
                                    <button
                                        onClick={() => handleDelete(service.id)}
                                        className="text-red-500 hover:text-red-900 px-2 py-1.5 rounded-lg opacity-40 group-hover:opacity-100 transition-opacity"
                                    >
                                        Sil
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </motion.div>
    );
}
