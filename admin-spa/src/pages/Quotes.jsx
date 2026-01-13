import { useEffect, useState } from 'react';
import api from '../api/client';

export default function Quotes() {
    const [quotes, setQuotes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');

    useEffect(() => {
        loadQuotes();
    }, []);

    const loadQuotes = async () => {
        try {
            const response = await api.get('/admin-update.php?table=quotes&action=list');
            if (response.data.success) {
                setQuotes(response.data.data || []);
            }
        } catch (error) {
            console.error('Failed to load quotes:', error);
        } finally {
            setLoading(false);
        }
    };

    const filteredQuotes = quotes.filter(q => {
        if (filter === 'new') return !q.is_read;
        if (filter === 'read') return q.is_read;
        return true;
    });

    if (loading) return <div className="text-center py-12">Yükleniyor...</div>;

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800">Teklif Talepleri</h1>
                <div className="flex gap-2">
                    <button
                        onClick={() => setFilter('all')}
                        className={`px-4 py-2 rounded-lg text-sm font-medium ${filter === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'
                            }`}
                    >
                        Tümü ({quotes.length})
                    </button>
                    <button
                        onClick={() => setFilter('new')}
                        className={`px-4 py-2 rounded-lg text-sm font-medium ${filter === 'new' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'
                            }`}
                    >
                        Yeni ({quotes.filter(q => !q.is_read).length})
                    </button>
                    <button
                        onClick={() => setFilter('read')}
                        className={`px-4 py-2 rounded-lg text-sm font-medium ${filter === 'read' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'
                            }`}
                    >
                        Okundu ({quotes.filter(q => q.is_read).length})
                    </button>
                </div>
            </div>

            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="w-full">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Müşteri</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Email</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Telefon</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Hizmet</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Durum</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredQuotes.map((quote) => (
                            <tr key={quote.id} className={`border-t hover:bg-gray-50 ${!quote.is_read ? 'bg-blue-50' : ''}`}>
                                <td className="px-6 py-4 text-sm font-medium text-gray-800">{quote.name}</td>
                                <td className="px-6 py-4 text-sm text-gray-600">{quote.email}</td>
                                <td className="px-6 py-4 text-sm text-gray-600">{quote.phone}</td>
                                <td className="px-6 py-4 text-sm text-gray-600">{quote.service}</td>
                                <td className="px-6 py-4">
                                    <span className={`px-2 py-1 rounded text-xs font-medium ${quote.is_read ? 'bg-gray-100 text-gray-700' : 'bg-green-100 text-green-700'
                                        }`}>
                                        {quote.is_read ? 'Okundu' : 'Yeni'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-sm text-gray-500">
                                    {new Date(quote.created_at).toLocaleDateString('tr-TR')}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
