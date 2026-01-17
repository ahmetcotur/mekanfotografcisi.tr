import { useEffect, useState } from 'react';
import api from '../api/client';
import Swal from 'sweetalert2';

import { Link } from 'react-router-dom';

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
            <div className="flex justify-between items-center">
                <h1 className="text-3xl font-bold text-gray-800 tracking-tight">Dashboard</h1>
                <div className="text-xs font-bold text-gray-400 bg-gray-100 px-3 py-1 rounded-full uppercase tracking-widest">Canlı Özet</div>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <Link to="/services">
                    <StatCard title="Aktif Hizmet" value={stats?.stats?.services || 0} icon="📷" color="blue" />
                </Link>
                <Link to="/blog">
                    <StatCard title="Blog Yazısı" value={stats?.stats?.blog_posts || 0} icon="✍️" color="amber" />
                </Link>
                <Link to="/seo-pages">
                    <StatCard title="SEO Sayfası" value={stats?.stats?.seo_pages || 0} icon="🌐" color="green" />
                </Link>
                <Link to="/quotes">
                    <StatCard title="Toplam Teklif" value={stats?.stats?.total_quotes || 0} icon="✉️" color="purple" />
                </Link>
                <Link to="/quotes?filter=new">
                    <StatCard title="Yeni Talep" value={stats?.stats?.new_quotes || 0} icon="🔔" color="red" />
                </Link>
                <Link to="/freelancers">
                    <StatCard title="Freelancer Başvuru" value={stats?.stats?.total_freelancers || 0} icon="👷" color="green" />
                </Link>
            </div>

            {/* Recent Content Table Section */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Recent Quotes */}
                <div className="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-xl font-bold text-gray-800">Son Teklif Talepleri</h2>
                        <Link to="/quotes" className="text-xs font-bold text-blue-600 hover:underline px-3 py-1 bg-blue-50 rounded-lg uppercase tracking-wider">Tümünü Gör</Link>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left border-b border-gray-50">
                                    <th className="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Müşteri</th>
                                    <th className="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Hizmet</th>
                                    <th className="pb-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tarih</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {stats?.recent_quotes?.map((quote) => (
                                    <tr key={quote.id} className="group hover:bg-gray-50 transition-colors">
                                        <td className="py-4">
                                            <div className="font-bold text-gray-800 text-sm">{quote.name}</div>
                                            <div className="text-[10px] text-gray-400">{quote.email}</div>
                                        </td>
                                        <td className="py-4">
                                            <span className="px-2 py-1 bg-slate-100 rounded text-[9px] font-black uppercase text-slate-500">{quote.service || 'Genel'}</span>
                                        </td>
                                        <td className="py-4 text-[11px] text-gray-500 font-medium">
                                            {new Date(quote.created_at).toLocaleDateString('tr-TR')}
                                        </td>
                                    </tr>
                                ))}
                                {(!stats?.recent_quotes || stats?.recent_quotes.length === 0) && (
                                    <tr>
                                        <td colSpan="3" className="py-12 text-center text-gray-300 italic">Henüz talep yok.</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Recent Pages */}
                <div className="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-xl font-bold text-gray-800">Son Güncellemeler</h2>
                        <Link to="/pages" className="text-xs font-bold text-blue-600 hover:underline px-3 py-1 bg-blue-50 rounded-lg uppercase tracking-wider">Tüm İçerik</Link>
                    </div>
                    <div className="space-y-4">
                        {stats?.recent_pages?.map((page) => (
                            <div key={page.id} className="flex items-center justify-between p-3 hover:bg-gray-50 rounded-2xl transition-colors border border-transparent hover:border-gray-100">
                                <div className="flex items-center gap-4">
                                    <div className="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-lg">
                                        {page.post_type === 'service' ? '📷' : page.post_type === 'blog' ? '✍️' : '📄'}
                                    </div>
                                    <div>
                                        <div className="text-sm font-bold text-gray-800">{page.title}</div>
                                        <div className="text-[10px] text-gray-400 uppercase font-bold tracking-widest">{page.post_type}</div>
                                    </div>
                                </div>
                                <div className="text-[10px] text-gray-400 font-bold whitespace-nowrap">
                                    {new Date(page.updated_at || page.created_at).toLocaleDateString('tr-TR')}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

function StatCard({ title, value, icon, color }) {
    const colors = {
        blue: { bg: 'bg-blue-50', icon: 'bg-blue-500', text: 'text-blue-600' },
        green: { bg: 'bg-green-50', icon: 'bg-green-500', text: 'text-green-600' },
        purple: { bg: 'bg-purple-50', icon: 'bg-purple-500', text: 'text-purple-600' },
        red: { bg: 'bg-red-50', icon: 'bg-red-500', text: 'text-red-600' },
        amber: { bg: 'bg-amber-50', icon: 'bg-amber-500', text: 'text-amber-600' }
    };

    const c = colors[color] || colors.blue;

    return (
        <div className={`p-6 rounded-3xl border border-transparent hover:border-gray-200 transition-all bg-white shadow-sm flex items-center justify-between group cursor-pointer active:scale-95`}>
            <div>
                <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 group-hover:text-gray-600 transition-colors">{title}</p>
                <p className="text-3xl font-black text-gray-900 tracking-tighter">{value}</p>
            </div>
            <div className={`w-14 h-14 ${c.icon} rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-gray-200 group-hover:scale-110 transition-transform`}>
                {icon}
            </div>
        </div>
    );
}
