import React, { createContext, useContext, useState, useEffect } from 'react';

const ThemeContext = createContext();
const LanguageContext = createContext();

const translations = {
  en: {
    // Nav Items
    dashboard: 'Dashboard',
    students: 'Students',
    teachers: 'Teachers',
    exams: 'Exams',
    billing: 'Billing',
    attendance: 'Attendance',
    reports: 'Reports',
    idCards: 'ID Cards',
    logout: 'Log Out',
    
    // Theme
    darkMode: 'Dark Mode',
    lightMode: 'Light Mode',
    
    // Dashboard / Common
    welcome: 'Welcome',
    studentRepository: 'Student Repository',
    manageComprehensive: 'Manage comprehensive student records and academic profiles.',
    addNew: 'Add New',
    export: 'Export',
    searchPlaceholder: 'Search by name, ID...',
    allClasses: 'All Classes',
    apply: 'Apply',
    attendanceSummary: "Today's Attendance",
    attendanceDesc: 'Real-time presence tracking for all classes.',
    markAttendance: 'Mark Attendance',
    feeCollection: 'Fee Collection',
    feeDesc: 'Monthly financial pulse and pending dues.',
    enterLedger: 'Enter Ledger',
  },
  np: {
    // Nav Items
    dashboard: 'ड्यासबोर्ड',
    students: 'विद्यार्थीहरू',
    teachers: 'शिक्षकहरू',
    exams: 'परीक्षाहरू',
    billing: 'बिलिङ',
    attendance: 'उपस्थिति',
    reports: 'रिपोर्टहरू',
    idCards: 'परिचयपत्र',
    logout: 'बाहिरिनुहोस्',
    
    // Theme
    darkMode: 'डार्क मोड',
    lightMode: 'लाइट मोड',
    
    // Dashboard / Common
    welcome: 'स्वागत छ',
    studentRepository: 'विद्यार्थी भण्डार',
    manageComprehensive: 'विद्यार्थी रेकर्डहरू र शैक्षिक प्रोफाइलहरू व्यवस्थापन गर्नुहोस्।',
    addNew: 'नयाँ थप्नुहोस्',
    export: 'निर्यात गर्नुहोस्',
    searchPlaceholder: 'नाम, आईडी द्वारा खोज्नुहोस्...',
    allClasses: 'सबै कक्षाहरू',
    apply: 'लागु गर्नुहोस्',
    attendanceSummary: 'आजको उपस्थिति',
    attendanceDesc: 'सबै कक्षाहरूको वास्तविक-समय उपस्थिति ट्र्याकिङ।',
    markAttendance: 'हाजिरी गर्नुहोस्',
    feeCollection: 'शुल्क संकलन',
    feeDesc: 'मासिक आर्थिक अवस्था र बाँकी शुल्क।',
    enterLedger: 'लेजर प्रविष्ट गर्नुहोस्',
  }
};

export const ThemeProvider = ({ children }) => {
  const [isDarkMode, setIsDarkMode] = useState(() => {
    return localStorage.getItem('theme') === 'dark' || 
           (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
  });

  useEffect(() => {
    const root = window.document.documentElement;
    root.setAttribute('data-theme', isDarkMode ? 'dark' : 'light');
    root.classList.toggle('dark', isDarkMode);
    localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
  }, [isDarkMode]);

  const toggleTheme = () => setIsDarkMode(prev => !prev);

  return (
    <ThemeContext.Provider value={{ isDarkMode, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const LanguageProvider = ({ children }) => {
  const [lang, setLang] = useState(() => localStorage.getItem('lang') || 'en');

  useEffect(() => {
    localStorage.setItem('lang', lang);
  }, [lang]);

  const toggleLang = () => setLang(prev => (prev === 'en' ? 'np' : 'en'));

  const t = (key) => {
    return translations[lang][key] || key;
  };

  return (
    <LanguageContext.Provider value={{ lang, toggleLang, t }}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useTheme = () => useContext(ThemeContext);
export const useLanguage = () => useContext(LanguageContext);
