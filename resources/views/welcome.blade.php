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
        .progress-bar { transition: width 0.5s ease-in-out; }
        [v-cloak] { display: none; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-12">
        <header class="mb-12 text-center">
            <h1 class="text-4xl font-extrabold text-indigo-600 mb-2">Mini Cloud Storage</h1>
            <p class="text-gray-500 italic">Select a user to manage their cloud storage.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar: User Selection -->
            <aside class="lg:col-span-1">
                <h2 class="text-xl font-bold mb-4 flex items-center text-gray-800">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Users
                </h2>
                <div class="space-y-3">
                    @forelse($users as $user)
                        <button onclick="selectUser({{ $user->id }}, '{{ $user->name }}')" 
                                id="user-btn-{{ $user->id }}"
                                class="user-selector w-full bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center transition hover:border-indigo-300 hover:shadow-md text-left">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold mr-3">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $user->name }}</h3>
                                <p class="text-gray-400 text-xs">ID: {{ $user->id }}</p>
                            </div>
                        </button>
                    @empty
                        <p class="text-gray-500 text-sm italic">Run seeders to see users.</p>
                    @endforelse
                </div>
            </aside>

            <!-- Main: File Manager -->
            <main class="lg:col-span-2">
                <div id="welcome-message" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center h-full flex flex-col justify-center items-center">
                    <div class="bg-indigo-50 p-4 rounded-full mb-4">
                        <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Ready to Manage Files?</h2>
                    <p class="text-gray-500 max-w-sm">Select a user from the sidebar to view their storage summary, upload new files, or manage existing ones.</p>
                </div>

                <div id="file-manager" class="hidden space-y-6">
                    <!-- Storage Summary Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-end mb-4">
                            <div>
                                <h2 id="current-user-name" class="text-2xl font-bold text-gray-900">User Name</h2>
                                <p class="text-gray-500 text-sm">Cloud Storage Summary</p>
                            </div>
                            <div class="text-right">
                                <span id="storage-text" class="text-sm font-bold text-indigo-600">0 MB / 500 MB</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 mb-2 overflow-hidden">
                            <div id="storage-bar" class="progress-bar bg-indigo-500 h-full w-0"></div>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                            <span>Used: <span id="used-mb">0</span> MB</span>
                            <span>Remaining: <span id="rem-mb">500</span> MB</span>
                        </div>
                    </div>

                    <!-- Upload & Files Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800">Active Files</h3>
                            <form id="upload-form" class="flex items-center space-x-2">
                                <input type="file" id="file-input" class="hidden" onchange="handleFileUpload()">
                                <button type="button" onclick="document.getElementById('file-input').click()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Upload File
                                </button>
                            </form>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                                    <tr>
                                        <th class="px-6 py-4">File Name</th>
                                        <th class="px-6 py-4">Size</th>
                                        <th class="px-6 py-4">Uploaded At</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="file-list" class="divide-y divide-gray-50">
                                    <!-- Files loaded via JS -->
                                </tbody>
                            </table>
                            <div id="no-files" class="p-12 text-center text-gray-400 italic hidden">
                                No files uploaded yet.
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        let currentUserId = null;
        const STORAGE_LIMIT = 524288000; // 500 MB

        async function selectUser(id, name) {
            currentUserId = id;
            
            // UI Updates
            document.querySelectorAll('.user-selector').forEach(el => el.classList.remove('ring-2', 'ring-indigo-500', 'border-indigo-500'));
            document.getElementById(`user-btn-${id}`).classList.add('ring-2', 'ring-indigo-500', 'border-indigo-500');
            
            document.getElementById('welcome-message').classList.add('hidden');
            document.getElementById('file-manager').classList.remove('hidden');
            document.getElementById('current-user-name').innerText = name;

            await refreshData();
        }

        async function refreshData() {
            if (!currentUserId) return;
            await Promise.all([updateSummary(), loadFiles()]);
        }

        async function updateSummary() {
            const response = await fetch(`/api/users/${currentUserId}/storage-summary`);
            const data = await response.json();
            
            const usedMB = (data.total_storage_used / 1024 / 1024).toFixed(2);
            const remMB = (data.remaining_storage / 1024 / 1024).toFixed(2);
            const percentage = (data.total_storage_used / STORAGE_LIMIT * 100).toFixed(1);

            document.getElementById('used-mb').innerText = usedMB;
            document.getElementById('rem-mb').innerText = remMB;
            document.getElementById('storage-text').innerText = `${usedMB} MB / 500 MB`;
            document.getElementById('storage-bar').style.width = `${percentage}%`;
            
            // Change bar color if near limit
            const bar = document.getElementById('storage-bar');
            bar.className = 'progress-bar h-full ' + (percentage > 90 ? 'bg-red-500' : (percentage > 70 ? 'bg-yellow-500' : 'bg-indigo-500'));
        }

        async function loadFiles() {
            const response = await fetch(`/api/users/${currentUserId}/files`);
            const files = await response.json();
            
            const list = document.getElementById('file-list');
            const emptyState = document.getElementById('no-files');
            list.innerHTML = '';

            if (files.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
                files.forEach(file => {
                    const row = `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">${file.name}</td>
                            <td class="px-6 py-4 text-gray-500 text-sm">${(file.size / 1024).toFixed(1)} KB</td>
                            <td class="px-6 py-4 text-gray-400 text-xs">${new Date(file.upload_time).toLocaleString()}</td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="deleteFile(${file.id})" class="text-red-400 hover:text-red-600 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    `;
                    list.insertAdjacentHTML('beforeend', row);
                });
            }
        }

        async function handleFileUpload() {
            const input = document.getElementById('file-input');
            if (!input.files.length) return;

            const formData = new FormData();
            formData.append('file', input.files[0]);

            try {
                const response = await fetch(`/api/users/${currentUserId}/files`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });

                const data = await response.json();
                
                if (!response.ok) {
                    alert(data.error || 'Upload failed');
                } else {
                    await refreshData();
                }
            } catch (e) {
                alert('An error occurred during upload.');
            } finally {
                input.value = ''; // Reset input
            }
        }

        async function deleteFile(fileId) {
            if (!confirm('Are you sure you want to delete this file?')) return;

            try {
                const response = await fetch(`/api/users/${currentUserId}/files/${fileId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    await refreshData();
                } else {
                    alert('Delete failed');
                }
            } catch (e) {
                alert('An error occurred during deletion.');
            }
        }
    </script>
</body>
</html>
