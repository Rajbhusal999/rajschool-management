import { supabase } from '../supabaseClient';

// Class Prefix Mapping for Symbol Numbers
const CLASS_PREFIXES = {
    'Nursery': 'NU',
    'LKG': 'LK',
    'UKG': 'UK',
    '1': 'ON',
    '2': 'TW',
    '3': 'TH',
    '4': 'FO',
    '5': 'FI',
    '6': 'SI',
    '7': 'SE',
    '8': 'EI',
    '9': 'NI',
    '10': 'TE',
    '11': 'EL',
    '12': 'TV'
};

// Helper to map camelCase to snake_case for Supabase insertion/update
const mapToSnakeCase = (data) => {
    if (!data) return data;
    const map = {};
    for (const key in data) {
        if (key === 'studentPhoto' || key === 'teacherPhoto') continue;
        if (key === 'studentClass') {
            map['class'] = data[key];
            continue;
        }
        const snakeKey = key.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);
        map[snakeKey] = data[key];
    }
    return map;
};

// Helper to map snake_case to camelCase for frontend consumption
const mapToCamelCase = (data) => {
    if (!data) return data;
    if (Array.isArray(data)) return data.map(item => mapToCamelCase(item));
    const map = {};
    for (const key in data) {
        if (key === 'class') {
            map['studentClass'] = data[key];
            continue;
        }
        const camelKey = key.replace(/([-_][a-z])/ig, ($1) => {
            return $1.toUpperCase().replace('-', '').replace('_', '');
        });
        map[camelKey] = data[key];
    }
    return map;
};

const handleError = (error, context) => {
    const msg = `[Supabase Error] ${context}: ${error.message}`;
    console.error(msg, error.details || '');
    // Alert the user so they can report the exact database error
    if (typeof window !== 'undefined') {
        window.alert(msg);
    }
    throw error;
};

/**
 * Re-sequences all students in a class alphabetically by name.
 * Uses a two-stage process to avoid unique constraint violations.
 */
const resequenceClassSymbolNumbers = async (schoolId, className) => {
    if (!className || !schoolId) return;
    const sId = Number(schoolId);
    
    try {
        // 1. Fetch current class members
        const { data: students, error: fetchError } = await supabase
            .from('students')
            .select('id, full_name')
            .eq('school_id', sId)
            .eq('class', className);
        
        if (fetchError) throw fetchError;
        if (!students || students.length === 0) return;

        // 2. Sort by name
        students.sort((a, b) => a.full_name.localeCompare(b.full_name, undefined, { sensitivity: 'base' }));

        const prefix = CLASS_PREFIXES[className] || 'REG';
        
        // 3. Stage 1: Clear existing symbol_nos in the class to a temporary unique value
        // Use a shorter string (CLR-) to avoid possible varchar(20) or similar limits
        const clearUpdates = students.map(s => ({
            id: s.id,
            symbol_no: `CLR-${s.id}-${Date.now().toString().slice(-6)}`
        }));
        
        const { error: clearError } = await supabase.from('students').upsert(clearUpdates);
        if (clearError) throw clearError;

        // 4. Stage 2: Assign final sequential numbers
        const finalUpdates = students.map((s, index) => ({
            id: s.id,
            symbol_no: `${prefix}${(index + 1).toString().padStart(3, '0')}`
        }));

        const { error: finalError } = await supabase.from('students').upsert(finalUpdates);
        if (finalError) throw finalError;

        console.log(`[Re-sequencing Complete] Class ${className}: ${students.length} students re-indexed.`);
        
    } catch (err) {
        handleError(err, `resequenceClassSymbolNumbers(${className})`);
    }
};

