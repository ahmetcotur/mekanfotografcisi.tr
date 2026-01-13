import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function Services() {
    const [services, setServices] = useState([]);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    useEffect(() => {
        loadServices();
    }, []);

    const loadServices = async () => {
        try {
            const response = await api.get('/admin-update.php?table=posts&action=list&post_type=service');
            if (response.data.success) {
                setServices(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load services:', error);
        } finally {
            setLoading(false);
        }
    };

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
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800">Hizmetler</h1>
                <button
                    onClick={() => navigate('/services/new')}
                    className="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition transform hover:-translate-y-0.5"
                >
                    + Yeni Hizmet Ekle
                </button>
            </div>

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hizmet Adı</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">URL (Slug)</th>
                            <th className="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Durum</th>
                            <th className="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {services.map((service) => (
                            <tr key={service.id} className="hover:bg-gray-50/50 transition">
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="text-sm font-semibold text-gray-800">{service.title}</div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="text-sm text-gray-500">/services/{service.slug}</div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold ${service.post_status === 'publish'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-600'
                                        }`}>
                                        {service.post_status === 'publish' ? 'YAYINDA' : 'TASLAK'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button
                                        onClick={() => navigate(`/services/edit/${service.id}`)}
                                        className="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-lg"
                                    >
                                        Düzenle
                                    </button>
                                    <button
                                        onClick={() => handleDelete(service.id)}
                                        className="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-lg"
                                    >
                                        Sil
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
