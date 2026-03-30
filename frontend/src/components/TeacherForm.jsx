import React, { useState, useEffect } from 'react';
import { teacherService } from '../services/api';
import { X, User, Users, Phone, MapPin, BookOpen, Calendar, ShieldCheck, Briefcase, Landmark, FileText } from 'lucide-react';

const TeacherForm = ({ teacher, onClose, onSave }) => {
  const [formData, setFormData] = useState({
    fullName: '',
    staffRole: 'Teacher',
    subject: '',
    contact: '',
    teacherType: 'Permanent',
    attendanceDateNepali: '',
    address: '',
    tah: 'प्रा.वि',
    panNo: '',
    bloodGroup: '',
    citizenshipNo: '',
    citizenshipFront: null,
    citizenshipBack: null,
    teacherPassword: '',
    bankName: '',
    accountNumber: '',
    schoolId: sessionStorage.getItem('institutionId')
  });

  useEffect(() => {
    if (teacher) {
      setFormData(teacher);
    }
  }, [teacher]);

  const handleChange = (e) => {
    let { name, value } = e.target;

    // Auto-format Attendance Date (BS) to YYYY/MM/DD
    if (name === 'attendanceDateNepali') {
      const digits = value.replace(/\D/g, '');
      let formatted = '';
      if (digits.length > 0) {
        formatted += digits.substring(0, 4);
        if (digits.length >= 5) {
          formatted += '/' + digits.substring(4, 6);
        }
        if (digits.length >= 7) {
          formatted += '/' + digits.substring(6, 8);
        }
      }
      value = formatted;
    }

    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (teacher) {
        await teacherService.update(teacher.id, formData);
      } else {
        await teacherService.create(formData);
      }
      onSave();
    } catch (err) {
      console.error(err);
      alert('Error saving teacher/staff. Check console for details.');
    }
  };

  const SectionTitle = ({ icon: Icon, title }) => (
    <div className="flex items-center gap-2 pb-2 border-b border-slate-100 mb-4 mt-6 first:mt-0">
      <Icon size={18} className="text-indigo-600" />
      <h3 className="font-bold text-slate-800 uppercase tracking-wider text-xs">{title}</h3>
    </div>
  );

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-md p-4 overflow-y-auto">
      <div className="bg-white w-full max-w-4xl rounded-[32px] shadow-2xl p-0 relative flex flex-col max-h-[95vh] overflow-hidden">
        {/* Header */}
        <div className="p-8 pb-4 flex items-center justify-between bg-white">
          <h2 className="text-3xl font-extrabold text-slate-800 font-outfit">Add New Teacher/Staff</h2>
          <button 
            onClick={onClose}
            className="p-3 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-2xl transition-all"
          >
            <X size={24} />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="px-8 pb-8 pt-2 overflow-y-auto custom-scrollbar">
          {/* Role Toggle */}
          <div className="flex p-2 bg-slate-100 rounded-[24px] mb-8 w-full">
            <button
              type="button"
              className={`flex-1 flex items-center justify-center gap-3 py-4 rounded-[20px] font-bold text-lg transition-all ${formData.staffRole === 'Teacher' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-slate-500 hover:text-slate-700'}`}
              onClick={() => setFormData(prev => ({ ...prev, staffRole: 'Teacher' }))}
            >
              <Users size={20} />
              Teacher
            </button>
            <button
              type="button"
              className={`flex-1 flex items-center justify-center gap-3 py-4 rounded-[20px] font-bold text-lg transition-all ${formData.staffRole === 'Staff' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-slate-500 hover:text-slate-700'}`}
              onClick={() => setFormData(prev => ({ ...prev, staffRole: 'Staff' }))}
            >
              <User size={20} />
              Staff
            </button>
          </div>

          {/* Form Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            {/* Common Fields */}
            <div className="space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Full Name *</label>
              <input 
                type="text" 
                name="fullName" 
                required 
                placeholder="Enter full name"
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800" 
                value={formData.fullName} 
                onChange={handleChange} 
              />
            </div>

            <div className="space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Teacher Photo</label>
              <div className="relative">
                <input 
                  type="file" 
                  name="teacherPhoto"
                  className="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-[18px] outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                  onChange={(e) => setFormData(prev => ({ ...prev, teacherPhoto: e.target.files[0] }))}
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Type *</label>
              <select 
                name="teacherType" 
                required 
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800 appearance-none bg-no-repeat bg-[right_1.25rem_center]" 
                value={formData.teacherType} 
                onChange={handleChange}
              >
                <option value="Permanent">Permanent</option>
                <option value="Temporary">Temporary</option>
                <option value="Internal source">Internal source</option>
                <option value="School source">School source</option>
                <option value="अनुदान">अनुदान (Grant)</option>
              </select>
            </div>

            {formData.staffRole === 'Teacher' && (
              <div className="space-y-2">
                <label className="block text-sm font-bold text-slate-700 ml-1">Tah (Level) *</label>
                <select 
                  name="tah" 
                  required 
                  className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800 appearance-none" 
                  value={formData.tah} 
                  onChange={handleChange}
                >
                  <option value="प्रा.वि">प्रा.वि (Primary)</option>
                  <option value="नि.मा.वि">नि.मा.वि (Lower Secondary)</option>
                  <option value="मा.वि">मा.वि (Secondary)</option>
                </select>
              </div>
            )}

            <div className="space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Attendance Date (BS) *</label>
              <input 
                type="text" 
                name="attendanceDateNepali" 
                placeholder="YYYY/MM/DD" 
                required 
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800" 
                value={formData.attendanceDateNepali} 
                onChange={handleChange} 
              />
            </div>

            {formData.staffRole === 'Teacher' && (
              <div className="space-y-2">
                <label className="block text-sm font-bold text-slate-700 ml-1">Subject *</label>
                <input 
                  type="text" 
                  name="subject" 
                  required 
                  placeholder="e.g. Mathematics"
                  className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800" 
                  value={formData.subject} 
                  onChange={handleChange} 
                />
              </div>
            )}

            <div className="space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Phone Number *</label>
              <input 
                type="text" 
                name="contact" 
                required 
                placeholder="Enter contact number"
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800" 
                value={formData.contact} 
                onChange={handleChange} 
              />
            </div>

          {/* Identification Section */}
          <div className="md:col-span-2">
            <SectionTitle icon={FileText} title="Identification Documents" />
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div className="space-y-2">
                <label className="block text-sm font-bold text-slate-700 ml-1 flex items-center gap-2">
                  <FileText size={16} className="text-slate-400" />
                  Citizenship Number
                </label>
                <input 
                  type="text" 
                  name="citizenshipNo" 
                  placeholder="Enter citizenship no"
                  className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800" 
                  value={formData.citizenshipNo} 
                  onChange={handleChange} 
                />
              </div>

              <div className="space-y-2">
                <label className="block text-sm font-bold text-slate-700 ml-1 flex items-center gap-2">
                  <FileText size={16} className="text-slate-400" />
                  Citizenship Front Photo
                </label>
                <div className="relative group">
                  <input 
                    type="file" 
                    name="citizenshipFront"
                    className="w-full px-5 py-3.5 bg-slate-50 border border-dashed border-slate-300 rounded-[18px] outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer transition-all hover:border-indigo-300"
                    onChange={(e) => setFormData(prev => ({ ...prev, citizenshipFront: e.target.files[0] }))}
                  />
                </div>
              </div>

              <div className="space-y-2">
                <label className="block text-sm font-bold text-slate-700 ml-1 flex items-center gap-2">
                  <FileText size={16} className="text-slate-400" />
                  Citizenship Back Photo
                </label>
                <div className="relative group">
                  <input 
                    type="file" 
                    name="citizenshipBack"
                    className="w-full px-5 py-3.5 bg-slate-50 border border-dashed border-slate-300 rounded-[18px] outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer transition-all hover:border-indigo-300"
                    onChange={(e) => setFormData(prev => ({ ...prev, citizenshipBack: e.target.files[0] }))}
                  />
                </div>
              </div>
            </div>
          </div>

            <div className="space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Bank Name</label>
              <input 
                type="text" 
                name="bankName" 
                placeholder="Enter bank name"
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800" 
                value={formData.bankName} 
                onChange={handleChange} 
              />
            </div>

            <div className="space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Account Number</label>
              <input 
                type="text" 
                name="accountNumber" 
                placeholder="Enter bank account number"
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800" 
                value={formData.accountNumber} 
                onChange={handleChange} 
              />
            </div>

            <div className="space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Blood Group</label>
              <select 
                name="bloodGroup" 
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800 appearance-none" 
                value={formData.bloodGroup} 
                onChange={handleChange}
              >
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
              </select>
            </div>

            <div className="md:col-span-2 space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Password *</label>
              <input 
                type="password" 
                name="teacherPassword" 
                placeholder="Set login password"
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[18px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800" 
                value={formData.teacherPassword} 
                onChange={handleChange} 
              />
              <span className="block text-[10px] text-slate-400 mt-1 ml-1 font-medium italic">
                Min 8 chars: upper, lower, num, special (@$!%*?&amp;)
              </span>
            </div>

            <div className="md:col-span-2 space-y-2">
              <label className="block text-sm font-bold text-slate-700 ml-1">Address</label>
              <textarea 
                name="address" 
                placeholder="Enter permanent address"
                className="w-full px-5 py-4 bg-white border border-slate-200 rounded-[24px] outline-none focus:ring-4 focus:ring-indigo-50 transition-all font-medium text-slate-800 h-32 resize-none" 
                value={formData.address} 
                onChange={handleChange}
              ></textarea>
            </div>
          </div>

          <div className="pt-10 pb-4 flex items-center justify-end gap-6 sticky bottom-0 bg-white/90 backdrop-blur-sm -mx-8 px-8 z-10">
            <button 
              type="button" 
              onClick={onClose} 
              className="px-10 py-4 text-slate-800 font-bold hover:bg-slate-100 rounded-[20px] transition-all bg-slate-50"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              className="px-12 py-4 bg-indigo-600 text-white font-bold rounded-[20px] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100"
            >
              {teacher ? 'Update Profile' : 'Save Teacher/Staff'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default TeacherForm;
