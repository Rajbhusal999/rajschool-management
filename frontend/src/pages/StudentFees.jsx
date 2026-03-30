import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { supabase } from '../supabaseClient';
import { 
  ArrowLeft, 
  Printer, 
  Save, 
  Languages, 
  History,
  CheckCircle2,
  AlertCircle
} from 'lucide-react';

const ReceiptBody = ({ 
  type, 
  schoolName, 
  schoolAddress, 
  estdYear, 
  translations, 
  receiptNo, 
  formData, 
  fees, 
  language, 
  setFormData, 
  handleFeeChange, 
  handleCustomLabelChange, 
  handleKeyDown, 
  calculateTotal,
  numberToWords,
  schoolLogo
}) => (
  <div className="relative border-2 border-slate-900 p-6 pt-4 pb-8 bg-white shadow-sm max-w-[48%] flex-1 print:shadow-none print:bg-white print:p-0 print:pb-0 print:max-h-[190mm] overflow-hidden print:w-[138mm] print:min-w-[138mm]">
    {/* Watermark */}
    <div className="absolute inset-0 flex items-center justify-center opacity-[0.04] pointer-events-none select-none z-0">
      <span className="text-4xl font-black rotate-[-45deg] whitespace-nowrap uppercase tracking-[0.3em] text-slate-900 border-8 border-slate-900 px-8 py-4">
        {type === 'school' ? 'SCHOOL COPY' : 'STUDENT COPY'}
      </span>
    </div>

    <div className="relative z-10 space-y-4 print:space-y-0.5 print:p-4">
      {/* Header */}
      <div className="relative border-b-2 border-slate-900 pb-3 print:pb-1 min-h-[80px] print:min-h-[60px] flex items-center justify-center">
        <div className="absolute left-0 top-1/2 -translate-y-1/2 w-20 h-20 bg-white border-2 border-slate-900 rounded-lg flex items-center justify-center print:w-14 print:h-14 overflow-hidden shadow-sm">
          {schoolLogo ? (
            <img src={schoolLogo} alt="Logo" className="w-full h-full object-contain" />
          ) : (
            <div className="flex flex-col items-center">
              <span className="text-[10px] text-slate-300 font-bold print:hidden italic">NO LOGO</span>
            </div>
          )}
        </div>
        <div className="text-center px-20">
          <h2 className="text-3xl font-black text-slate-900 tracking-tighter uppercase print:text-2xl leading-none">{schoolName}</h2>
          <p className="text-[11px] font-black text-slate-700 uppercase tracking-widest print:text-[8px] mt-1 mb-0.5">{schoolAddress}</p>
          <p className="text-[10px] font-bold text-slate-500 print:text-[7px] leading-none mb-1">ESTD: {estdYear}</p>
          <div className="inline-block px-6 py-1 bg-slate-900 text-white rounded-full mt-1.5 font-black text-xs uppercase tracking-[0.2em] print:text-[10px] print:mt-0.5 shadow-sm">
            {translations.receiptTitle}
          </div>
        </div>
      </div>

      {/* Top Info */}
      <div className="flex justify-between items-center text-xs font-black text-slate-900 border-b border-slate-300 pb-2 print:pb-1 print:text-[10px]">
        <div className="flex items-center gap-1">
          <span className="opacity-70">{translations.receiptNo}:</span>
          <span className="text-lg print:text-sm font-mono tracking-tighter px-1">{receiptNo}</span>
        </div>
        <div className="flex items-center gap-2">
          <span className="opacity-70">{translations.date}:</span>
          <div className="min-w-[120px] print:min-w-[100px]">
            <input 
              id="date"
              type="text" 
              autoComplete="off"
              className="w-full bg-transparent outline-none border-none print:hidden h-5 text-right focus:bg-slate-50 transition-colors px-1"
              value={formData.date}
              onChange={(e) => setFormData({...formData, date: e.target.value})}
              onKeyDown={(e) => handleKeyDown(e, 'fee-1')}
            />
            <span className="hidden print:inline font-mono">{formData.date}</span>
          </div>
        </div>
      </div>

      {/* Student Details */}
      <div className="space-y-2.5 print:space-y-0 text-xs print:text-[10px] font-bold text-slate-900">
        <div className="flex items-center gap-2 border-b border-dotted border-slate-500">
          <span className="whitespace-nowrap opacity-70 italic">{translations.studentName}:</span>
          <div className="flex-1">
            <input 
              id="studentName"
              type="text" 
              autoComplete="off"
              className="w-full bg-transparent outline-none border-none print:hidden h-5 focus:bg-slate-50 transition-colors px-2 font-black"
              value={formData.studentName}
              onChange={(e) => setFormData({...formData, studentName: e.target.value})}
              onKeyDown={(e) => handleKeyDown(e, 'rollNo')}
            />
            <span className="hidden print:inline font-black uppercase leading-tight">{formData.studentName}</span>
          </div>
        </div>
        
        <div className="grid grid-cols-3 gap-4 border-b border-dotted border-slate-500 pb-0.5 print:pb-0">
          <div className="flex items-center gap-2">
            <span className="whitespace-nowrap opacity-70 italic">{translations.rollNo}:</span>
            <div className="flex-1">
              <input 
                id="rollNo"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-5 text-center focus:bg-slate-50 transition-colors font-black"
                value={formData.rollNo}
                onChange={(e) => setFormData({...formData, rollNo: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'section')}
              />
              <span className="hidden print:inline font-black leading-tight">{formData.rollNo}</span>
            </div>
          </div>
          <div className="flex items-center gap-2 border-l border-dotted border-slate-400 px-3">
            <span className="whitespace-nowrap opacity-70 italic">{translations.section}:</span>
            <div className="flex-1">
              <input 
                id="section"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-5 text-center focus:bg-slate-50 transition-colors font-black"
                value={formData.section}
                onChange={(e) => setFormData({...formData, section: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'className')}
              />
              <span className="hidden print:inline font-black uppercase leading-tight">{formData.section}</span>
            </div>
          </div>
          <div className="flex items-center gap-2 border-l border-dotted border-slate-400 pl-3">
            <span className="whitespace-nowrap opacity-70 italic">{translations.class}:</span>
            <div className="flex-1">
              <input 
                id="className"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-5 text-center focus:bg-slate-50 transition-colors font-black"
                value={formData.className}
                onChange={(e) => setFormData({...formData, className: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'month')}
              />
              <span className="hidden print:inline font-black uppercase text-lg print:text-xs leading-tight">{formData.className}</span>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4 border-b border-dotted border-slate-500 pb-0.5 print:pb-0">
          <div className="flex items-center gap-2">
            <span className="whitespace-nowrap opacity-70 italic">{translations.month}:</span>
            <div className="flex-1">
              <input 
                id="month"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-5 focus:bg-slate-50 transition-colors px-2 font-black"
                value={formData.month}
                onChange={(e) => setFormData({...formData, month: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'guardianName')}
              />
              <span className="hidden print:inline font-black uppercase leading-tight">{formData.month}</span>
            </div>
          </div>
          <div className="flex items-center gap-2 border-l border-dotted border-slate-400 pl-3">
            <span className="whitespace-nowrap opacity-70 italic">{translations.guardianName}:</span>
            <div className="flex-1">
              <input 
                id="guardianName"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-5 focus:bg-slate-50 transition-colors px-2 font-black"
                value={formData.guardianName}
                onChange={(e) => setFormData({...formData, guardianName: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'date')}
              />
              <span className="hidden print:inline font-black uppercase leading-tight">{formData.guardianName}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Table */}
      <table className="w-full border-collapse border-b-2 border-t-2 border-slate-900 text-xs print:text-[10px] font-bold mt-2">
        <thead>
          <tr className="bg-slate-100 border-b-2 border-slate-900 print:bg-transparent">
            <th className="border-r border-slate-900 w-12 py-2 print:py-0.5">{translations.serialNo}</th>
            <th className="border-r border-slate-900 px-4 py-2 print:py-0.5 text-left">{translations.particulars}</th>
            <th className="w-28 py-2 print:py-0.5">{translations.amount}</th>
          </tr>
        </thead>
        <tbody>
          {fees.map((fee, idx) => (
            <tr key={fee.id} className="border-b border-slate-200 last:border-none h-8 print:h-[4.8mm] leading-none">
              <td className="border-r border-slate-900 text-center py-0 font-mono opacity-60">{fee.id}</td>
              <td className="border-r border-slate-900 px-4 py-0 print:px-3">
                <div className="print:hidden">
                  {idx < 15 ? (
                    language === 'ne' ? fee.nameNe : fee.nameEn
                  ) : (
                    <input 
                      type="text" 
                      placeholder="..."
                      className="w-full bg-transparent outline-none border-none h-4"
                      value={language === 'ne' ? fee.nameNe : fee.nameEn}
                      onChange={(e) => handleCustomLabelChange(idx, e.target.value)}
                    />
                  )}
                </div>
                <span className="hidden print:inline">{language === 'ne' ? fee.nameNe : fee.nameEn}</span>
              </td>
              <td className="py-0 px-0 relative">
                <input 
                  id={`fee-${idx + 1}`}
                  type="number" 
                  autoComplete="off"
                  className="w-full h-full bg-transparent outline-none border-none text-right px-3 font-mono print:hidden [-moz-appearance:_textfield] [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:m-0 focus:bg-slate-50 transition-colors"
                  value={fee.amount}
                  onChange={(e) => handleFeeChange(idx, e.target.value)}
                  onKeyDown={(e) => handleKeyDown(e, idx < fees.length - 1 ? `fee-${idx + 2}` : 'submit-btn')}
                />
                <span className="hidden print:block text-right px-3 font-mono font-black">{fee.amount ? parseFloat(fee.amount).toFixed(2) : ''}</span>
              </td>
            </tr>
          ))}
          {/* Total Row */}
          <tr className="bg-slate-50 font-black text-sm print:text-[11px] print:bg-transparent border-t-2 border-slate-900 h-9 print:h-6">
            <td colSpan={2} className="border-r border-slate-900 px-6 text-right uppercase tracking-[0.1em]">{translations.total}</td>
            <td className="px-3 text-right font-mono bg-slate-900 text-white print:bg-white print:text-black">{calculateTotal().toFixed(2)}</td>
          </tr>
        </tbody>
      </table>

      {/* Footer Area */}
      <div className="pt-2 space-y-6 print:space-y-2">
        <div className="flex items-center gap-3 text-[11px] font-black print:text-[10px]">
          <span className="opacity-70 italic">{translations.inWords}:</span>
          <div className="border-b border-dotted border-slate-600 flex-1 min-h-[20px] px-2 text-slate-800 italic uppercase">
            {numberToWords(calculateTotal())}
          </div>
        </div>
        
        <div className="flex justify-end pt-6 print:pt-2">
          <div className="text-center min-w-[140px]">
            <div className="border-t-2 border-slate-900 pt-1.5 text-[11px] font-black uppercase tracking-[0.2em]">
              {translations.receiverSign}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
);

const StudentFees = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [language, setLanguage] = useState('ne');
  const [receiptNo, setReceiptNo] = useState(100);
  const [loading, setLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });
  const [isReprintMode, setIsReprintMode] = useState(false);
  
  const [formData, setFormData] = useState({
    studentName: '',
    rollNo: '',
    section: '',
    className: '',
    month: '',
    guardianName: '',
    date: '2082-12-17' // Default Nepali Date
  });

  const [fees, setFees] = useState([
    { id: 1, nameEn: 'Operational Support', nameNe: 'सञ्चालन सहयोग', amount: '' },
    { id: 2, nameEn: 'Exam Operation Support', nameNe: 'परीक्षा सञ्चालन सहयोग', amount: '' },
    { id: 3, nameEn: 'Sports', nameNe: 'खेलकुद', amount: '' },
    { id: 4, nameEn: 'Book', nameNe: 'पुस्तक', amount: '' },
    { id: 5, nameEn: 'Poor Student Aid', nameNe: 'गरीब विद्यार्थी सहायता', amount: '' },
    { id: 6, nameEn: 'Advertisement Support', nameNe: 'विज्ञापन सहयोग', amount: '' },
    { id: 7, nameEn: 'Tie Belt', nameNe: 'टाई बेल्ट', amount: '' },
    { id: 8, nameEn: 'Extra Subject Operation', nameNe: 'अतिरिक्त विषय सञ्चालन', amount: '' },
    { id: 9, nameEn: 'Transfer Certificate', nameNe: 'स्थानान्तरण प्रमाण-पत्र', amount: '' },
    { id: 10, nameEn: 'Educational Materials', nameNe: 'शैक्षिक सामग्री', amount: '' },
    { id: 11, nameEn: 'ID Card', nameNe: 'परिचय पत्र', amount: '' },
    { id: 12, nameEn: 'Educational Tour', nameNe: 'शैक्षिक भ्रमण', amount: '' },
    { id: 13, nameEn: 'Red Cross', nameNe: 'रेडक्रस', amount: '' },
    { id: 14, nameEn: 'Recommendation/Certificate', nameNe: 'सिफारिश', amount: '' },
    { id: 15, nameEn: 'Miscellaneous', nameNe: 'विविध', amount: '' },
    { id: 16, nameEn: '', nameNe: '', amount: '' },
    { id: 17, nameEn: '', nameNe: '', amount: '' },
  ]);

  const schoolName = sessionStorage.getItem('schoolName') || 'RAJ SCHOOL';
  const schoolAddress = sessionStorage.getItem('schoolAddress') || 'Bharatpur-11, Chitwan';
  const estdYear = sessionStorage.getItem('estdYear') || '2050';
  const schoolLogo = sessionStorage.getItem('schoolLogo');
  const institutionId = sessionStorage.getItem('institutionId');
  
  const [lastIssued, setLastIssued] = useState({ no: null, date: null });

  useEffect(() => {
    if (id) {
      loadReceiptForReprint();
    } else {
      fetchLatestReceiptNo();
    }
  }, [id]);

  const loadReceiptForReprint = async () => {
    try {
      const { data, error } = await supabase
        .from('fee_receipts')
        .select('*')
        .eq('id', id)
        .single();

      if (error) throw error;
      if (data) {
        setIsReprintMode(true);
        setReceiptNo(data.receipt_no);
        setLanguage(data.language || 'ne');
        setFormData({
          studentName: data.student_name || '',
          rollNo: data.roll_no || '',
          section: data.section || '',
          className: data.class || '',
          month: data.month || '',
          guardian_name: data.guardian_name || '',
          date: data.date || ''
        });

        if (data.items && Array.isArray(data.items)) {
          const updatedFees = fees.map(f => {
            const item = data.items.find(it => it.id === f.id);
            return item ? { ...f, amount: item.amount } : { ...f, amount: '' };
          });
          setFees(updatedFees);
        }
      }
    } catch (err) {
      console.error('Error loading receipt:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchLatestReceiptNo = async () => {
    try {
      const { data, error } = await supabase
        .from('fee_receipts')
        .select('receipt_no, date')
        .eq('institution_id', institutionId)
        .order('receipt_no', { ascending: false })
        .limit(1);

      if (error) throw error;
      if (data && data.length > 0) {
        setReceiptNo(data[0].receipt_no + 1);
        setLastIssued({ no: data[0].receipt_no, date: data[0].date });
      } else {
        setReceiptNo(100);
        setLastIssued({ no: 'N/A', date: '--' });
      }
    } catch (err) {
      console.error('Error fetching receipt no:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleFeeChange = (index, value) => {
    const updatedFees = [...fees];
    updatedFees[index].amount = value;
    setFees(updatedFees);
  };

  const handleCustomLabelChange = (index, value) => {
    const updatedFees = [...fees];
    if (language === 'ne') {
      updatedFees[index].nameNe = value;
    } else {
      updatedFees[index].nameEn = value;
    }
    setFees(updatedFees);
  };

  const calculateTotal = () => {
    return fees.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
  };

  const handleSaveAndPrint = async () => {
    if (!formData.studentName) {
      setMessage({ type: 'error', text: 'Student name is required' });
      return;
    }

    if (isReprintMode) {
      window.print();
      return;
    }

    setIsSubmitting(true);
    try {
      const { error } = await supabase
        .from('fee_receipts')
        .insert([{
          receipt_no: receiptNo,
          institution_id: institutionId,
          student_name: formData.studentName,
          roll_no: formData.rollNo,
          section: formData.section,
          class: formData.className,
          month: formData.month,
          guardian_name: formData.guardianName,
          date: formData.date,
          total_amount: calculateTotal(),
          language: language,
          items: fees.filter(f => f.amount !== '')
        }]);

      if (error) throw error;
      setMessage({ type: 'success', text: `Receipt #${receiptNo} saved!` });
      window.print();
      setReceiptNo(prev => prev + 1);
      setFormData({ ...formData, studentName: '', rollNo: '', month: '', guardianName: '' });
      setFees(fees.map(f => ({ ...f, amount: '' })));
    } catch (err) {
      console.error('Save error:', err);
      setMessage({ type: 'error', text: 'Failed to save' });
    } finally {
      setIsSubmitting(false);
      setTimeout(() => setMessage({ type: '', text: '' }), 3000);
    }
  };

  const handleKeyDown = (e, nextFieldId) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const nextField = document.getElementById(nextFieldId);
      if (nextField) {
        nextField.focus();
        if (nextField.select) nextField.select();
      }
    }
  };

  const numberToWords = (num) => {
    if (!num || isNaN(num)) return '';
    const single = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
    const double = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    const convert = (n) => {
      if (n < 10) return single[n];
      if (n < 20) return double[n - 10];
      if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 !== 0 ? ' ' + single[n % 10] : '');
      if (n < 1000) return single[Math.floor(n / 100)] + ' Hundred' + (n % 100 !== 0 ? ' and ' + convert(n % 100) : '');
      if (n < 100000) return convert(Math.floor(n / 1000)) + ' Thousand' + (n % 1000 !== 0 ? ' ' + convert(n % 1000) : '');
      if (n < 10000000) return convert(Math.floor(n / 100000)) + ' Lakh' + (n % 100000 !== 0 ? ' ' + convert(n % 100000) : '');
      return convert(Math.floor(n / 10000000)) + ' Crore' + (n % 10000000 !== 0 ? ' ' + convert(n % 10000000) : '');
    };
    const words = convert(Math.floor(num));
    return words ? words + ' Only' : '';
  };

  const translations = {
    receiptTitle: language === 'ne' ? 'नगदी रसिद' : 'CASH RECEIPT',
    receiptNo: language === 'ne' ? 'र. नं.' : 'Receipt No.',
    date: language === 'ne' ? 'मिति' : 'Date',
    studentName: language === 'ne' ? 'विद्यार्थीको नाम' : 'Student Name',
    rollNo: language === 'ne' ? 'रोल नं.' : 'Roll No.',
    section: language === 'ne' ? 'सेक्शन' : 'Section',
    class: language === 'ne' ? 'कक्षा' : 'Class',
    month: language === 'ne' ? 'महिना' : 'Month',
    guardianName: language === 'ne' ? 'अभिभावक' : 'Guardian',
    serialNo: language === 'ne' ? 'क्र.सं.' : 'S.N.',
    particulars: language === 'ne' ? 'विवरण' : 'Particulars',
    amount: language === 'ne' ? 'रकम' : 'Amount',
    total: language === 'ne' ? 'जम्मा' : 'Total',
    inWords: language === 'ne' ? 'अक्षरेपी' : 'In Words',
    receiverSign: language === 'ne' ? 'बुझिलिनेको सही' : 'Receiver\'s Sign',
    saveAndPrint: language === 'ne' ? (isReprintMode ? 'रसिद पुन: प्रिन्ट' : 'बचत र प्रिन्ट') : (isReprintMode ? 'Reprint Receipt' : 'Save & Print'),
    viewHistory: language === 'ne' ? 'इतिहास हेर्नुहोस्' : 'View History',
    back: language === 'ne' ? 'फिर्ता' : 'Back',
  };

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col font-['Outfit',sans-serif]">
<<<<<<< HEAD
      <div className="max-w-[1400px] mx-auto mt-6 mb-4 px-4 print:hidden no-print">
=======
      <div className="max-w-[1400px] mx-auto mt-6 mb-4 px-4 print:hidden nav-header">
>>>>>>> 88b3f7253acf843a522be7f8fd35535b5c9e0da9
        <div className="bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/50 p-4 flex flex-wrap items-center justify-between gap-6">
          <div className="flex items-center gap-6">
            <button 
              onClick={() => navigate('/billing')} 
              className="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 rounded-2xl transition-all font-black text-slate-500 uppercase text-xs"
            >
              <ArrowLeft size={18} /> {translations.back}
            </button>
            
            <div className="h-8 w-px bg-slate-100"></div>

            <h1 className="text-3xl font-[1000] text-slate-900 tracking-tighter">New Receipt</h1>
            
            <button 
              onClick={() => navigate('/billing/history')} 
              className="flex items-center gap-2 px-6 py-2.5 bg-sky-500 text-white rounded-2xl text-xs font-black uppercase tracking-[0.1em] hover:bg-sky-600 transition-all shadow-lg shadow-sky-500/20"
            >
              <History size={16} /> {translations.viewHistory}
            </button>
          </div>

          <div className="flex items-center gap-4">
            <div className="flex items-center gap-4 px-6 py-3 bg-sky-50 border border-sky-100 rounded-2xl shadow-sm">
              <div className="flex items-center gap-3 text-sky-800">
                <History size={16} className="text-sky-500" strokeWidth={2.5} />
                <div className="flex items-center gap-2">
                  <span className="text-[10px] font-black uppercase tracking-widest opacity-60">Last Issued:</span>
                  <span className="text-sm font-[1000] tracking-tighter text-sky-900 font-mono">#{lastIssued.no}</span>
                  <span className="text-[10px] font-bold opacity-60">({lastIssued.date})</span>
                </div>
              </div>
              
              <div className="h-4 w-[2px] bg-sky-200"></div>
              
              <div className="flex items-center gap-2 text-sky-900">
                <span className="text-[10px] font-black uppercase tracking-widest opacity-60">Next:</span>
                <span className="text-sm font-[1000] tracking-tighter text-sky-600 font-mono">#{receiptNo}</span>
              </div>
            </div>

            <button 
              onClick={() => setLanguage(language === 'en' ? 'ne' : 'en')} 
              className="px-5 py-3 bg-white border border-slate-200 rounded-2xl flex items-center gap-2 text-[10px] font-[1000] text-slate-500 hover:bg-slate-50 transition-all uppercase tracking-widest shadow-sm"
            >
              <Languages size={16} className="text-indigo-500" /> {language === 'en' ? 'Nepali' : 'English'}
            </button>
          </div>
        </div>
      </div>

      <div className="flex-1 p-8 overflow-auto print:p-0 receipt-page-container">
        <div className="flex flex-col md:flex-row gap-8 justify-center max-w-[1400px] mx-auto print:gap-1 print:max-w-none print:flex-row print:justify-center">
            <ReceiptBody 
              type="school" schoolName={schoolName} schoolAddress={schoolAddress} estdYear={estdYear} 
              translations={translations} receiptNo={receiptNo} formData={formData} fees={fees} 
              language={language} setFormData={setFormData} handleFeeChange={handleFeeChange} 
              handleCustomLabelChange={handleCustomLabelChange} handleKeyDown={handleKeyDown} 
              calculateTotal={calculateTotal} numberToWords={numberToWords}
              schoolLogo={schoolLogo}
            />
            <ReceiptBody 
              type="student" schoolName={schoolName} schoolAddress={schoolAddress} estdYear={estdYear} 
              translations={translations} receiptNo={receiptNo} formData={formData} fees={fees} 
              language={language} setFormData={setFormData} handleFeeChange={handleFeeChange} 
              handleCustomLabelChange={handleCustomLabelChange} handleKeyDown={handleKeyDown} 
              calculateTotal={calculateTotal} numberToWords={numberToWords}
              schoolLogo={schoolLogo}
            />
        </div>

        <div className="max-w-[1400px] mx-auto mt-12 mb-20 flex justify-center print:hidden">
          <button 
            id="submit-btn" disabled={isSubmitting || loading} onClick={handleSaveAndPrint}
            className="group relative flex items-center gap-4 px-12 py-6 bg-indigo-600 text-white rounded-full font-black uppercase tracking-widest text-sm shadow-xl hover:bg-indigo-700 transition-all hover:scale-105 active:scale-95"
          >
            <Printer size={24} /> {isSubmitting ? 'Processing...' : translations.saveAndPrint}
          </button>
        </div>
      </div>

      <style dangerouslySetInnerHTML={{ __html: `
        @media print {
          @page { size: landscape; margin: 0; }
          body, html { 
            background: white !important; 
            margin: 0 !important; 
            padding: 0 !important;
            width: 297mm !important; 
            height: 210mm !important; 
          }
          #root { height: auto !important; }
          .print\\:hidden, .print-hide, .nav-header { display: none !important; }
          .flex-1 { flex: none !important; }
          .flex { 
            display: flex !important; 
            flex-direction: row !important; 
            flex-wrap: nowrap !important;
            gap: 12mm !important; 
            justify-content: center !important; 
            align-items: flex-start !important;
            padding-top: 5mm !important;
            width: 100% !important;
            margin: 0 !important;
          }
          .receipt-page-container { margin: 0 !important; padding: 0 !important; }
>>>>>>> 88b3f7253acf843a522be7f8fd35535b5c9e0da9
          .relative.border-2 { 
            zoom: 0.9; 
            width: 138mm !important; 
            min-width: 138mm !important;
            max-height: 190mm !important;
            border-color: #000 !important;
            border-width: 1.5pt !important;
<<<<<<< HEAD
            box-shadow: none !important;
            page-break-inside: avoid !important;
          }
          table, th, td { border-color: #000 !important; border-width: 1pt !important; }
=======
            background: white !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
            overflow: hidden !important;
          }
          table, th, td { border-color: #000 !important; border-width: 1pt !important; }
          
          /* Extra safety for nested headers */
          header, [role="navigation"] { display: none !important; }
>>>>>>> 88b3f7253acf843a522be7f8fd35535b5c9e0da9
        }
      ` }} />
    </div>
  );
};

export default StudentFees;
