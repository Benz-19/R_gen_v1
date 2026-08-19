<!-- system-status.html -->
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReconAgent — System Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            accent: '#10b981',
                            cyan: '#06b6d4',
                            amber: '#f59e0b',
                            rose: '#f43f5e'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .bg-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
        .glass-panel {
            background: rgba(10, 10, 10, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        @keyframes pageSlideUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-page-entry {
            animation: pageSlideUp 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-black text-neutral-300 antialiased font-sans min-h-screen bg-grid flex flex-col justify-between overflow-x-hidden">

    <div class="flex-1 flex flex-col opacity-0 animate-page-entry">
        <!-- Header -->
        <header class="border-b border-white/10 bg-black/60 backdrop-blur-xl sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="/" class="w-8 h-8 bg-white text-black font-bold flex items-center justify-center rounded-lg hover:scale-105 transition shrink-0">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </a>
                    <span class="font-bold text-base sm:text-lg text-white tracking-tight shrink-0">ReconAgent</span>
                    <span class="text-[10px] sm:text-xs font-semibold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-brand-accent/10 border border-brand-accent/20 text-brand-accent flex items-center gap-1.5 shrink-0">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-brand-accent animate-pulse"></span> 
                        <span class="hidden xs:inline">Systems Operational</span>
                        <span class="xs:hidden">Operational</span>
                    </span>
                </div>
                
                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    <a href="/" class="text-xs font-medium text-neutral-400 hover:text-white transition px-2.5 sm:px-3 py-1.5 rounded-lg border border-white/10 bg-white/5">
                        <span class="hidden sm:inline">&larr; Back to App</span>
                        <span class="sm:hidden">&larr; App</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-12 space-y-6 sm:space-y-8">
            <!-- Status Banner -->
            <div class="glass-panel p-4 sm:p-6 rounded-2xl border-brand-accent/30 bg-brand-accent/[0.03] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-brand-accent/10 border border-brand-accent/20 flex items-center justify-center text-brand-accent shrink-0">
                        <i data-lucide="check-circle-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight">All Systems Operational</h1>
                        <p class="text-xs text-neutral-400 leading-relaxed">ReconEngine Business Workspace is running normally without interruptions.</p>
                    </div>
                </div>
                <div class="text-[10px] sm:text-xs font-mono text-neutral-500 bg-white/5 px-2.5 sm:px-3 py-1.5 rounded-lg border border-white/5 self-start sm:self-center shrink-0">
                    Uptime (99.98%)
                </div>
            </div>

            <!-- Monitored Services List -->
            <div class="space-y-3 sm:space-y-4">
                <h2 class="font-bold text-white uppercase tracking-wider text-[10px] sm:text-xs text-neutral-400">Core Service Status</h2>
                <div class="glass-panel rounded-2xl overflow-hidden divide-y divide-white/5">
                    
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-start sm:items-center gap-3">
                            <i data-lucide="cpu" class="w-4 h-4 text-brand-cyan shrink-0 mt-0.5 sm:mt-0"></i>
                            <div>
                                <span class="text-xs sm:text-sm font-semibold text-white block">Automated Data Matching Engine</span>
                                <span class="text-[11px] sm:text-xs text-neutral-500 block sm:inline">Transaction reconciliation processing and pattern recognition</span>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-xs font-medium text-brand-accent bg-brand-accent/10 border border-brand-accent/20 px-2.5 py-1 rounded-full self-start sm:self-center shrink-0">Operational</span>
                    </div>

                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-start sm:items-center gap-3">
                            <i data-lucide="link-2" class="w-4 h-4 text-brand-cyan shrink-0 mt-0.5 sm:mt-0"></i>
                            <div>
                                <span class="text-xs sm:text-sm font-semibold text-white block">Direct Bank & ERP Sync Integrations</span>
                                <span class="text-[11px] sm:text-xs text-neutral-500 block sm:inline">Stripe, QuickBooks, Shopify, and Open Banking API pipelines</span>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-xs font-medium text-brand-accent bg-brand-accent/10 border border-brand-accent/20 px-2.5 py-1 rounded-full self-start sm:self-center shrink-0">Operational</span>
                    </div>

                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-start sm:items-center gap-3">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-brand-cyan shrink-0 mt-0.5 sm:mt-0"></i>
                            <div>
                                <span class="text-xs sm:text-sm font-semibold text-white block">File Parser (CSV / Excel)</span>
                                <span class="text-[11px] sm:text-xs text-neutral-500 block sm:inline">Spreadsheet ingestion, header mapping, and data validation</span>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-xs font-medium text-brand-accent bg-brand-accent/10 border border-brand-accent/20 px-2.5 py-1 rounded-full self-start sm:self-center shrink-0">Operational</span>
                    </div>

                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-start sm:items-center gap-3">
                            <i data-lucide="shield-alert" class="w-4 h-4 text-brand-cyan shrink-0 mt-0.5 sm:mt-0"></i>
                            <div>
                                <span class="text-xs sm:text-sm font-semibold text-white block">Discrepancy Detection Agent</span>
                                <span class="text-[11px] sm:text-xs text-neutral-500 block sm:inline">Automated audit rule evaluations and variance flagging</span>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-xs font-medium text-brand-accent bg-brand-accent/10 border border-brand-accent/20 px-2.5 py-1 rounded-full self-start sm:self-center shrink-0">Operational</span>
                    </div>

                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-start sm:items-center gap-3">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 text-brand-cyan shrink-0 mt-0.5 sm:mt-0"></i>
                            <div>
                                <span class="text-xs sm:text-sm font-semibold text-white block">Executive Report Generator</span>
                                <span class="text-[11px] sm:text-xs text-neutral-500 block sm:inline">PDF exports, financial summaries, and audit trail compiling</span>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-xs font-medium text-brand-accent bg-brand-accent/10 border border-brand-accent/20 px-2.5 py-1 rounded-full self-start sm:self-center shrink-0">Operational</span>
                    </div>

                </div>
            </div>

            <!-- Recent Activity Log -->
            <div class="space-y-3 sm:space-y-4">
                <h2 class="font-bold text-white uppercase tracking-wider text-[10px] sm:text-xs text-neutral-400">Past Incidents & Maintenance</h2>
                <div class="glass-panel p-4 sm:p-6 rounded-2xl space-y-4">
                    <div class="border-l-2 border-brand-accent pl-3 sm:pl-4 space-y-1">
                        <span class="text-[11px] sm:text-xs text-neutral-500 font-mono block">Today — All Systems Clear</span>
                        <p class="text-xs text-neutral-300">No downtime or performance degradation reported in the past 24 hours.</p>
                    </div>
                    <div class="border-l-2 border-white/10 pl-3 sm:pl-4 space-y-1">
                        <span class="text-[11px] sm:text-xs text-neutral-500 font-mono block">Completed Maintenance</span>
                        <p class="text-xs text-neutral-300">Routine database optimization and indexing maintenance performed successfully with zero service interruption.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-white/10 bg-black/80 backdrop-blur-xl py-4 sm:py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 text-xs font-mono text-neutral-500 text-center sm:text-left">
                <div class="flex items-center justify-center sm:justify-start gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse shrink-0"></span>
                    <span class="truncate">ReconEngine Business Workspace Active</span>
                </div>
                <p>&copy; 2026 ReconAgent Inc. All rights reserved.</p>
                <div class="flex items-center justify-center gap-3 sm:gap-4 flex-wrap">
                    <a href="/help-center" class="hover:text-white transition">Help Center</a>
                    <a href="/system-status" class="text-white">System Status</a>
                    <a href="/privacy" class="hover:text-white transition">Privacy & Terms</a>
                </div>
            </div>
        </footer>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>