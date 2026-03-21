<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Cloud Storage System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="max-w-5xl mx-auto px-6 py-12">
        <header class="mb-16 text-center">
            <h1 class="text-4xl font-extrabold text-indigo-600 mb-4">Mini Cloud Storage System</h1>
            <p class="text-xl text-gray-600">A high-performance backend system for file management with deduplication and storage quotas.</p>
        </header>

        <main class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Left Column: Users -->
            <section>
                <h2 class="text-2xl font-bold mb-6 flex items-center text-gray-800">
                    <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Sample Users
                </h2>
                <div class="space-y-4">
                    @forelse($users as $user)
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center transition hover:shadow-md">
                            <div>
                                <h3 class="font-semibold text-lg text-gray-900">{{ $user->name }}</h3>
                                <p class="text-gray-500 text-sm">ID: {{ $user->id }} • {{ $user->email }}</p>
                            </div>
                            <div class="text-right">
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Storage Used</span>
                                <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-sm font-medium">
                                    {{ number_format($user->used_storage / 1024 / 1024, 2) }} / 500 MB
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white p-8 rounded-xl border border-dashed border-gray-300 text-center">
                            <p class="text-gray-500 italic">No users found. Run <code>php artisan db:seed</code> to add sample users.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Right Column: API Documentation -->
            <section>
                <h2 class="text-2xl font-bold mb-6 flex items-center text-gray-800">
                    <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    API Quick Guide
                </h2>
                <div class="space-y-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-black rounded tracking-tighter">POST</span>
                            <code class="text-xs text-indigo-600 font-mono font-semibold">/api/users/{id}/files</code>
                        </div>
                        <p class="text-xs text-gray-500">Upload a new file. Enforces 500MB limit and deduplication logic.</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-black rounded tracking-tighter">DELETE</span>
                            <code class="text-xs text-indigo-600 font-mono font-semibold">/api/users/{id}/files/{file_id}</code>
                        </div>
                        <p class="text-xs text-gray-500">Delete a user's file reference and free up their storage quota.</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-black rounded tracking-tighter">GET</span>
                            <code class="text-xs text-indigo-600 font-mono font-semibold">/api/users/{id}/storage-summary</code>
                        </div>
                        <p class="text-xs text-gray-500">Retrieve real-time storage metrics and file counts.</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-black rounded tracking-tighter">GET</span>
                            <code class="text-xs text-indigo-600 font-mono font-semibold">/api/users/{id}/files</code>
                        </div>
                        <p class="text-xs text-gray-500">List all active files belonging to the specified user.</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="mt-20 pt-8 border-t border-gray-200 text-center text-gray-400 text-sm">
            <p>&copy; 2026 Mini Cloud Storage. Designed for reliability and performance.</p>
        </footer>
    </div>
</body>
</html>
