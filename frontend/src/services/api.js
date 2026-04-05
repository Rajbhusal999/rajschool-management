import { supabase } from '../supabaseClient';

const getInstitutionId = () => {
    try {
        const userJson = localStorage.getItem('user');
        const user = userJson ? JSON.parse(userJson) : null;
        return sessionStorage.getItem('institutionId') || localStorage.getItem('institutionId') || localStorage.getItem('schoolId') || user?.id;
    } catch (e) {
        return sessionStorage.getItem('institutionId') || localStorage.getItem('institutionId');
    }
};

const CLASS_PREFIXES = {
    'Nursery': 'NU', 'LKG': 'LK', 'UKG': 'UK', '1': 'ON', '2': 'TW', '3': 'TH', '4': 'FO', '5': 'FI', '6': 'SI', '7': 'SE', '8': 'EI', '9': 'NI', '10': 'TE', '11': 'EL', '12': 'TV'
};

const mapToSnakeCase = (data) => {
    if (!data) return data;
    const map = {};
    for (const key in data) {
        if (['studentPhoto', 'teacherPhoto', 'citizenshipFront', 'citizenshipBack'].includes(key)) continue;
        if (key === 'studentClass') { map['class'] = data[key]; continue; }
        if (key === 'totalDays') { map['total_working_days'] = data[key]; continue; }
        if (key === 'presentDays') { map['days_present'] = data[key]; continue; }
        const snakeKey = key.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);
        map[snakeKey] = data[key];
    }
    return map;
};

const mapToCamelCase = (data) => {
    if (!data) return data;
    if (Array.isArray(data)) return data.map(item => mapToCamelCase(item));
    const map = {};
    for (const key in data) {
        if (key === 'class') { map['studentClass'] = data[key]; continue; }
        if (key === 'total_working_days') { map['totalDays'] = data[key]; continue; }
        if (key === 'days_present') { map['presentDays'] = data[key]; continue; }
        const camelKey = key.replace(/([-_][a-z])/ig, ($1) => $1.toUpperCase().replace('-', '').replace('_', ''));
        map[camelKey] = data[key];
    }
    return map;
};

const handleError = (error, context) => {
    console.error(`[Supabase Error] ${context}:`, error);
    throw error;
};

const resequenceClassSymbolNumbers = async (schoolId, className) => {
    if (!className || !schoolId) return;
    try {
        const { data: students } = await supabase.from('students').select('*').eq('school_id', schoolId).eq('class', className);
        if (!students || students.length === 0) return;
        students.sort((a, b) => a.full_name.localeCompare(b.full_name));
        const prefix = CLASS_PREFIXES[className] || 'REG';
        const finalUpdates = students.map((s, index) => ({ ...s, symbol_no: `${prefix}${(index + 1).toString().padStart(3, '0')}` }));
        await supabase.from('students').upsert(finalUpdates);
    } catch (err) { handleError(err, 'resequence'); }
};

export const institutionService = {
    get: async () => {
        const id = getInstitutionId();
        const { data, error } = await supabase.from('institutions').select('*').eq('id', id).single();
        if (error) handleError(error, 'institution.get');
        return { data: mapToCamelCase(data) };
    },
    update: async (dataSpec) => {
        const id = getInstitutionId();
        const { data, error } = await supabase.from('institutions').update(mapToSnakeCase(dataSpec)).eq('id', id).select();
        if (error) handleError(error, 'institution.update');
        return { data: mapToCamelCase(data?.[0]) };
    }
};

export const studentService = {
    getAll: async (params = {}) => {
        let query = supabase.from('students').select('*');
        const sId = params.schoolId || getInstitutionId();
        if (sId) query = query.eq('school_id', sId);
        const fClass = params.studentClass || params.class;
        if (fClass) query = query.eq('class', fClass);
        const { data, error } = await query;
        if (error) handleError(error, 'student.getAll');
        return { data: mapToCamelCase(data) };
    },
    getById: async (id) => {
        const { data, error } = await supabase.from('students').select('*').eq('id', id).single();
        return { data: mapToCamelCase(data), error };
    },
    create: async (dataSpec) => {
        const sId = getInstitutionId();
        const payload = mapToSnakeCase({ ...dataSpec, schoolId: sId });
        const { data, error } = await supabase.from('students').insert(payload).select();
        if (error) handleError(error, 'student.create');
        
        // Trigger auto-symbol number resequencing for the class
        if (dataSpec.studentClass) {
            await resequenceClassSymbolNumbers(sId, dataSpec.studentClass);
        }
        
        return { data: mapToCamelCase(data?.[0]) };
    },
    update: async (id, dataSpec) => {
        const sId = getInstitutionId();
        const { data, error } = await supabase.from('students').update(mapToSnakeCase(dataSpec)).eq('id', id).select();
        if (error) handleError(error, 'student.update');
        
        // If class changed, resequence both old and new class or just current
        if (dataSpec.studentClass) {
            await resequenceClassSymbolNumbers(sId, dataSpec.studentClass);
        }
        
        return { data: mapToCamelCase(data?.[0]) };
    },
    delete: async (id) => {
        const { error } = await supabase.from('students').delete().eq('id', id);
        if (error) handleError(error, 'student.delete');
        return { success: true };
    }
};

