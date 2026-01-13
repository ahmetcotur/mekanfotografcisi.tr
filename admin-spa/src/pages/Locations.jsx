import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion, AnimatePresence } from 'framer-motion';

export default function Locations() {
    const [provinces, setProvinces] = useState([]);
    const [districts, setDistricts] = useState([]);
    const [towns, setTowns] = useState([]);

    const [selectedProvince, setSelectedProvince] = useState(null);
    const [selectedDistrict, setSelectedDistrict] = useState(null);

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

    const loadTowns = async (districtId) => {
        try {
            const response = await api.get(`/admin-update.php?action=list&table=locations_town&district_id=${districtId}`);
            if (response.data.success) {
                setTowns(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load towns');
        }
    };

    const handleSelectProvince = (province) => {
        setSelectedProvince(province);
        setSelectedDistrict(null); // Reset detail selection
        setTowns([]); // Clear detail data
        loadDistricts(province.id);
    };

    const handleSelectDistrict = (district) => {
        setSelectedDistrict(district);
        loadTowns(district.id);
    };

    const toggleActive = async (id, isActive, table) => {
        try {
            await api.post('/admin-update.php', {
                action: 'update',
                table,
                id,
                data: { is_active: !isActive }
            });

            // Refresh logic
            if (table === 'locations_province') loadProvinces();
            else if (table === 'locations_district') loadDistricts(selectedProvince.id);
            else if (table === 'locations_town') loadTowns(selectedDistrict.id);

            Swal.fire({
                title: 'Güncellendi',
                icon: 'success',
                timer: 800,
                showConfirmButton: false,
                toast: true,
                position: 'bottom-end'
            });
        } catch (error) {
            Swal.fire('Hata', 'Güncelleme başarısız', 'error');
        }
    };

    const handleDelete = async (id, table) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu işlem ve altındaki tüm bağlı veriler silinecektir!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal',
            customClass: {
                confirmButton: 'bg-red-500 rounded-xl px-6 py-2.5 font-bold',
                cancelButton: 'bg-gray-100 text-gray-600 rounded-xl px-6 py-2.5 font-bold'
            }
        });

        if (result.isConfirmed) {
            try {
                await api.post('/admin-update.php', { action: 'delete', table, id });

                if (table === 'locations_province') {
                    loadProvinces();
                    setSelectedProvince(null);
                    setSelectedDistrict(null);
                    setTowns([]);
                } else if (table === 'locations_district') {
                    loadDistricts(selectedProvince.id);
                    setSelectedDistrict(null);
                    setTowns([]);
                } else {
                    loadTowns(selectedDistrict.id);
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
            inputPlaceholder: 'Örn: İstanbul, Antalya...',
            showCancelButton: true,
            confirmButtonText: 'Ekle',
            cancelButtonText: 'İptal'
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
            inputPlaceholder: 'Örn: Kaş, Bodrum...',
            showCancelButton: true,
            confirmButtonText: 'Ekle',
            cancelButtonText: 'İptal'
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

    const handleAddTown = async () => {
        if (!selectedDistrict) return;
        const { value: name } = await Swal.fire({
            title: 'Yeni Mahalle/Belde Ekle',
            input: 'text',
            inputLabel: 'Mahalle/Belde Adı',
            inputPlaceholder: 'Örn: Göcek, Kalkan, Bitez...',
            showCancelButton: true,
            confirmButtonText: 'Ekle',
            cancelButtonText: 'İptal'
        });

        if (name) {
            try {
                await api.post('/admin-update.php', {
                    action: 'save-location',
                    table: 'locations_town',
                    name,
                    district_id: selectedDistrict.id
                });
                loadTowns(selectedDistrict.id);
            } catch (error) {
                Swal.fire('Hata', 'Eklenemedi', 'error');
            }
        }
    };

    const handleImportTowns = async () => {
        if (!selectedDistrict || !selectedProvince) return;

        try {
            const response = await api.post('/admin-update.php', {
                action: 'get-available-towns',
                province: selectedProvince.name,
                district: selectedDistrict.name
            });

            const availableTowns = response.data.data;

            if (!availableTowns || availableTowns.length === 0) {
                Swal.fire('Bilgi', 'Bu bölge için hazır mahalle bulunamadı. Lütfen manuel ekleyin.', 'info');
                return;
            }

            const { value: selectedTowns } = await Swal.fire({
                title: 'Kütüphaneden Seç',
                input: 'select',
                inputOptions: availableTowns.reduce((acc, curr) => ({ ...acc, [curr]: curr }), {}),
                inputPlaceholder: 'Mahalle Seçiniz',
                showCancelButton: true,
                confirmButtonText: 'Ekle'
            });

            if (selectedTowns) {
                await api.post('/admin-update.php', {
                    action: 'save-location',
                    table: 'locations_town',
                    name: selectedTowns,
                    district_id: selectedDistrict.id
                });
                loadTowns(selectedDistrict.id);
                Swal.fire('Eklendi', '', 'success');
            }

        } catch (error) {
            console.error(error);
            Swal.fire('Hata', 'Liste alınamadı', 'error');
        }
    };

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            {/* 1. Column: Provinces */}
            <div className="space-y-4">
                <div className="flex justify-between items-center px-2">
                    <h2 className="text-xl font-bold text-gray-800 tracking-tight">İller</h2>
                    <button onClick={handleAddProvince} className="p-2 bg-white hover:bg-gray-50 border border-gray-200 text-blue-600 rounded-xl transition-all shadow-sm">
                        + Ekle
                    </button>
                </div>
                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-[calc(100vh-200px)] overflow-y-auto custom-scrollbar">
                    {provinces.map((province) => (
                        <div
                            key={province.id}
                            className={`p-4 border-b border-gray-50 cursor-pointer transition-all ${selectedProvince?.id === province.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : 'hover:bg-gray-50'}`}
                            onClick={() => handleSelectProvince(province)}
                        >
                            <div className="flex justify-between items-center">
                                <span className="font-bold text-gray-700">{province.name}</span>
                                <div className="flex gap-2">
                                    <button
                                        onClick={(e) => { e.stopPropagation(); toggleActive(province.id, province.is_active, 'locations_province'); }}
                                        className={`w-2 h-2 rounded-full ${province.is_active ? 'bg-green-500' : 'bg-red-300'}`}
                                        title={province.is_active ? 'Aktif' : 'Pasif'}
                                    ></button>
                                    <button onClick={(e) => { e.stopPropagation(); handleDelete(province.id, 'locations_province'); }} className="text-xs text-red-500 opacity-20 hover:opacity-100">🗑️</button>
                                </div>
                            </div>
                        </div>
                    ))}
                    {provinces.length === 0 && <div className="p-8 text-center text-gray-400 text-sm">İl bulunamadı.</div>}
                </div>
            </div>

            {/* 2. Column: Districts */}
            <div className="space-y-4">
                <div className="flex justify-between items-center px-2">
                    <div>
                        <h2 className="text-xl font-bold text-gray-800 tracking-tight">İlçeler</h2>
                        {selectedProvince && <span className="text-[10px] text-gray-400 font-mono uppercase tracking-widest">{selectedProvince.name}</span>}
                    </div>

                    {selectedProvince && (
                        <button onClick={handleAddDistrict} className="p-2 bg-white hover:bg-gray-50 border border-gray-200 text-blue-600 rounded-xl transition-all shadow-sm">
                            + Ekle
                        </button>
                    )}
                </div>
                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-[calc(100vh-200px)] overflow-y-auto custom-scrollbar">
                    {!selectedProvince ? (
                        <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8">
                            <span className="text-3xl mb-2">👈</span>
                            <span className="text-xs uppercase font-bold tracking-widest">İl Seçiniz</span>
                        </div>
                    ) : districts.length === 0 ? (
                        <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8">
                            <span className="text-3xl mb-2">🏜️</span>
                            <span className="text-xs uppercase font-bold tracking-widest">İlçe Yok</span>
                        </div>
                    ) : (
                        districts.map((district) => (
                            <div
                                key={district.id}
                                className={`p-4 border-b border-gray-50 cursor-pointer transition-all ${selectedDistrict?.id === district.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : 'hover:bg-gray-50'}`}
                                onClick={() => handleSelectDistrict(district)}
                            >
                                <div className="flex justify-between items-center">
                                    <span className="font-bold text-gray-700">{district.name}</span>
                                    <div className="flex gap-2">
                                        <button
                                            onClick={(e) => { e.stopPropagation(); toggleActive(district.id, district.is_active, 'locations_district'); }}
                                            className={`w-2 h-2 rounded-full ${district.is_active ? 'bg-green-500' : 'bg-red-300'}`}
                                            title={district.is_active ? 'Aktif' : 'Pasif'}
                                        ></button>
                                        <button onClick={(e) => { e.stopPropagation(); handleDelete(district.id, 'locations_district'); }} className="text-xs text-red-500 opacity-20 hover:opacity-100">🗑️</button>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>

            {/* 3. Column: Towns */}
            <div className="space-y-4">
                <div className="flex justify-between items-center px-2">
                    <div>
                        <h2 className="text-xl font-bold text-gray-800 tracking-tight">Mahalleler</h2>
                        {selectedDistrict && <span className="text-[10px] text-gray-400 font-mono uppercase tracking-widest">{selectedDistrict.name}</span>}
                    </div>
                    {selectedDistrict && (
                        <div className="flex gap-2">
                            <button onClick={handleImportTowns} className="p-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-600 rounded-xl transition-all shadow-sm" title="Kütüphaneden Aktar">
                                📥
                            </button>
                            <button onClick={handleAddTown} className="p-2 bg-white hover:bg-gray-50 border border-gray-200 text-blue-600 rounded-xl transition-all shadow-sm">
                                + Ekle
                            </button>
                        </div>
                    )}
                </div>
                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-[calc(100vh-200px)] overflow-y-auto custom-scrollbar">
                    {!selectedDistrict ? (
                        <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8">
                            <span className="text-3xl mb-2">👈</span>
                            <span className="text-xs uppercase font-bold tracking-widest">İlçe Seçiniz</span>
                        </div>
                    ) : towns.length === 0 ? (
                        <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8">
                            <span className="text-3xl mb-2">🏘️</span>
                            <span className="text-xs uppercase font-bold tracking-widest">Mahalle Yok</span>
                        </div>
                    ) : (
                        towns.map((town) => (
                            <div
                                key={town.id}
                                className="p-4 border-b border-gray-50 hover:bg-gray-50 transition-all group"
                            >
                                <div className="flex justify-between items-center">
                                    <span className="font-bold text-gray-700">{town.name}</span>
                                    <div className="flex gap-2">
                                        <button
                                            onClick={() => toggleActive(town.id, town.is_active, 'locations_town')}
                                            className={`px-2 py-0.5 rounded text-[10px] uppercase font-bold ${town.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}
                                        >
                                            {town.is_active ? 'AKTİF' : 'PASİF'}
                                        </button>
                                        <button onClick={() => handleDelete(town.id, 'locations_town')} className="text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">🗑️</button>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </motion.div>
    );
}