export const studentService = {
    getAll: async (params = {}) => {
        let query = supabase.from('students').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', Number(schoolId));
        const filterClass = params.studentClass || params.class;
        if (filterClass) query = query.eq('class', filterClass);
        if (params.search) {
            query = query.or(`full_name.ilike.%${params.search}%,symbol_no.ilike.%${params.search}%`);
        }
        const { data, error } = await query;
        if (error) handleError(error, 'studentService.getAll');
        return { data: mapToCamelCase(data) };
    },
    
    getById: async (id) => {
        const { data, error } = await supabase.from('students').select('*').eq('id', id).single();
        if (error) handleError(error, 'studentService.getById');
        return { data: mapToCamelCase(data) };
    },
    
    create: async (dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const className = dataSpec.studentClass || dataSpec.class;
        let photoUrl = null;
        
        if (dataSpec.studentPhoto instanceof File) {
            const fileExt = dataSpec.studentPhoto.name.split('.').pop();
            const fileName = `${schoolId}_${Date.now()}.${fileExt}`;
            const { error: uploadError } = await supabase.storage.from('students').upload(fileName, dataSpec.studentPhoto);
            if (uploadError) handleError(uploadError, 'studentService.storageUpload');
            const { data: publicUrlData } = supabase.storage.from('students').getPublicUrl(fileName);
            photoUrl = publicUrlData.publicUrl;
        }

        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId: Number(schoolId) });
        if (photoUrl) mappedData.student_photo = photoUrl;
        
        // Temporary symbol_no to satisfy unique constraint before re-sequencing
        mappedData.symbol_no = `TEMP-${Date.now()}`;

        const { data: insertedData, error } = await supabase.from('students').insert([mappedData]).select();
        if (error) handleError(error, 'studentService.create');
        
        // WAIT for re-sequencing to finish
        await resequenceClassSymbolNumbers(schoolId, className);
        
        // RE-FETCH the newly created student to return the finalized symbol_no
        const finalStudent = await studentService.getById(insertedData[0].id);
        return finalStudent;
    },
    
    update: async (id, dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const oldData = await studentService.getById(id);
        let photoUrl = null;
        
        if (dataSpec.studentPhoto instanceof File) {
            const fileExt = dataSpec.studentPhoto.name.split('.').pop();
            const fileName = `${schoolId}_${Date.now()}.${fileExt}`;
            const { error: uploadError } = await supabase.storage.from('students').upload(fileName, dataSpec.studentPhoto);
            if (uploadError) handleError(uploadError, 'studentService.storageUpdate');
            const { data: publicUrlData } = supabase.storage.from('students').getPublicUrl(fileName);
            photoUrl = publicUrlData.publicUrl;
        }

        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId: Number(schoolId) });
        if (photoUrl) mappedData.student_photo = photoUrl;
        else if (typeof dataSpec.studentPhoto === 'string') {
            mappedData.student_photo = dataSpec.studentPhoto;
        }

        const { error } = await supabase.from('students').update(mappedData).eq('id', id);
        if (error) handleError(error, 'studentService.update');
        
        const newClass = dataSpec.studentClass || dataSpec.class;
        const oldClass = oldData?.data?.studentClass;
        
        await resequenceClassSymbolNumbers(schoolId, newClass);
        if (oldClass && oldClass !== newClass) {
            await resequenceClassSymbolNumbers(schoolId, oldClass);
        }

        return await studentService.getById(id);
    },
    
    delete: async (id) => {
        const student = await studentService.getById(id);
        const { error } = await supabase.from('students').delete().eq('id', id);
        if (!error && student?.data) {
             await resequenceClassSymbolNumbers(sessionStorage.getItem('institutionId'), student.data.studentClass);
        }
        return { error };
    },
    
    resequenceClass: async (className) => {
        const schoolId = sessionStorage.getItem('institutionId');
        if (!schoolId || !className) return { error: 'Missing requirements' };
        await resequenceClassSymbolNumbers(schoolId, className);
        return { success: true };
    }
};

