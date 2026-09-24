import { useEffect, useState } from 'react';
import api from '../api/client';
import { motion } from 'framer-motion';

const STATUS_STYLES = {
    pending: 'bg-amber-100 text-amber-700',
    succeeded: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    refunded: 'bg-slate-200 text-slate-700',
};

const STATUS_LABELS = {
    pending: 'Beklemede',
    succeeded: 'Başarılı',
    failed: 'Başarısız',
    refunded: 'İade Edildi',
};

export default function Payments() {
    const [payments, setPayments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');

    useEffect(() => {
        loadPayments();
    }, []);

    const loadPayments = async () => {
        try {
            const response = await api.get('/admin-update.php?table=payments&action=list');
            if (response.data.success) {
                const sorted = (response.data.data || []).sort((a, b) =>
                    new Date(b.created_at) - new Date(a.created_at)
                );
                setPayments(sorted);
            }
        } catch (error) {
            console.error('Failed to load payments:', error);
        } finally {
            setLoading(false);
        }
    };

    const filteredPayments = payments.filter(p => filter === 'all' || p.status === filter);
    const totalSucceeded = payments
        .filter(p => p.status === 'succeeded')
        .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 className="text-3xl font-black text-gray-900 tracking-tight">Ödemeler</h1>
                    <p className="text-gray-500 text-sm">
                        Toplam başarılı tahsilat: <span className="font-bold text-green-600">{totalSucceeded.toLocaleString('tr-TR')} TRY</span>
                    </p>
                </div>
                <div className="flex bg-gray-100 p-1 rounded-xl flex-wrap">
                    {['all', 'pending', 'succeeded', 'failed', 'refunded'].map(s => (
                        <button
                            key={s}
                            onClick={() => setFilter(s)}
                            className={`px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${filter === s ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            {s === 'all' ? 'Tümü' : STATUS_LABELS[s]} ({s === 'all' ? payments.length : payments.filter(p => p.status === s).length})
                        </button>
                    ))}
                </div>
            </div>

            <div className="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 text-gray-400 text-[10px] uppercase tracking-widest">
                        <tr>
                            <th className="text-left px-6 py-4">Tarih</th>
                            <th className="text-left px-6 py-4">Talep</th>
                            <th className="text-left px-6 py-4">Tür</th>
                            <th className="text-left px-6 py-4">Tutar</th>
                            <th className="text-left px-6 py-4">Durum</th>
                            <th className="text-left px-6 py-4">Sağlayıcı</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredPayments.map(p => (
                            <tr key={p.id} className="border-t border-gray-50 hover:bg-gray-50/50">
                                <td className="px-6 py-4 text-gray-500">{new Date(p.created_at).toLocaleString('tr-TR')}</td>
                                <td className="px-6 py-4 font-bold text-gray-800">
                                    {p.quote_id ? `MF-${String(p.quote_id).padStart(5, '0')}` : '-'}
                                </td>
                                <td className="px-6 py-4 text-gray-600">{p.type}</td>
                                <td className="px-6 py-4 font-bold text-gray-900">{parseFloat(p.amount).toLocaleString('tr-TR')} {p.currency}</td>
                                <td className="px-6 py-4">
                                    <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase ${STATUS_STYLES[p.status] || 'bg-gray-100 text-gray-600'}`}>
                                        {STATUS_LABELS[p.status] || p.status}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-gray-400 uppercase text-xs">{p.provider}</td>
                            </tr>
                        ))}
                        {filteredPayments.length === 0 && (
                            <tr>
                                <td colSpan={6} className="text-center py-12 text-gray-400 italic">Ödeme bulunamadı.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </motion.div>
    );
}
