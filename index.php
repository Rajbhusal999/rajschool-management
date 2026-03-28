<?php
// RajSchool Modern Portal
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RajSchool | Integrated Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-5xl w-full grid md:grid-cols-2 gap-8 items-center">
        <!-- Left Column -->
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-xl">
                    <i data-lucide="graduation-cap" class="w-8 h-8"></i>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">RajSchool <span class="text-indigo-600">Pro</span></h1>
            </div>
            
            <h2 class="text-5xl font-extrabold text-slate-900 leading-[1.1]">The future of school <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">management.</span></h2>
            
            <p class="text-slate-600 text-lg max-w-md">
                Experience our high-performance React frontend and Java Spring Boot backend, integrated with your legacy PHP codebase.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <a href="http://localhost:5173" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-bold flex items-center justify-center gap-3 hover:bg-indigo-700 transition-all shadow-lg hover:shadow-indigo-200 group">
                    Enter Modern App
                    <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="/legacy-php/" class="px-8 py-4 bg-white text-slate-800 border border-slate-200 rounded-2xl font-bold flex items-center justify-center gap-3 hover:bg-slate-50 transition-all shadow-sm">
                    Legacy PHP Version
                    <i data-lucide="history" class="w-5 h-5"></i>
                </a>
            </div>
        </div>

        <!-- Right Column (Features Card) -->
        <div class="grid grid-cols-2 gap-4">
            <div class="glass p-6 rounded-3xl space-y-3 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-slate-900">Vite + React</h3>
                <p class="text-slate-500 text-xs">Lightning fast frontend with Tailwind CSS 3.</p>
            </div>
            <div class="glass p-6 rounded-3xl space-y-3 shadow-sm hover:shadow-md transition-shadow translate-y-8">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="database" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-slate-900">Spring Boot</h3>
                <p class="text-slate-500 text-xs">High performance Java API with JPA & MySQL.</p>
            </div>
            <div class="glass p-6 rounded-3xl space-y-3 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="code-2" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-slate-900">Legacy PHP</h3>
                <p class="text-slate-500 text-xs">Integrated access to your original codebase.</p>
            </div>
            <div class="glass p-6 rounded-3xl space-y-3 shadow-sm hover:shadow-md transition-shadow translate-y-8">
                <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-slate-900">Secure</h3>
                <p class="text-slate-500 text-xs">Modern security practices and architecture.</p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
