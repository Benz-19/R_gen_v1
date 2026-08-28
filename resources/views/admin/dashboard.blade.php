<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ReconAgent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-grid-pattern {
            background-size: 30px 30px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="bg-black text-slate-100 font-sans antialiased bg-grid-pattern min-h-screen">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-black/80 border-r border-neutral-800 flex flex-col justify-between shrink-0 backdrop-blur-md">
            <div>
                <div class="p-6 border-b border-neutral-800">
                    <div class="flex items-center gap-3">
                        <!-- ReconAgent Logo -->
                        <div class="w-8 h-8 rounded-lg bg-neutral-900 border border-neutral-700 flex items-center justify-center">
                            <svg
                                class="w-5 h-5 text-white"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <!-- Top layer -->
                                <path
                                    d="M12 4L18 7.5L12 11L6 7.5L12 4Z"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linejoin="round"
                                />

                                <!-- Middle layer -->
                                <path
                                    d="M6 10.5L12 14L18 10.5"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <!-- Bottom layer -->
                                <path
                                    d="M6 14L12 17.5L18 14"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <!-- Bottom connection -->
                                <path
                                    d="M6 17.5L12 21L18 17.5"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <!-- Wordmark -->
                        <span class="text-lg font-bold tracking-tight text-white">
                            ReconAgent
                        </span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1 text-sm font-medium">
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg bg-white/10 text-white font-semibold">
                        Overview
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-neutral-400 hover:bg-neutral-900 hover:text-white transition-colors">
                        Reconciliation Runs
                    </a>
                    <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-neutral-400 hover:bg-neutral-900 hover:text-white transition-colors">
                        <span>Exceptions Queue</span>
                        <span class="px-2 py-0.5 text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 rounded-full font-mono">0</span>
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-neutral-400 hover:bg-neutral-900 hover:text-white transition-colors">
                        User Management
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-neutral-400 hover:bg-neutral-900 hover:text-white transition-colors">
                        Audit Trail
                    </a>
                </nav>
            </div>

            <!-- User Session Info -->
            <div class="p-4 border-t border-neutral-800 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-white">System Administrator</p>
                    <p class="text-[10px] text-neutral-500">Admin Role</p>
                </div>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-neutral-400 hover:text-white font-medium transition-colors">Logout</button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-8">
            
            <!-- Header Bar -->
            <header class="flex justify-between items-center mb-8 pb-4 border-b border-neutral-800">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Admin Dashboard</h1>
                    <p class="text-xs text-neutral-400 mt-1">Tenant Workspace: <span class="text-white font-medium">{{ $metrics['active_workspace'] ?? 'Organization Workspace' }}</span></p>
                </div>
                
                <div class="flex space-x-3">
                    <button class="px-4 py-2 bg-white text-black hover:bg-neutral-200 font-semibold rounded-lg text-xs transition-colors flex items-center space-x-2">
                        <span>+ New Import Session</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </header>

            <!-- Metrics Grid -->
            <section class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl font-bold mt-2 text-white font-mono">{{ $metrics['total_users'] ?? 1 }}</p>
                </div>

                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Matched Records</p>
                    <p class="text-2xl font-bold mt-2 text-white font-mono">{{ $metrics['total_reconciled'] ?? 0 }}</p>
                </div>

                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Open Exceptions</p>
                    <p class="text-2xl font-bold mt-2 text-emerald-400 font-mono">{{ $metrics['pending_exceptions'] ?? 0 }}</p>
                </div>

                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">System Audit Events</p>
                    <p class="text-2xl font-bold mt-2 text-neutral-300 font-mono">0</p>
                </div>
            </section>

            <!-- Workspace Users Section -->
            <section class="bg-black/40 border border-neutral-800 rounded-xl p-6 backdrop-blur-sm">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-white">Organization Members</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Manage user access and authorization roles within this tenant.</p>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-neutral-900/60 text-neutral-400 uppercase tracking-wider font-mono border-b border-neutral-800">
                            <tr>
                                <th class="p-3">User</th>
                                <th class="p-3">Role</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 text-neutral-300">
                            <tr>
                                <td class="p-3 font-medium text-white">Admin Account</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-white/10 text-white border border-white/20 font-mono">ADMIN</span>
                                </td>
                                <td class="p-3 text-emerald-400 font-medium">Active</td>
                                <td class="p-3 text-right">
                                    <button class="text-neutral-400 hover:text-white transition-colors">Manage</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

</body>
</html>