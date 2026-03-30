import React, { useState, useEffect } from 'react';
import { examService } from '../services/api';
import { BookOpen, Plus, Edit2, Trash2, Save, X, Info } from 'lucide-react';

const SubjectList = () => {
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(false);
  const [selectedGroup, setSelectedGroup] = useState('1-3');
  const [showModal, setShowModal] = useState(false);
  const [editingSubject, setEditingSubject] = useState(null);
  const [formData, setFormData] = useState({
    subjectName: '',
    subjectCode: '',
    creditHour: '',
    classGroup: '1-3',
    subjectType: 'Compulsory',
    hasCreditHour: true
  });

  const classGroups = ['PG', 'LKG', 'NURSERY', '1-3', '4-5', '6-8', '9-10'];

  useEffect(() => {
    fetchSubjects();
  }, [selectedGroup]);

  const fetchSubjects = async () => {
    setLoading(true);
    try {
      const response = await examService.getSubjects({ schoolId: sessionStorage.getItem('institutionId'), classGroup: selectedGroup });
      setSubjects(response.data);
    } catch (error) {
      console.error('Error fetching subjects:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const payload = {
        ...formData,
        schoolId: sessionStorage.getItem('institutionId'),
        classGroup: selectedGroup,
        id: editingSubject?.id
      };
      await examService.saveSubject(payload);
      setShowModal(false);
      setEditingSubject(null);
      setFormData({ subjectName: '', subjectCode: '', creditHour: '', classGroup: selectedGroup, subjectType: 'Compulsory', hasCreditHour: true });
      fetchSubjects();
    } catch (error) {
      console.error('Error saving subject:', error);
    }
  };

  const handleEdit = (subject) => {
    setEditingSubject(subject);
    setFormData({
      subjectName: subject.subjectName,
      subjectCode: subject.subjectCode || '',
      creditHour: subject.creditHour,
      classGroup: subject.classGroup,
      subjectType: subject.subjectType || 'Compulsory',
      hasCreditHour: subject.hasCreditHour !== undefined ? subject.hasCreditHour : true
    });
    setShowModal(true);
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this subject?')) {
      try {
        await examService.deleteSubject(id);
        fetchSubjects();
      } catch (error) {
        console.error('Error deleting subject:', error);
      }
    }
  };

  const isEarlyYears = ['PG', 'LKG', 'NURSERY'].includes(selectedGroup);

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex justify-between items-center bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <div className="flex items-center gap-4">
          <div className="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
            <BookOpen size={24} />
          </div>
          <div>
            <h1 className="text-2xl font-black text-slate-800 tracking-tight">Curriculum Management</h1>
            <p className="text-slate-500 font-medium text-sm">Design subjects and credit hours</p>
          </div>
        </div>
        <button 
          onClick={() => {
            setEditingSubject(null);
            setFormData({ subjectName: '', subjectCode: '', creditHour: '', classGroup: selectedGroup, subjectType: 'Compulsory', hasCreditHour: true });
            setShowModal(true);
          }}
          className="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100"
        >
          <Plus size={20} />
          Add Subject
        </button>
      </div>

      <div className="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm overflow-x-auto">
        <div className="flex gap-2 p-1 bg-slate-50 rounded-2xl min-w-max">
          {classGroups.map(group => (
            <button
              key={group}
              onClick={() => setSelectedGroup(group)}
              className={`px-6 py-2 rounded-xl font-bold text-sm transition-all ${selectedGroup === group ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'}`}
            >
              {group}
            </button>
          ))}
        </div>
      </div>

      {loading ? (
        <div className="flex justify-center py-12">
          <div className="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {subjects.map(subject => (
            <div key={subject.id} className="group bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-indigo-50/50 transition-all hover:-translate-y-1">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-black text-slate-800">{subject.subjectName}</h3>
                  <div className="flex gap-2 items-center mt-1">
                    <span className="text-[10px] font-black uppercase text-indigo-500 tracking-widest">{subject.subjectCode || 'No Code'}</span>
                    {subject.subjectType === 'Optional' && (
                      <span className="text-[8px] font-black uppercase bg-amber-100 text-amber-600 px-1.5 py-0.5 rounded-md tracking-widest leading-none">Optional</span>
                    )}
                  </div>
                </div>
                <div className="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button onClick={() => handleEdit(subject)} className="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"><Edit2 size={16} /></button>
                  <button onClick={() => handleDelete(subject.id)} className="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all"><Trash2 size={16} /></button>
                </div>
              </div>
              <div className="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl w-fit">
                <Info size={14} className="text-slate-400" />
                <span className="text-xs font-bold text-slate-600">
                  {!subject.hasCreditHour ? 'Credit: Absent' : `${subject.creditHour} ${isEarlyYears ? 'Total Marks' : 'Credit Hours'}`}
                </span>
              </div>
            </div>
          ))}
          {subjects.length === 0 && (
            <div className="col-span-full py-20 bg-white rounded-[40px] border border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
              <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-4">
                <BookOpen size={32} />
              </div>
              <h3 className="text-lg font-bold text-slate-400">No subjects defined for Group {selectedGroup}</h3>
              <p className="text-slate-400 text-sm max-w-xs mt-2">Start building the curriculum by adding subjects to this class group.</p>
            </div>
          )}
        </div>
      )}

      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-200">
          <div className="bg-white w-full max-w-md rounded-[40px] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
            <div className="px-8 pt-8 pb-4 flex justify-between items-center">
              <h2 className="text-2xl font-black text-slate-800">{editingSubject ? 'Edit Subject' : 'Add New Subject'}</h2>
              <button onClick={() => setShowModal(false)} className="p-2 hover:bg-slate-100 rounded-full transition-colors"><X size={20} /></button>
            </div>
            <form onSubmit={handleSubmit} className="p-8 space-y-6">
              <div className="space-y-2">
                <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Subject Name</label>
                <input
                  type="text"
                  name="subjectName"
                  value={formData.subjectName}
                  onChange={handleInputChange}
                  className="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold transition-all"
                  placeholder="e.g. Mathematics"
                  required
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Subject Category</label>
                  <div className="flex gap-3 bg-slate-50 p-1 rounded-2xl">
                    {['Compulsory', 'Optional'].map(type => (
                      <button
                        key={type}
                        type="button"
                        onClick={() => setFormData(prev => ({ ...prev, subjectType: type }))}
                        className={`flex-1 py-3 rounded-xl font-bold text-sm transition-all ${formData.subjectType === type ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'}`}
                      >
                        {type}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="space-y-2">
                  <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Credit Status</label>
                  <div className="flex gap-3 bg-slate-50 p-1 rounded-2xl">
                    {[
                      { label: 'Present', value: true },
                      { label: 'Absent', value: false }
                    ].map(option => (
                      <button
                        key={option.label}
                        type="button"
                        onClick={() => setFormData(prev => ({ ...prev, hasCreditHour: option.value }))}
                        className={`flex-1 py-3 rounded-xl font-bold text-sm transition-all ${formData.hasCreditHour === option.value ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'}`}
                      >
                        {option.label}
                      </button>
                    ))}
                  </div>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Subject Code</label>
                  <input
                    type="text"
                    name="subjectCode"
                    value={formData.subjectCode}
                    onChange={handleInputChange}
                    className="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold transition-all"
                    placeholder="MATH-101"
                  />
                </div>
                {formData.hasCreditHour && (
                  <div className="space-y-2 animate-in slide-in-from-top-2 duration-200">
                    <label className="text-xs font-black uppercase text-slate-400 tracking-wider">{isEarlyYears ? 'Total Marks' : 'Credit Hours'}</label>
                    <input
                      type="number"
                      name="creditHour"
                      value={formData.creditHour}
                      onChange={handleInputChange}
                      step={isEarlyYears ? "1" : "0.5"}
                      min="0"
                      className="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 font-bold transition-all"
                      placeholder="e.g. 4"
                      required
                    />
                  </div>
                )}
              </div>
              <div className="pt-4 flex gap-3">
                <button type="button" onClick={() => setShowModal(false)} className="flex-1 py-4 font-bold text-slate-500 hover:bg-slate-50 rounded-2xl transition-all">Cancel</button>
                <button type="submit" className="flex-[2] py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all">
                  {editingSubject ? 'Update Subject' : 'Save Subject'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default SubjectList;
