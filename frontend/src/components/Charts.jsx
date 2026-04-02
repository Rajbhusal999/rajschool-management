import React from 'react';
import { 
  LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell, Legend
} from 'recharts';

export const ActivityTrendChart = () => {
    const data = [
        { date: '2082-12-02', admissions: 0, exams: 0, billing: 0 },
        { date: '2082-12-03', admissions: 0, exams: 0, billing: 0 },
        { date: '2082-12-04', admissions: 0, exams: 0, billing: 0 },
        { date: '2082-12-05', admissions: 0, exams: -0.2, billing: 0 },
        { date: '2082-12-06', admissions: 0, exams: 0, billing: 0.1 },
        { date: '2082-12-07', admissions: 0.2, exams: 0, billing: 0 },
        { date: '2082-12-08', admissions: 0, exams: 0, billing: 0 },
        { date: '2082-12-09', admissions: 0, exams: 0, billing: 0 },
        { date: '2082-12-10', admissions: 0, exams: 0.1, billing: 0.1 },
        { date: '2082-12-11', admissions: 0, exams: 0, billing: 0 },
        { date: '2082-12-12', admissions: 0.1, exams: 0.2, billing: 0 },
        { date: '2082-12-13', admissions: 0, exams: 0, billing: 0 },
        { date: '2082-12-14', admissions: 0, exams: 0.3, billing: 0 },
        { date: '2082-12-15', admissions: 0, exams: 0, billing: 0.2 },
        { date: '2082-12-16', admissions: 0, exams: 0, billing: 0 },
    ];

    return (
        <ResponsiveContainer width="100%" height={300}>
            <LineChart data={data}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#E2E8F0" />
                <XAxis 
                    dataKey="date" 
                    axisLine={false} 
                    tickLine={false} 
                    tick={{ fontSize: 10, fontWeight: 900, fill: '#64748B' }} 
                    dy={10}
                />
                <YAxis 
                    axisLine={false} 
                    tickLine={false} 
                    tick={{ fontSize: 10, fontWeight: 900, fill: '#64748B' }} 
                />
                <Tooltip 
                    contentStyle={{ borderRadius: '16px', border: 'none', boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)' }}
                />
                <Line type="monotone" dataKey="admissions" stroke="#10B981" strokeWidth={3} dot={{ r: 4, strokeWidth: 2, fill: '#FFFFFF' }} activeDot={{ r: 6 }} />
                <Line type="monotone" dataKey="exams" stroke="#6366F1" strokeWidth={3} dot={{ r: 4, strokeWidth: 2, fill: '#FFFFFF' }} activeDot={{ r: 6 }} />
                <Line type="monotone" dataKey="billing" stroke="#A855F7" strokeWidth={3} dot={{ r: 4, strokeWidth: 2, fill: '#FFFFFF' }} activeDot={{ r: 6 }} />
            </LineChart>
        </ResponsiveContainer>
    );
};

export const CompositionChart = ({ students = 0, staff = 0 }) => {
    const data = [
        { name: 'Students', value: students, color: '#10B981' },
        { name: 'Staff', value: staff, color: '#6366F1' },
    ];

    return (
        <ResponsiveContainer width="100%" height={250}>
            <PieChart>
                <Pie
                    data={data}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={80}
                    paddingAngle={5}
                    dataKey="value"
                >
                    {data.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                </Pie>
                <Tooltip />
                <Legend 
                    verticalAlign="bottom" 
                    align="center" 
                    iconType="circle"
                    formatter={(value) => <span className="text-xs font-black text-slate-600 uppercase tracking-widest">{value}</span>}
                />
            </PieChart>
        </ResponsiveContainer>
    );
};
