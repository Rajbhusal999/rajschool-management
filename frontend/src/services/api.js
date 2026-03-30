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
        // Skip photo objects and other non-field data
        if (key === 'studentPhoto' || key === 'teacherPhoto') continue;
        
        // Special case for 'studentClass' -> 'class'
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
        // Special case for 'class' -> 'studentClass'
        if (key === 'class') {
            map['studentClass'] = data[key];
            continue;
        }
        
        const camelKey = key.replace(/([-_][a-z])/ig, ($1) => {
            return $1.toUpperCase()
                .replace('-', '')
                .replace('_', '');
        });
        map[camelKey] = data[key];
    }
    return map;
};

// Enhanced error logger
const handleError = (error, context) => {
    console.error(`[Supabase Error] ${context}:`, error.message, error.details || '');
    throw error;
};

// Alphabetical Re-sequencing for Symbol Numbers
const resequenceClassSymbolNumbers = async (schoolId, className) => {
    if (!className || !schoolId) return;
    try {
        const { data: students, error: fetchError } = await supabase
            .from('students')
            .select('id, full_name')
            .eq('school_id', schoolId)
            .eq('class', className);
        
        if (fetchError) throw fetchError;
        if (!students || students.length === 0) return;

        // Sort alphabetically by full_name (case-insensitive)
        students.sort((a, b) => a.full_name.localeCompare(b.full_name, undefined, { sensitivity: 'base' }));

        const prefix = CLASS_PREFIXES[className] || 'REG';
        const updates = students.map((s, index) => ({
            id: s.id,
            symbol_no: `${prefix}${(index + 1).toString().padStart(3, '0')}`
        }));

        // Bulk update using upsert (expects primary key 'id')
        const { error: updateError } = await supabase.from('students').upsert(updates);
        if (updateError) throw updateError;
        
    } catch (err) {
        console.error(`[Auto-Sequence Error] Class ${className}:`, err);
    }
};

export const studentService = {
    getAll: async (params = {}) => {
        let query = supabase.from('students').select('*');
        
        // Dynamic ID handling
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', schoolId);
        
        // Class Filtering
        const filterClass = params.studentClass || params.class;
        if (filterClass) query = query.eq('class', filterClass);
        
        // Search
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
        
        // 1. Storage Upload
        if (dataSpec.studentPhoto instanceof File) {
            const fileExt = dataSpec.studentPhoto.name.split('.').pop();
            const fileName = `${schoolId}_${Date.now()}.${fileExt}`;
            const { error: uploadError } = await supabase.storage
                .from('students')
                .upload(fileName, dataSpec.studentPhoto);
            
            if (uploadError) handleError(uploadError, 'studentService.storageUpload');
            
            const { data: publicUrlData } = supabase.storage
                .from('students')
                .getPublicUrl(fileName);
            photoUrl = publicUrlData.publicUrl;
        }

        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId });
        if (photoUrl) mappedData.student_photo = photoUrl;
        
        // Add a temporary symbol_no to avoid unique constraint if needed, or just let re-sequencing fill it
        // We actually want a valid insertion first.
        mappedData.symbol_no = `TEMP-${Date.now()}`;

        const { data: insertedData, error } = await supabase.from('students').insert([mappedData]).select();
        if (error) handleError(error, 'studentService.create');
        
        // 2. Trigger Alpha Re-sequencing for the entire class
        await resequenceClassSymbolNumbers(schoolId, className);
        
        return { data: mapToCamelCase(insertedData[0]) };
    },
    
    update: async (id, dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const oldData = await studentService.getById(id); // To check for class shifts
        let photoUrl = null;
        
        if (dataSpec.studentPhoto instanceof File) {
            const fileExt = dataSpec.studentPhoto.name.split('.').pop();
            const fileName = `${schoolId}_${Date.now()}.${fileExt}`;
            const { error: uploadError } = await supabase.storage
                .from('students')
                .upload(fileName, dataSpec.studentPhoto);
            
            if (uploadError) handleError(uploadError, 'studentService.storageUpdate');
            
            const { data: publicUrlData } = supabase.storage
                .from('students')
                .getPublicUrl(fileName);
            photoUrl = publicUrlData.publicUrl;
        }

        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId });
        if (photoUrl) mappedData.student_photo = photoUrl;
        else if (typeof dataSpec.studentPhoto === 'string') {
            mappedData.student_photo = dataSpec.studentPhoto;
        }

        const { data: updatedData, error } = await supabase.from('students').update(mappedData).eq('id', id).select();
        if (error) handleError(error, 'studentService.update');
        
        // Trigger Re-sequencing if name or class changed
        const newClass = dataSpec.studentClass || dataSpec.class;
        const oldClass = oldData?.data?.studentClass;
        
        await resequenceClassSymbolNumbers(schoolId, newClass);
        if (oldClass && oldClass !== newClass) {
            await resequenceClassSymbolNumbers(schoolId, oldClass);
        }

        return { data: mapToCamelCase(updatedData[0]) };
    },
    
    delete: async (id) => {
        const student = await studentService.getById(id);
        const { error } = await supabase.from('students').delete().eq('id', id);
        if (!error && student?.data) {
             await resequenceClassSymbolNumbers(sessionStorage.getItem('institutionId'), student.data.studentClass);
        }
        return { error };
    }
};