export const teacherService = {
    getAll: async (params = {}) => {
        let query = supabase.from('teachers').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', Number(schoolId));
        const { data, error } = await query;
        if (error) handleError(error, 'teacherService.getAll');
        return { data: mapToCamelCase(data) };
    },
    getById: async (id) => {
        const { data, error } = await supabase.from('teachers').select('*').eq('id', id).single();
        if (error) handleError(error, 'teacherService.getById');
        return { data: mapToCamelCase(data) };
    },
    create: async (dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId: Number(schoolId) });
        const { data: insertedData, error } = await supabase.from('teachers').insert([mappedData]).select();
        if (error) handleError(error, 'teacherService.create');
        return { data: mapToCamelCase(insertedData[0]) };
    },
    update: async (id, dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId: Number(schoolId) });
        const { data: updatedData, error } = await supabase.from('teachers').update(mappedData).eq('id', id).select();
        if (error) handleError(error, 'teacherService.update');
        return { data: mapToCamelCase(updatedData[0]) };
    },
    delete: async (id) => await supabase.from('teachers').delete().eq('id', id)
};

export const attendanceService = {
    get: async (params) => {
        let query = supabase.from('student_attendance').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', Number(schoolId));
        if (params.date) query = query.eq('attendance_date', params.date);
        if (params.studentClass || params.class) query = query.eq('student_class', params.studentClass || params.class);
        const { data, error } = await query;
        if (error) handleError(error, 'attendanceService.get');
        return { data: mapToCamelCase(data) };
    },
    saveBulk: async (dataSpec) => {
        const schoolId = sessionStorage.getItem('institutionId');
        const mappedData = dataSpec.map(item => mapToSnakeCase({ ...item, schoolId: Number(schoolId) }));
        const { error } = await supabase.from('student_attendance').insert(mappedData);
        if (error) handleError(error, 'attendanceService.saveBulk');
        return { error };
    }
};

export const examService = {
    getSubjects: async (params) => {
        let query = supabase.from('subjects').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', Number(schoolId));
        if (params.classGroup || params.class) query = query.eq('class_group', params.classGroup || params.class);
        const { data, error } = await query;
        if (error) handleError(error, 'examService.getSubjects');
        return { data: mapToCamelCase(data) };
    },
    saveSubject: async (dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId: Number(schoolId) });
        const { data: insertedData, error } = await supabase.from('subjects').insert([mappedData]).select();
        if (error) handleError(error, 'examService.saveSubject');
        return { data: mapToCamelCase(insertedData[0]) };
    },
    deleteSubject: async (id) => await supabase.from('subjects').delete().eq('id', id),
    getMarks: async (params) => {
        let query = supabase.from('exam_marks').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', Number(schoolId));
        if (params.examType) query = query.eq('exam_type', params.examType);
        if (params.studentClass || params.class) query = query.eq('class', params.studentClass || params.class);
        const { data, error } = await query;
        if (error) handleError(error, 'examService.getMarks');
        return { data: mapToCamelCase(data) };
    },
    saveMarksBulk: async (dataSpec) => {
        const schoolId = sessionStorage.getItem('institutionId');
        const mappedData = dataSpec.map(item => mapToSnakeCase({ ...item, schoolId: Number(schoolId) }));
        const { error } = await supabase.from('exam_marks').insert(mappedData);
        if (error) handleError(error, 'examService.saveMarksBulk');
        return { error };
    },
    getLedger: async (params) => {
         return await examService.getMarks(params);
    },
    getAttendance: async (params) => {
        let query = supabase.from('exam_attendance').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', Number(schoolId));
        const { data, error } = await query;
        if (error) handleError(error, 'examService.getAttendance');
        return { data: mapToCamelCase(data) };
    },
    saveAttendance: async (dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId: Number(schoolId) });
        const { error } = await supabase.from('exam_attendance').insert([mappedData]);
        if (error) handleError(error, 'examService.saveAttendance');
        return { error };
    }
};

export default supabase;