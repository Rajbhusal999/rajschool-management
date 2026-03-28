import React, { useState, useEffect } from 'react';
import { teacherService } from '../services/api';
import { X, User, Users, Phone, MapPin, BookOpen, Calendar, ShieldCheck, Briefcase, Landmark } from 'lucide-react';

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
    teacherPassword: '',
    bankName: '',
    accountNumber: '',
    schoolId: 1
  });

  useEffect(() => {
    if (teacher) {
      setFormData(teacher);
    }
  }, [teacher]);

  const handleChange = (e) => {
    const { name, value } = e.target;
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
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
      <div className="bg-white w-full max-w-3xl rounded-2xl shadow-2xl p-0 relative flex flex-col max-h-[90vh]">
        <div className="p-6 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10 rounded-t-2xl">
          <h2 className="text-xl font-bold text-slate-900">
            {teacher ? 'Edit Staff Profile' : 'Add New Teacher/Staff'}
          </h2>
          <button 
            onClick={onClose}
            className="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition"
          >
            <X size={20} />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-8 overflow-y-auto space-y-6">
          {/* Role Selection */}
          <div className="flex p-1 bg-slate-100 rounded-xl max-w-sm mx-auto">
            <button
              type="button"
              className={`flex-1 py-2 px-4 rounded-lg font-bold text-sm transition ${formData.staffRole === 'Teacher' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
              onClick={() => setFormData(prev => ({ ...prev, staffRole: 'Teacher' }))}
            >
              Teacher
            </button>
            <button
              type="button"
              className={`flex-1 py-2 px-4 rounded-lg font-bold text-sm transition ${formData.staffRole === 'Staff' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
              onClick={() => setFormData(prev => ({ ...prev, staffRole: 'Staff' }))}
            >
              Support Staff
            </button>
          </div>

          {/* General Info */}
          <section>
            <SectionTitle icon={User} title="Personal & Academic Info" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="md:col-span-2">
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Full Name *</label>
                <input type="text" name="fullName" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.fullName} onChange={handleChange} />
              </div>
              {formData.staffRole === 'Teacher' && (
                <>
                  <div>
                    <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Subject *</label>
                    <input type="text" name="subject" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.subject} onChange={handleChange} />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Level (Tah) *</label>
                    <select name="tah" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.tah} onChange={handleChange}>
                      <option value="प्रा.वि">प्रा.वि (Primary)</option>
                      <option value="नि.मा.वि">नि.मा.वि (Lower Secondary)</option>
                      <option value="मा.वि">मा.वि (Secondary)</option>
                    </select>
                  </div>
                </>
              )}
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Type *</label>
                <select name="teacherType" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.teacherType} onChange={handleChange}>
                  <option value="Permanent">Permanent</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Internal source">Internal source</option>
                  <option value="School source">School source</option>
                  <option value="अनुदान">अनुदान (Grant)</option>
                </select>
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Attendance Date (BS) *</label>
                <input type="text" name="attendanceDateNepali" placeholder="YYYY/MM/DD" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.attendanceDateNepali} onChange={handleChange} />
              </div>
            </div>
          </section>

          {/* Contact & Identity */}
          <section>
            <SectionTitle icon={Phone} title="Contact & Identity" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Phone Number *</label>
                <input type="text" name="contact" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.contact} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">PAN Number</label>
                <input type="text" name="panNo" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.panNo} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Citizenship No</label>
                <input type="text" name="citizenshipNo" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.citizenshipNo} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Blood Group</label>
                <input type="text" name="bloodGroup" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.bloodGroup} onChange={handleChange} />
              </div>
            </div>
            <div className="mt-4">
              <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Full Address</label>
              <textarea name="address" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500 h-20" value={formData.address} onChange={handleChange}></textarea>
            </div>
          </section>

          {/* Bank Info */}
          <section>
            <SectionTitle icon={Landmark} title="Banking Information" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Bank Name</label>
                <input type="text" name="bankName" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.bankName} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Account Number</label>
                <input type="text" name="accountNumber" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.accountNumber} onChange={handleChange} />
              </div>
            </div>
          </section>

          {formData.staffRole === 'Teacher' && (
            <section>
              <SectionTitle icon={ShieldCheck} title="System Access" />
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Portal Password</label>
                <input type="password" name="teacherPassword" placeholder="Minimum 8 characters" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" value={formData.teacherPassword} onChange={handleChange} />
              </div>
            </section>
          )}

          <div className="pt-8 pb-4 flex items-center justify-end gap-4 sticky bottom-0 bg-white border-t border-slate-100 -mx-8 px-8 z-10">
            <button type="button" onClick={onClose} className="px-6 py-2.5 text-slate-600 font-bold hover:bg-slate-50 rounded-xl transition">Cancel</button>
            <button type="submit" className="px-10 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
              {teacher ? 'Update Details' : 'Register Staff'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default TeacherForm;
