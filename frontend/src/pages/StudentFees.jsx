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
  numberToWords
}) => (
  <div className="relative border-2 border-slate-300 p-6 pt-4 pb-8 bg-[#FDFCF8] shadow-sm max-w-[48%] flex-1 print:border-slate-800 print:shadow-none print:bg-white print:p-1 print:pb-1 print:max-h-[195mm] overflow-hidden print:text-[9px]">
    {/* Watermark */}
    <div className="absolute inset-0 flex items-center justify-center opacity-[0.06] pointer-events-none select-none z-0">
      <span className="text-4xl font-black rotate-[-45deg] whitespace-nowrap uppercase tracking-widest text-slate-900 border-4 border-slate-900 px-6 py-2">
        {type === 'school' ? 'SCHOOL COPY' : 'STUDENT COPY'}
      </span>
    </div>

    <div className="relative z-10 space-y-3 print:space-y-1">
      {/* Header */}
      <div className="text-center space-y-1">
        <h2 className="text-2xl font-black text-slate-800 tracking-tight uppercase print:text-sm">{schoolName}</h2>
        <p className="text-[10px] font-bold text-slate-500 uppercase tracking-widest print:text-[7.5px]">{schoolAddress} | ESTD: {estdYear}</p>
        <div className="inline-block px-6 py-1 bg-slate-100 border border-slate-300 rounded-full mt-2 font-black text-xs uppercase tracking-widest print:py-0 print:px-2 print:text-[9px] print:mt-0">
          {translations.receiptTitle}
        </div>
      </div>

      {/* Top Info */}
      <div className="grid grid-cols-2 text-[11px] font-bold text-slate-700">
        <div className="flex items-center gap-2">
          <span>{translations.receiptNo}:</span>
          <span className="border-b-2 border-dotted border-slate-400 flex-1 px-2">{receiptNo}</span>
        </div>
        <div className="flex items-center gap-2 justify-end text-right">
          <span>{translations.date}:</span>
          <div className="border-b-2 border-dotted border-slate-400 px-2 min-w-[100px]">
            <input 
              id="date"
              type="text" 
              autoComplete="off"
              className="w-full bg-transparent outline-none border-none print:hidden h-4 text-right focus:bg-slate-50 transition-colors px-1 rounded"
              value={formData.date}
              onChange={(e) => setFormData({...formData, date: e.target.value})}
              onKeyDown={(e) => handleKeyDown(e, 'fee-1')}
            />
            <span className="hidden print:inline">{formData.date}</span>
          </div>
        </div>
      </div>

      {/* Student Details */}
      <div className="space-y-3 print:space-y-0 text-[11px] print:text-[8.5px] font-bold text-slate-700">
        <div className="flex items-center gap-2">
          <span>{translations.studentName}:</span>
          <div className="border-b-2 border-dotted border-slate-400 flex-1 px-2">
            <input 
              id="studentName"
              type="text" 
              autoComplete="off"
              className="w-full bg-transparent outline-none border-none print:hidden h-4 focus:bg-slate-50 transition-colors px-1 rounded"
              value={formData.studentName}
              onChange={(e) => setFormData({...formData, studentName: e.target.value})}
              onKeyDown={(e) => handleKeyDown(e, 'rollNo')}
            />
            <span className="hidden print:inline">{formData.studentName}</span>
          </div>
        </div>
        <div className="grid grid-cols-3 gap-4 print:gap-1">
          <div className="flex items-center gap-2">
            <span>{translations.rollNo}:</span>
            <div className="border-b-2 border-dotted border-slate-400 flex-1 px-1">
              <input 
                id="rollNo"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-4 text-center focus:bg-slate-50 transition-colors rounded"
                value={formData.rollNo}
                onChange={(e) => setFormData({...formData, rollNo: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'section')}
              />
              <span className="hidden print:inline">{formData.rollNo}</span>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <span>{translations.section}:</span>
            <div className="border-b-2 border-dotted border-slate-400 flex-1 px-1">
              <input 
                id="section"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-4 text-center focus:bg-slate-50 transition-colors rounded"
                value={formData.section}
                onChange={(e) => setFormData({...formData, section: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'className')}
              />
              <span className="hidden print:inline">{formData.section}</span>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <span>{translations.class}:</span>
            <div className="border-b-2 border-dotted border-slate-400 flex-1 px-1">
              <input 
                id="className"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-4 text-center focus:bg-slate-50 transition-colors rounded"
                value={formData.className}
                onChange={(e) => setFormData({...formData, className: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'month')}
              />
              <span className="hidden print:inline">{formData.className}</span>
            </div>
          </div>
        </div>
        <div className="grid grid-cols-2 gap-4 print:gap-1">
          <div className="flex items-center gap-2">
            <span>{translations.month}:</span>
            <div className="border-b-2 border-dotted border-slate-400 flex-1 px-2">
              <input 
                id="month"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-4 focus:bg-slate-50 transition-colors px-1 rounded"
                value={formData.month}
                onChange={(e) => setFormData({...formData, month: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'guardianName')}
              />
              <span className="hidden print:inline">{formData.month}</span>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <span>{translations.guardianName}:</span>
            <div className="border-b-2 border-dotted border-slate-400 flex-1 px-2">
              <input 
                id="guardianName"
                type="text" 
                autoComplete="off"
                className="w-full bg-transparent outline-none border-none print:hidden h-4 focus:bg-slate-50 transition-colors px-1 rounded"
                value={formData.guardianName}
                onChange={(e) => setFormData({...formData, guardianName: e.target.value})}
                onKeyDown={(e) => handleKeyDown(e, 'date')}
              />
              <span className="hidden print:inline">{formData.guardianName}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Table */}
      <table className="w-full border-collapse border-y border-slate-800 text-[10px] print:text-[8.5px] font-bold mt-2 print:mt-1">
        <thead>
          <tr className="bg-slate-50 border-b border-slate-800 print:bg-transparent">
            <th className="border-r border-slate-800 w-10 py-1.5 print:py-0">{translations.serialNo}</th>
            <th className="border-r border-slate-800 px-4 py-1.5 print:py-0 text-left">{translations.particulars}</th>
            <th className="w-24 py-1.5 print:py-0">{translations.amount}</th>
          </tr>
        </thead>
        <tbody>
          {fees.map((fee, idx) => (
            <tr key={fee.id} className="border-b border-slate-200 print:border-slate-100 last:border-slate-800 print:leading-none">
              <td className="border-r border-slate-800 text-center py-1 print:py-0 bg-slate-50/50 print:bg-transparent h-4 print:h-3">{fee.id}</td>
              <td className="border-r border-slate-800 px-4 py-1 print:px-2 print:py-0">
                {idx < 18 ? (
                  language === 'ne' ? fee.nameNe : fee.nameEn
                ) : (
                  <input 
                    type="text" 
                    placeholder="..."
                    className="w-full bg-transparent outline-none border-none print:hidden h-3"
                    value={language === 'ne' ? fee.nameNe : fee.nameEn}
                    onChange={(e) => handleCustomLabelChange(idx, e.target.value)}
                  />
                )}
                <span className="hidden print:inline">{language === 'ne' ? fee.nameNe : fee.nameEn}</span>
              </td>
              <td className="py-0 px-0 relative">
                <input 
                  id={`fee-${idx + 1}`}
                  type="number" 
                  autoComplete="off"
                  className="w-full h-full bg-transparent outline-none border-none text-right px-2 font-mono print:hidden [-moz-appearance:_textfield] [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:m-0 focus:bg-slate-50 transition-colors"
                  value={fee.amount}
                  onChange={(e) => handleFeeChange(idx, e.target.value)}
                  onKeyDown={(e) => handleKeyDown(e, idx < fees.length - 1 ? `fee-${idx + 2}` : 'submit-btn')}
                />
                <span className="hidden print:block text-right px-2 font-mono">{fee.amount ? parseFloat(fee.amount).toFixed(2) : ''}</span>
              </td>
            </tr>
          ))}
          {/* Total Row */}
          <tr className="bg-slate-100/50 font-black text-xs print:text-[9.5px] print:bg-transparent">
            <td colSpan={2} className="border-r border-slate-800 px-4 py-2 print:py-0 text-right">{translations.total}</td>
            <td className="px-2 py-2 print:py-0 text-right font-mono">{calculateTotal().toFixed(2)}</td>
          </tr>
        </tbody>
      </table>

      {/* Footer Area */}
      <div className="pt-2 space-y-4 print:space-y-0 print:pt-1">
        <div className="flex items-center gap-2 text-[10px] font-bold print:text-[8.5px]">
          <span>{translations.inWords}:</span>
          <div className="border-b-2 border-dotted border-slate-400 flex-1 min-h-[16px] print:min-h-0 px-2 font-black text-slate-600 italic">
            {numberToWords(calculateTotal())}
          </div>
        </div>
        
        <div className="flex justify-end pt-4 print:pt-1">
          <div className="text-center min-w-[120px]">
            <div className="border-t border-slate-800 pt-1 text-[10px] font-black uppercase tracking-widest print:text-[8px]">
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
    date: '2082-12-17' // Default Nepali Date for March 30, 2026
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
    { id: 16, nameEn: 'Support', nameNe: 'सहयोग', amount: '' },
    { id: 17, nameEn: 'School Diary', nameNe: 'वि. डायरी', amount: '' },
    { id: 18, nameEn: 'Other', nameNe: 'अन्य', amount: '' },
    { id: 19, nameEn: '', nameNe: '', amount: '' },
    { id: 20, nameEn: '', nameNe: '', amount: '' },
    { id: 21, nameEn: '', nameNe: '', amount: '' },
  ]);

  const schoolName = sessionStorage.getItem('schoolName') || 'RAJ SCHOOL';
  const schoolAddress = sessionStorage.getItem('schoolAddress') || 'Bharatpur-11, Chitwan';
  const estdYear = sessionStorage.getItem('estdYear') || '2050';
  const institutionId = sessionStorage.getItem('institutionId');

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
          guardianName: data.guardian_name || '',
          date: data.date || ''
        });

        // Sync fees table
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
      setMessage({ type: 'error', text: 'Error loading receipt data' });
    } finally {
      setLoading(false);
    }
  };

  const fetchLatestReceiptNo = async () => {
    try {
      const { data, error } = await supabase
        .from('fee_receipts')
        .select('receipt_no')
        .eq('institution_id', institutionId)
        .order('receipt_no', { ascending: false })
        .limit(1);

      if (error) throw error;
      if (data && data.length > 0) {
        setReceiptNo(data[0].receipt_no + 1);
      } else {
        setReceiptNo(100);
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

      setMessage({ type: 'success', text: `Receipt #${receiptNo} saved successfully!` });
      
      // Trigger Print
      window.print();

      // Reset and increment
      setReceiptNo(prev => prev + 1);
      setFormData({
        ...formData,
        studentName: '',
        rollNo: '',
        month: '',
        guardianName: ''
      });
      setFees(fees.map(f => ({ ...f, amount: '' })));
      
    } catch (err) {
      console.error('Error saving receipt:', err);
      setMessage({ type: 'error', text: 'Failed to save receipt' });
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
    saveAndPrint: language === 'ne' 
      ? (isReprintMode ? 'रसिद पुन: मुद्रण गर्नुहोस्' : 'बचत र प्रिन्ट रसिद') 
      : (isReprintMode ? 'Reprint Receipt' : 'Save & Print Receipt'),
    viewHistory: language === 'ne' ? 'इतिहास हेर्नुहोस्' : 'View History',
    back: language === 'ne' ? 'फिर्ता' : 'Back',
  };

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col font-['Outfit',sans-serif]">
      {/* Action Bar - Hidden on Print */}
      <div className="sticky top-0 z-50 bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between shadow-sm print:hidden">
        <div className="flex items-center gap-6">
          <button 
            onClick={() => isReprintMode ? navigate('/billing/history') : navigate('/billing')} 
            className="flex items-center gap-2 p-2 hover:bg-slate-100 rounded-xl transition-all font-black text-slate-600 uppercase text-xs tracking-widest"
          >
            <ArrowLeft size={18} /> {translations.back}
          </button>
          <h1 className="text-2xl font-black text-slate-800 tracking-tighter">
            {isReprintMode ? 'View/Reprint Receipt' : 'New Receipt'}
          </h1>
        </div>

        <div className="flex items-center gap-4">
          {/* Status info */}
          <div className="px-4 py-2.5 bg-sky-50 border border-sky-100 rounded-2xl flex items-center gap-3">
             <History size={16} className="text-sky-500" />
             <p className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
               Last Issued: <span className="text-sky-600 font-black">#{(receiptNo - 1)}</span> | Next: <span className="text-sky-600 font-black">#{receiptNo}</span>
             </p>
          </div>

          <button 
             onClick={() => setLanguage(language === 'en' ? 'ne' : 'en')}
             className="px-6 py-2.5 bg-slate-100 border border-slate-200 rounded-2xl flex items-center gap-2 text-xs font-black text-slate-600 hover:bg-slate-200 transition-all uppercase tracking-widest"
          >
            <Languages size={18} className="text-indigo-500" />
            {language === 'en' ? 'Nepali' : 'English'}
          </button>

          <button 
             onClick={() => navigate('/billing/history')}
             className="px-6 py-2.5 bg-indigo-600 text-white rounded-2xl flex items-center gap-2 text-xs font-black hover:bg-indigo-700 transition-all uppercase tracking-widest shadow-lg shadow-indigo-100"
          >
            <History size={18} /> {translations.viewHistory}
          </button>
        </div>
      </div>

      {/* Main Content Area */}
      <div className="flex-1 p-8 overflow-auto print:p-0">
        
        {/* Alerts */}
        {message.text && (
          <div className={`max-w-4xl mx-auto mb-8 p-4 rounded-2xl border flex items-center gap-3 animate-in fade-in slide-in-from-top-4
            ${message.type === 'success' ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-rose-50 border-rose-100 text-rose-600'}`}
          >
            {message.type === 'success' ? <CheckCircle2 size={20} /> : <AlertCircle size={20} />}
            <span className="text-xs font-black uppercase tracking-widest">{message.text}</span>
          </div>
        )}

        {/* Dual Receipt Canvas */}
        <div className="flex flex-col md:flex-row gap-8 justify-center max-w-[1200px] mx-auto print:gap-4 print:max-w-none print:flex-row">
            <ReceiptBody 
              type="school" 
              schoolName={schoolName}
              schoolAddress={schoolAddress}
              estdYear={estdYear}
              translations={translations}
              receiptNo={receiptNo}
              formData={formData}
              fees={fees}
              language={language}
              setFormData={setFormData}
              handleFeeChange={handleFeeChange}
              handleCustomLabelChange={handleCustomLabelChange}
              handleKeyDown={handleKeyDown}
              calculateTotal={calculateTotal}
              numberToWords={numberToWords}
            />
            <ReceiptBody 
              type="student" 
              schoolName={schoolName}
              schoolAddress={schoolAddress}
              estdYear={estdYear}
              translations={translations}
              receiptNo={receiptNo}
              formData={formData}
              fees={fees}
              language={language}
              setFormData={setFormData}
              handleFeeChange={handleFeeChange}
              handleCustomLabelChange={handleCustomLabelChange}
              handleKeyDown={handleKeyDown}
              calculateTotal={calculateTotal}
              numberToWords={numberToWords}
            />
        </div>

        {/* Submission Button - Hidden on Print */}
        <div className="max-w-[1200px] mx-auto mt-12 mb-20 flex justify-center print:hidden">
          <button 
            id="submit-btn"
            disabled={isSubmitting || loading}
            onClick={handleSaveAndPrint}
            className="group relative flex items-center gap-4 px-12 py-6 bg-rose-500 text-white rounded-[32px] font-black uppercase tracking-[0.2em] text-sm shadow-[0_20px_40px_rgba(244,63,94,0.3)] hover:bg-rose-600 hover:translate-y-[-4px] active:translate-y-0 transition-all disabled:opacity-50 disabled:translate-y-0 focus:ring-4 focus:ring-rose-200"
          >
            <Printer size={24} className="group-hover:rotate-12 transition-transform" />
            {isSubmitting ? 'Syncing Vault...' : translations.saveAndPrint}
            <div className="absolute inset-0 rounded-[32px] bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
          </button>
        </div>
      </div>

      {/* Print Specific Styles */}
      <style dangerouslySetInnerHTML={{ __html: `
        @media print {
          body { background: white !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; width: 297mm !important; }
          #root { height: auto !important; }
          .print\\:hidden { display: none !important; }
          @page { size: landscape; margin: 0; }
          .min-h-screen { min-height: 0 !important; height: auto !important; }
          .overflow-auto { overflow: visible !important; }
          .flex-1 { flex: none !important; }
          .flex { 
            display: flex !important; 
            flex-direction: row !important; 
            flex-wrap: nowrap !important;
            gap: 2mm !important; 
            justify-content: center !important; 
            align-items: flex-start !important;
          }
          /* Extreme scaling hack to fit A4 height */
          .relative.border-2 { 
            zoom: 0.88; 
            margin-top: 2mm !important;
            max-height: 200mm !important;
          }
        }
      ` }} />
    </div>
  );
};

export default StudentFees;
