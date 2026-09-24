<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Members - ReconAgent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-grid-pattern {
            background-size: 30px 30px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        /* Staggered Kinetic Entrance */
        @keyframes revealCard {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.96);
                filter: blur(4px);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        .animate-reveal {
            opacity: 0;
            animation: revealCard 6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.12s; }
    </style>
</head>
<body class="bg-black text-slate-100 font-sans antialiased bg-grid-pattern min-h-screen">

    <div class="flex flex-col md:flex-row h-screen overflow-hidden">
        
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

        <aside id="sidebar" class="fixed inset-y-0 left-0 z-20 w-64 bg-black/95 md:bg-black/80 border-r border-neutral-800 flex flex-col justify-between shrink-0 backdrop-blur-md -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:static">
            <div>
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

                <x-admin.nav :total_unmatched_discrepancies="$total_unmatched_discrepancies" />
            </div>

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

        <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-10 hidden md:hidden"></div>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
            
            <header class="animate-reveal flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 md:mb-8 pb-4 border-b border-neutral-800">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Reconciliation Operations</h1>
                    <p class="text-xs text-neutral-400 mt-1">Active Environment: <span class="text-white font-medium">{{ $metrics['active_workspace'] ?? 'Production Workspace' }}</span></p>
                </div>
                
            </header>

            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 md:mb-8">
                <div class="animate-reveal delay-1 bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm hover:border-neutral-700 transition-colors">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Active Team Members</p>
                    <p class="text-2xl font-bold mt-2 text-white font-mono">{{ $metrics['total_users'] ?? 1 }}</p>
                </div>
            </section>

            <section class="animate-reveal delay-2 bg-black/40 border border-neutral-800 rounded-xl p-4 sm:p-6 backdrop-blur-sm">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-white">Team Access & Roles</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Manage authorized developers and system operators.</p>
                    </div>
                </div>

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
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-neutral-500 font-mono">No Record Found!</td>
                                </tr>
                            @else
                                @foreach($user_management as $user)
                                    <tr class="hover:bg-white/[0.02] transition-colors">
                                        <td class="p-3 font-medium text-white font-mono">{{ $loop->iteration }}</td>
                                        <td class="p-3 font-medium text-white">
                                            {{ $user->username }} <br> 
                                            <span class="text-neutral-500 text-[11px] font-normal">{{ $user->email }}</span>
                                        </td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] bg-white/10 text-white border border-white/20 font-mono">{{ $user->is_admin ? 'ADMIN' : 'EMPLOYEE' }}</span>
                                        </td>
                                        <td class="p-3 font-medium {{ $user->account_status ? 'text-emerald-400' : 'text-amber-400' }}">
                                            {{ $user->account_status ? 'Active' : 'Inactive' }}
                                        </td>
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