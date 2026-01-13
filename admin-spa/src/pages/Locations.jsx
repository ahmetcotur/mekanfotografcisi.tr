import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion, AnimatePresence } from 'framer-motion';

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
            text: 'Bu işlem geri alınamaz!',
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

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="grid grid-cols-1 lg:grid-cols-2 gap-8"
        >
            {/* Provinces Column */}
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-800 tracking-tight">İller</h1>
                        <p className="text-gray-500 text-sm">Hizmet verdiğiniz ana bölgeler</p>
                    </div>
                    <button
                        onClick={handleAddProvince}
                        className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2"
                    >
                        <span>+</span> Yeni İl
                    </button>
                </div>

                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    {provinces.length === 0 ? (
                        <div className="p-12 text-center text-gray-400 italic">Henüz il eklenmemiş.</div>
                    ) : (
                        provinces.map((province) => (
                            <motion.div
                                layout
                                key={province.id}
                                className={`p-4 flex items-center justify-between border-b last:border-b-0 hover:bg-gray-50/50 transition-all cursor-pointer ${selectedProvince?.id === province.id ? 'bg-blue-50/50' : ''}`}
                                onClick={() => handleSelectProvince(province)}
                            >
                                <div className="flex items-center gap-4">
                                    <div className={`w-10 h-10 rounded-2xl flex items-center justify-center text-lg shadow-sm ${selectedProvince?.id === province.id ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-400'}`}>
                                        📍
                                    </div>
                                    <div>
                                        <h3 className="font-bold text-gray-800">{province.name}</h3>
                                        <p className="text-[10px] text-gray-400 font-mono">/{province.slug}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2" onClick={e => e.stopPropagation()}>
                                    <button
                                        onClick={() => toggleActive(province.id, province.is_active, 'locations_province')}
                                        className={`px-3 py-1 rounded-full text-[10px] font-black tracking-widest transition-all ${province.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-amber-100 text-amber-700 hover:bg-amber-200'}`}
                                    >
                                        {province.is_active ? 'AKTİF' : 'PASİF'}
                                    </button>
                                    <button
                                        onClick={() => handleDelete(province.id, 'locations_province')}
                                        className="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </motion.div>
                        ))
                    )}
                </div>
            </div>

            {/* Districts Column */}
            <div className="space-y-6">
                <div className="flex justify-between items-center min-h-[60px]">
                    <div>
                        <h2 className="text-3xl font-bold text-gray-800 tracking-tight">
                            {selectedProvince ? `${selectedProvince.name} İlçeleri` : 'İl Seçiniz'}
                        </h2>
                        {selectedProvince && <p className="text-gray-500 text-sm">Bu il altındaki alt bölgeler</p>}
                    </div>
                    {selectedProvince && (
                        <button
                            onClick={handleAddDistrict}
                            className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2"
                        >
                            <span>+</span> Yeni İlçe
                        </button>
                    )}
                </div>

                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden min-h-[400px]">
                    <AnimatePresence mode="wait">
                        {!selectedProvince ? (
                            <motion.div
                                key="none"
                                initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                                className="p-20 text-center flex flex-col items-center justify-center text-gray-300"
                            >
                                <span className="text-5xl mb-4">👈</span>
                                <p className="font-bold uppercase text-xs tracking-[0.2em] opacity-50">İlçeleri görmek için soldan bir il seçin</p>
                            </motion.div>
                        ) : districts.length === 0 ? (
                            <motion.div
                                key="empty"
                                initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                                className="p-20 text-center flex flex-col items-center justify-center text-gray-300"
                            >
                                <span className="text-5xl mb-4">🏜️</span>
                                <p className="font-bold uppercase text-xs tracking-[0.2em] opacity-50">Bu il için henüz ilçe eklenmemiş</p>
                            </motion.div>
                        ) : (
                            <motion.div
                                key="list"
                                initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                                className="divide-y divide-gray-50"
                            >
                                {districts.map((district) => (
                                    <div key={district.id} className="p-4 flex items-center justify-between hover:bg-gray-50/50 transition-all">
                                        <div className="flex items-center gap-4">
                                            <div className="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">🏙️</div>
                                            <div>
                                                <h3 className="font-bold text-gray-800">{district.name}</h3>
                                                <p className="text-[10px] text-gray-400 font-mono">/{selectedProvince.slug}/{district.slug}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <button
                                                onClick={() => toggleActive(district.id, district.is_active, 'locations_district')}
                                                className={`px-3 py-1 rounded-full text-[10px] font-black tracking-widest transition-all ${district.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-amber-100 text-amber-700 hover:bg-amber-200'}`}
                                            >
                                                {district.is_active ? 'AKTİF' : 'PASİF'}
                                            </button>
                                            <button
                                                onClick={() => handleDelete(district.id, 'locations_district')}
                                                className="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                                            >
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </motion.div>
                        )}
                    </AnimatePresence>
                </div>
            </div>
        </motion.div>
    );
}