export const teacherService = {
    getAll: async (params = {}) => {
        let query = supabase.from('teachers').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', schoolId);
        
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
        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId });
        const { data: insertedData, error } = await supabase.from('teachers').insert([mappedData]).select();
        if (error) handleError(error, 'teacherService.create');
        return { data: mapToCamelCase(insertedData[0]) };
    },
    update: async (id, dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId });
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
        if (schoolId) query = query.eq('school_id', schoolId);
        if (params.date) query = query.eq('attendance_date', params.date);
        if (params.studentClass || params.class) query = query.eq('student_class', params.studentClass || params.class);
        
        const { data, error } = await query;
        if (error) handleError(error, 'attendanceService.get');
        return { data: mapToCamelCase(data) };
    },
    saveBulk: async (dataSpec) => {
        const schoolId = sessionStorage.getItem('institutionId');
        const mappedData = dataSpec.map(item => mapToSnakeCase({ ...item, schoolId }));
        const { error } = await supabase.from('student_attendance').insert(mappedData);
        if (error) handleError(error, 'attendanceService.saveBulk');
        return { error };
    }
};

export const examService = {
    getSubjects: async (params) => {
        let query = supabase.from('subjects').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', schoolId);
        if (params.classGroup || params.class) query = query.eq('class_group', params.classGroup || params.class);
        
        const { data, error } = await query;
        if (error) handleError(error, 'examService.getSubjects');
        return { data: mapToCamelCase(data) };
    },
    saveSubject: async (dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId });
        const { data: insertedData, error } = await supabase.from('subjects').insert([mappedData]).select();
        if (error) handleError(error, 'examService.saveSubject');
        return { data: mapToCamelCase(insertedData[0]) };
    },
    deleteSubject: async (id) => await supabase.from('subjects').delete().eq('id', id),
    getMarks: async (params) => {
        let query = supabase.from('exam_marks').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', schoolId);
        if (params.examType) query = query.eq('exam_type', params.examType);
        if (params.studentClass || params.class) query = query.eq('class', params.studentClass || params.class);
        
        const { data, error } = await query;
        if (error) handleError(error, 'examService.getMarks');
        return { data: mapToCamelCase(data) };
    },
    saveMarksBulk: async (dataSpec) => {
        const schoolId = sessionStorage.getItem('institutionId');
        const mappedData = dataSpec.map(item => mapToSnakeCase({ ...item, schoolId }));
        const { error } = await supabase.from('exam_marks').insert(mappedData);
        if (error) handleError(error, 'examService.saveMarksBulk');
        return { error };
    },
    getLedger: async (params) => {
         return await this.getMarks(params);
    },
    getAttendance: async (params) => {
        let query = supabase.from('exam_attendance').select('*');
        const schoolId = params.schoolId || sessionStorage.getItem('institutionId');
        if (schoolId) query = query.eq('school_id', schoolId);
        
        const { data, error } = await query;
        if (error) handleError(error, 'examService.getAttendance');
        return { data: mapToCamelCase(data) };
    },
    saveAttendance: async (dataSpec) => {
        const schoolId = dataSpec.schoolId || sessionStorage.getItem('institutionId');
        const mappedData = mapToSnakeCase({ ...dataSpec, schoolId });
        const { error } = await supabase.from('exam_attendance').insert([mappedData]);
        if (error) handleError(error, 'examService.saveAttendance');
        return { error };
    }
};

export default supabase;
