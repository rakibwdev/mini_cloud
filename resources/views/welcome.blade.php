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
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-12">
        <header class="mb-12 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-extrabold text-indigo-600">Mini Cloud Storage</h1>
                <p class="text-gray-500 text-sm">Secure personal file management</p>
            </div>
            <div id="auth-nav">
                @auth
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-gray-700">Hi, {{ auth()->user()->name }}</span>
                        <button onclick="logout()" class="text-sm text-red-600 hover:text-red-800 font-semibold">Logout</button>
                    </div>
                @else
                    <div class="space-x-4">
                        <button onclick="showAuth('login')" class="text-sm font-semibold text-gray-600 hover:text-indigo-600">Login</button>
                        <button onclick="showAuth('register')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition">Register</button>
                    </div>
                @endauth
            </div>
        </header>

        <main>
            <!-- Auth Forms -->
            <div id="auth-section" class="@auth hidden @endauth max-w-md mx-auto mt-20">
                <!-- Login Form -->
                <div id="login-form" class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
                    <form onsubmit="handleAuth(event, 'login')" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Email</label>
                            <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-bold hover:bg-indigo-700 transition">Login</button>
                    </form>
                    <p class="mt-4 text-center text-sm text-gray-500">Don't have an account? <button onclick="showAuth('register')" class="text-indigo-600 font-semibold">Register</button></p>
                </div>

                <!-- Register Form -->
                <div id="register-form" class="hidden bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-2xl font-bold mb-6 text-center">Create Account</h2>
                    <form onsubmit="handleAuth(event, 'register')" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Name</label>
                            <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Email</label>
                            <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-bold hover:bg-indigo-700 transition">Register</button>
                    </form>
                    <p class="mt-4 text-center text-sm text-gray-500">Already have an account? <button onclick="showAuth('login')" class="text-indigo-600 font-semibold">Login</button></p>
                </div>
            </div>

            <!-- File Manager -->
            <div id="file-manager" class="@guest hidden @endguest space-y-6 max-w-4xl mx-auto">
                <!-- Storage Summary Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Your Storage</h2>
                            <p class="text-gray-500 text-sm">Manage your private cloud space</p>
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
                        <h3 class="font-bold text-gray-800">My Files</h3>
                        <form id="upload-form" class="flex items-center space-x-2">
                            <input type="file" id="file-input" class="hidden" onchange="handleFileUpload()">
                            <button type="button" onclick="document.getElementById('file-input').click()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Upload
                            </button>
                        </form>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">File Name</th>
                                    <th class="px-6 py-4">Size</th>
                                    <th class="px-6 py-4">Uploaded</th>
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

    <script>
        const STORAGE_LIMIT = 524288000;

        function showAuth(type) {
            document.getElementById('login-form').classList.toggle('hidden', type !== 'login');
            document.getElementById('register-form').classList.toggle('hidden', type !== 'register');
        }

        async function handleAuth(event, type) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(`/api/${type}`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (response.ok) {
                    location.reload();
                } else {
                    alert(result.message || 'Authentication failed');
                }
            } catch (e) {
                alert('An error occurred.');
            }
        }

        async function logout() {
            try {
                const response = await fetch('/api/logout', { 
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) location.reload();
            } catch (e) {
                alert('Logout failed');
            }
        }

        @auth
        // Initialization for authenticated users
        window.addEventListener('DOMContentLoaded', () => {
            refreshData();
        });

        async function refreshData() {
            await Promise.all([updateSummary(), loadFiles()]);
        }

        async function updateSummary() {
            const response = await fetch('/api/storage-summary');
            if (!response.ok) return;
            const data = await response.json();
            
            const usedMB = (data.total_storage_used / 1024 / 1024).toFixed(2);
            const remMB = (data.remaining_storage / 1024 / 1024).toFixed(2);
            const percentage = (data.total_storage_used / STORAGE_LIMIT * 100).toFixed(1);

            document.getElementById('used-mb').innerText = usedMB;
            document.getElementById('rem-mb').innerText = remMB;
            document.getElementById('storage-text').innerText = `${usedMB} MB / 500 MB`;
            document.getElementById('storage-bar').style.width = `${percentage}%`;
            
            const bar = document.getElementById('storage-bar');
            bar.className = 'progress-bar h-full ' + (percentage > 90 ? 'bg-red-500' : (percentage > 70 ? 'bg-yellow-500' : 'bg-indigo-500'));
        }

        async function loadFiles() {
            const response = await fetch('/api/files');
            if (!response.ok) return;
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
                            <td class="px-6 py-4 text-gray-400 text-xs">${new Date(file.upload_time).toLocaleDateString()}</td>
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
                const response = await fetch('/api/files', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });

                const data = await response.json();
                if (!response.ok) {
                    alert(data.error || data.message || 'Upload failed');
                } else {
                    await refreshData();
                }
            } catch (e) {
                alert('An error occurred during upload.');
            } finally {
                input.value = '';
            }
        }

        async function deleteFile(fileId) {
            if (!confirm('Are you sure you want to delete this file?')) return;

            try {
                const response = await fetch(`/api/files/${fileId}`, {
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
        @endauth
    </script>
</body>
</html>
