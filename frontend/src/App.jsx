import React from "react";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Sidebar from "./components/Sidebar";
import TopNav from "./components/TopNav";
import StudentList from "./pages/StudentList";
import TeacherList from "./pages/TeacherList";
import AttendanceEntry from "./pages/AttendanceEntry";
import AttendanceReports from "./pages/AttendanceReports";
import LandingPage from "./pages/LandingPage";
import { useLocation } from "react-router-dom";

import SubjectList from "./pages/SubjectList";
import MarkEntry from "./pages/MarkEntry";
import ResultSheets from "./pages/ResultSheets";
import GradeSheetPrint from "./pages/GradeSheetPrint";
import Register from "./pages/Register";
import AdminDashboard from "./pages/AdminDashboard";
import SchoolDashboard from "./pages/SchoolDashboard";
import Login from "./pages/Login";
import AdminLogin from "./pages/AdminLogin";
import Subscription from "./pages/Subscription";
import Payment from "./pages/Payment";
import About from "./pages/About";

import { GraduationCap } from "lucide-react";

function App() {
  return (
    <Router>
      <Layout />
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
    <div className="min-h-screen bg-slate-50 flex flex-col font-['Outfit',sans-serif]">
      <TopNav />
      <div className="flex-1 flex flex-col min-w-0">
        <main className="flex-1 p-4 md:p-8 max-w-[1600px] mx-auto w-full">
          <Routes>
            <Route path="/dashboard" element={<SchoolDashboard />} />
            <Route path="/login" element={<Login />} />
            <Route path="/admin-login" element={<AdminLogin />} />
            <Route path="/students" element={<StudentList />} />

            <Route path="/teachers" element={<TeacherList />} />
            <Route path="/attendance/entry" element={<AttendanceEntry />} />
            <Route path="/attendance/reports" element={<AttendanceReports />} />
            <Route path="/curriculum" element={<SubjectList />} />
            <Route path="/exams/entry" element={<MarkEntry />} />
            <Route path="/exams/results" element={<ResultSheets />} />
            <Route path="/exams/print" element={<GradeSheetPrint />} />
          </Routes>
        </main>
      </div>
    </div>
  );
};


export default App;
