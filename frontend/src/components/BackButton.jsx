import React from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';

const BackButton = () => {
  const navigate = useNavigate();
  const location = useLocation();

  // Hide the back button on the main dashboard or the print view
  if (location.pathname === '/dashboard' || location.pathname === '/' || location.pathname === '/exams/admit-cards/print') {
    return null;
  }

  const handleBack = () => {
    if (window.history.length > 1) {
      navigate(-1);
    } else {
      navigate('/dashboard');
    }
  };

  return (
    <button
      onClick={handleBack}
      className="group mb-6 inline-flex items-center gap-2.5 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl text-slate-500 hover:text-indigo-600 font-black text-xs uppercase tracking-[0.15em] transition-all duration-300 shadow-sm hover:shadow-md hover:-translate-x-1"
    >
      <ArrowLeft 
        size={16} 
        strokeWidth={3} 
        className="transition-transform group-hover:-translate-x-0.5" 
      />
      <span>Go Back</span>
    </button>
  );
};

export default BackButton;
