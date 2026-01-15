import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function QuoteAssignmentModal({ quote, isOpen, onClose, onAssigned }) {
    const [matches, setMatches] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedFreelancer, setSelectedFreelancer] = useState(null);
    const [adminNote, setAdminNote] = useState('');
    const [assigning, setAssigning] = useState(false);

    useEffect(() => {
        if (isOpen && quote) {
            loadMatches();
        }
    }, [isOpen, quote]);

    const loadMatches = async () => {
        setLoading(true);
        try {
            const response = await api.get(`/freelancer-matches.php?quote_id=${quote.id}`);
            if (response.data.success) {
                setMatches(response.data.matches || []);
            }
        } catch (error) {
            console.error('Failed to load matches:', error);
            Swal.fire('Hata', 'Eşleşmeler yüklenemedi', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleAssign = async () => {
        if (!selectedFreelancer) {
            Swal.fire('Uyarı', 'Lütfen bir freelancer seçin', 'warning');
            return;
        }

        setAssigning(true);
        try {
            const response = await api.post('/quote-assignments.php', {
                quote_id: quote.id,
                freelancer_id: selectedFreelancer.id,
                admin_note: adminNote,
                status: 'pending'
            });

            if (response.data.success) {
                Swal.fire('Başarılı', 'Teklif freelancer\'a atandı', 'success');
                onAssigned?.();
                onClose();
            }
        } catch (error) {
            console.error('Assignment failed:', error);
            Swal.fire('Hata', error.response?.data?.error || 'Atama başarısız', 'error');
        } finally {
            setAssigning(false);
        }
    };

    const getScoreColor = (score) => {
        if (score >= 150) return 'text-green-600 bg-green-50';
        if (score >= 100) return 'text-blue-600 bg-blue-50';
        if (score >= 50) return 'text-amber-600 bg-amber-50';
        return 'text-gray-600 bg-gray-50';
    };

    const getScoreLabel = (score) => {
        if (score >= 150) return 'Mükemmel Eşleşme';
        if (score >= 100) return 'İyi Eşleşme';
        if (score >= 50) return 'Orta Eşleşme';
        return 'Düşük Eşleşme';
    };

    if (!isOpen) return null;

    return (
        <AnimatePresence>
            <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <motion.div
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    exit={{ opacity: 0, scale: 0.95 }}
                    className="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
                >
                    {/* Header */}
                    <div className="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-white">
                        <div>
                            <h2 className="text-2xl font-black text-gray-900">Freelancer Ata</h2>
                            <p className="text-sm text-gray-500 mt-1">
                                <span className="font-bold">{quote.name}</span> · {quote.location || 'Lokasyon belirtilmemiş'}
                            </p>
                        </div>
                        <button
                            onClick={onClose}
                            className="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
                        >
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {/* Content */}
                    <div className="flex-1 overflow-y-auto p-8">
                        {loading ? (
                            <div className="text-center py-12">
                                <div className="inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                <p className="mt-4 text-gray-500">Uygun freelancer'lar aranıyor...</p>
                            </div>
                        ) : matches.length === 0 ? (
                            <div className="text-center py-12">
                                <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <p className="text-gray-500 font-medium">Uygun freelancer bulunamadı</p>
                                <p className="text-sm text-gray-400 mt-1">Bu bölgede çalışan onaylı freelancer yok</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {matches.map((match) => {
                                    const f = match.freelancer;
                                    const isSelected = selectedFreelancer?.id === f.id;

                                    return (
                                        <motion.div
                                            key={f.id}
                                            whileHover={{ scale: 1.01 }}
                                            onClick={() => setSelectedFreelancer(f)}
                                            className={`p-6 rounded-2xl border-2 cursor-pointer transition-all ${isSelected
                                                    ? 'border-blue-500 bg-blue-50 shadow-lg'
                                                    : 'border-gray-100 hover:border-gray-200 bg-white'
                                                }`}
                                        >
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-3 mb-2">
                                                        <h3 className="text-lg font-bold text-gray-900">{f.name}</h3>
                                                        <span className={`px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider ${getScoreColor(match.score)}`}>
                                                            {getScoreLabel(match.score)}
                                                        </span>
                                                    </div>

                                                    <div className="grid grid-cols-2 gap-4 text-sm mb-3">
                                                        <div>
                                                            <span className="text-gray-400 text-xs font-bold uppercase tracking-wider block mb-1">Şehir</span>
                                                            <span className="text-gray-700 font-medium">{f.city}</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-400 text-xs font-bold uppercase tracking-wider block mb-1">Deneyim</span>
                                                            <span className="text-gray-700 font-medium">{f.experience} yıl</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-400 text-xs font-bold uppercase tracking-wider block mb-1">İletişim</span>
                                                            <span className="text-gray-700 font-medium text-xs">{f.email}</span>
                                                        </div>
                                                        <div>
                                                            <span className="text-gray-400 text-xs font-bold uppercase tracking-wider block mb-1">Telefon</span>
                                                            <span className="text-gray-700 font-medium">{f.phone}</span>
                                                        </div>
                                                    </div>

                                                    {f.specialization && (
                                                        <div className="flex flex-wrap gap-2 mb-3">
                                                            {JSON.parse(f.specialization).map((spec) => (
                                                                <span key={spec} className="px-2 py-1 bg-gray-100 rounded text-xs font-bold text-gray-600 uppercase">
                                                                    {spec}
                                                                </span>
                                                            ))}
                                                        </div>
                                                    )}

                                                    {match.match_details && (
                                                        <div className="text-xs text-gray-500 space-y-1">
                                                            <div>📍 {match.match_details.location}</div>
                                                            {match.match_details.specialization && (
                                                                <div>🎯 {match.match_details.specialization}</div>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>

                                                <div className="ml-4">
                                                    <div className={`w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black ${getScoreColor(match.score)}`}>
                                                        {match.score}
                                                    </div>
                                                </div>
                                            </div>
                                        </motion.div>
                                    );
                                })}
                            </div>
                        )}

                        {selectedFreelancer && (
                            <div className="mt-6 p-6 bg-gray-50 rounded-2xl border border-gray-200">
                                <label className="block text-sm font-bold text-gray-700 mb-2">Admin Notu (Opsiyonel)</label>
                                <textarea
                                    value={adminNote}
                                    onChange={(e) => setAdminNote(e.target.value)}
                                    className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all resize-none"
                                    rows="3"
                                    placeholder="Bu atama hakkında not ekleyin..."
                                />
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    <div className="px-8 py-6 border-t border-gray-100 flex items-center justify-between bg-gray-50">
                        <button
                            onClick={onClose}
                            className="px-6 py-3 rounded-xl font-bold text-gray-600 hover:bg-gray-200 transition-colors"
                        >
                            İptal
                        </button>
                        <button
                            onClick={handleAssign}
                            disabled={!selectedFreelancer || assigning}
                            className="px-8 py-3 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-500 disabled:bg-gray-300 disabled:cursor-not-allowed transition-all shadow-lg shadow-blue-500/20"
                        >
                            {assigning ? 'Atanıyor...' : 'Freelancer\'a Ata'}
                        </button>
                    </div>
                </motion.div>
            </div>
        </AnimatePresence>
    );
}
