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

    // Filters
    const [provinceSearch, setProvinceSearch] = useState('');
    const [provinceFilter, setProvinceFilter] = useState('all'); // all, active, passive
    const [districtSearch, setDistrictSearch] = useState('');
    const [districtFilter, setDistrictFilter] = useState('all');
    const [townSearch, setTownSearch] = useState('');
    const [townFilter, setTownFilter] = useState('all');

    useEffect(() => {
        loadProvinces();
    }, []);

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

            // Refresh logic - optimistic update could be better but this is safer
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
                console.error('Delete Error:', error);
                const msg = error.response?.data?.error || 'Silme başarısız';
                Swal.fire('Hata', msg, 'error');
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
        const expectedSlug = `${item.slug}-mekan-fotografcisi`;

        try {
            // Check if page exists
            const response = await api.get(`/admin-update.php?table=posts&action=list&post_type=seo_page&slug=${expectedSlug}`);
            const existingPage = response.data.data?.[0];

            if (existingPage) {
                setEditingPage(existingPage);
            } else {
                // Prepare new page template
                setEditingPage({
                    title: `${item.name} Mekan Fotoğrafçısı`,
                    slug: expectedSlug,
                    content: `<p>${item.name} bölgesinde profesyonel mekan çekimi hizmetleri.</p>`,
                    excerpt: `${item.name} mekan fotoğrafçısı, otel, villa ve emlak çekimi hizmetleri.`,
                    post_type: 'seo_page',
                    post_status: 'publish'
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

    const FilterBar = ({ search, setSearch, filter, setFilter }) => (
        <div className="flex gap-2 mb-4 px-1">
            <input
                type="text"
                placeholder="Ara..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
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
    );

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

                <FilterBar
                    search={provinceSearch}
                    setSearch={setProvinceSearch}
                    filter={provinceFilter}
                    setFilter={setProvinceFilter}
                />

                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-[calc(100vh-240px)] overflow-y-auto custom-scrollbar">
                    {filterItems(provinces, provinceSearch, provinceFilter).map((province) => (
                        <div
                            key={province.id}
                            className={`p-4 border-b border-gray-50 cursor-pointer transition-all ${selectedProvince?.id === province.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : 'hover:bg-gray-50'}`}
                            onClick={() => handleSelectProvince(province)}
                        >
                            <div className="flex justify-between items-center group">
                                <div className="flex items-center gap-2">
                                    <span className="font-bold text-gray-700">{province.name}</span>
                                    <button
                                        onClick={(e) => { e.stopPropagation(); handleEditContent(province); }}
                                        className="text-gray-300 hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100"
                                        title="İçeriği Düzenle"
                                    >
                                        ✏️
                                    </button>
                                    <a
                                        href={`/${province.slug}-mekan-fotografcisi`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-gray-300 hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100"
                                        onClick={(e) => e.stopPropagation()}
                                        title="Görüntüle"
                                    >
                                        🔗
                                    </a>
                                </div>
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

                {selectedProvince && (
                    <FilterBar
                        search={districtSearch}
                        setSearch={setDistrictSearch}
                        filter={districtFilter}
                        setFilter={setDistrictFilter}
                    />
                )}

                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-[calc(100vh-240px)] overflow-y-auto custom-scrollbar">
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
                        filterItems(districts, districtSearch, districtFilter).map((district) => (
                            <div
                                key={district.id}
                                className={`p-4 border-b border-gray-50 cursor-pointer transition-all ${selectedDistrict?.id === district.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : 'hover:bg-gray-50'}`}
                                onClick={() => handleSelectDistrict(district)}
                            >
                                <div className="flex justify-between items-center group">
                                    <div className="flex items-center gap-2">
                                        <span className="font-bold text-gray-700">{district.name}</span>
                                        <button
                                            onClick={(e) => { e.stopPropagation(); handleEditContent(district); }}
                                            className="text-gray-300 hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100"
                                            title="İçeriği Düzenle"
                                        >
                                            ✏️
                                        </button>
                                        <a
                                            href={`/${district.slug}-mekan-fotografcisi`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-gray-300 hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100"
                                            onClick={(e) => e.stopPropagation()}
                                            title="Görüntüle"
                                        >
                                            🔗
                                        </a>
                                    </div>
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

                {selectedDistrict && (
                    <FilterBar
                        search={townSearch}
                        setSearch={setTownSearch}
                        filter={townFilter}
                        setFilter={setTownFilter}
                    />
                )}

                <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-[calc(100vh-240px)] overflow-y-auto custom-scrollbar">
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
                        filterItems(towns, townSearch, townFilter).map((town) => (
                            <div
                                key={town.id}
                                className="p-4 border-b border-gray-50 hover:bg-gray-50 transition-all group"
                            >
                                <div className="flex justify-between items-center">
                                    <div className="flex items-center gap-2">
                                        <span className="font-bold text-gray-700">{town.name}</span>
                                        <button
                                            onClick={() => handleEditContent(town)}
                                            className="text-gray-300 hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100"
                                            title="İçeriği Düzenle"
                                        >
                                            ✏️
                                        </button>
                                        <a href={`/${town.slug}-mekan-fotografcisi`} target="_blank" rel="noopener noreferrer" className="text-gray-300 hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100" title="Görüntüle">
                                            🔗
                                        </a>
                                    </div>
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

            {/* Edit Content Modal */}
            {editingPage && (
                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95 }}
                        animate={{ opacity: 1, scale: 1 }}
                        className="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden"
                    >
                        <div className="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h2 className="text-xl font-bold text-gray-800">İçerik Düzenle</h2>
                            <button onClick={() => setEditingPage(null)} className="text-gray-400 hover:text-gray-600">
                                ✕
                            </button>
                        </div>

                        <div className="p-6 overflow-y-auto custom-scrollbar space-y-6">
                            {/* SEO Tips Alert */}
                            <div className="bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-3">
                                <span className="text-xl">💡</span>
                                <div>
                                    <h4 className="font-bold text-blue-900 text-sm">SEO İpucu</h4>
                                    <p className="text-xs text-blue-700 mt-1">
                                        Her lokasyon için özgün başlık ve içerik girmek SEO performansını artırır.
                                        Şehir/semt ismini başlıkta ve metnin ilk paragrafında geçirmeye özen gösterin.
                                    </p>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Sayfa Başlığı (H1)</label>
                                    <input
                                        type="text"
                                        value={editingPage.title}
                                        onChange={(e) => setEditingPage({ ...editingPage, title: e.target.value })}
                                        className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none font-bold text-gray-800"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">URL (Slug)</label>
                                    <input
                                        type="text"
                                        value={editingPage.slug}
                                        readOnly
                                        disabled
                                        className="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-400 font-mono text-xs cursor-not-allowed select-none"
                                        title="Lokasyon URL yapısı standarttır, değiştirilemez."
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">İçerik (HTML)</label>
                                <textarea
                                    value={editingPage.content || ''}
                                    onChange={(e) => setEditingPage({ ...editingPage, content: e.target.value })}
                                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none font-mono text-sm h-64"
                                    placeholder="<p>Sayfa içeriği...</p>"
                                />
                                <p className="text-[10px] text-gray-400 mt-1">İpucu: HTML etiketleri kullanabilirsiniz (&lt;h2&gt;, &lt;p&gt;, &lt;ul&gt; vb.)</p>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Meta Açıklaması (Description)</label>
                                <textarea
                                    value={editingPage.excerpt || ''}
                                    onChange={(e) => setEditingPage({ ...editingPage, excerpt: e.target.value })}
                                    className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm h-24"
                                    placeholder="Google arama sonuçlarında görünecek kısa açıklama..."
                                />
                            </div>
                        </div>

                        <div className="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                            <button
                                onClick={() => setEditingPage(null)}
                                className="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors"
                            >
                                İptal
                            </button>
                            <button
                                onClick={handleSaveContent}
                                className="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all"
                            >
                                Değişiklikleri Kaydet
                            </button>
                        </div>
                    </motion.div>
                </div>
            )}
        </motion.div>
    );
}
