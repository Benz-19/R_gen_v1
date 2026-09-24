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

        /* Staggered Spring Reveal */
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
        .delay-3 { animation-delay: 0.18s; }
        .delay-4 { animation-delay: 0.24s; }
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
                    <p class="text-xs text-neutral-400 mt-1">Active Environment: <span class="text-white font-medium">{{ $metrics['active_workspace'] ?? 'default organization' }}</span></p>
                </div>
                
                <div class="flex space-x-3">
                    <button class="w-full sm:w-auto justify-center px-4 py-2 bg-white text-black hover:bg-neutral-200 font-semibold rounded-lg text-xs transition-transform active:scale-95 duration-150 flex items-center space-x-2">
                        <a href="/execute-recon-runs"><span>+ Run Reconciliation</span></a>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </header>

            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 md:mb-8">
                <div class="animate-reveal delay-1 bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm hover:border-neutral-700 transition-colors">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Active Team Members</p>
                    <p class="text-2xl font-bold mt-2 text-white font-mono">{{ $metrics['total_users'] ?? 2 }}</p>
                </div>

                <div class="animate-reveal delay-2 bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm hover:border-neutral-700 transition-colors">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Reconciled Transactions</p>
                    <p class="text-2xl font-bold mt-2 text-white font-mono">{{ $metrics['total_reconciled'] ?? 20 }}</p>
                </div>

                <div class="animate-reveal delay-3 bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm hover:border-neutral-700 transition-colors">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Unmatched Discrepancies</p>
                    <p class="text-2xl font-bold mt-2 text-emerald-400 font-mono">{{ $metrics['pending_exceptions'] ?? 0 }}</p>
                </div>

                <div class="animate-reveal delay-4 bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm hover:border-neutral-700 transition-colors">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Automation Match Rate</p>
                    <p class="text-2xl font-bold mt-2 text-neutral-300 font-mono">100%</p>
                </div>
            </section>

            <section class="animate-reveal delay-4 bg-black/40 border border-neutral-800 rounded-xl p-4 sm:p-6 backdrop-blur-sm">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-white">Recent Data Processing Runs</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Automated background matching sessions across integrated APIs and datasets.</p>
                    </div>
                </div>

                    <section class="animate-reveal delay-1 bg-black/40 border border-neutral-800 rounded-xl p-4 sm:p-6 backdrop-blur-sm">

                    <div class="overflow-x-auto">

                        <table class="w-full text-left text-xs min-w-[500px]">

                            <thead class="bg-neutral-900/60 text-neutral-400 uppercase tracking-wider font-mono border-b border-neutral-800">

                                <tr>

                                    <th class="p-3">
                                        Run ID
                                    </th>

                                    <th class="p-3">
                                        Source Dataset(s)
                                    </th>

                                    <th class="p-3">
                                        Status
                                    </th>

                                    <th class="p-3">
                                        Execution Speed
                                    </th>

                                    <th class="p-3 text-right">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-neutral-800 text-neutral-300">

                                @forelse($runs as $run)

                                    <tr class="hover:bg-white/[0.02] transition-colors">

                                        <td class="p-3 font-medium text-white font-mono">
                                            #RUN-{{ $run->id }}
                                        </td>


                                        @if(!empty($run->source_a_filename) || !empty($run->source_b_filename))

                                            <td class="p-3">
                                                (a.) {{ $run->source_a_filename }}
                                                <br>
                                                (b.) {{ $run->source_b_filename }}
                                            </td>

                                        @else

                                            <td class="p-3"></td>

                                        @endif


                                        <td class="p-3 font-medium {{ strtolower($run->status) === 'completed' ? 'text-emerald-400' : 'text-amber-400' }}">

                                            {{ $run->status }}

                                        </td>


                                        <td class="p-3 font-mono">
                                            {{ $run->execution_speed }}ms
                                        </td>


                                        <td class="p-3 text-right"><a href="/execute-recon-runs">View Log</a></td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="5"
                                            class="p-4 text-center text-neutral-500 font-mono"
                                        >
                                            No processing runs recorded yet.
                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </section>

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