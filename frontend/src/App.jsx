import React from "react";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Sidebar from "./components/Sidebar";
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
import Login from "./pages/Login";
import AdminLogin from "./pages/AdminLogin";
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
  const publicRoutes = ["/", "/register", "/login", "/admin-login", "/features"];
  const isPublicPage = publicRoutes.includes(location.pathname);

  if (isPublicPage) {
    return (
      <Routes>
        <Route path="/" element={<LandingPage />} />
        <Route path="/register" element={<Register />} />
        <Route path="/features" element={<About />} />
        <Route path="/login" element={<Login />} />
        <Route path="/admin-login" element={<AdminLogin />} />
      </Routes>
    );
  }

  return (
    <div className="flex min-h-screen bg-slate-50">
      <Sidebar />
      <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
        <main className="flex-1 overflow-y-auto p-4 md:p-8">
          <Routes>
            <Route path="/dashboard" element={<AdminDashboard />} />
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
