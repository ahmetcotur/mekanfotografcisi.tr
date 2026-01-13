import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function Services() {
    const [services, setServices] = useState([]);
    const [loading, setLoading] = useState(true);

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

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800">Hizmetler</h1>
            </div>

            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Başlık</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Slug</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Durum</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        {services.map((service) => (
                            <tr key={service.id} className="border-t hover:bg-gray-50">
                                <td className="px-6 py-4 text-sm font-medium text-gray-800">{service.title}</td>
                                <td className="px-6 py-4 text-sm text-gray-600">{service.slug}</td>
                                <td className="px-6 py-4">
                                    <span className={`px-2 py-1 rounded text-xs font-medium ${service.post_status === 'publish' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'
                                        }`}>
                                        {service.post_status}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-sm text-gray-500">
                                    {new Date(service.created_at).toLocaleDateString('tr-TR')}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