export const teacherService = {
    getAll: async (params = {}) => {
        let query = supabase.from('teachers').select('*');
        const sId = params.schoolId || getInstitutionId();
        if (sId) query = query.eq('school_id', sId);
        const { data, error } = await query;
        if (error) handleError(error, 'teacher.getAll');
        return { data: mapToCamelCase(data) };
    },
    create: async (dataSpec) => {
        const sId = getInstitutionId();
        const payload = mapToSnakeCase({ ...dataSpec, schoolId: sId });
        const { data, error } = await supabase.from('teachers').insert(payload).select();
        if (error) handleError(error, 'teacher.create');
        return { data: mapToCamelCase(data?.[0]) };
    },
    update: async (id, dataSpec) => {
        const { data, error } = await supabase.from('teachers').update(mapToSnakeCase(dataSpec)).eq('id', id).select();
        if (error) handleError(error, 'teacher.update');
        return { data: mapToCamelCase(data?.[0]) };
    },
    delete: async (id) => {
        const { error } = await supabase.from('teachers').delete().eq('id', id);
        if (error) handleError(error, 'teacher.delete');
        return { success: true };
    }
};

export const attendanceService = {
    get: async (params) => {
        let query = supabase.from('student_attendance').select('*').eq('school_id', getInstitutionId());
        if (params.attendanceDate) query = query.eq('attendance_date', params.attendanceDate);
        if (params.datePrefix) query = query.ilike('attendance_date', `${params.datePrefix}%`);
        if (params.session) query = query.eq('session', params.session);
        if (params.studentClass || params.class) query = query.eq('class', params.studentClass || params.class);
        const { data, error } = await query;
        return { data: mapToCamelCase(data) };
    },
    saveBulk: async (dataSpec) => {
        const sId = getInstitutionId();
        const mapped = dataSpec.map(item => mapToSnakeCase({ ...item, schoolId: sId }));
        return await supabase.from('student_attendance').upsert(mapped, { onConflict: 'school_id,student_id,attendance_date,session' });
    }
};

export const notificationService = {
    sendRealSms: async (toNumber, message, forcedConfig = null) => {
        let config = forcedConfig;
        if (!config) {
            const { data: inst } = await institutionService.get();
            config = {
                token: inst?.smsToken,
                provider: inst?.smsProvider,
                identity: inst?.smsIdentity || 'SmartSchool'
            };
        }

        if (!config.token || !config.provider) {
            return { success: false, error: 'Config Missing' };
        }

        let url = '';
        if (config.provider === 'SPARROW') {
            url = `http://api.sparrowsms.com/v2/sms/?token=${config.token}&from=${config.identity}&to=${toNumber}&text=${encodeURIComponent(message)}`;
        } else if (config.provider === 'AAKASH') {
            url = `https://aakashsms.com/admin/public/sms/v3/send/?auth_token=${config.token}&to=${toNumber}&text=${encodeURIComponent(message)}`;
        }

        if (url) {
            try {
                // In a browser environment, fetch will likely hit CORS issues with these APIs.
                // We use 'no-cors' to fire the request, but we won't get a response body.
                await fetch(url, { mode: 'no-cors' });
                return { success: true };
            } catch (err) {
                console.error(`[SMS Gateway] Error sending to ${toNumber}:`, err);
                return { success: false, error: err.message };
            }
        }
        return { success: false };
    },
    logBulk: async (logs) => {
        const sId = getInstitutionId();
        try {
            const { data: inst } = await institutionService.get();
            const config = {
                token: inst?.smsToken,
                provider: inst?.smsProvider,
                identity: inst?.smsIdentity || 'SmartSchool'
            };

            const mapped = logs.map(log => ({ 
                ...mapToSnakeCase(log), 
                school_id: sId 
            }));

            // Trigger real SMS calls in parallel
            if (config.token && config.provider) {
                console.log(`[SMS Gateway] Preparing to send ${logs.length} messages via ${config.provider}...`);
                Promise.allSettled(logs.map(log => {
                    if (log.phoneNumber && log.phoneNumber !== 'N/A') {
                        return notificationService.sendRealSms(log.phoneNumber, log.message, config);
                    }
                    return Promise.resolve();
                })).then(results => {
                    const successCount = results.filter(r => r.status === 'fulfilled' && r.value?.success).length;
                    console.log(`[SMS Gateway] Batch Complete: ${successCount}/${logs.length} triggered successfully.`);
                });
            } else {
                console.warn('[SMS Gateway] Skipping delivery: Configuration (Token/Provider) is missing.');
            }

            return await supabase.from('notification_logs').insert(mapped);
        } catch (err) {
            handleError(err, 'notificationService.logBulk');
        }
    },
    getRecent: async (limit = 20) => {
        const sId = getInstitutionId();
        const { data, error } = await supabase.from('notification_logs').select('*').eq('school_id', sId).order('created_at', { ascending: false }).limit(limit);
        return { data: mapToCamelCase(data), error };
    }
};

