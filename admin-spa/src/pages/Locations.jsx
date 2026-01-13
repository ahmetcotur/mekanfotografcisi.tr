import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function Locations() {
    const [provinces, setProvinces] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadLocations();
    }, []);

    const loadLocations = async () => {
        try {
            const response = await api.get('/admin-update.php?table=locations_province&action=list');
            if (response.data.success) {
                setProvinces(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load locations:', error);
        } finally {
            setLoading(false);
        }
    };

    const toggleActive = async (id, isActive, type) => {
        try {
            const table = type === 'province' ? 'locations_province' : 'locations_district';
            await api.post('/admin-update.php', {
                table,
                id,
                data: { is_active: !isActive }
            });
            loadLocations();
            Swal.fire('Başarılı', 'Durum güncellendi', 'success');
        } catch (error) {
            Swal.fire('Hata', error.response?.data?.error || 'Güncelleme başarısız', 'error');
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800">Lokasyonlar</h1>
            </div>

            <div className="bg-white rounded-lg shadow">
                {provinces.map((province) => (
                    <div key={province.id} className="border-b last:border-b-0">
                        <div className="p-4 flex items-center justify-between hover:bg-gray-50">
                            <div className="flex items-center gap-3">
                                <span className="text-xl">📍</span>
                                <div>
                                    <h3 className="font-semibold text-gray-800">{province.name}</h3>
                                    <p className="text-sm text-gray-500">{province.slug}</p>
                                </div>
                            </div>
                            <button
                                onClick={() => toggleActive(province.id, province.is_active, 'province')}
                                className={`px-4 py-2 rounded-lg text-sm font-medium ${province.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-700'
                                    }`}
                            >
                                {province.is_active ? 'Aktif' : 'Pasif'}
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
