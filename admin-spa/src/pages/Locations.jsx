import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion, AnimatePresence } from 'framer-motion';

export default function Locations() {
    const [provinces, setProvinces] = useState([]);
    const [districts, setDistricts] = useState([]);
    const [towns, setTowns] = useState([]);
    const [distances, setDistances] = useState([]);

    const [selectedProvince, setSelectedProvince] = useState(null);
    const [selectedDistrict, setSelectedDistrict] = useState(null);

    // Multi-selection states
    const [selectedProvinceIds, setSelectedProvinceIds] = useState([]);
    const [selectedDistrictIds, setSelectedDistrictIds] = useState([]);
    const [selectedTownIds, setSelectedTownIds] = useState([]);

    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('locations'); // locations, distances

    // Filters
    const [provinceSearch, setProvinceSearch] = useState('');
    const [provinceFilter, setProvinceFilter] = useState('all'); // all, active, passive
    const [districtSearch, setDistrictSearch] = useState('');
    const [districtFilter, setDistrictFilter] = useState('all');
    const [townSearch, setTownSearch] = useState('');
    const [townFilter, setTownFilter] = useState('all');

    const [seoSettings, setSeoSettings] = useState({
        suffix: '-mekan-fotografcisi',
        titleTemplate: '{name} Mekan Fotoğrafçısı'
    });

    useEffect(() => {
        loadProvinces();
        loadSeoSettings();
    }, []);

    const loadSeoSettings = async () => {
        try {
            const response = await api.get('/admin-update.php?table=settings&action=list');
            if (response.data.success) {
                const settings = response.data.data;
                const suffix = settings.find(s => s.key === 'seo_location_suffix')?.value || '-mekan-fotografcisi';
                const titleTemplate = settings.find(s => s.key === 'seo_location_title_template')?.value || '{name} Mekan Fotoğrafçısı';
                setSeoSettings({ suffix, titleTemplate });
            }
        } catch (error) {
            console.error('Failed to load SEO settings');
        }
    };

    // Selection Helpers
    const toggleSelection = (id, type) => {
        if (type === 'province') {
            setSelectedProvinceIds(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
        } else if (type === 'district') {
            setSelectedDistrictIds(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
        } else if (type === 'town') {
            setSelectedTownIds(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
        }
    };

    const toggleAll = (items, type) => {
        const ids = items.map(i => i.id);
        if (type === 'province') {
            setSelectedProvinceIds(prev => prev.length === ids.length ? [] : ids);
        } else if (type === 'district') {
            setSelectedDistrictIds(prev => prev.length === ids.length ? [] : ids);
        } else if (type === 'town') {
            setSelectedTownIds(prev => prev.length === ids.length ? [] : ids);
        }
    };

    // Filter Logic
    const filterItems = (items, search, filter) => {
        return items.filter(item => {
            const matchesSearch = item.name.toLowerCase().includes(search.toLowerCase());
            const matchesFilter = filter === 'all'
                ? true
                : filter === 'active' ? item.is_active : !item.is_active;
            return matchesSearch && matchesFilter;
        });
    };

    const loadProvinces = async () => {
        setLoading(true);
        try {
            const response = await api.get('/admin-update.php?table=locations_province&action=list');
            if (response.data.success) {
                setProvinces(response.data.data || []);
                setSelectedProvinceIds([]);
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
                setSelectedDistrictIds([]);
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
                setSelectedTownIds([]);
            }
        } catch (error) {
            console.error('Failed to load towns');
        }
    };

    const loadDistances = async (provinceId) => {
        try {
            const response = await api.get(`/admin-update.php?action=get-distances&province_id=${provinceId}`);
            if (response.data.success) {
                setDistances(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load distances');
        }
    };

    const handleSelectProvince = (province) => {
        setSelectedProvince(province);
        setSelectedDistrict(null); // Reset detail selection
        setTowns([]); // Clear detail data
        if (activeTab === 'locations') {
            loadDistricts(province.id);
        } else {
            loadDistances(province.id);
        }
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
            console.error('Toggle Error:', error);
            const msg = error.response?.data?.error || 'Güncelleme başarısız';
            Swal.fire('Hata', msg, 'error');
        }
    };

    const handleBulkAction = async (table, action, ids) => {
        if (ids.length === 0) return;

        const actionText = action === 'delete' ? 'silmek' : (action === 'activate' ? 'aktif etmek' : 'pasif yapmak');
        const confirmResult = await Swal.fire({
            title: 'Emin misiniz?',
            text: `${ids.length} adet kaydı ${actionText} istediğinize emin misiniz?`,
            icon: action === 'delete' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: 'Evet',
            cancelButtonText: 'İptal'
        });

        if (confirmResult.isConfirmed) {
            try {
                if (action === 'delete') {
                    await api.post('/admin-update.php', { action: 'delete', table, ids });
                } else {
                    await api.post('/admin-update.php', {
                        action: 'update',
                        table,
                        ids,
                        data: { is_active: action === 'activate' }
                    });
                }

                Swal.fire('Başarılı', 'İşlem tamamlandı', 'success');

                // Refresh logic
                if (table === 'locations_province') {
                    loadProvinces();
                    if (action === 'delete') {
                        setSelectedProvince(null);
                        setDistricts([]);
                    }
                } else if (table === 'locations_district') {
                    loadDistricts(selectedProvince.id);
                    if (action === 'delete') {
                        setSelectedDistrict(null);
                        setTowns([]);
                    }
                } else if (table === 'locations_town') {
                    loadTowns(selectedDistrict.id);
                }

            } catch (error) {
                Swal.fire('Hata', 'İşlem gerçekleştirilemedi', 'error');
            }
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
                } else if (table === 'locations_town') {
                    loadTowns(selectedDistrict.id);
                } else if (table === 'locations_city_distance') {
                    loadDistances(selectedProvince.id);
                }

                Swal.fire('Silindi', '', 'success');
            } catch (error) {
                console.error('Delete Error:', error);
                const msg = error.response?.data?.error || 'Silme başarısız';
                Swal.fire('Hata', msg, 'error');
            }
        }
    };

    const handleImportAll = async () => {
        const result = await Swal.fire({
            title: 'Verileri Güncelle',
            text: 'Tüm Türkiye il, ilçe ve mahalle verileri GitHub üzerinden çekilip güncellenecektir. Bu işlem uzun sürebilir ve arka planda devam eder.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Şimdi Başlat',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                await api.post('/admin-update.php', { action: 'import-locations' });
                Swal.fire('Başlatıldı', 'İşlem arka planda başlatıldı. Tamamlandığında veriler güncellenmiş olacak.', 'success');
            } catch (error) {
                Swal.fire('Hata', 'İşlem başlatılamadı', 'error');
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

    // Content Editing
    const [editingPage, setEditingPage] = useState(null);

    const handleEditContent = async (item) => {
        // Construct expected slug
        const expectedSlug = `${item.slug}${seoSettings.suffix}`;

        try {
            // Check if page exists
            const response = await api.get(`/admin-update.php?table=posts&action=list&post_type=seo_page&slug=${expectedSlug}`);
            const existingPage = response.data.data?.[0];

            if (existingPage) {
                setEditingPage(existingPage);
            } else {
                // Prepare new page template
                const title = seoSettings.titleTemplate.replace('{name}', item.name);
                setEditingPage({
                    title: title,
                    slug: expectedSlug,
                    content: `<p>${item.name} bölgesinde profesyonel mekan çekimi hizmetleri.</p>`,
                    excerpt: `${item.name} mekan fotoğrafçısı, otel, villa ve emlak çekimi hizmetleri.`,
                    post_type: 'seo_page',
                    post_status: 'draft'
                });
            }
        } catch (error) {
            console.error('Error fetching page content', error);
            Swal.fire('Hata', 'İçerik yüklenemedi', 'error');
        }
    };

    const handleSaveContent = async () => {
        try {
            await api.post('/admin-update.php', {
                action: 'save-post',
                ...editingPage
            });
            Swal.fire('Kaydedildi', 'İçerik güncellendi', 'success');
            setEditingPage(null);
        } catch (error) {
            Swal.fire('Hata', 'Kaydetme başarısız', 'error');
        }
    };

    const FilterBar = ({ search, setSearch, filter, setFilter, items, selectedIds, onToggleAll, type, table }) => (
        <div className="flex flex-col gap-2 mb-4 px-1">
            <div className="flex gap-2">
                <input
                    type="text"
                    placeholder="Ara..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 shadow-inner bg-gray-50/50"
                />
                <select
                    value={filter}
                    onChange={(e) => setFilter(e.target.value)}
                    className="px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 bg-white"
                >
                    <option value="all">Tümü</option>
                    <option value="active">Aktif</option>
                    <option value="passive">Pasif</option>
                </select>
            </div>
            <div className="flex justify-between items-center px-2 py-1 bg-gray-50/80 rounded-lg border border-gray-100">
                <label className="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        className="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        checked={items.length > 0 && selectedIds.length === items.length}
                        onChange={() => onToggleAll(items, type)}
                    />
                    <span className="text-[10px] font-black text-gray-500 uppercase tracking-tighter">Tümünü Seç ({selectedIds.length})</span>
                </label>

                {selectedIds.length > 0 && (
                    <div className="flex gap-1">
                        <button
                            onClick={() => handleBulkAction(table, 'activate', selectedIds)}
                            className="bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded text-[9px] hover:bg-green-200"
                        >AKTİF</button>
                        <button
                            onClick={() => handleBulkAction(table, 'deactivate', selectedIds)}
                            className="bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded text-[9px] hover:bg-red-200"
                        >PASİF</button>
                        <button
                            onClick={() => handleBulkAction(table, 'delete', selectedIds)}
                            className="bg-gray-200 text-gray-700 font-bold px-2 py-0.5 rounded text-[9px] hover:bg-gray-300"
                        >SİL</button>
                    </div>
                )}
            </div>
        </div>
    );

    if (loading && provinces.length === 0) return (
        <div className="flex flex-col items-center justify-center py-24 gap-4">
            <div className="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <p className="text-gray-500 font-medium">Veriler Yükleniyor...</p>
        </div>
    );

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-6"
        >
            {/* Header & Main Actions */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/50 backdrop-blur-md p-6 rounded-[2rem] border border-white shadow-sm">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 tracking-tight">Lokasyon Yönetimi</h1>
                    <p className="text-gray-500 text-sm mt-1">Türkiye il, ilçe ve mahalle verileri</p>
                </div>
                <div className="flex items-center gap-2 bg-gray-100/50 p-1.5 rounded-2xl border border-gray-200 shadow-inner">
                    <button
                        onClick={() => setActiveTab('locations')}
                        className={`px-6 py-2 rounded-xl font-bold transition-all ${activeTab === 'locations' ? 'bg-white text-blue-600 shadow-md translate-y-[-1px]' : 'text-gray-500 hover:text-gray-700'}`}
                    >
                        🏘️ Yerleşimler
                    </button>
                    <button
                        onClick={() => {
                            setActiveTab('distances');
                            if (selectedProvince) loadDistances(selectedProvince.id);
                        }}
                        className={`px-6 py-2 rounded-xl font-bold transition-all ${activeTab === 'distances' ? 'bg-white text-blue-600 shadow-md translate-y-[-1px]' : 'text-gray-500 hover:text-gray-700'}`}
                    >
                        📏 Mesafeler
                    </button>
                    <div className="w-px h-6 bg-gray-200 mx-1"></div>
                    <button
                        onClick={handleImportAll}
                        className="px-6 py-2 rounded-xl font-bold text-gray-600 hover:text-blue-600 hover:bg-white transition-all"
                        title="Tüm verileri GitHub'dan güncelle"
                    >
                        🔄 Güncelle
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 h-[calc(100vh-280px)]">
                {/* 1. Column: Provinces */}
                <div className="flex flex-col h-full space-y-4">
                    <div className="flex justify-between items-center px-4">
                        <h2 className="text-lg font-black text-gray-800 tracking-tight flex items-center gap-2">
                            <span className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs">81</span>
                            İller
                        </h2>
                        <button onClick={handleAddProvince} className="w-8 h-8 flex items-center justify-center bg-white hover:bg-blue-50 border border-gray-200 text-blue-600 rounded-xl transition-all shadow-sm font-bold">
                            +
                        </button>
                    </div>

                    <FilterBar
                        search={provinceSearch}
                        setSearch={setProvinceSearch}
                        filter={provinceFilter}
                        setFilter={setProvinceFilter}
                        items={filterItems(provinces, provinceSearch, provinceFilter)}
                        selectedIds={selectedProvinceIds}
                        onToggleAll={toggleAll}
                        type="province"
                        table="locations_province"
                    />

                    <div className="flex-1 bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden overflow-y-auto custom-scrollbar">
                        {filterItems(provinces, provinceSearch, provinceFilter).map((province) => (
                            <div
                                key={province.id}
                                className={`p-5 border-b border-gray-50 cursor-pointer transition-all ${selectedProvince?.id === province.id ? 'bg-blue-50/50 border-l-4 border-l-blue-500' : 'hover:bg-gray-50/50'}`}
                                onClick={() => handleSelectProvince(province)}
                            >
                                <div className="flex justify-between items-center group">
                                    <div className="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            className="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                            checked={selectedProvinceIds.includes(province.id)}
                                            onChange={(e) => { e.stopPropagation(); toggleSelection(province.id, 'province'); }}
                                        />
                                        <div className={`w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-sm shadow-sm transition-all ${selectedProvince?.id === province.id ? 'bg-blue-500 text-white' : 'bg-gray-50 text-gray-400 group-hover:bg-white group-hover:text-blue-500'}`}>
                                            {province.plate_code || '??'}
                                        </div>
                                        <div>
                                            <span className={`font-bold block transition-colors ${selectedProvince?.id === province.id ? 'text-blue-900' : 'text-gray-700'}`}>{province.name}</span>
                                            <div className="flex gap-2 mt-1">
                                                <button
                                                    onClick={(e) => { e.stopPropagation(); handleEditContent(province); }}
                                                    className="text-[10px] text-gray-300 hover:text-blue-500 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-all scale-95 hover:scale-100"
                                                >
                                                    📝 Düzenle
                                                </button>
                                                <a
                                                    href={`/${province.slug}${seoSettings.suffix}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="text-[10px] text-gray-300 hover:text-blue-500 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-all scale-95 hover:scale-100"
                                                    onClick={(e) => e.stopPropagation()}
                                                >
                                                    👁️ Gör
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex gap-2">
                                        <button
                                            onClick={(e) => { e.stopPropagation(); toggleActive(province.id, province.is_active, 'locations_province'); }}
                                            className={`w-2.5 h-2.5 rounded-full ring-4 ring-transparent ${province.is_active ? 'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.4)]' : 'bg-red-300'}`}
                                            title={province.is_active ? 'Aktif' : 'Pasif'}
                                        ></button>
                                        <button onClick={(e) => { e.stopPropagation(); handleDelete(province.id, 'locations_province'); }} className="text-xs text-red-500 opacity-0 group-hover:opacity-20 hover:!opacity-100 transition-all">🗑️</button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* 2. & 3. Column: Conditional Content */}
                <AnimatePresence mode="wait">
                    {activeTab === 'locations' ? (
                        <>
                            {/* 2. Column: Districts */}
                            <motion.div
                                key="districts"
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                exit={{ opacity: 0, x: -20 }}
                                className="flex flex-col h-full space-y-4"
                            >
                                <div className="flex justify-between items-center px-4">
                                    <div>
                                        <h2 className="text-lg font-black text-gray-800 tracking-tight">İlçeler</h2>
                                        {selectedProvince && <span className="text-[10px] text-blue-500 font-bold uppercase tracking-widest">{selectedProvince.name}</span>}
                                    </div>

                                    {selectedProvince && (
                                        <button onClick={handleAddDistrict} className="w-8 h-8 flex items-center justify-center bg-white hover:bg-blue-50 border border-gray-200 text-blue-600 rounded-xl transition-all shadow-sm font-bold">
                                            +
                                        </button>
                                    )}
                                </div>

                                {selectedProvince && (
                                    <FilterBar
                                        search={districtSearch}
                                        setSearch={setDistrictSearch}
                                        filter={districtFilter}
                                        setFilter={setDistrictFilter}
                                        items={filterItems(districts, districtSearch, districtFilter)}
                                        selectedIds={selectedDistrictIds}
                                        onToggleAll={toggleAll}
                                        type="district"
                                        table="locations_district"
                                    />
                                )}

                                <div className="flex-1 bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden overflow-y-auto custom-scrollbar">
                                    {!selectedProvince ? (
                                        <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8 grayscale opacity-50">
                                            <span className="text-5xl mb-4">📍</span>
                                            <span className="text-xs uppercase font-black tracking-widest bg-gray-100 px-4 py-2 rounded-full">Sol taraftan il seçin</span>
                                        </div>
                                    ) : districts.length === 0 ? (
                                        <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8">
                                            <span className="text-3xl mb-2">🏜️</span>
                                            <span className="text-xs uppercase font-bold tracking-widest">Henüz ilçe yok</span>
                                        </div>
                                    ) : (
                                        filterItems(districts, districtSearch, districtFilter).map((district) => (
                                            <div
                                                key={district.id}
                                                className={`p-5 border-b border-gray-50 cursor-pointer transition-all ${selectedDistrict?.id === district.id ? 'bg-blue-50/50 border-l-4 border-l-blue-500' : 'hover:bg-gray-50/50'}`}
                                                onClick={() => handleSelectDistrict(district)}
                                            >
                                                <div className="flex justify-between items-center group">
                                                    <div className="flex items-center gap-3">
                                                        <input
                                                            type="checkbox"
                                                            className="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                            checked={selectedDistrictIds.includes(district.id)}
                                                            onChange={(e) => { e.stopPropagation(); toggleSelection(district.id, 'district'); }}
                                                        />
                                                        <div>
                                                            <span className={`font-bold block ${selectedDistrict?.id === district.id ? 'text-blue-900' : 'text-gray-700'}`}>{district.name}</span>
                                                            <div className="flex gap-2 mt-1">
                                                                <button
                                                                    onClick={(e) => { e.stopPropagation(); handleEditContent(district); }}
                                                                    className="text-[10px] text-gray-300 hover:text-blue-500 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-all"
                                                                >
                                                                    📝 Düzenle
                                                                </button>
                                                                <a
                                                                    href={`/${district.slug}${seoSettings.suffix}`}
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    className="text-[10px] text-gray-300 hover:text-blue-500 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-all"
                                                                    onClick={(e) => e.stopPropagation()}
                                                                >
                                                                    👁️ Gör
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="flex gap-2">
                                                        <button
                                                            onClick={(e) => { e.stopPropagation(); toggleActive(district.id, district.is_active, 'locations_district'); }}
                                                            className={`w-2.5 h-2.5 rounded-full ${district.is_active ? 'bg-green-500' : 'bg-red-300'}`}
                                                            title={district.is_active ? 'Aktif' : 'Pasif'}
                                                        ></button>
                                                        <button onClick={(e) => { e.stopPropagation(); handleDelete(district.id, 'locations_district'); }} className="text-xs text-red-500 opacity-0 group-hover:opacity-20 hover:!opacity-100 transition-all">🗑️</button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </motion.div>

                            {/* 3. Column: Towns */}
                            <motion.div
                                key="towns"
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                exit={{ opacity: 0, x: -20 }}
                                className="flex flex-col h-full space-y-4"
                            >
                                <div className="flex justify-between items-center px-4">
                                    <div>
                                        <h2 className="text-lg font-black text-gray-800 tracking-tight">Mahalleler</h2>
                                        {selectedDistrict && <span className="text-[10px] text-blue-500 font-bold uppercase tracking-widest">{selectedDistrict.name}</span>}
                                    </div>
                                    {selectedDistrict && (
                                        <div className="flex gap-2">
                                            <button onClick={handleImportTowns} className="w-8 h-8 flex items-center justify-center bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-600 rounded-xl transition-all shadow-sm text-xs" title="Kütüphaneden Aktar">
                                                📥
                                            </button>
                                            <button onClick={handleAddTown} className="w-8 h-8 flex items-center justify-center bg-white hover:bg-gray-50 border border-gray-200 text-blue-600 rounded-xl transition-all shadow-sm font-bold">
                                                +
                                            </button>
                                        </div>
                                    )}
                                </div>

                                {selectedDistrict && (
                                    <FilterBar
                                        search={townSearch}
                                        setSearch={setTownSearch}
                                        filter={townFilter}
                                        setFilter={setTownFilter}
                                        items={filterItems(towns, townSearch, townFilter)}
                                        selectedIds={selectedTownIds}
                                        onToggleAll={toggleAll}
                                        type="town"
                                        table="locations_town"
                                    />
                                )}

                                <div className="flex-1 bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden overflow-y-auto custom-scrollbar">
                                    {!selectedDistrict ? (
                                        <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8 grayscale opacity-50">
                                            <span className="text-5xl mb-4">🏠</span>
                                            <span className="text-xs uppercase font-black tracking-widest bg-gray-100 px-4 py-2 rounded-full">İlçe seçin</span>
                                        </div>
                                    ) : towns.length === 0 ? (
                                        <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8">
                                            <span className="text-3xl mb-2">🏘️</span>
                                            <span className="text-xs uppercase font-bold tracking-widest">Henüz mahalle yok</span>
                                        </div>
                                    ) : (
                                        filterItems(towns, townSearch, townFilter).map((town) => (
                                            <div
                                                key={town.id}
                                                className="p-5 border-b border-gray-50 hover:bg-gray-50/50 transition-all group"
                                            >
                                                <div className="flex justify-between items-center">
                                                    <div className="flex items-center gap-3">
                                                        <input
                                                            type="checkbox"
                                                            className="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                            checked={selectedTownIds.includes(town.id)}
                                                            onChange={(e) => { e.stopPropagation(); toggleSelection(town.id, 'town'); }}
                                                        />
                                                        <div>
                                                            <span className="font-bold text-gray-700 block">{town.name}</span>
                                                            <div className="flex gap-2 mt-1">
                                                                <button
                                                                    onClick={(e) => { e.stopPropagation(); handleEditContent(town); }}
                                                                    className="text-[10px] text-gray-300 hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-all"
                                                                >
                                                                    📝 Düzenle
                                                                </button>
                                                                <a href={`/${town.slug}${seoSettings.suffix}`} target="_blank" rel="noopener noreferrer" className="text-[10px] text-gray-300 hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-all" onClick={(e) => e.stopPropagation()}>
                                                                    👁️ Gör
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="flex gap-2 items-center">
                                                        <button
                                                            onClick={(e) => { e.stopPropagation(); toggleActive(town.id, town.is_active, 'locations_town'); }}
                                                            className={`px-3 py-1 rounded-lg text-[9px] font-black tracking-tighter transition-all ${town.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}
                                                        >
                                                            {town.is_active ? 'AKTİF' : 'PASİF'}
                                                        </button>
                                                        <button onClick={(e) => { e.stopPropagation(); handleDelete(town.id, 'locations_town'); }} className="text-red-500 opacity-0 group-hover:opacity-20 hover:!opacity-100 transition-all text-xs">🗑️</button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </motion.div>
                        </>
                    ) : (
                        /* Distances View */
                        <motion.div
                            key="distances_view"
                            initial={{ opacity: 0, scale: 0.98 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.98 }}
                            className="lg:col-span-2 flex flex-col h-full bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden"
                        >
                            {!selectedProvince ? (
                                <div className="h-full flex flex-col items-center justify-center text-gray-300 p-8 grayscale opacity-50">
                                    <span className="text-6xl mb-6">📏</span>
                                    <span className="text-xs uppercase font-black tracking-widest bg-gray-100 px-6 py-3 rounded-full">Mesafeleri görmek için listeden il seçin</span>
                                </div>
                            ) : (
                                <div className="flex flex-col h-full">
                                    <div className="p-8 border-b border-gray-50 bg-gray-50/50">
                                        <div className="flex justify-between items-center mb-6">
                                            <h3 className="text-2xl font-black text-gray-900 tracking-tight">
                                                {selectedProvince.name} Çıkışlı Mesafeler
                                            </h3>
                                            <div className="bg-blue-100 text-blue-700 font-black px-4 py-2 rounded-2xl text-xs uppercase tracking-widest">
                                                {distances.length} Şehir Kayıtlı
                                            </div>
                                        </div>
                                        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                            {/* Top Stat Boxes */}
                                            <div className="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm">
                                                <span className="text-[10px] font-black text-gray-400 uppercase block mb-1">En Yakın</span>
                                                <span className="text-lg font-bold text-gray-800">
                                                    {distances.length > 0 ? distances.sort((a, b) => a.distance_km - b.distance_km).filter(d => d.distance_km > 0)[0]?.to_province_name : '-'}
                                                </span>
                                            </div>
                                            <div className="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm">
                                                <span className="text-[10px] font-black text-gray-400 uppercase block mb-1">En Uzak</span>
                                                <span className="text-lg font-bold text-gray-800">
                                                    {distances.length > 0 ? distances.sort((a, b) => b.distance_km - a.distance_km)[0]?.to_province_name : '-'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex-1 p-8 overflow-y-auto custom-scrollbar">
                                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                            {distances.map(dist => (
                                                <div key={dist.id} className="group bg-gray-50 hover:bg-white hover:shadow-lg hover:shadow-blue-500/5 p-5 rounded-3xl border border-transparent hover:border-blue-100 transition-all">
                                                    <div className="flex justify-between items-center mb-2">
                                                        <span className="font-bold text-gray-700">{dist.to_province_name}</span>
                                                        <span className="text-blue-600 font-black text-sm">{dist.distance_km} km</span>
                                                    </div>
                                                    <div className="w-full bg-gray-200 h-1 rounded-full overflow-hidden">
                                                        <div
                                                            className="bg-blue-500 h-full rounded-full transition-all duration-1000"
                                                            style={{ width: `${Math.min(100, (dist.distance_km / 2000) * 100)}%` }}
                                                        ></div>
                                                    </div>
                                                </div>
                                            ))}
                                            {distances.length === 0 && (
                                                <div className="col-span-full py-12 text-center text-gray-400 font-medium">
                                                    Bu il için mesafe verisi bulunamadı.
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>

            {/* Edit Content Modal */}
            {editingPage && (
                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95 }}
                        animate={{ opacity: 1, scale: 1 }}
                        className="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-white"
                    >
                        <div className="p-8 border-b border-gray-100 flex justify-between items-center bg-gray-50/80">
                            <div>
                                <h2 className="text-2xl font-black text-gray-900 tracking-tight">İçerik Düzenle</h2>
                                <p className="text-gray-500 text-xs mt-0.5">SEO uyumlu lokasyon sayfası ayarları</p>
                            </div>
                            <button onClick={() => setEditingPage(null)} className="w-10 h-10 flex items-center justify-center bg-white rounded-full text-gray-400 hover:text-gray-600 shadow-sm border border-gray-100 transition-all">
                                ✕
                            </button>
                        </div>

                        <div className="p-8 overflow-y-auto custom-scrollbar space-y-8">
                            {/* SEO Tips Alert */}
                            <div className="bg-blue-500 text-white rounded-3xl p-6 flex gap-4 shadow-lg shadow-blue-500/20">
                                <span className="text-2xl">💡</span>
                                <div>
                                    <h4 className="font-black text-sm uppercase tracking-widest">SEO Tavsiyesi</h4>
                                    <p className="text-sm opacity-90 mt-1 font-medium leading-relaxed">
                                        Her lokasyon için özgün başlık ve içerik girmek SEO performansını artırır.
                                        Şehir/semt ismini başlıkta ve metnin ilk paragrafında geçirmeye özen gösterin.
                                    </p>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-8">
                                <div className="space-y-2">
                                    <label className="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Sayfa Başlığı (H1)</label>
                                    <input
                                        type="text"
                                        value={editingPage.title}
                                        onChange={(e) => setEditingPage({ ...editingPage, title: e.target.value })}
                                        className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none font-bold text-gray-800 transition-all"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <label className="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">URL (Slug)</label>
                                    <input
                                        type="text"
                                        value={editingPage.slug}
                                        readOnly
                                        disabled
                                        className="w-full px-5 py-4 bg-gray-100/50 border border-gray-100 rounded-2xl text-gray-400 font-mono text-xs cursor-not-allowed select-none"
                                        title="Lokasyon URL yapısı standarttır, değiştirilemez."
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">İçerik (HTML)</label>
                                <textarea
                                    value={editingPage.content || ''}
                                    onChange={(e) => setEditingPage({ ...editingPage, content: e.target.value })}
                                    className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none font-mono text-sm h-64 transition-all"
                                    placeholder="<p>Sayfa içeriği...</p>"
                                />
                                <p className="text-[10px] text-gray-400 font-bold px-1">Tip: HTML etiketleri (&lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;) kullanabilirsiniz.</p>
                            </div>

                            <div className="space-y-2">
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest px-1">Meta Açıklaması (Description)</label>
                                <textarea
                                    value={editingPage.excerpt || ''}
                                    onChange={(e) => setEditingPage({ ...editingPage, excerpt: e.target.value })}
                                    className="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none text-sm h-28 transition-all"
                                    placeholder="Google arama sonuçlarında görünecek kısa açıklama..."
                                />
                            </div>
                        </div>

                        <div className="p-8 border-t border-gray-100 bg-gray-50/80 flex justify-end gap-3">
                            <button
                                onClick={() => setEditingPage(null)}
                                className="px-8 py-3 bg-white border border-gray-200 text-gray-700 font-black rounded-2xl hover:bg-gray-100 transition-all uppercase tracking-widest text-xs"
                            >
                                İptal
                            </button>
                            <button
                                onClick={handleSaveContent}
                                className="px-8 py-3 bg-blue-600 text-white font-black rounded-2xl hover:bg-blue-700 shadow-xl shadow-blue-500/20 transition-all uppercase tracking-widest text-xs"
                            >
                                Kaydet
                            </button>
                        </div>
                    </motion.div>
                </div>
            )}
        </motion.div>
    );
}