export const examService = {
    getSubjects: async (params) => {
        let query = supabase.from('subjects').select('*').eq('school_id', getInstitutionId());
        if (params.classGroup) query = query.eq('class_group', params.classGroup);
        const { data, error } = await query;
        return { data: mapToCamelCase(data) };
    },
    getMarks: async (params) => {
        let query = supabase.from('exam_marks').select('*').eq('school_id', getInstitutionId());
        if (params.studentId) query = query.eq('student_id', params.studentId);
        if (params.examType) query = query.eq('exam_type', params.examType);
        if (params.year) query = query.eq('year', params.year);
        const { data, error } = await query;
        return { data: mapToCamelCase(data) };
    },
    saveMarksBulk: async (payload) => {
        const mapped = payload.map(m => mapToSnakeCase({ ...m, schoolId: getInstitutionId() }));
        return await supabase.from('exam_marks').upsert(mapped, { onConflict: 'school_id,student_id,subject,exam_type,year' });
    },
    getAttendance: async (params) => {
        let query = supabase.from('exam_attendance').select('*').eq('school_id', getInstitutionId());
        if (params.class) query = query.eq('class', params.class);
        if (params.examType) query = query.eq('exam_type', params.examType);
        if (params.year) query = query.eq('year', params.year);
        const { data, error } = await query;
        return { data: mapToCamelCase(data) };
    },
    saveAttendanceBulk: async (payload) => {
        const mapped = payload.map(m => mapToSnakeCase({ ...m, schoolId: getInstitutionId() }));
        return await supabase.from('exam_attendance').upsert(mapped, { onConflict: 'school_id,student_id,exam_type,year' });
    },
    getLedger: async (params) => {
        const sId = params.schoolId || getInstitutionId();
        let query = supabase.from('exam_marks').select('*').eq('school_id', sId);
        if (params.studentClass) query = query.eq('class', params.studentClass);
        if (params.examType) query = query.eq('exam_type', params.examType);
        if (params.year) query = query.eq('year', params.year);
        const { data, error } = await query;
        return { data: mapToCamelCase(data) };
    },
    getSchedule: async (params) => {
        const sId = getInstitutionId();
        const { data, error } = await supabase.from('exam_schedules')
            .select('*')
            .eq('school_id', sId)
            .eq('class', params.class)
            .eq('exam_type', params.examType)
            .eq('year', params.year)
            .single();
        return { data: mapToCamelCase(data) };
    },
    saveSchedule: async (dataSpec) => {
        const sId = getInstitutionId();
        const mapped = mapToSnakeCase({ ...dataSpec, schoolId: sId });
        return await supabase.from('exam_schedules').upsert(mapped, { onConflict: 'school_id,class,exam_type,year' });
    }
};

export const billingService = {
    getReceipts: async (params = {}) => {
        let query = supabase.from('fee_receipts').select('*').eq('institution_id', getInstitutionId());
        if (params.studentName) query = query.ilike('student_name', `%${params.studentName}%`);
        const { data, error } = await query.order('receipt_no', { ascending: false });
        return { data: mapToCamelCase(data), error };
    },
    saveReceipt: async (receipt) => {
        const mapped = mapToSnakeCase({ ...receipt, institutionId: getInstitutionId() });
        return await supabase.from('fee_receipts').insert([mapped]);
    },
    getDonors: async (params = {}) => {
        let query = supabase.from('donor_receipts').select('*').eq('institution_id', getInstitutionId());
        const { data, error } = await query.order('receipt_no', { ascending: false });
        return { data: mapToCamelCase(data), error };
    },
    saveDonorReceipt: async (receipt) => {
        const mapped = mapToSnakeCase({ ...receipt, institutionId: getInstitutionId() });
        return await supabase.from('donor_receipts').insert([mapped]);
    }
};

const api = {
    institutionService,
    studentService,
    teacherService,
    attendanceService,
    notificationService,
    examService,
    billingService
};

export default api;
