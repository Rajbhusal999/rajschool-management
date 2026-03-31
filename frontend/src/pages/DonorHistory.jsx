import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { supabase } from '../supabaseClient';
import { 
  ArrowLeft, 
  Plus, 
  Printer, 
  Search,
  Filter,
  Calendar,
  User,
  Hash,
  ArrowRight,
  History,
  HeartHandshake
} from 'lucide-react';

const DonorHistory = () => {
  const navigate = useNavigate();
  const [receipts, setReceipts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [language, setLanguage] = useState('en');
  const institutionId = sessionStorage.getItem('institutionId');

  useEffect(() => {
    fetchHistory();
  }, []);

  const fetchHistory = async () => {
    try {
      const { data, error } = await supabase
        .from('donor_receipts')
        .select('*')
        .eq('institution_id', institutionId)
        .order('created_at', { ascending: false });

      if (error) throw error;
      setReceipts(data || []);
    } catch (err) {
      console.error('Error fetching history:', err);
    } finally {
      setLoading(false);
    }
  };

  const filteredReceipts = receipts.filter(r => 
    r.donor_name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    r.receipt_no?.toString().includes(searchTerm)
  );

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getTopics = (items) => {
    if (!items || !Array.isArray(items)) return '-';
    return items
      .filter(item => item.amount && Number(item.amount) > 0)
      .map(item => item.en || item.ne || 'Donation')
      .join(', ') || 'No specific details';
  };

  const translations = {
    back: language === 'ne' ? 'फिर्ता' : 'Back',
    newReceipt: language === 'ne' ? 'नयाँ रसिद' : 'New Receipt',
    donorHistory: language === 'ne' ? 'सहयोग इतिहास' : 'Donation History',
    manageTrack: language === 'ne' ? 'सबै जारी गरिएका सहयोग रसिदहरू व्यवस्थापन र ट्र्याक गर्नुहोस्' : 'Manage and track all issued donation receipts',
  };

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col font-['Outfit',sans-serif]">
      <div className="max-w-[1400px] mx-auto mt-6 mb-4 px-4">
        <div className="bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/50 p-6 flex flex-wrap items-center justify-between gap-6">
          <div className="space-y-1">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center">
                <HeartHandshake size={24} />
              </div>
              <h1 className="text-3xl font-[1000] text-slate-900 tracking-tighter">{translations.donorHistory}</h1>
            </div>
            <p className="text-slate-500 font-bold text-[10px] uppercase tracking-[0.2em] opacity-70 ml-13">
              {translations.manageTrack}
            </p>
          </div>

          <div className="flex items-center gap-4">
            <button 
              onClick={() => navigate('/billing/donor-receipts')} 
              className="flex items-center gap-2 px-6 py-3 bg-rose-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-rose-600 transition-all shadow-lg shadow-rose-100"
            >
              <Plus size={18} /> {translations.newReceipt}
            </button>
            <button 
              onClick={() => navigate('/billing')} 
              className="flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-500 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-slate-200 transition-all"
            >
              <ArrowLeft size={18} /> {translations.back}
            </button>
          </div>
        </div>
      </div>

      <div className="flex-1 p-8">
        <div className="bg-white rounded-[32px] shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden flex flex-col min-h-[600px]">
          
          <div className="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center gap-4">
            <div className="flex-1 min-w-[300px] relative">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
              <input 
                type="text"
                placeholder="Search by donor name or receipt number..."
                className="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-rose-100 focus:border-rose-400 transition-all outline-none"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
          </div>

          <div className="flex-1 overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-slate-50/80 border-b border-slate-100">
                  <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Receipt No</th>
                  <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                  <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Donor Name</th>
                  <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Details</th>
                  <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                  <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50">
                {loading ? (
                  Array(5).fill(0).map((_, i) => (
                    <tr key={i} className="animate-pulse">
                      <td colSpan={6} className="px-8 py-4"><div className="h-8 bg-slate-100 rounded-xl w-full"></div></td>
                    </tr>
                  ))
                ) : filteredReceipts.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="px-8 py-20 text-center">
                      <div className="flex flex-col items-center gap-4 text-slate-400">
                        <History size={48} className="opacity-20" />
                        <p className="font-bold uppercase tracking-widest text-xs">No records found</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  filteredReceipts.map((receipt) => (
                    <tr key={receipt.id} className="group hover:bg-slate-50/50 transition-colors">
                      <td className="px-8 py-5">
                        <span className="text-rose-600 font-black text-sm">#{receipt.receipt_no}</span>
                      </td>
                      <td className="px-8 py-5">
                        <div className="flex items-center gap-2 text-slate-700 font-bold text-sm">
                          <Calendar size={14} className="text-slate-300" />
                          {receipt.date}
                        </div>
                      </td>
                      <td className="px-8 py-5">
                        <div className="flex items-center gap-2 text-slate-800 font-black text-sm uppercase">
                          <User size={14} className="text-slate-300" />
                          {receipt.donor_name}
                        </div>
                      </td>
                      <td className="px-8 py-5">
                        <p className="text-slate-500 font-medium text-xs truncate max-w-[300px]" title={getTopics(receipt.items)}>
                          {getTopics(receipt.items)}
                        </p>
                      </td>
                      <td className="px-8 py-5 text-right">
                        <span className="text-slate-900 font-black text-sm flex items-center justify-end gap-1">
                          <span className="text-[10px] text-slate-400">Rs.</span>
                          {receipt.total_amount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                        </span>
                      </td>
                      <td className="px-8 py-5 text-center">
                        <button 
                          onClick={() => navigate(`/billing/donor-receipts/${receipt.id}`)}
                          className="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all group-hover:scale-105 active:scale-95"
                        >
                          <Printer size={14} /> Print
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          <div className="p-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest">
              Total Donation Records: <span className="text-rose-600">{filteredReceipts.length}</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DonorHistory;
