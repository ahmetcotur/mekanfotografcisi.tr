import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';
import { motion, AnimatePresence } from 'framer-motion';
import QuoteAssignmentModal from '../components/QuoteAssignmentModal';

export default function Quotes() {
    const [quotes, setQuotes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');
    const [selectedQuote, setSelectedQuote] = useState(null);
    const [assignments, setAssignments] = useState([]);
    const [showAssignmentModal, setShowAssignmentModal] = useState(false);

    useEffect(() => {
        loadQuotes();
    }, []);

    useEffect(() => {
        if (selectedQuote) {
            loadAssignments(selectedQuote.id);
        }
    }, [selectedQuote]);

    const loadQuotes = async () => {
        try {
            const response = await api.get('/admin-update.php?table=quotes&action=list');
            if (response.data.success) {
                // Sort by date descending
                const sorted = (response.data.data || []).sort((a, b) =>
                    new Date(b.created_at) - new Date(a.created_at)
                );
                setQuotes(sorted);
            }
        } catch (error) {
            console.error('Failed to load quotes:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleToggleRead = async (quote) => {
        try {
            await api.post('/admin-update.php', {
                action: 'update',
                table: 'quotes',
                id: quote.id,
                data: { is_read: !quote.is_read }
            });
            loadQuotes();
            if (selectedQuote?.id === quote.id) {
                setSelectedQuote({ ...quote, is_read: !quote.is_read });
            }
        } catch (error) {
            Swal.fire('Hata', 'Durum güncellenemedi', 'error');
        }
    };

    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu talep kalıcı olarak silinecektir!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                await api.post('/admin-update.php', { action: 'delete', table: 'quotes', id });
                loadQuotes();
                setSelectedQuote(null);
                Swal.fire('Silindi!', 'Talep başarıyla silindi.', 'success');
            } catch (error) {
                Swal.fire('Hata', 'Silme işlemi başarısız', 'error');
            }
        }
    };

    const loadAssignments = async (quoteId) => {
        try {
            const response = await api.get(`/quote-assignments.php?quote_id=${quoteId}`);
            console.log('Assignments response:', response.data);
            if (response.data.success) {
                // API returns array directly in success response, not nested in data
                const assignmentsData = Array.isArray(response.data.data) ? response.data.data : [];
                setAssignments(assignmentsData);
            }
        } catch (error) {
            console.error('Failed to load assignments:', error);
            setAssignments([]);
        }
    };

    const handleAssignmentComplete = () => {
        if (selectedQuote) {
            loadAssignments(selectedQuote.id);
        }
        loadQuotes();
    };


    const filteredQuotes = quotes.filter(q => {
        if (filter === 'new') return !q.is_read;
        if (filter === 'read') return q.is_read;
        return true;
    });

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-6"
        >
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Teklif Talepleri</h1>
                    <p className="text-gray-500 text-sm">Gelen iş talepleri ve müşteri mesajları</p>
                </div>
                <div className="flex bg-gray-100 p-1 rounded-xl">
                    {[
                        { id: 'all', label: 'Tümü', count: quotes.length },
                        { id: 'new', label: 'Yeni', count: quotes.filter(q => !q.is_read).length },
                        { id: 'read', label: 'Okundu', count: quotes.filter(q => q.is_read).length }
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
                <div className={`xl:col-span-1 space-y-3 max-h-[700px] overflow-y-auto pr-2 custom-scrollbar ${selectedQuote ? 'hidden xl:block' : 'block'}`}>
                    {filteredQuotes.map((quote) => (
                        <motion.div
                            layout
                            key={quote.id}
                            onClick={() => setSelectedQuote(quote)}
                            className={`p-4 rounded-2xl border cursor-pointer transition-all ${selectedQuote?.id === quote.id
                                ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200'
                                : `bg-white hover:bg-gray-50 border-gray-100 ${!quote.is_read ? 'border-l-4 border-l-blue-500' : ''}`}`}
                        >
                            <div className="flex justify-between items-start mb-1">
                                <span className="text-[10px] font-black uppercase text-blue-500 tracking-widest">
                                    {quote.id ? `MF-${String(quote.id).padStart(5, '0')}` : '---'}
                                </span>
                                <span className={`text-[10px] opacity-60`}>
                                    {new Date(quote.created_at).toLocaleDateString('tr-TR')}
                                </span>
                            </div>
                            <div className="flex justify-between items-center mb-1">
                                <span className="text-xs font-black uppercase opacity-60 tracking-widest">{quote.service || 'Genel'}</span>
                            </div>
                            <div className={`font-bold ${selectedQuote?.id === quote.id ? 'text-white' : 'text-gray-800'}`}>{quote.name}</div>
                            <div className={`text-xs truncate opacity-70`}>{quote.email}</div>
                        </motion.div>
                    ))}
                    {filteredQuotes.length === 0 && (
                        <div className="text-center py-12 text-gray-400 italic bg-white rounded-3xl border border-dashed border-gray-200">
                            Talep bulunamadı.
                        </div>
                    )}
                </div>

                {/* Detail Portion */}
                <div className={`xl:col-span-2 ${!selectedQuote ? 'hidden xl:flex' : 'block'}`}>
                    <AnimatePresence mode="wait">
                        {selectedQuote ? (
                            <motion.div
                                key={selectedQuote.id}
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                exit={{ opacity: 0, x: -20 }}
                                className="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 h-full flex flex-col"
                            >
                                <div className="flex justify-between items-start mb-8">
                                    <button
                                        onClick={() => setSelectedQuote(null)}
                                        className="xl:hidden p-2 hover:bg-gray-100 rounded-lg text-gray-400"
                                    >
                                        ← Geri
                                    </button>
                                    <div className="flex gap-2">
                                        <button
                                            onClick={() => handleToggleRead(selectedQuote)}
                                            className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${selectedQuote.is_read ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-700'}`}
                                        >
                                            {selectedQuote.is_read ? 'Okunmadı Olarak İşaretle' : 'Okundu Olarak İşaretle'}
                                        </button>
                                        <button
                                            onClick={() => handleDelete(selectedQuote.id)}
                                            className="p-2 text-red-400 hover:bg-red-50 rounded-xl transition-colors"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>

                                <div className="space-y-6 flex-1">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div>
                                            <h3 className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Müşteri Bilgileri</h3>
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xs">👤</span>
                                                    <div className="text-lg font-bold text-gray-800">{selectedQuote.name}</div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xs">📧</span>
                                                    <div className="text-sm text-gray-600 font-medium">{selectedQuote.email}</div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xs">📞</span>
                                                    <div className="text-sm text-gray-600 font-medium">{selectedQuote.phone}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Talep Detayları</h3>
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-xs">📸</span>
                                                    <div>
                                                        <div className="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Hizmet</div>
                                                        <div className="text-sm font-bold text-gray-800">{selectedQuote.service || 'Genel'}</div>
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <span className="w-8 h-8 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-xs">📅</span>
                                                    <div>
                                                        <div className="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Tarih</div>
                                                        <div className="text-sm font-bold text-gray-800">{new Date(selectedQuote.created_at).toLocaleString('tr-TR')}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {selectedQuote.wizard_details && (
                                        <div className="mt-8 pt-8 border-t border-gray-100">
                                            <h3 className="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-4">Sihirbaz Detayları</h3>
                                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                {Object.entries(JSON.parse(typeof selectedQuote.wizard_details === 'string' ? selectedQuote.wizard_details : JSON.stringify(selectedQuote.wizard_details))).map(([key, val]) => (
                                                    <div key={key} className="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                                        <div className="text-[9px] text-gray-400 font-bold uppercase tracking-wider mb-1">{key.replace(/_/g, ' ')}</div>
                                                        <div className="text-sm font-bold text-gray-800">{String(val)}</div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    <div className="mt-8 pt-8 border-t border-gray-50">
                                        <h3 className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Mesaj / Ek Bilgiler</h3>
                                        <div className="bg-gray-50 p-6 rounded-2xl text-gray-700 leading-relaxed whitespace-pre-wrap italic border border-dashed border-gray-200">
                                            {selectedQuote.message || 'Ek bilgi belirtilmemiş.'}
                                        </div>
                                    </div>

                                    {/* Freelancer Assignments Section */}
                                    <div className="mt-8 pt-8 border-t border-gray-100">
                                        <div className="flex items-center justify-between mb-4">
                                            <h3 className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Freelancer Atamaları</h3>
                                            <button
                                                onClick={() => setShowAssignmentModal(true)}
                                                className="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2"
                                            >
                                                <span>👷</span>
                                                Freelancer Ata
                                            </button>
                                        </div>

                                        {assignments.length > 0 ? (
                                            <div className="space-y-3">
                                                {assignments.map((assignment) => (
                                                    <div key={assignment.id} className="p-4 bg-slate-50 rounded-xl border border-slate-200">
                                                        <div className="flex items-center justify-between mb-2">
                                                            <div className="font-bold text-gray-900">{assignment.freelancer?.name}</div>
                                                            <span className={`px-3 py-1 rounded-full text-xs font-black uppercase ${assignment.status === 'accepted' ? 'bg-green-100 text-green-700' :
                                                                assignment.status === 'rejected' ? 'bg-red-100 text-red-700' :
                                                                    assignment.status === 'completed' ? 'bg-blue-100 text-blue-700' :
                                                                        'bg-amber-100 text-amber-700'
                                                                }`}>
                                                                {assignment.status === 'pending' ? 'Beklemede' :
                                                                    assignment.status === 'accepted' ? 'Kabul Edildi' :
                                                                        assignment.status === 'rejected' ? 'Reddedildi' :
                                                                            'Tamamlandı'}
                                                            </span>
                                                        </div>
                                                        <div className="text-xs text-gray-500 space-y-1">
                                                            <div>📧 {assignment.freelancer?.email}</div>
                                                            <div>📞 {assignment.freelancer?.phone}</div>
                                                            <div>📍 {assignment.freelancer?.city}</div>
                                                            <div>📅 Atandı: {new Date(assignment.assigned_at).toLocaleString('tr-TR')}</div>
                                                            {assignment.admin_note && (
                                                                <div className="mt-2 p-2 bg-white rounded border border-slate-200">
                                                                    <div className="text-[9px] font-bold text-gray-400 uppercase mb-1">Admin Notu:</div>
                                                                    <div className="text-xs text-gray-700">{assignment.admin_note}</div>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="text-center py-8 text-gray-400 text-sm">
                                                Henüz freelancer ataması yapılmamış
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <p className="text-[10px] text-blue-500 mt-8 text-center uppercase tracking-[0.3em] font-black">
                                    Teklif No: {selectedQuote.id ? `MF-${String(selectedQuote.id).padStart(5, '0')}` : selectedQuote.id}
                                </p>
                            </motion.div>
                        ) : (
                            <div className="w-full h-full flex flex-col items-center justify-center text-gray-300 border-2 border-dashed border-gray-100 rounded-3xl bg-gray-50/30">
                                <span className="text-6xl mb-4">📬</span>
                                <p className="font-bold tracking-tight uppercase text-xs opacity-50">Detayları görmek için bir talep seçin</p>
                            </div>
                        )}
                    </AnimatePresence>
                </div>
            </div>

            {/* Assignment Modal */}
            <QuoteAssignmentModal
                quote={selectedQuote}
                isOpen={showAssignmentModal}
                onClose={() => setShowAssignmentModal(false)}
                onAssigned={handleAssignmentComplete}
            />
        </motion.div>
    );
}
