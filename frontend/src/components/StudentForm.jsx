import React, { useState, useEffect } from 'react';
import { studentService } from '../services/api';
import { X, User, Users, MapPin, Info } from 'lucide-react';

const StudentForm = ({ student, onClose, onSave }) => {
  const [formData, setFormData] = useState({
    fullName: '',
    studentClass: '',
    rollNo: '',
    parentContact: '',
    gender: 'Male',
    dobNepali: '',
    caste: '',
    emisNo: '',
    fatherName: '',
    motherName: '',
    guardianName: '',
    guardianContact: '',
    guardianEmail: '',
    permProvince: '',
    permDistrict: '',
    permLocalLevel: '',
    permWardNo: '',
    permTole: '',
    tempProvince: '',
    tempDistrict: '',
    tempLocalLevel: '',
    tempWardNo: '',
    tempTole: '',
    scholarshipType: 'None',
    disabilityType: 'None',
    schoolId: 1
  });

  const [sameAsPerm, setSameAsPerm] = useState(false);

  useEffect(() => {
    if (student) {
      setFormData(student);
    }
  }, [student]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSameAsPerm = (e) => {
    const checked = e.target.checked;
    setSameAsPerm(checked);
    if (checked) {
      setFormData(prev => ({
        ...prev,
        tempProvince: prev.permProvince,
        tempDistrict: prev.permDistrict,
        tempLocalLevel: prev.permLocalLevel,
        tempWardNo: prev.permWardNo,
        tempTole: prev.permTole
      }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (student) {
        await studentService.update(student.id, formData);
      } else {
        await studentService.create(formData);
      }
      onSave();
    } catch (err) {
      console.error(err);
      alert('Error saving student. Check console for details.');
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
            {student ? 'Edit Student Profile' : 'Register New Student'}
          </h2>
          <button 
            onClick={onClose}
            className="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition"
          >
            <X size={20} />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-8 overflow-y-auto space-y-6">
          {/* General Info */}
          <section>
            <SectionTitle icon={User} title="Academic & General Info" />
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="md:col-span-2">
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Full Name *</label>
                <input type="text" name="fullName" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.fullName} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Class *</label>
                <select name="studentClass" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.studentClass} onChange={handleChange}>
                  <option value="">Select</option>
                  {['Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'].map(c => <option key={c} value={c}>{c}</option>)}
                </select>
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Roll No</label>
                <input type="text" name="rollNo" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.rollNo} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">DOB (Nepali) *</label>
                <input type="text" name="dobNepali" placeholder="YYYY/MM/DD" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.dobNepali} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Gender *</label>
                <select name="gender" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.gender} onChange={handleChange}>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">EMIS No</label>
                <input type="text" name="emisNo" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.emisNo} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Caste</label>
                <input type="text" name="caste" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.caste} onChange={handleChange} />
              </div>
            </div>
          </section>

          {/* Parent Info */}
          <section>
            <SectionTitle icon={Users} title="Parent / Guardian Details" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Father's Name</label>
                <input type="text" name="fatherName" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.fatherName} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Mother's Name</label>
                <input type="text" name="motherName" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.motherName} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Guardian's Name</label>
                <input type="text" name="guardianName" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.guardianName} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Parent's Phone *</label>
                <input type="text" name="parentContact" required className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.parentContact} onChange={handleChange} />
              </div>
            </div>
          </section>

          {/* Addresses */}
          <section>
            <SectionTitle icon={MapPin} title="Permanent Address" />
            <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
              <div className="md:col-span-2">
                <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Province</label>
                <input type="text" name="permProvince" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.permProvince} onChange={handleChange} />
              </div>
              <div className="md:col-span-2">
                <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">District</label>
                <input type="text" name="permDistrict" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.permDistrict} onChange={handleChange} />
              </div>
              <div>
                <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Ward</label>
                <input type="text" name="permWardNo" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.permWardNo} onChange={handleChange} />
              </div>
              <div className="md:col-span-2">
                <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Local Level</label>
                <input type="text" name="permLocalLevel" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.permLocalLevel} onChange={handleChange} />
              </div>
              <div className="md:col-span-3">
                <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Tole / Village</label>
                <input type="text" name="permTole" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.permTole} onChange={handleChange} />
              </div>
            </div>

            <div className="flex items-center gap-2 mt-6 mb-4">
              <input type="checkbox" id="sameAsPerm" className="w-4 h-4 text-indigo-600 rounded" checked={sameAsPerm} onChange={handleSameAsPerm} />
              <label htmlFor="sameAsPerm" className="text-sm font-semibold text-slate-600">Temporary Address same as Permanent</label>
            </div>

            {!sameAsPerm && (
              <div className="grid grid-cols-2 md:grid-cols-5 gap-3 animate-in fade-in duration-300">
                <div className="md:col-span-2">
                  <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Province</label>
                  <input type="text" name="tempProvince" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.tempProvince} onChange={handleChange} />
                </div>
                <div className="md:col-span-2">
                  <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">District</label>
                  <input type="text" name="tempDistrict" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.tempDistrict} onChange={handleChange} />
                </div>
                <div>
                  <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Ward</label>
                  <input type="text" name="tempWardNo" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.tempWardNo} onChange={handleChange} />
                </div>
                <div className="md:col-span-2">
                  <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Local Level</label>
                  <input type="text" name="tempLocalLevel" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.tempLocalLevel} onChange={handleChange} />
                </div>
                <div className="md:col-span-3">
                  <label className="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Tole / Village</label>
                  <input type="text" name="tempTole" className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none transition" value={formData.tempTole} onChange={handleChange} />
                </div>
              </div>
            )}
          </section>

          {/* Additional Info */}
          <section>
            <SectionTitle icon={Info} title="Additional Information" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Scholarship Category</label>
                <select name="scholarshipType" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.scholarshipType} onChange={handleChange}>
                  <option value="None">None</option>
                  <option value="Dalit">Dalit</option>
                  <option value="Marginalised">Marginalised</option>
                  <option value="100% Girl">100% Girl</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-500 mb-1 uppercase">Disability Status</label>
                <select name="disabilityType" className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition" value={formData.disabilityType} onChange={handleChange}>
                  <option value="None">None</option>
                  <option value="Physical">Physical</option>
                  <option value="Mental">Mental</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
          </section>

          <div className="pt-8 pb-4 flex items-center justify-end gap-4 sticky bottom-0 bg-white border-t border-slate-100 -mx-8 px-8 z-10">
            <button type="button" onClick={onClose} className="px-6 py-2.5 text-slate-600 font-bold hover:bg-slate-50 rounded-xl transition">Cancel</button>
            <button type="submit" className="px-10 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
              {student ? 'Update Profile' : 'Register Student'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default StudentForm;
