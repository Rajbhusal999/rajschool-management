import React from "react";
import { BrowserRouter as Router, Routes, Route, useLocation } from "react-router-dom";
import Sidebar from "./components/Sidebar";
import TopNav from "./components/TopNav";
import StudentList from "./pages/StudentList";
import TeacherList from "./pages/TeacherList";
import AttendanceEntry from "./pages/AttendanceEntry";
import AttendanceReports from "./pages/AttendanceReports";
import LandingPage from "./pages/LandingPage";

import SubjectList from "./pages/SubjectList";
import MarkEntry from "./pages/MarkEntry";
import ResultSheets from "./pages/ResultSheets";
import GradeSheetPrint from "./pages/GradeSheetPrint";
import ExamAttendance from "./pages/ExamAttendance";
import AdmitCard from "./pages/AdmitCard";
import AdmitCardConfig from "./pages/AdmitCardConfig";
import AdmitCardPrint from "./pages/AdmitCardPrint";
import ExamPortal from "./pages/ExamPortal";
import Register from "./pages/Register";
import AdminDashboard from "./pages/AdminDashboard";
import SchoolDashboard from "./pages/SchoolDashboard";
import Login from "./pages/Login";
import AdminLogin from "./pages/AdminLogin";
import Subscription from "./pages/Subscription";
import Payment from "./pages/Payment";
import MarkSlipConfig from "./pages/MarkSlipConfig";
import MarkSlipPrint from "./pages/MarkSlipPrint";
import About from "./pages/About";
import Billing from "./pages/Billing";
import StudentFees from "./pages/StudentFees";
import DonorFees from "./pages/DonorFees";
import BillingHistory from "./pages/BillingHistory";
import DonorHistory from "./pages/DonorHistory";
import SmsSettings from "./pages/SmsSettings";

import { GraduationCap, ShieldAlert } from "lucide-react";
import BackButton from "./components/BackButton";

// Error Boundary Component
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }
  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }
  componentDidCatch(error, errorInfo) {
    console.error("Uncaught error:", error, errorInfo);
  }
  render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen bg-rose-50 flex items-center justify-center p-6">
          <div className="max-w-md w-full bg-white p-8 rounded-[32px] shadow-xl border-2 border-rose-100 text-center space-y-4">
            <div className="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto">
              <ShieldAlert size={32} />
            </div>
            <h1 className="text-2xl font-black text-slate-800 tracking-tight">System Interrupted</h1>
            <p className="text-slate-500 font-bold text-sm leading-relaxed">
              We encountered a rendering error. Our core engine has safely paused the interface.
            </p>
            <div className="bg-slate-50 p-4 rounded-xl text-left overflow-auto max-h-40 border border-slate-100">
              <code className="text-[10px] text-rose-600 font-mono leading-none break-all">
                {this.state.error?.toString()}
              </code>
            </div>
            <button 
              onClick={() => window.location.reload()}
              className="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200"
            >
              Restart Engine
            </button>
          </div>
        </div>
      );
    }
    return this.props.children;
  }
}

function App() {
  return (
    <Router>
      <ErrorBoundary>
        <Layout />
      </ErrorBoundary>
    </Router>
  );
}

const Layout = () => {
  const location = useLocation();
  const publicRoutes = ["/", "/register", "/login", "/admin-login", "/features", "/admin/nexus", "/subscription", "/payment"];
  const isPublicPage = publicRoutes.includes(location.pathname);

  if (isPublicPage) {
    return (
      <Routes>
        <Route path="/" element={<LandingPage />} />
        <Route path="/register" element={<Register />} />
        <Route path="/features" element={<About />} />
        <Route path="/login" element={<Login />} />
        <Route path="/admin-login" element={<AdminLogin />} />
        <Route path="/admin/nexus" element={<AdminDashboard />} />
        <Route path="/subscription" element={<Subscription />} />
        <Route path="/payment" element={<Payment />} />
      </Routes>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col font-['Outfit',sans-serif] app-layout-root">
      <style dangerouslySetInnerHTML={{ __html: `
        @media print {
          .no-print, .print\\:hidden, nav, header, .app-layout-root > nav, .app-layout-root > .BackButton { 
            display: none !important; 
          }
          .min-h-screen, .app-layout-root { 
            min-height: 0 !important; 
            height: auto !important; 
            display: block !important;
            background: white !important;
          }
          main { 
            flex: 1 !important; 
            display: block !important;
          }
        }
      ` }} />
      <TopNav className="no-print" />
      <div className="flex-1 flex flex-col min-w-0">
        <main className="flex-1 p-4 md:p-8 max-w-[1600px] mx-auto w-full">
          <div className="no-print">
            <BackButton />
          </div>
          <Routes>
            <Route path="/dashboard" element={<SchoolDashboard />} />
            <Route path="/students" element={<StudentList />} />

            <Route path="/teachers" element={<TeacherList />} />
            <Route path="/attendance/entry" element={<AttendanceEntry />} />
            <Route path="/exams/attendance" element={<ExamAttendance />} />
            <Route path="/exams/admit-cards" element={<AdmitCard />} />
            <Route path="/exams/admit-cards/configure" element={<AdmitCardConfig />} />
            <Route path="/exams/admit-cards/print" element={<AdmitCardPrint />} />
            <Route path="/attendance/reports" element={<AttendanceReports />} />
            <Route path="/curriculum" element={<SubjectList />} />
            <Route path="/exams" element={<ExamPortal />} />
            <Route path="/exams/entry" element={<MarkEntry />} />
            <Route path="/exams/results" element={<ResultSheets />} />
            <Route path="/exams/print" element={<GradeSheetPrint />} />
            <Route path="/exams/subject-slips" element={<MarkSlipConfig />} />
            <Route path="/exams/subject-slips/view" element={<MarkSlipPrint />} />
            <Route path="/billing" element={<Billing />} />
            <Route path="/billing/student-fees/:id?" element={<StudentFees />} />
            <Route path="/billing/donor-receipts/:id?" element={<DonorFees />} />
            <Route path="/billing/history" element={<BillingHistory />} />
            <Route path="/billing/donor-history" element={<DonorHistory />} />
            <Route path="/settings/sms" element={<SmsSettings />} />
          </Routes>
        </main>
      </div>
    </div>
  );
};


export default App;
