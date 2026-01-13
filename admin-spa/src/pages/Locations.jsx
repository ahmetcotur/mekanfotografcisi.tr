import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function Locations() {
    const [provinces, setProvinces] = useState([]);
    const [districts, setDistricts] = useState([]);
    const [selectedProvince, setSelectedProvince] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadProvinces();
    }, []);

    const loadProvinces = async () => {
        setLoading(true);
        try {
            const response = await api.get('/admin-update.php?table=locations_province&action=list');
            if (response.data.success) {
                setProvinces(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load provinces');
        } finally {
            setLoading(false);
        }
    };

    const loadDistricts = async (provinceId) => {
        try {
            const response = await api.get(`/admin-update.php?action=list&table=locations_district&province_id=${provinceId}`);
            if (response.data.success) {
                setDistricts(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load districts');
        }
    };

    const handleSelectProvince = (province) => {
        setSelectedProvince(province);
        loadDistricts(province.id);
    };

    const toggleActive = async (id, isActive, table) => {
        try {
            await api.post('/admin-update.php', {
                action: 'update',
                table,
                id,
                data: { is_active: !isActive }
            });
            if (table === 'locations_province') loadProvinces();
            else loadDistricts(selectedProvince.id);
            Swal.fire('Başarılı', 'Durum güncellendi', 'success');
        } catch (error) {
            Swal.fire('Hata', 'Güncelleme başarısız', 'error');
        }
    };

    const handleDelete = async (id, table) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu işlem geri alınamaz!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
        });

        if (result.isConfirmed) {
            try {
                await api.post('/admin-update.php', { action: 'delete', table, id });
                if (table === 'locations_province') {
                    loadProvinces();
                    setSelectedProvince(null);
                } else {
                    loadDistricts(selectedProvince.id);
                }
                Swal.fire('Silindi', '', 'success');
            } catch (error) {
                Swal.fire('Hata', 'Silme başarısız', 'error');
            }
        }
    };

    const handleAddProvince = async () => {
        const { value: name } = await Swal.fire({
            title: 'Yeni İl Ekle',
            input: 'text',
            inputLabel: 'İl Adı',
            showCancelButton: true,
        });

        if (name) {
            try {
                await api.post('/admin-update.php', {
                    action: 'save-location',
                    table: 'locations_province',
                    name
                });
                loadProvinces();
            } catch (error) {
                Swal.fire('Hata', 'Eklenemedi', 'error');
            }
        }
    };

    const handleAddDistrict = async () => {
        if (!selectedProvince) return;
        const { value: name } = await Swal.fire({
            title: 'Yeni İlçe Ekle',
            input: 'text',
            inputLabel: 'İlçe Adı',
            showCancelButton: true,
        });

        if (name) {
            try {
                await api.post('/admin-update.php', {
                    action: 'save-location',
                    table: 'locations_district',
                    name,
                    province_id: selectedProvince.id
                });
                loadDistricts(selectedProvince.id);
            } catch (error) {
                Swal.fire('Hata', 'Eklenemedi', 'error');
            }
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {/* Provinces Column */}
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h1 className="text-3xl font-bold text-gray-800">İller</h1>
                    <button
                        onClick={handleAddProvince}
                        className="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        + Yeni İl
                    </button>
                </div>

                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    {provinces.map((province) => (
                        <div
                            key={province.id}
                            className={`p-4 flex items-center justify-between border-b last:border-b-0 hover:bg-gray-50 transition cursor-pointer ${selectedProvince?.id === province.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''
                                }`}
                            onClick={() => handleSelectProvince(province)}
                        >
                            <div className="flex items-center gap-3">
                                <span className="text-xl">📍</span>
                                <div>
                                    <h3 className="font-semibold text-gray-800">{province.name}</h3>
                                    <p className="text-xs text-gray-500">/{province.slug}</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-2" onClick={e => e.stopPropagation()}>
                                <button
                                    onClick={() => toggleActive(province.id, province.is_active, 'locations_province')}
                                    className={`px-3 py-1 rounded-lg text-xs font-bold ${province.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}
                                >
                                    {province.is_active ? 'AKTİF' : 'PASİF'}
                                </button>
                                <button
                                    onClick={() => handleDelete(province.id, 'locations_province')}
                                    className="p-1 text-red-500 hover:bg-red-50 rounded"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Districts Column */}
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <h2 className="text-3xl font-bold text-gray-800">
                        {selectedProvince ? `${selectedProvince.name} İlçeleri` : 'İl Seçiniz'}
                    </h2>
                    {selectedProvince && (
                        <button
                            onClick={handleAddDistrict}
                            className="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        >
                            + Yeni İlçe
                        </button>
                    )}
                </div>

                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    {!selectedProvince ? (
                        <div className="p-12 text-center text-gray-400">
                            İlçeleri görmek için soldan bir il seçin.
                        </div>
                    ) : districts.length === 0 ? (
                        <div className="p-12 text-center text-gray-400">
                            Bu il için henüz ilçe eklenmemiş.
                        </div>
                    ) : (
                        districts.map((district) => (
                            <div key={district.id} className="p-4 flex items-center justify-between border-b last:border-b-0 hover:bg-gray-50 transition">
                                <div>
                                    <h3 className="font-semibold text-gray-800">{district.name}</h3>
                                    <p className="text-xs text-gray-500">/{selectedProvince.slug}/{district.slug}</p>
                                </div>
                                <div className="flex items-center gap-2">
                                    <button
                                        onClick={() => toggleActive(district.id, district.is_active, 'locations_district')}
                                        className={`px-3 py-1 rounded-lg text-xs font-bold ${district.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}
                                    >
                                        {district.is_active ? 'AKTİF' : 'PASİF'}
                                    </button>
                                    <button
                                        onClick={() => handleDelete(district.id, 'locations_district')}
                                        className="p-1 text-red-500 hover:bg-red-50 rounded"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </div>
    );
}
