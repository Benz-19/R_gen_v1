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

    <div class="flex flex-col md:flex-row h-screen overflow-hidden">
        
        <!-- Mobile Header Bar -->
        <header class="md:hidden flex items-center justify-between p-4 bg-black/90 border-b border-neutral-800 shrink-0 backdrop-blur-md z-30">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">
                    <svg class="w-5 h-5 text-black" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4L20 8L12 12L4 8L12 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4 12L12 16L20 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4 16L12 20L20 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="text-lg font-bold tracking-tight text-white">ReconAgent</span>
            </div>
            <button id="menu-toggle" class="p-2 text-neutral-400 hover:text-white focus:outline-none">
                <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </header>

        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-20 w-64 bg-black/95 md:bg-black/80 border-r border-neutral-800 flex flex-col justify-between shrink-0 backdrop-blur-md -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:static">
            <div>
                <!-- Desktop Logo Container -->
                <div class="hidden md:block p-6 border-b border-neutral-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-black" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4L20 8L12 12L4 8L12 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 12L12 16L20 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 16L12 20L20 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold tracking-tight text-white">ReconAgent</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 space-y-1 text-sm font-medium pt-20 md:pt-4">
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg bg-white/10 text-white font-semibold">
                        Dashboard <span>{{ $metrics['user_id'] }}</span>
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-neutral-400 hover:bg-neutral-900 hover:text-white transition-colors">
                        Reconciliation Runs
                    </a>
                    <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-neutral-400 hover:bg-neutral-900 hover:text-white transition-colors">
                        <span>Unmatched Discrepancies</span>
                        <span class="px-2 py-0.5 text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 rounded-full font-mono">0</span>
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-neutral-400 hover:bg-neutral-900 hover:text-white transition-colors">
                        Data Sources & APIs
                    </a>
                    <a href="#" class="flex items-center px-3 py-2.5 rounded-lg text-neutral-400 hover:bg-neutral-900 hover:text-white transition-colors">
                        Team Members
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

        <!-- Overlay backdrop for mobile menu -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-10 hidden md:hidden"></div>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
            
            <!-- Page Header Bar -->
            <header class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 md:mb-8 pb-4 border-b border-neutral-800">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Reconciliation Operations</h1>
                    <p class="text-xs text-neutral-400 mt-1">Active Environment: <span class="text-white font-medium">{{ $metrics['active_workspace'] ?? 'Production Workspace' }}</span></p>
                </div>
                
                <div class="flex space-x-3">
                    <button class="w-full sm:w-auto justify-center px-4 py-2 bg-white text-black hover:bg-neutral-200 font-semibold rounded-lg text-xs transition-colors flex items-center space-x-2">
                        <span>+ Run Reconciliation</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </header>

            <!-- Metrics Grid -->
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 md:mb-8">
                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Active Team Members</p>
                    <p class="text-2xl font-bold mt-2 text-white font-mono">{{ $metrics['total_users'] ?? 1 }}</p>
                </div>

                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Reconciled Transactions</p>
                    <p class="text-2xl font-bold mt-2 text-white font-mono">{{ $metrics['total_reconciled'] ?? 0 }}</p>
                </div>

                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Unmatched Discrepancies</p>
                    <p class="text-2xl font-bold mt-2 text-emerald-400 font-mono">{{ $metrics['pending_exceptions'] ?? 0 }}</p>
                </div>

                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Automation Match Rate</p>
                    <p class="text-2xl font-bold mt-2 text-neutral-300 font-mono">100%</p>
                </div>
            </section>

            <!-- Recent Reconciliation Activity -->
            <section class="bg-black/40 border border-neutral-800 rounded-xl p-4 sm:p-6 backdrop-blur-sm mb-6 md:mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-white">Recent Data Processing Runs</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Automated background matching sessions across integrated APIs and datasets.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[500px]">
                        <thead class="bg-neutral-900/60 text-neutral-400 uppercase tracking-wider font-mono border-b border-neutral-800">
                            <tr>
                                <th class="p-3">Run ID</th>
                                <th class="p-3">Source Dataset</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Execution Speed</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 text-neutral-300">
                            <tr>
                                <td class="p-3 font-medium text-white font-mono">#RUN-8921</td>
                                <td class="p-3">API Data Sync & Rest Integration</td>
                                <td class="p-3 text-emerald-400 font-medium">Completed</td>
                                <td class="p-3 font-mono">120ms</td>
                                <td class="p-3 text-right">
                                    <button class="text-neutral-400 hover:text-white transition-colors">View Log</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Workspace Users Section -->
            <section class="bg-black/40 border border-neutral-800 rounded-xl p-4 sm:p-6 backdrop-blur-sm">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-white">Team Access & Roles</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Manage authorized developers and system operators.</p>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[400px]">
                        <thead class="bg-neutral-900/60 text-neutral-400 uppercase tracking-wider font-mono border-b border-neutral-800">
                            <tr>
                                <th class="p-3">S/N</th>
                                <th class="p-3">User</th>
                                <th class="p-3">Role</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 text-neutral-300">
                            @if(blank($user_management))
                                <p>No Record Found!</p>
                            @else
                                @foreach($user_management as $user)
                                    <tr>
                                        <td class="p-3 font-medium text-white">{{$loop->iteration}}</td>
                                        <td class="p-3 font-medium text-white">{{$user->username}} <br> {{$user->email}}</td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] bg-white/10 text-white border border-white/20 font-mono">{{$user->is_admin  ? 'ADMIN' : 'EMPLOYEE'}}</span>
                                        </td>
                                        <td class="p-3 font-medium {{$user->account_status ? 'text-emerald-400' : 'text-amber-400' }}">{{$user->account_status ? 'Active' : 'Inactive'}}</td>
                                        <td class="p-3 text-right">
                                            <button class="text-neutral-400 hover:text-white transition-colors">Manage</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <!-- Mobile Drawer Toggle Script -->
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>