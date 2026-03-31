import React, { useState, useEffect, useRef } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { 
  ArrowLeft, Printer, Save, History, 
  Search, Plus, Trash2, Calendar, 
  User, Hash, MapPin, Phone,
  Languages, GraduationCap, School,
  UserCheck, CreditCard, PenLine,
  Image as ImageIcon
} from 'lucide-react';
import { supabase } from '../supabaseClient';
import BackButton from '../components/BackButton';

const ReceiptBody = ({ 
  type, schoolName, schoolAddress, estdYear, 
  translations, receiptNo, formData, fees, 
  language, setFormData, handleFeeChange, 
  handleCustomLabelChange, handleKeyDown, 
  calculateTotal, numberToWords,
  schoolLogo, handleDateChange
}) => {
  return (
    <div className="relative border-2 border-slate-900 bg-white p-3 md:p-6 rounded-[2px] shadow-sm mb-4 md:mb-0 receipt-container min-h-[170mm]">
      {/* Watermark */}
      <div className="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03] overflow-hidden">
        <span className="text-[60px] font-black uppercase -rotate-45 tracking-[10px] select-none whitespace-nowrap">
          {type === 'school' ? 'SCHOOL COPY' : 'STUDENT COPY'}
        </span>
      </div>

      {/* Header */}
      <div className="relative mb-4 flex flex-col items-center">
        {schoolLogo && (
          <div className="absolute left-0 top-0 w-16 h-16 md:w-20 md:h-20 bg-white flex items-center justify-center p-1 border border-slate-100 rounded-lg shadow-sm">
            <img src={schoolLogo} alt="School Logo" className="max-w-full max-h-full object-contain" />
          </div>
        )}
        <div className="text-center w-full">
          <h2 className="text-xl md:text-3xl font-[1000] text-slate-950 uppercase tracking-tight mb-0.5">{schoolName}</h2>
          <p className="text-[10px] md:text-xs font-black text-slate-700 uppercase tracking-[0.2em] mb-0.5">{schoolAddress}</p>
          <p className="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase tracking-widest">ESTD: {estdYear}</p>
          <div className="inline-block mt-2 px-6 py-1 bg-slate-900 text-white text-[10px] md:text-xs font-black uppercase tracking-[0.3em] rounded-[2px]">
            {language === 'ne' ? 'नगदी रसिद' : 'Cash Receipt'}
          </div>
        </div>
      </div>

      {/* Info Grid */}
      <div className="grid grid-cols-2 gap-y-2 text-[10px] md:text-xs mb-4 border-b-2 border-slate-900 pb-3">
        <div className="flex items-center gap-2">
          <span className="font-black text-slate-500 uppercase tracking-wider">{language === 'ne' ? 'र. नं.:' : 'Receipt No.:'}</span>
          <span className="font-black text-slate-950 font-mono text-sm tracking-tighter">#{receiptNo}</span>
        </div>
        <div className="flex items-center gap-2 justify-end">
          <span className="font-black text-slate-500 uppercase tracking-wider">{language === 'ne' ? 'मिति:' : 'Date:'}</span>
          <input 
            type="text" value={formData.date}
            placeholder="YYYY/MM/DD"
            onChange={handleDateChange}
            className="w-24 md:w-32 bg-slate-50 border-b-2 border-dotted border-slate-950 px-2 py-0.5 font-black text-slate-950 rounded-none focus:ring-0 text-xs text-right"
          />
        </div>
        <div className="flex items-center gap-2 col-span-2">
          <span className="font-black text-slate-500 uppercase tracking-wider whitespace-nowrap">{language === 'ne' ? 'विद्यार्थीको नाम:' : 'Student\'s Name:'}</span>
          <input 
            type="text" value={formData.studentName}
            onChange={(e) => setFormData({...formData, studentName: e.target.value})}
            className="flex-1 bg-transparent border-0 border-b-2 border-dotted border-slate-950 p-0 font-black text-slate-950 uppercase tracking-tighter focus:ring-0 focus:border-slate-900"
          />
        </div>
        <div className="flex items-center gap-4 col-span-2">
          <div className="flex items-center gap-2">
            <span className="font-black text-slate-500 uppercase tracking-wider">{language === 'ne' ? 'रोल नं.:' : 'Roll No.:'}</span>
            <input 
              type="text" value={formData.rollNo}
              onChange={(e) => setFormData({...formData, rollNo: e.target.value})}
              className="w-12 bg-transparent border-0 border-b-2 border-dotted border-slate-950 p-0 font-black text-slate-950 font-mono focus:ring-0 focus:border-slate-900"
            />
          </div>
          <div className="flex items-center gap-2">
            <span className="font-black text-slate-500 uppercase tracking-wider">{language === 'ne' ? 'सेक्सन:' : 'Section:'}</span>
            <input 
              type="text" value={formData.section}
              onChange={(e) => setFormData({...formData, section: e.target.value})}
              className="w-12 bg-transparent border-0 border-b-2 border-dotted border-slate-950 p-0 font-black text-slate-950 uppercase focus:ring-0 focus:border-slate-900"
            />
          </div>
          <div className="flex items-center gap-2">
            <span className="font-black text-slate-500 uppercase tracking-wider">{language === 'ne' ? 'कक्षा:' : 'Class:'}</span>
            <input 
              type="text" value={formData.class}
              onChange={(e) => setFormData({...formData, class: e.target.value})}
              className="w-12 bg-transparent border-0 border-b-2 border-dotted border-slate-950 p-0 font-black text-slate-950 uppercase focus:ring-0 focus:border-slate-900"
            />
          </div>
        </div>
        <div className="flex items-center gap-2 col-span-2">
          <span className="font-black text-slate-500 uppercase tracking-wider whitespace-nowrap">{language === 'ne' ? 'महिना:' : 'Month:'}</span>
          <input 
            type="text" value={formData.month}
            onChange={(e) => setFormData({...formData, month: e.target.value})}
            className="flex-1 bg-transparent border-0 border-b-2 border-dotted border-slate-950 p-0 font-black text-slate-950 uppercase tracking-tighter focus:ring-0 focus:border-slate-900"
          />
          <span className="font-black text-slate-500 uppercase tracking-wider whitespace-nowrap ml-4">{language === 'ne' ? 'अभिभावक:' : 'Guardian:'}</span>
          <input 
            type="text" value={formData.guardian}
            onChange={(e) => setFormData({...formData, guardian: e.target.value})}
            className="flex-1 bg-transparent border-0 border-b-2 border-dotted border-slate-950 p-0 font-black text-slate-950 uppercase tracking-tighter focus:ring-0 focus:border-slate-900"
          />
        </div>
      </div>

      {/* Table */}
      <table className="w-full border-collapse border-slate-900 border mb-4">
        <thead>
          <tr className="bg-slate-50">
            <th className="border-slate-900 border text-[9px] md:text-[10px] font-black uppercase text-center w-12 py-1">{language === 'ne' ? 'क्र.सं.' : 'S.N.'}</th>
            <th className="border-slate-900 border text-[9px] md:text-[10px] font-black uppercase text-left pl-3 py-1">{translations.particulars}</th>
            <th className="border-slate-900 border text-[9px] md:text-[10px] font-black uppercase text-right pr-3 w-24 md:w-32 py-1">{translations.amount}</th>
          </tr>
        </thead>
        <tbody>
          {fees.map((fee, index) => (
            <tr key={fee.id} className="leading-tight h-[4.8mm]">
              <td className="border-slate-900 border text-[10px] md:text-xs font-bold text-center py-0">{index + 1}</td>
              <td className="border-slate-900 border text-[10px] md:text-xs font-black uppercase text-slate-800 pl-3 py-0">
                {index < 18 ? (
                    language === 'ne' ? fee.ne : fee.en
                ) : (
                    <input 
                        type="text" 
                        value={fee.en}
                        onChange={(e) => handleCustomLabelChange(fee.id, e.target.value)}
                        placeholder="..."
                        className="w-full bg-transparent border-none p-0 focus:ring-0 text-[10px] md:text-xs font-black uppercase placeholder:opacity-30"
                    />
                )}
              </td>
              <td className="border-slate-900 border text-[10px] md:text-xs font-black text-right py-0 pr-0">
                <input 
                  type="text"
                  value={fee.amount}
                  onChange={(e) => handleFeeChange(fee.id, e.target.value)}
                  onKeyDown={(e) => handleKeyDown(e, index)}
                  className="w-full bg-transparent border-none text-right pr-3 font-black text-slate-950 focus:ring-0 py-0"
                />
              </td>
            </tr>
          ))}
          <tr className="bg-slate-50 h-[6mm]">
            <td colSpan={2} className="border-slate-900 border text-[10px] md:text-xs font-black text-right pr-3 uppercase tracking-widest">{translations.total}</td>
            <td className="border-slate-900 border text-[10px] md:text-xs font-black text-right pr-3 font-mono text-indigo-600">{calculateTotal().toLocaleString('en-NP', { minimumFractionDigits: 2 })}</td>
          </tr>
        </tbody>
      </table>

      {/* Footer */}
      <div className="space-y-4">
        <div className="flex gap-2">
          <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest whitespace-nowrap">{translations.inWords}:</span>
          <span className="flex-1 text-[10px] font-black text-slate-950 uppercase border-b border-dotted border-slate-300 leading-tight">
            {numberToWords(calculateTotal())} {language === 'ne' ? 'मात्र ।' : 'Only.'}
          </span>
        </div>

        <div className="flex justify-between items-end pt-8">
          <div className="text-[9px] font-bold text-slate-400 italic">
            {language === 'ne' ? '* यो रसिद कम्प्युटर प्रणालीबाट तयार पारिएको हो ।' : '* System generated electronic receipt.'}
          </div>
          <div className="text-center min-w-32 md:min-w-40 mr-4">
            <div className="border-t-2 border-slate-900 pt-1 font-black text-[10px] md:text-xs text-slate-900 uppercase tracking-widest">
              {translations.receiverSign}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

const StudentFees = () => {
    const navigate = useNavigate();
    const { id } = useParams();
    const isReprintMode = !!id;
    const [language, setLanguage] = useState('en');
    const [loading, setLoading] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [schoolName, setSchoolName] = useState('');
    const [schoolAddress, setSchoolAddress] = useState('');
    const [estdYear, setEstdYear] = useState('');
    const [schoolLogo, setSchoolLogo] = useState(null);
    const [receiptNo, setReceiptNo] = useState('');
    const [institutionId, setInstitutionId] = useState(null);
    const [lastIssued, setLastIssued] = useState({ no: '---', date: '---' });

    const [formData, setFormData] = useState({
        date: '', // Clear default for manual entry in Nepali/Gregorian
        studentName: '',
        rollNo: '',
        class: '',
        section: '',
        month: '',
        guardian: ''
    });

    const [fees, setFees] = useState([
        { id: 1, en: 'Admission Fee', ne: 'सञ्चालन सहयोग', amount: '' },
        { id: 2, en: 'Exam Fee', ne: 'परीक्षा सञ्चालन सहयोग', amount: '' },
        { id: 3, en: 'Sports Fee', ne: 'खेलकुद', amount: '' },
        { id: 4, en: 'Book Fee', ne: 'पुस्तक', amount: '' },
        { id: 5, en: 'Welfare Fee', ne: 'गरीब विद्यार्थी सहायता', amount: '' },
        { id: 6, en: 'Advertisement Fee', ne: 'विज्ञापन सहयोग', amount: '' },
        { id: 7, en: 'Tie-Belt', ne: 'टाई बेल्ट', amount: '' },
        { id: 8, en: 'Extra Subject Fee', ne: 'अतिरिक्त विषय सञ्चालन', amount: '' },
        { id: 9, en: 'Transfer Certificate Fee', ne: 'स्थानान्तरण प्रमाण-पत्र', amount: '' },
        { id: 10, en: 'Practical Fee', ne: 'शैक्षिक सामग्री', amount: '' },
        { id: 11, en: 'ID Card Fee', ne: 'परिचय पत्र', amount: '' },
        { id: 12, en: 'Educational Tour Fee', ne: 'शैक्षिक भ्रमण', amount: '' },
        { id: 13, en: 'Red Cross Fee', ne: 'रेडक्रस', amount: '' },
        { id: 14, en: 'Sifaris', ne: 'सिफारिस', amount: '' },
        { id: 15, ne: 'विविध', en: 'Miscellaneous', amount: '' },
        { id: 16, ne: 'सहयोग', en: 'Support', amount: '' },
        { id: 17, ne: 'वि. डायरी', en: 'School Diary', amount: '' },
        { id: 18, ne: 'अन्य', en: 'Other', amount: '' },
        { id: 19, ne: '', en: '', amount: '' },
        { id: 20, ne: '', en: '', amount: '' }
    ]);

    useEffect(() => {
        loadInitialData();
    }, [id]);

    const loadInitialData = async () => {
        setLoading(true);
        try {
            // Read individual keys as set in Login.jsx
            // Try reading from sessionStorage first
            let name = sessionStorage.getItem('schoolName');
            let address = sessionStorage.getItem('schoolAddress');
            let estd = sessionStorage.getItem('estdYear');
            let logo = sessionStorage.getItem('schoolLogo');
            const instId = sessionStorage.getItem('institutionId');

            // Fallback: If session data is missing but we have an ID, fetch from DB
            if (!name && instId) {
                const { data: inst } = await supabase
                    .from('institutions')
                    .select('school_name, address, establishment, logo_url')
                    .eq('id', instId)
                    .single();
                
                if (inst) {
                    name = inst.school_name;
                    address = inst.address;
                    estd = inst.establishment;
                    logo = inst.logo_url;
                    
                    // Sync back to session for next time
                    sessionStorage.setItem('schoolName', name);
                    if (address) sessionStorage.setItem('schoolAddress', address);
                    if (estd) sessionStorage.setItem('estdYear', estd);
                    if (logo) sessionStorage.setItem('schoolLogo', logo);
                }
            }

            setSchoolName(name || 'Your School Name');
            setSchoolAddress(address || 'Your Address');
            setEstdYear(estd || '---');
            setSchoolLogo(logo);
            setInstitutionId(instId);

            const { data: lastReceipt } = await supabase
                .from('fee_receipts')
                .select('receipt_no, created_at')
                .order('created_at', { ascending: false })
                .limit(1);

            if (lastReceipt && lastReceipt.length > 0) {
                setLastIssued({
                    no: lastReceipt[0].receipt_no,
                    date: new Date(lastReceipt[0].created_at).toLocaleDateString()
                });
                if (!isReprintMode) {
                    setReceiptNo(String(Number(lastReceipt[0].receipt_no) + 1));
                }
            } else if (!isReprintMode) {
                setReceiptNo('101');
            }

            if (isReprintMode) {
                const { data: receipt, error } = await supabase
                    .from('fee_receipts')
                    .select('*')
                    .eq('id', id)
                    .single();

                if (receipt) {
                    setReceiptNo(receipt.receipt_no);
                    setFormData({
                        date: receipt.date,
                        studentName: receipt.student_name,
                        rollNo: receipt.roll_no,
                        class: receipt.class,
                        section: receipt.section,
                        month: receipt.month,
                        guardian: receipt.guardian_name || receipt.guardian
                    });
                    const parsedFees = receipt.items || receipt.fees; // Fallback for safety
                    setFees(typeof parsedFees === 'string' ? JSON.parse(parsedFees) : (parsedFees || []));
                }
            }
        } catch (error) {
            console.error('Error fetching data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleFeeChange = (id, value) => {
        if (value !== '' && isNaN(Number(value))) return;
        setFees(fees.map(f => f.id === id ? { ...f, amount: value } : f));
    };

    const handleCustomLabelChange = (id, value) => {
        setFees(fees.map(f => f.id === id ? { ...f, en: value, ne: value } : f));
    };

    const handleKeyDown = (e, index) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const inputs = document.querySelectorAll('input[type="text"]');
            const currentInputs = Array.from(inputs);
            const currentIdx = currentInputs.indexOf(e.target);
            if (currentIdx < currentInputs.length - 1) {
                currentInputs[currentIdx + 1].focus();
            }
        }
    };

    const calculateTotal = () => {
        return fees.reduce((sum, f) => sum + (Number(f.amount) || 0), 0);
    };

    const numberToWords = (num) => {
        if (num === 0) return 'Zero';
        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        const convert = (n) => {
            if (n < 20) return ones[n];
            if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 !== 0 ? ' ' + ones[n % 10] : '');
            if (n < 1000) return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 !== 0 ? ' and ' + convert(n % 100) : '');
            if (n < 100000) return convert(Math.floor(n / 1000)) + ' Thousand' + (n % 1000 !== 0 ? ' ' + convert(n % 1000) : '');
            if (n < 10000000) return convert(Math.floor(n / 100000)) + ' Lakh' + (n % 100000 !== 0 ? ' ' + convert(n % 100000) : '');
            return convert(Math.floor(n / 10000000)) + ' Crore' + (n % 10000000 !== 0 ? ' ' + convert(n % 10000000) : '');
        };
        return convert(Math.floor(num));
    };

    const handleDateChange = (e) => {
        let val = e.target.value.replace(/[^0-9/]/g, ''); // Allow only numbers and already typed slashes
        const parts = val.split('/').join('');
        
        let formatted = parts;
        if (parts.length > 4) {
            formatted = parts.slice(0, 4) + '/' + parts.slice(4);
        }
        if (parts.length > 6) {
            formatted = formatted.slice(0, 7) + '/' + formatted.slice(7);
        }
        
        // Limit to YYYY/MM/DD length (10 chars)
        if (formatted.length <= 10) {
            setFormData({ ...formData, date: formatted });
        }
    };

    const handleSaveAndPrint = async () => {
        if (!formData.studentName) {
            alert('Please enter student name');
            return;
        }

        try {
            setIsSubmitting(true);
            const total = calculateTotal();
            
            if (!isReprintMode) {
                const { error } = await supabase
                    .from('fee_receipts')
                    .insert([{
                        institution_id: institutionId,
                        receipt_no: parseInt(receiptNo),
                        date: formData.date,
                        student_name: formData.studentName,
                        roll_no: formData.rollNo,
                        class: formData.class,
                        section: formData.section,
                        month: formData.month,
                        guardian_name: formData.guardian,
                        items: fees, // Supabase handles jsonb mapping automatically
                        total_amount: total,
                        language: language
                    }]);

                if (error) throw error;
            }

            // Delay print slightly to allow state updates and layout changes to reflect
            setTimeout(() => {
                window.print();
                setIsSubmitting(false);
            }, 500);

        } catch (error) {
            console.error('Error saving receipt:', error);
            alert('Failed to save receipt. Data might be lost.');
            setIsSubmitting(false);
        }
    };

    const translations = {
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
            <div className="max-w-[1400px] mx-auto mt-6 mb-4 px-4 print:hidden no-print">
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
                <div className="flex flex-col md:flex-row gap-8 justify-center max-w-[1400px] mx-auto print:gap-1 print:max-w-none print:flex-row print:justify-center receipt-print-wrapper">
                    <ReceiptBody 
                        type="school" schoolName={schoolName} schoolAddress={schoolAddress} estdYear={estdYear} 
                        translations={translations} receiptNo={receiptNo} formData={formData} fees={fees} 
                        language={language} setFormData={setFormData} handleFeeChange={handleFeeChange} 
                        handleCustomLabelChange={handleCustomLabelChange} handleKeyDown={handleKeyDown} 
                        calculateTotal={calculateTotal} numberToWords={numberToWords}
                        schoolLogo={schoolLogo}
                        handleDateChange={handleDateChange}
                    />
                    <ReceiptBody 
                        type="student" schoolName={schoolName} schoolAddress={schoolAddress} estdYear={estdYear} 
                        translations={translations} receiptNo={receiptNo} formData={formData} fees={fees} 
                        language={language} setFormData={setFormData} handleFeeChange={handleFeeChange} 
                        handleCustomLabelChange={handleCustomLabelChange} handleKeyDown={handleKeyDown} 
                        calculateTotal={calculateTotal} numberToWords={numberToWords}
                        schoolLogo={schoolLogo}
                        handleDateChange={handleDateChange}
                    />
                </div>

                <div className="max-w-[1400px] mx-auto mt-12 mb-20 flex justify-center print:hidden no-print">
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
                    .receipt-print-wrapper { 
                        display: flex !important; 
                        flex-direction: row !important;
                        justify-content: center !important;
                        gap: 12mm !important;
                        width: 297mm !important; 
                        margin: 0 !important;
                        padding: 0 !important;
                        padding-top: 10mm !important;
                    }
                    .receipt-container { margin: 0 !important; padding: 0 !important; }
                    .relative.border-2 { 
                        zoom: 0.9; 
                        width: 138mm !important; 
                        min-width: 138mm !important;
                        height: 180mm !important;
                        max-height: 180mm !important;
                        border-color: #000 !important;
                        border-width: 1.5pt !important;
                        box-shadow: none !important;
                        page-break-inside: avoid !important;
                        overflow: visible !important;
                    }
                    table, th, td { border-color: #000 !important; border-width: 1pt !important; }
                }
            ` }} />
        </div>
    );
};

export default StudentFees;
