import React, { useState, useEffect } from 'react';
import { studentService } from '../services/api';
import { X, User, Users, MapPin, Info, ChevronRight } from 'lucide-react';
import { nepalAddressData } from '../utils/nepal_address_data';

const SectionTitle = ({ title }) => (
  <div className="pb-4 border-b border-slate-100 mb-6 mt-10 first:mt-0">
    <h3 className="font-extrabold text-slate-800 tracking-tight text-lg">{title}</h3>
  </div>
);

const FormField = ({ label, name, required, placeholder, type = "text", options, disabled, formData, handleChange, errors }) => (
  <div className="space-y-2">
    <label className="block text-sm font-bold text-slate-600 ml-1">
      {label} {required && <span className="text-rose-500">*</span>}
    </label>
    {type === "select" ? (
      <div className="relative group/select">
        <select 
          name={name} 
          required={required}
          disabled={disabled}
          className={`w-full h-12 px-5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-100 outline-none transition font-semibold text-slate-700 appearance-none disabled:opacity-50 disabled:cursor-not-allowed ${errors[name] ? 'border-rose-300 ring-4 ring-rose-50' : ''}`}
          value={formData[name] || ''} 
          onChange={handleChange}
        >
          {options.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
        </select>
        <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover/select:text-indigo-500 transition-colors">
          <ChevronRight size={18} className="rotate-90" />
        </div>
      </div>
    ) : (
      <input 
        type={type} 
        name={name} 
        required={required}
        placeholder={placeholder}
        className={`w-full h-12 px-5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-100 outline-none transition font-semibold text-slate-700 placeholder:text-slate-300 ${errors[name] ? 'border-rose-300 ring-4 ring-rose-50' : ''}`}
        value={formData[name] || ''} 
        onChange={handleChange} 
      />
    )}
    {errors[name] && <p className="text-[10px] font-bold text-rose-500 ml-2 animate-in slide-in-from-top-1 duration-200">{errors[name]}</p>}
  </div>
);

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
    studentPhoto: null,
    schoolId: sessionStorage.getItem('institutionId') || 1
  });

  // Derived Address Options
  const getProvinceData = (provinceName) => nepalAddressData.find(p => p.province === provinceName);
  const getDistrictData = (provinceName, districtName) => {
    const pData = getProvinceData(provinceName);
    return pData ? pData.districts.find(d => d.name === districtName) : null;
  };

  const permProvinceData = getProvinceData(formData.permProvince);
  const permDistricts = permProvinceData ? permProvinceData.districts.map(d => ({ label: d.name, value: d.name })) : [];
  const permDistrictData = getDistrictData(formData.permProvince, formData.permDistrict);
  const permLocalLevels = permDistrictData ? permDistrictData.local_levels.map(l => ({ label: l, value: l })) : [];

  const tempProvinceData = getProvinceData(formData.tempProvince);
  const tempDistricts = tempProvinceData ? tempProvinceData.districts.map(d => ({ label: d.name, value: d.name })) : [];
  const tempDistrictData = getDistrictData(formData.tempProvince, formData.tempDistrict);
  const tempLocalLevels = tempDistrictData ? tempDistrictData.local_levels.map(l => ({ label: l, value: l })) : [];

  const [sameAsPerm, setSameAsPerm] = useState(false);

  useEffect(() => {
    if (student) {
      setFormData(student);
    }
  }, [student]);

  const handleChange = (e) => {
    let { name, value } = e.target;
    
    // Auto-format for dobNepali (YYYY/MM/DD)
    if (name === 'dobNepali') {
        // Remove all non-numeric characters for processing
        const digits = value.replace(/\D/g, '');
        const prevDigits = formData.dobNepali.replace(/\D/g, '');
        
        // If user is adding characters
        if (digits.length > prevDigits.length) {
            if (digits.length <= 4) {
                value = digits;
            } else if (digits.length <= 6) {
                value = `${digits.slice(0, 4)}/${digits.slice(4)}`;
            } else {
                value = `${digits.slice(0, 4)}/${digits.slice(4, 6)}/${digits.slice(6, 8)}`;
            }
        }
    }

    setFormData(prev => {
        const newData = { ...prev, [name]: value };
        
        // Cascading Resets
        if (name === 'permProvince') {
            newData.permDistrict = '';
            newData.permLocalLevel = '';
        } else if (name === 'permDistrict') {
            newData.permLocalLevel = '';
        } else if (name === 'tempProvince') {
            newData.tempDistrict = '';
            newData.tempLocalLevel = '';
        } else if (name === 'tempDistrict') {
            newData.tempLocalLevel = '';
        }

        // Apply "Same as Perm" logic in real-time if enabled
        if (sameAsPerm && name.startsWith('perm')) {
            const tempName = name.replace('perm', 'temp');
            newData[tempName] = newData[name];
        }

        return newData;
    });
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

  const [errors, setErrors] = useState({});

  const validate = () => {
      const newErrors = {};
      if (!formData.fullName) newErrors.fullName = 'Full Name is required';
      if (!formData.studentClass) newErrors.studentClass = 'Class selection is required';
      if (!formData.parentContact) newErrors.parentContact = "Parent's Phone is required";
      if (!formData.dobNepali) newErrors.dobNepali = 'Date of Birth (BS) is required';
      
      // DOB Pattern YYYY/MM/DD
      const dobPattern = /^\d{4}\/\d{2}\/\d{2}$/;
      if (formData.dobNepali && !dobPattern.test(formData.dobNepali)) {
          newErrors.dobNepali = 'Use YYYY/MM/DD format (e.g. 2060/01/15)';
      }

      // Phone Format (Nepal: 10 digits)
      const phonePattern = /^\d{10}$/;
      if (formData.parentContact && !phonePattern.test(formData.parentContact)) {
          newErrors.parentContact = 'Valid 10-digit phone required';
      }

      setErrors(newErrors);
      return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) return;
    
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


  return (
    <div className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4 overflow-y-auto font-['Outfit',sans-serif]">
      <div className="bg-white w-full max-w-4xl rounded-[40px] shadow-2xl p-0 relative flex flex-col max-h-[92vh] animate-in zoom-in-95 duration-300">
        <div className="px-10 py-8 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-20 rounded-t-[40px]">
          <h2 className="text-2xl font-black text-slate-900 tracking-tight">
            {student ? 'Edit Student Profile' : 'Add New Student'}
          </h2>
          <button 
            onClick={onClose}
            className="p-3 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-2xl transition-all"
          >
            <X size={24} strokeWidth={2.5} />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="px-10 py-10 overflow-y-auto space-y-12">
          {/* Basic Info Section */}
          <section>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
              <FormField 
                label="Full Name" 
                name="fullName" 
                required 
                placeholder="Institutional Title" 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="Select Class" 
                name="studentClass" 
                required 
                type="select"
                options={[
                  { label: "-- Select Class --", value: "" },
                  ...['PG', 'Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'].map(c => ({ label: c, value: c }))
                ]}
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="Roll No (Class Roll)" 
                name="rollNo" 
                placeholder="Optional (Auto-filled if empty)" 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="Date of Birth (BS)" 
                name="dobNepali" 
                required 
                placeholder="YYYY/MM/DD" 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="Gender" 
                name="gender" 
                required 
                type="select"
                options={[
                  { label: "Male", value: "Male" },
                  { label: "Female", value: "Female" },
                  { label: "Other", value: "Other" }
                ]}
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="EMIS No (Optional)" 
                name="emisNo" 
                placeholder="EMIS Identifier" 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <div className="space-y-2">
                <label className="block text-sm font-bold text-slate-600 ml-1">Student Photo (Max 2MB, JPG/PNG)</label>
                <div className="relative group">
                    <input 
                      type="file" 
                      className="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-500 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition-all cursor-pointer"
                      accept="image/*"
                      onChange={(e) => {
                          const file = e.target.files[0];
                          if (file && file.size > 2 * 1024 * 1024) {
                              setErrors(prev => ({ ...prev, studentPhoto: 'File must be under 2MB' }));
                          } else {
                              setFormData(prev => ({ ...prev, studentPhoto: file }));
                              setErrors(prev => {
                                  const { studentPhoto: _, ...rest } = prev;
                                  return rest;
                              });
                          }
                      }}
                    />
                </div>
                {errors.studentPhoto && <p className="text-[10px] font-bold text-rose-500 ml-2">{errors.studentPhoto}</p>}
              </div>
              <FormField 
                label="Caste" 
                name="caste" 
                placeholder="Enter Caste" 
                formData={formData} handleChange={handleChange} errors={errors}
              />
            </div>
            <p className="mt-8 text-xs font-bold text-slate-400 italic">
              * Symbol No will be auto-generated sequentially (e.g. HI4101 for Class 4)
            </p>
          </section>

          {/* Parent Info */}
          <section>
            <SectionTitle title="Parent / Guardian Details" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
              <FormField label="Father's Name" name="fatherName" placeholder="Paternal Name" formData={formData} handleChange={handleChange} errors={errors} />
              <FormField label="Mother's Name" name="motherName" placeholder="Maternal Name" formData={formData} handleChange={handleChange} errors={errors} />
              <FormField label="Guardian's Name" name="guardianName" placeholder="Legal Guardian" formData={formData} handleChange={handleChange} errors={errors} />
              <FormField label="Parent's Phone" name="parentContact" required placeholder="Primary Contact" formData={formData} handleChange={handleChange} errors={errors} />
              <FormField label="Guardian's Phone" name="guardianContact" placeholder="Alternate Contact" formData={formData} handleChange={handleChange} errors={errors} />
              <FormField label="Guardian's Email" name="guardianEmail" placeholder="Optional" formData={formData} handleChange={handleChange} errors={errors} />
            </div>
          </section>

          {/* Permanent Address */}
          <section>
            <SectionTitle title="Permanent Address" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
              <FormField 
                label="Province" 
                name="permProvince" 
                type="select" 
                options={[
                  { label: "-- Select Province --", value: "" },
                  ...nepalAddressData.map(p => ({ label: p.province, value: p.province }))
                ]} 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="District" 
                name="permDistrict" 
                type="select" 
                disabled={!formData.permProvince}
                options={[
                  { label: formData.permProvince ? "-- Select District --" : "-- Choose Province First --", value: "" },
                  ...permDistricts
                ]} 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="Local Level" 
                name="permLocalLevel" 
                type="select" 
                disabled={!formData.permDistrict}
                options={[
                  { label: formData.permDistrict ? "-- Select Local Level --" : "-- Choose District First --", value: "" },
                  ...permLocalLevels
                ]} 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField label="Ward No" name="permWardNo" placeholder="e.g. 04" formData={formData} handleChange={handleChange} errors={errors} />
              <FormField label="Tole" name="permTole" placeholder="Neighborhood Name" formData={formData} handleChange={handleChange} errors={errors} />
            </div>
          </section>

          {/* Temporary Address */}
          <section>
            <div className="flex items-center justify-between mb-6">
                <SectionTitle title="Temporary Address" />
                <label className="flex items-center gap-3 cursor-pointer group mt-6 pr-2">
                    <input type="checkbox" className="peer sr-only" checked={sameAsPerm} onChange={handleSameAsPerm} />
                    <div className="w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-indigo-500 transition-all peer-checked:bg-indigo-600 peer-checked:border-indigo-600 relative">
                        <div className="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                            <div className="w-3 h-1.5 border-l-2 border-b-2 border-white -rotate-45 mb-0.5"></div>
                        </div>
                    </div>
                    <span className="text-sm font-black text-slate-500 group-hover:text-indigo-600 transition-colors">Same as Permanent Address</span>
                </label>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8 animate-in slide-in-from-top-4 duration-300">
              <FormField 
                label="Province" 
                name="tempProvince" 
                type="select" 
                disabled={sameAsPerm}
                options={[
                  { label: "-- Select Province --", value: "" },
                  ...nepalAddressData.map(p => ({ label: p.province, value: p.province }))
                ]} 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="District" 
                name="tempDistrict" 
                type="select" 
                disabled={sameAsPerm || !formData.tempProvince}
                options={[
                  { label: formData.tempProvince ? "-- Select District --" : "-- Choose Province First --", value: "" },
                  ...tempDistricts
                ]} 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="Local Level" 
                name="tempLocalLevel" 
                type="select" 
                disabled={sameAsPerm || !formData.tempDistrict}
                options={[
                  { label: formData.tempDistrict ? "-- Select Local Level --" : "-- Choose District First --", value: "" },
                  ...tempLocalLevels
                ]} 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField label="Ward No" name="tempWardNo" placeholder="e.g. 02" disabled={sameAsPerm} formData={formData} handleChange={handleChange} errors={errors} />
              <FormField label="Tole" name="tempTole" placeholder="Neighborhood Name" disabled={sameAsPerm} formData={formData} handleChange={handleChange} errors={errors} />
            </div>
          </section>

          {/* Additional Info */}
          <section>
            <SectionTitle title="Additional Info" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
              <FormField 
                label="Scholarship Type" 
                name="scholarshipType" 
                type="select" 
                options={[
                  { label: "None", value: "None" },
                  { label: "Dalit", value: "Dalit" },
                  { label: "Marginalised", value: "Marginalised" },
                  { label: "Other", value: "Other" }
                ]} 
                formData={formData} handleChange={handleChange} errors={errors}
              />
              <FormField 
                label="Disability" 
                name="disabilityType" 
                type="select" 
                options={[
                  { label: "None", value: "None" },
                  { label: "Physical", value: "Physical" },
                  { label: "Mental", value: "Mental" },
                  { label: "Other", value: "Other" }
                ]} 
                formData={formData} handleChange={handleChange} errors={errors}
              />
            </div>
          </section>

          {/* Form Actions */}
          <div className="pt-10 pb-4 flex items-center justify-end gap-6 sticky bottom-0 bg-white border-t border-slate-100 -mx-10 px-10 z-20">
            <button 
              type="button" 
              onClick={onClose} 
              className="flex-1 h-14 bg-slate-100 hover:bg-slate-200 text-slate-800 font-black text-sm uppercase tracking-widest rounded-2xl transition-all max-w-[240px]"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              className="flex-1 h-14 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm uppercase tracking-widest rounded-2xl transition-all shadow-xl shadow-indigo-100 max-w-[320px]"
            >
              {student ? 'Update Changes' : 'Save Student'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default StudentForm;
