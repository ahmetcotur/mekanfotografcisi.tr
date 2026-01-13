import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';

export default function Dashboard() {
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadDashboard();
    }, []);

    const loadDashboard = async () => {
        try {
            const response = await api.get('/dashboard.php');
            if (response.data.success) {
                setStats(response.data);
            }
        } catch (error) {
            console.error('Failed to load dashboard:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return <div className="text-center py-12">Yükleniyor...</div>;
    }

    return (
        <div className="space-y-6">
            <h1 className="text-3xl font-bold text-gray-800">Dashboard</h1>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard title="Aktif Hizmet" value={stats?.stats?.services || 0} icon="📷" color="blue" />
                <StatCard title="SEO Sayfası" value={stats?.stats?.seo_pages || 0} icon="🌐" color="green" />
                <StatCard title="Toplam Teklif" value={stats?.stats?.total_quotes || 0} icon="✉️" color="purple" />
                <StatCard title="Yeni Talep" value={stats?.stats?.new_quotes || 0} icon="🔔" color="red" />
            </div>

            {/* Recent Quotes */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-xl font-bold mb-4">Son Teklif Talepleri</h2>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-2 text-left text-sm font-semibold text-gray-600">Müşteri</th>
                                <th className="px-4 py-2 text-left text-sm font-semibold text-gray-600">Hizmet</th>
                                <th className="px-4 py-2 text-left text-sm font-semibold text-gray-600">Durum</th>
                                <th className="px-4 py-2 text-left text-sm font-semibold text-gray-600">Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            {stats?.recent_quotes?.map((quote) => (
                                <tr key={quote.id} className="border-t">
                                    <td className="px-4 py-3 text-sm">{quote.name}</td>
                                    <td className="px-4 py-3 text-sm">{quote.service}</td>
                                    <td className="px-4 py-3 text-sm">{quote.status}</td>
                                    <td className="px-4 py-3 text-sm text-gray-500">
                                        {new Date(quote.created_at).toLocaleDateString('tr-TR')}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

function StatCard({ title, value, icon, color }) {
    const colors = {
        blue: 'bg-blue-500',
        green: 'bg-green-500',
        purple: 'bg-purple-500',
        red: 'bg-red-500'
    };

    return (
        <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm text-gray-600 mb-1">{title}</p>
                    <p className="text-3xl font-bold text-gray-800">{value}</p>
                </div>
                <div className={`w-12 h-12 ${colors[color]} rounded-lg flex items-center justify-center text-2xl`}>
                    {icon}
                </div>
            </div>
        </div>
    );
}
