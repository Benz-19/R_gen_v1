<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Execute Reconciliation - ReconAgent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-grid-pattern {
            background-size: 30px 30px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }

        ::-webkit-calendar-picker-indicator {
            filter: invert(0.8);
            cursor: pointer;
        }

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

        @keyframes toastIn {
            0% { opacity: 0; transform: translateY(-20px) scale(0.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes toastOut {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-20px) scale(0.95); }
        }

        .toast-enter {
            animation: toastIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .toast-exit {
            animation: toastOut 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-black text-slate-100 font-sans antialiased bg-grid-pattern min-h-screen">

    <!-- Dynamic Toast Notification Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none"></div>

    <div class="flex flex-col md:flex-row h-screen overflow-hidden">
        
        <!-- Mobile Header Bar -->
        <header class="md:hidden flex items-center justify-between p-4 bg-black/90 border-b border-neutral-800 shrink-0 backdrop-blur-md z-30">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">
                    <svg class="w-5 h-5 text-black" viewBox="0 0 24 24" fill="none"><path d="M12 4L20 8L12 12L4 8L12 4Z" stroke="currentColor" stroke-width="2"/><path d="M4 12L12 16L20 12" stroke="currentColor" stroke-width="2"/><path d="M4 16L12 20L20 16" stroke="currentColor" stroke-width="2"/></svg>
                </div>
                <span class="text-lg font-bold tracking-tight text-white">ReconAgent</span>
            </div>
            <button id="menu-toggle" class="p-2 text-neutral-400 hover:text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-20 w-64 bg-black/95 md:bg-black/80 border-r border-neutral-800 flex flex-col justify-between shrink-0 backdrop-blur-md -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:static">
            <div>
                <div class="hidden md:block p-6 border-b border-neutral-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-black" viewBox="0 0 24 24" fill="none"><path d="M12 4L20 8L12 12L4 8L12 4Z" stroke="currentColor" stroke-width="2"/><path d="M4 12L12 16L20 12" stroke="currentColor" stroke-width="2"/><path d="M4 16L12 20L20 16" stroke="currentColor" stroke-width="2"/></svg>
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

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
            
            <header class="animate-reveal flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 md:mb-8 pb-4 border-b border-neutral-800">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Trigger New Reconciliation</h1>
                    <p class="text-xs text-neutral-400 mt-1">Upload target statement files to run automated ML comparison matching.</p>
                </div>
                
                <a href="/execute-recon-runs" class="w-full sm:w-auto text-center px-4 py-2 bg-neutral-900 border border-neutral-800 hover:bg-neutral-800 text-neutral-300 font-semibold rounded-lg text-xs transition-colors">
                    &larr; Back to Runs Log
                </a>
            </header>

            <form id="recon-form" action="{{ route('reconciliation.runs.execute') }}" method="POST" enctype="multipart/form-data" novalidate class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                @csrf
                
                <!-- Dataset Uploads Section -->
                <div class="lg:col-span-7 space-y-6 animate-reveal delay-1">
                    
                    <!-- Source File A (Primary) -->
                    <div id="card_source_a" class="bg-black/40 border border-neutral-800 rounded-xl p-5 backdrop-blur-sm space-y-4 transition-colors">
                        <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                            <div class="group relative flex items-center gap-2 cursor-help">
                                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-xs font-mono uppercase tracking-wider text-neutral-300">Source Document A</span>
                                <svg class="w-3.5 h-3.5 text-neutral-500 hover:text-neutral-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                                <!-- Tooltip Popup -->
                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900/95 border border-neutral-700 rounded-lg shadow-2xl backdrop-blur-md z-30 text-[11px] text-neutral-300 font-sans leading-relaxed normal-case tracking-normal">
                                    <span class="font-semibold text-white block mb-0.5">Primary Payment Source</span>
                                    The primary transaction file (e.g. Bank statement, Stripe export, or Payment Gateway log) used as the baseline for matching.
                                </div>
                            </div>
                            <span class="text-[10px] bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-2 py-0.5 rounded font-mono">Active</span>
                        </div>

                        <div>
                            <label class="block text-[10px] text-neutral-400 uppercase tracking-wider mb-1.5">Source Type</label>
                            <div class="relative">
                                <select id="source_a_select" name="source_a_connection" class="w-full appearance-none bg-neutral-900/90 border border-neutral-800 rounded-lg pl-3 pr-10 py-2.5 text-xs text-white focus:outline-none focus:border-neutral-500 font-mono transition-colors">
                                    <option value="">-- Select Source A Type --</option>
                                    <option value="csv_batch_upload">Custom File Upload (CSV / XLSX)</option>
                                    <option value="stripe_api" disabled class="text-neutral-600">Stripe Live API (Coming Soon)</option>
                                    <option value="adyen_settlements" disabled class="text-neutral-600">Adyen Merchant Feed (Coming Soon)</option>
                                    <option value="paypal_payouts" disabled class="text-neutral-600">PayPal Payout Log (Coming Soon)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Active File Drag and Drop Zone -->
                        <div id="drop_zone_a" class="border-2 border-dashed border-neutral-800 hover:border-neutral-700 bg-neutral-900/40 rounded-xl p-5 text-center transition-colors">
                            <input type="file" name="file_source_a" id="file_source_a" accept=".csv,.xlsx" class="hidden" onchange="handleFileSelect(this, 'file_name_a', 'drop_zone_a')">
                            <label for="file_source_a" class="cursor-pointer flex flex-col items-center justify-center space-y-2">
                                <div class="w-8 h-8 rounded-full bg-neutral-800/80 flex items-center justify-center text-neutral-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-neutral-200">Upload Gateway Statement / Bank File</span>
                                    <span class="text-[10px] text-neutral-500 block mt-0.5">CSV or XLSX (Max 25MB)</span>
                                </div>
                                <span id="file_name_a" class="text-xs font-mono text-emerald-400 font-semibold pt-1"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Source File B (Comparison Target) -->
                    <div id="card_source_b" class="bg-black/40 border border-neutral-800 rounded-xl p-5 backdrop-blur-sm space-y-4 transition-colors">
                        <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                            <div class="group relative flex items-center gap-2 cursor-help">
                                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path></svg>
                                <span class="text-xs font-mono uppercase tracking-wider text-neutral-300">Source Document B</span>
                                <svg class="w-3.5 h-3.5 text-neutral-500 hover:text-neutral-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                                <!-- Tooltip Popup -->
                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900/95 border border-neutral-700 rounded-lg shadow-2xl backdrop-blur-md z-30 text-[11px] text-neutral-300 font-sans leading-relaxed normal-case tracking-normal">
                                    <span class="font-semibold text-white block mb-0.5">Secondary Comparison Source</span>
                                    The target dataset (e.g. Internal Ledger, ERP file, NetSuite dump, or Order log) to compare against Source A.
                                </div>
                            </div>
                            <span class="text-[10px] bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-2 py-0.5 rounded font-mono">Active</span>
                        </div>

                        <div>
                            <label class="block text-[10px] text-neutral-400 uppercase tracking-wider mb-1.5">Source Type</label>
                            <div class="relative">
                                <select id="source_b_select" name="source_b_connection" class="w-full appearance-none bg-neutral-900/90 border border-neutral-800 rounded-lg pl-3 pr-10 py-2.5 text-xs text-white focus:outline-none focus:border-neutral-500 font-mono transition-colors">
                                    <option value="">-- Select Source B Type --</option>
                                    <option value="csv_batch_upload_b">Custom File Upload (CSV / XLSX)</option>
                                    <option value="internal_transactions" disabled class="text-neutral-600">Direct Database Connection (Coming Soon)</option>
                                    <option value="netsuite_erp" disabled class="text-neutral-600">NetSuite Sync (Coming Soon)</option>
                                    <option value="quickbooks_online" disabled class="text-neutral-600">QuickBooks Sync (Coming Soon)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Active File Drag and Drop Zone -->
                        <div id="drop_zone_b" class="border-2 border-dashed border-neutral-800 hover:border-neutral-700 bg-neutral-900/40 rounded-xl p-5 text-center transition-colors">
                            <input type="file" name="file_source_b" id="file_source_b" accept=".csv,.xlsx" class="hidden" onchange="handleFileSelect(this, 'file_name_b', 'drop_zone_b')">
                            <label for="file_source_b" class="cursor-pointer flex flex-col items-center justify-center space-y-2">
                                <div class="w-8 h-8 rounded-full bg-neutral-800/80 flex items-center justify-center text-neutral-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-neutral-200">Upload Internal Ledger / Export File</span>
                                    <span class="text-[10px] text-neutral-500 block mt-0.5">CSV or XLSX (Max 25MB)</span>
                                </div>
                                <span id="file_name_b" class="text-xs font-mono text-emerald-400 font-semibold pt-1"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Blurred / Coming Soon Integration Card -->
                    <div class="relative overflow-hidden bg-black/20 border border-neutral-800/60 rounded-xl p-5 backdrop-blur-md">
                        <div class="filter blur-[2px] opacity-40 pointer-events-none space-y-3">
                            <div class="flex items-center gap-2 pb-2 border-b border-neutral-800">
                                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span class="text-xs font-mono uppercase tracking-wider text-neutral-300">Automated Direct API Connectors</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-xs font-mono text-neutral-400">
                                <div class="p-2 bg-neutral-900 border border-neutral-800 rounded">Stripe API Live Feed</div>
                                <div class="p-2 bg-neutral-900 border border-neutral-800 rounded">NetSuite ERP Connector</div>
                                <div class="p-2 bg-neutral-900 border border-neutral-800 rounded">Adyen Settlement Sync</div>
                                <div class="p-2 bg-neutral-900 border border-neutral-800 rounded">PostgreSQL Ledger Sync</div>
                            </div>
                        </div>
                        
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/40 backdrop-blur-[1px]">
                            <div class="group relative cursor-pointer">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-neutral-900/90 border border-neutral-700 text-[11px] font-mono text-neutral-300 shadow-xl transition-colors hover:border-neutral-500 hover:bg-neutral-900">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Direct API Integration — Planned v2.0
                                </span>

                                <div class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 hidden group-hover:flex flex-col items-center w-64 p-3 bg-neutral-900/95 border border-neutral-700 rounded-lg shadow-2xl backdrop-blur-md z-30 text-center transition-all">
                                    <div class="flex items-center gap-1.5 text-amber-400 text-xs font-semibold mb-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Coming Soon</span>
                                    </div>
                                    <p class="text-[11px] text-neutral-300 leading-snug">
                                        Direct API syncing with Stripe, NetSuite, and databases is under development and will be available in the v2.0 release.
                                    </p>
                                    <div class="w-2 h-2 bg-neutral-900 border-r border-b border-neutral-700 rotate-45 -mb-4 mt-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Run & ML Logic Parameters -->
                <div class="lg:col-span-5 space-y-6 animate-reveal delay-2">
                    <div class="bg-black/40 border border-neutral-800 rounded-xl p-5 backdrop-blur-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                            <h2 class="text-xs font-mono uppercase tracking-wider text-neutral-300">Run & ML Configurations</h2>
                            <span class="text-[10px] bg-neutral-900 border border-neutral-800 px-2 py-0.5 rounded text-neutral-400 font-mono">v1.0 File Engine</span>
                        </div>

                        <!-- Date Range Selection Filters -->
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <div class="group relative flex items-center gap-1.5 mb-1 cursor-help w-max">
                                    <label class="block text-[10px] text-neutral-400 uppercase tracking-wider">Target Start Date</label>
                                    <svg class="w-3.5 h-3.5 text-neutral-500 hover:text-neutral-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                                    <!-- Tooltip Popup -->
                                    <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-56 p-2.5 bg-neutral-900/95 border border-neutral-700 rounded-lg shadow-2xl backdrop-blur-md z-30 text-[11px] text-neutral-300 font-sans leading-relaxed normal-case tracking-normal">
                                        <span class="font-semibold text-white block mb-0.5">Start Date Limit</span>
                                        Filters out any transactions that occurred prior to this date from the evaluation window.
                                    </div>
                                </div>
                                <input type="date" id="start_date_input" name="target_start_date" class="w-full bg-neutral-900/90 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-neutral-500 font-mono transition-colors">
                            </div>
                            <div>
                                <div class="group relative flex items-center gap-1.5 mb-1 cursor-help w-max">
                                    <label class="block text-[10px] text-neutral-400 uppercase tracking-wider">Target End Date</label>
                                    <svg class="w-3.5 h-3.5 text-neutral-500 hover:text-neutral-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                                    <!-- Tooltip Popup -->
                                    <div class="pointer-events-none absolute bottom-full right-0 mb-2 hidden group-hover:block w-56 p-2.5 bg-neutral-900/95 border border-neutral-700 rounded-lg shadow-2xl backdrop-blur-md z-30 text-[11px] text-neutral-300 font-sans leading-relaxed normal-case tracking-normal">
                                        <span class="font-semibold text-white block mb-0.5">End Date Limit</span>
                                        Sets the cutoff date for matching transactions to prevent processing future periods.
                                    </div>
                                </div>
                                <input type="date" id="end_date_input" name="target_end_date" class="w-full bg-neutral-900/90 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-neutral-500 font-mono transition-colors">
                            </div>
                        </div>

                        <!-- Parameter 1: Deterministic Variance Tolerance -->
                        <div>
                            <div class="group relative flex items-center gap-1.5 mb-1 cursor-help w-max">
                                <label class="block text-[10px] text-neutral-400 uppercase tracking-wider">Deterministic Variance Tolerance ($)</label>
                                <svg class="w-3.5 h-3.5 text-neutral-500 hover:text-neutral-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900/95 border border-neutral-700 rounded-lg shadow-2xl backdrop-blur-md z-30 text-[11px] text-neutral-300 font-sans leading-relaxed">
                                    <span class="font-semibold text-white block mb-0.5">Amount Tolerance ($)</span>
                                    Acceptable dollar difference between matching records (e.g. $0.05 allows minor rounding or fee discrepancies).
                                </div>
                            </div>
                            <div class="relative flex items-center">
                                <input type="number" step="0.01" id="tolerance_input" name="amount_tolerance" value="0.00" class="w-full bg-neutral-900/90 border border-neutral-800 rounded-lg pl-3 pr-16 py-2 text-xs text-white focus:outline-none focus:border-neutral-500 font-mono">
                                <div class="absolute right-1 flex items-center gap-1">
                                    <button type="button" onclick="decrement('tolerance_input', 0.05)" class="w-6 h-6 rounded bg-neutral-800 hover:bg-neutral-700 text-neutral-300 text-xs font-mono flex items-center justify-center border border-neutral-700">-</button>
                                    <button type="button" onclick="increment('tolerance_input', 0.05)" class="w-6 h-6 rounded bg-neutral-800 hover:bg-neutral-700 text-neutral-300 text-xs font-mono flex items-center justify-center border border-neutral-700">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Parameter 2: Settlement Buffer -->
                        <div>
                            <div class="group relative flex items-center gap-1.5 mb-1 cursor-help w-max">
                                <label class="block text-[10px] text-neutral-400 uppercase tracking-wider">Settlement Buffer (Days)</label>
                                <svg class="w-3.5 h-3.5 text-neutral-500 hover:text-neutral-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900/95 border border-neutral-700 rounded-lg shadow-2xl backdrop-blur-md z-30 text-[11px] text-neutral-300 font-sans leading-relaxed">
                                    <span class="font-semibold text-white block mb-0.5">Date Flexibility (Days)</span>
                                    Allows matching transactions separated by weekend delays or bank processing times.
                                </div>
                            </div>
                            <div class="relative flex items-center">
                                <input type="number" id="window_input" name="date_window_days" value="2" class="w-full bg-neutral-900/90 border border-neutral-800 rounded-lg pl-3 pr-16 py-2 text-xs text-white focus:outline-none focus:border-neutral-500 font-mono">
                                <div class="absolute right-1 flex items-center gap-1">
                                    <button type="button" onclick="decrement('window_input', 1)" class="w-6 h-6 rounded bg-neutral-800 hover:bg-neutral-700 text-neutral-300 text-xs font-mono flex items-center justify-center border border-neutral-700">-</button>
                                    <button type="button" onclick="increment('window_input', 1)" class="w-6 h-6 rounded bg-neutral-800 hover:bg-neutral-700 text-neutral-300 text-xs font-mono flex items-center justify-center border border-neutral-700">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Parameter 3: ML Fuzzy Logic Threshold -->
                        <div>
                            <div class="group relative flex items-center gap-1.5 mb-1 cursor-help w-max">
                                <label class="block text-[10px] text-neutral-400 uppercase tracking-wider">ML Fuzzy Logic Threshold (%)</label>
                                <svg class="w-3.5 h-3.5 text-neutral-500 hover:text-neutral-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>

                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900/95 border border-neutral-700 rounded-lg shadow-2xl backdrop-blur-md z-30 text-[11px] text-neutral-300 font-sans leading-relaxed">
                                    <span class="font-semibold text-white block mb-0.5">Matching Strictness (%)</span>
                                    Minimum confidence score required for Machine Learning to auto-approve fuzzy matches.
                                </div>
                            </div>
                            <div class="relative flex items-center">
                                <input type="number" id="threshold_input" name="ml_threshold" value="85" class="w-full bg-neutral-900/90 border border-neutral-800 rounded-lg pl-3 pr-16 py-2 text-xs text-white focus:outline-none focus:border-neutral-500 font-mono">
                                <div class="absolute right-1 flex items-center gap-1">
                                    <button type="button" onclick="decrement('threshold_input', 5)" class="w-6 h-6 rounded bg-neutral-800 hover:bg-neutral-700 text-neutral-300 text-xs font-mono flex items-center justify-center border border-neutral-700">-</button>
                                    <button type="button" onclick="increment('threshold_input', 5)" class="w-6 h-6 rounded bg-neutral-800 hover:bg-neutral-700 text-neutral-300 text-xs font-mono flex items-center justify-center border border-neutral-700">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- ML Modules Checklist -->
                        <div id="ml_modules_container" class="pt-2 border-t border-neutral-800/60 space-y-3 transition-colors rounded-lg p-1">
                            <div class="flex items-center justify-between">
                                <span class="block text-[10px] text-neutral-400 uppercase tracking-wider">Active ML Pipelines</span>
                                <span id="module-counter" class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-neutral-800 text-emerald-400 border border-neutral-700">
                                    4 / 4 Active
                                </span>
                            </div>
                            
                            <div class="group relative flex items-center justify-between">
                                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                    <input type="checkbox" name="ml_modules[]" value="levenshtein_string" checked onchange="updateModuleCounter()" class="ml-checkbox w-3.5 h-3.5 rounded bg-neutral-900 border-neutral-800 text-white focus:ring-0 focus:ring-offset-0">
                                    <span class="text-xs text-neutral-300 font-mono">Fuzzy String Matching</span>
                                </label>
                                <div class="cursor-help text-neutral-500 hover:text-neutral-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900 border border-neutral-700 rounded-lg shadow-xl text-[11px] text-neutral-300 z-20 backdrop-blur-md">
                                    Matches misspelled or truncated references between files.
                                </div>
                            </div>

                            <div class="group relative flex items-center justify-between">
                                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                    <input type="checkbox" name="ml_modules[]" value="fx_conversion" checked onchange="updateModuleCounter()" class="ml-checkbox w-3.5 h-3.5 rounded bg-neutral-900 border-neutral-800 text-white focus:ring-0 focus:ring-offset-0">
                                    <span class="text-xs text-neutral-300 font-mono">Dynamic FX Rate Estimation</span>
                                </label>
                                <div class="cursor-help text-neutral-500 hover:text-neutral-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900 border border-neutral-700 rounded-lg shadow-xl text-[11px] text-neutral-300 z-20 backdrop-blur-md">
                                    Adjusts for currency conversion variances across timestamps.
                                </div>
                            </div>

                            <div class="group relative flex items-center justify-between">
                                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                    <input type="checkbox" name="ml_modules[]" value="n_to_one" checked onchange="updateModuleCounter()" class="ml-checkbox w-3.5 h-3.5 rounded bg-neutral-900 border-neutral-800 text-white focus:ring-0 focus:ring-offset-0">
                                    <span class="text-xs text-neutral-300 font-mono">Split Payment Clustering</span>
                                </label>
                                <div class="cursor-help text-neutral-500 hover:text-neutral-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900 border border-neutral-700 rounded-lg shadow-xl text-[11px] text-neutral-300 z-20 backdrop-blur-md">
                                    Groups multiple transaction records matching a single payout.
                                </div>
                            </div>

                            <div class="group relative flex items-center justify-between">
                                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                    <input type="checkbox" name="ml_modules[]" value="fee_deduction_model" checked onchange="updateModuleCounter()" class="ml-checkbox w-3.5 h-3.5 rounded bg-neutral-900 border-neutral-800 text-white focus:ring-0 focus:ring-offset-0">
                                    <span class="text-xs text-neutral-300 font-mono">Gateway Fee Deduction Inference</span>
                                </label>
                                <div class="cursor-help text-neutral-500 hover:text-neutral-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:block w-64 p-2.5 bg-neutral-900 border border-neutral-700 rounded-lg shadow-xl text-[11px] text-neutral-300 z-20 backdrop-blur-md">
                                    Identifies processor fees deducted prior to settlement.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submit-btn" class="group w-full py-3 bg-white text-black hover:bg-neutral-200 font-semibold rounded-lg text-xs transition-all active:scale-[0.98] duration-150 flex items-center justify-center space-x-2 mt-4 cursor-pointer">
                            <span id="btn-text">Execute ML Reconciliation</span>
                            <svg id="btn-icon" class="w-4 h-4 transition-transform duration-200 ease-out group-hover:translate-x-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <svg id="btn-spinner" class="hidden animate-spin h-4 w-4 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Modal Backdrop -->
            <div id="ra-recon-modal-backdrop" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
                <!-- Centered Modal Box -->
                <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative animate-reveal">
                    
                    <!-- Modal Close Cross (Hidden during execution) -->
                    <button id="ra-modal-close-x" type="button" class="hidden absolute top-4 right-4 text-neutral-400 hover:text-white transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <!-- Processing State -->
                    <div id="ra-progress-state">
                        <h3 class="text-lg font-bold text-white tracking-tight mb-1">Reconciliation in Progress</h3>
                        <p class="text-xs text-neutral-400 mb-6">Our ML pipeline is analyzing and matching your uploaded datasets.</p>
                        
                        <ul class="space-y-3.5">
                            <li id="ra-step-1" class="flex items-center gap-3 text-xs text-neutral-500 transition-colors">
                                <span class="w-5 h-5 flex items-center justify-center shrink-0">
                                    <svg class="ra-step-spinner hidden w-3.5 h-3.5 animate-spin text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span class="ra-step-dot w-2 h-2 rounded-full bg-neutral-700"></span>
                                    <svg class="ra-step-check hidden w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                <span>Initializing dataset pipelines</span>
                            </li>
                            <li id="ra-step-2" class="flex items-center gap-3 text-xs text-neutral-500 transition-colors">
                                <span class="w-5 h-5 flex items-center justify-center shrink-0">
                                    <svg class="ra-step-spinner hidden w-3.5 h-3.5 animate-spin text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span class="ra-step-dot w-2 h-2 rounded-full bg-neutral-700"></span>
                                    <svg class="ra-step-check hidden w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                <span>Schema detection & mapping validation</span>
                            </li>
                            <li id="ra-step-3" class="flex items-center gap-3 text-xs text-neutral-500 transition-colors">
                                <span class="w-5 h-5 flex items-center justify-center shrink-0">
                                    <svg class="ra-step-spinner hidden w-3.5 h-3.5 animate-spin text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span class="ra-step-dot w-2 h-2 rounded-full bg-neutral-700"></span>
                                    <svg class="ra-step-check hidden w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                <span>Contacting Reconciliation ML Model</span>
                            </li>
                            <li id="ra-step-4" class="flex items-center gap-3 text-xs text-neutral-500 transition-colors">
                                <span class="w-5 h-5 flex items-center justify-center shrink-0">
                                    <svg class="ra-step-spinner hidden w-3.5 h-3.5 animate-spin text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span class="ra-step-dot w-2 h-2 rounded-full bg-neutral-700"></span>
                                    <svg class="ra-step-check hidden w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                <span>Evaluating Fuzzy & Gateway Fee inferences</span>
                            </li>
                            <li id="ra-step-5" class="flex items-center gap-3 text-xs text-neutral-500 transition-colors">
                                <span class="w-5 h-5 flex items-center justify-center shrink-0">
                                    <svg class="ra-step-spinner hidden w-3.5 h-3.5 animate-spin text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span class="ra-step-dot w-2 h-2 rounded-full bg-neutral-700"></span>
                                    <svg class="ra-step-check hidden w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                <span>Generating final match classifications</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Success/Results State -->
                    <div id="ra-result-state" class="hidden space-y-5">
                        <div class="flex items-center gap-2.5 p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Reconciliation Completed Successfully!</span>
                        </div>

                        <!-- Key Metrics Grid -->
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-black/40 border border-neutral-800 p-3 rounded-lg text-center">
                                <span id="ra-match-rate" class="block text-base font-bold text-white font-mono">--</span>
                                <span class="block text-[10px] text-neutral-400 mt-0.5">Match Rate</span>
                            </div>

                            <div class="bg-black/40 border border-neutral-800 p-3 rounded-lg text-center">
                                <span id="ra-matched-count" class="block text-base font-bold text-white font-mono">--</span>
                                <span class="block text-[10px] text-neutral-400 mt-0.5">Matched</span>
                            </div>

                            <div class="bg-black/40 border border-neutral-800 p-3 rounded-lg text-center">
                                <span id="ra-unmatched-count" class="block text-base font-bold text-white font-mono">--</span>
                                <span class="block text-[10px] text-neutral-400 mt-0.5">Unmatched</span>
                            </div>
                        </div>

                        <!-- Match Breakdown -->
                        <div class="bg-black/40 border border-neutral-800 rounded-lg p-3.5 space-y-2 text-xs">
                            <div class="text-[10px] font-mono uppercase tracking-wider text-neutral-400 font-bold mb-1">
                                ML Match Breakdown
                            </div>

                            <div class="flex justify-between text-neutral-300">
                                <span>Fuzzy Matches</span>
                                <strong id="ra-fuzzy-matches" class="font-mono text-white">---</strong>
                            </div>

                            <div class="flex justify-between text-neutral-300">
                                <span>Gateway Fee Deductions</span>
                                <strong id="ra-gateway-fee-deductions" class="font-mono text-white">--</strong>
                            </div>

                            <div class="flex justify-between text-neutral-300">
                                <span>Split Payments</span>
                                <strong id="ra-split-payments" class="font-mono text-white">--</strong>
                            </div>
                        </div>

                        <!-- Action Buttons + Close Button -->
                        <div class="space-y-2 pt-2">
                            <div class="flex gap-2">
                                <button type="button" id="ra-btn-summary" class="flex-1 py-2.5 px-3 bg-white hover:bg-neutral-200 text-black font-semibold rounded-lg text-xs transition-colors">Download Summary</button>
                                <button type="button" id="ra-btn-unmatched" class="flex-1 py-2.5 px-3 bg-neutral-900 border border-neutral-800 hover:bg-neutral-800 text-neutral-300 font-semibold rounded-lg text-xs transition-colors">Export Unmatched</button>
                            </div>
                            <button type="button" id="ra-btn-close-modal" class="w-full py-2 px-3 bg-neutral-800 hover:bg-neutral-700 text-neutral-300 font-medium rounded-lg text-xs transition-colors">Close</button>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>






<script>
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    /* =========================================================
       ELEMENTS
    ========================================================= */

    const form =
        document.getElementById('recon-form');

    if (!form) {
        console.error(
            'Reconciliation form #recon-form was not found.'
        );
        return;
    }

    const submitBtn =
        document.getElementById('ra-submit-btn');

    const modalBackdrop =
        document.getElementById(
            'ra-recon-modal-backdrop'
        );

    const progressState =
        document.getElementById(
            'ra-progress-state'
        );

    const resultState =
        document.getElementById(
            'ra-result-state'
        );

    const modalCloseX =
        document.getElementById(
            'ra-modal-close-x'
        );

    const closeModalBtn =
        document.getElementById(
            'ra-btn-close-modal'
        );

    const csrfToken =
        document
            .querySelector(
                'meta[name="csrf-token"]'
            )
            ?.getAttribute('content') ||
        form
            .querySelector(
                'input[name="_token"]'
            )
            ?.value ||
        '';

    const actionUrl =
        form.getAttribute('action');

    let currentRunId = null;

    let pollTimer = null;

    let isSubmitting = false;

    /*
     * Used only for the visual progress animation.
     *
     * The backend currently exposes:
     *
     * queued
     * processing
     * completed
     * failed
     *
     * It does not expose the exact Python sub-stage.
     * Therefore these sub-stages are visual only.
     */
    let processingStartedAt = null;

    let progressAnimationTimer = null;


    /* =========================================================
       TOAST
    ========================================================= */

    function showToast(
        title,
        message,
        type = 'green',
        duration = 5000
    ) {
        const existingToast =
            document.getElementById(
                'ra-toast'
            );

        if (existingToast) {
            existingToast.remove();
        }

        const toast =
            document.createElement('div');

        toast.id = 'ra-toast';

        const icon =
            type === 'red'
                ? `
                    <svg
                        class="w-5 h-5 flex-shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="10"
                        ></circle>

                        <line
                            x1="15"
                            y1="9"
                            x2="9"
                            y2="15"
                        ></line>

                        <line
                            x1="9"
                            y1="9"
                            x2="15"
                            y2="15"
                        ></line>
                    </svg>
                `
                : `
                    <svg
                        class="w-5 h-5 flex-shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M22 11.08V12a10 10 0 1 1-5.93-9.14"
                        ></path>

                        <polyline
                            points="22 4 12 14.01 9 11.01"
                        ></polyline>
                    </svg>
                `;

        toast.className = `
            fixed top-6 right-6 z-[99999]
            max-w-md w-[calc(100%-2rem)]
            rounded-xl shadow-2xl
            px-5 py-4
            flex items-start gap-3
            text-white
            ${type === 'red'
                ? 'bg-red-600'
                : 'bg-green-600'}
        `;

        toast.innerHTML = `
            ${icon}

            <div class="min-w-0">
                <div class="font-semibold">
                    ${escapeHtml(title)}
                </div>

                <div class="text-sm mt-1 opacity-95 break-words">
                    ${escapeHtml(message)}
                </div>
            </div>

            <button
                type="button"
                class="ml-auto text-white/80 hover:text-white text-xl leading-none"
                aria-label="Close notification"
            >
                &times;
            </button>
        `;

        document.body.appendChild(toast);

        toast
            .querySelector('button')
            ?.addEventListener(
                'click',
                () => {
                    toast.remove();
                }
            );

        if (duration > 0) {
            setTimeout(() => {
                if (toast.isConnected) {
                    toast.remove();
                }
            }, duration);
        }
    }


    /* =========================================================
       GENERAL HELPERS
    ========================================================= */

    function escapeHtml(value) {
        if (
            value === null ||
            value === undefined
        ) {
            return '';
        }

        return String(value)
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            );
    }


    function formatNumber(value) {
        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '—';
        }

        const number =
            Number(value);

        if (
            Number.isNaN(number)
        ) {
            return String(value);
        }

        return number.toLocaleString();
    }


    function formatPercentage(value) {
        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '—';
        }

        const number =
            Number(value);

        if (
            Number.isNaN(number)
        ) {
            return String(value);
        }

        return `${number.toFixed(2)}%`;
    }


    function setTextIfExists(
        id,
        value
    ) {
        const element =
            document.getElementById(id);

        if (
            element &&
            value !== null &&
            value !== undefined
        ) {
            element.textContent =
                value;
        }
    }


    function stopPolling() {
        if (pollTimer) {
            clearTimeout(
                pollTimer
            );

            pollTimer = null;
        }
    }


    function stopProgressAnimation() {
        if (progressAnimationTimer) {
            clearInterval(
                progressAnimationTimer
            );

            progressAnimationTimer = null;
        }

        processingStartedAt = null;
    }


    /* =========================================================
       SUBMIT BUTTON
    ========================================================= */

    function setSubmitButtonLoading(
        loading
    ) {
        if (!submitBtn) {
            return;
        }

        if (loading) {
            submitBtn.disabled = true;

            if (
                !submitBtn.dataset
                    .originalHtml
            ) {
                submitBtn.dataset.originalHtml =
                    submitBtn.innerHTML;
            }

            submitBtn.innerHTML = `
                <svg
                    class="animate-spin -ml-1 mr-2 h-5 w-5 inline-block"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                    ></path>
                </svg>

                Processing...
            `;
        } else {
            submitBtn.disabled = false;

            if (
                submitBtn.dataset
                    .originalHtml
            ) {
                submitBtn.innerHTML =
                    submitBtn.dataset.originalHtml;
            }
        }
    }


    /* =========================================================
       PROGRESS STEP ELEMENTS
    ========================================================= */

    function getProgressSteps() {
        return [
            document.getElementById(
                'ra-step-1'
            ),
            document.getElementById(
                'ra-step-2'
            ),
            document.getElementById(
                'ra-step-3'
            ),
            document.getElementById(
                'ra-step-4'
            ),
            document.getElementById(
                'ra-step-5'
            )
        ];
    }


    function getStepParts(step) {
        if (!step) {
            return null;
        }

        return {
            spinner:
                step.querySelector(
                    '.ra-step-spinner'
                ),

            dot:
                step.querySelector(
                    '.ra-step-dot'
                ),

            check:
                step.querySelector(
                    '.ra-step-check'
                ),

            text:
                step.querySelector(
                    '.ra-step-text'
                ) ||
                step.querySelector(
                    ':scope > span:last-child'
                )
        };
    }


    /* =========================================================
       RESET PROGRESS
    ========================================================= */

    function resetProgressSteps() {
        stopProgressAnimation();

        const steps =
            getProgressSteps();

        steps.forEach(step => {
            if (!step) {
                return;
            }

            const parts =
                getStepParts(step);

            step.classList.remove(
                'text-emerald-400',
                'text-blue-400',
                'text-red-400',
                'font-semibold'
            );

            step.classList.add(
                'text-neutral-500'
            );

            if (parts?.spinner) {
                parts.spinner.classList.add(
                    'hidden'
                );
            }

            if (parts?.check) {
                parts.check.classList.add(
                    'hidden'
                );
            }

            if (parts?.dot) {
                parts.dot.classList.remove(
                    'hidden'
                );

                parts.dot.classList.remove(
                    'bg-blue-400',
                    'bg-emerald-400',
                    'bg-red-500'
                );

                parts.dot.classList.add(
                    'bg-neutral-700'
                );
            }
        });

        /*
         * Start at step 1.
         */
        activateStep(1);
    }


    /* =========================================================
       ACTIVE STEP
    ========================================================= */

    function activateStep(
        stepNumber
    ) {
        const step =
            document.getElementById(
                `ra-step-${stepNumber}`
            );

        if (!step) {
            return;
        }

        const parts =
            getStepParts(step);

        step.classList.remove(
            'text-neutral-500',
            'text-emerald-400',
            'text-red-400'
        );

        step.classList.add(
            'text-blue-400',
            'font-semibold'
        );

        if (parts?.dot) {
            parts.dot.classList.add(
                'hidden'
            );
        }

        if (parts?.check) {
            parts.check.classList.add(
                'hidden'
            );
        }

        if (parts?.spinner) {
            parts.spinner.classList.remove(
                'hidden'
            );
        }
    }


    /* =========================================================
       COMPLETE STEP
    ========================================================= */

    function completeStep(
        stepNumber
    ) {
        const step =
            document.getElementById(
                `ra-step-${stepNumber}`
            );

        if (!step) {
            return;
        }

        const parts =
            getStepParts(step);

        step.classList.remove(
            'text-neutral-500',
            'text-blue-400',
            'text-red-400'
        );

        step.classList.add(
            'text-emerald-400',
            'font-semibold'
        );

        if (parts?.spinner) {
            parts.spinner.classList.add(
                'hidden'
            );
        }

        if (parts?.dot) {
            parts.dot.classList.add(
                'hidden'
            );
        }

        if (parts?.check) {
            parts.check.classList.remove(
                'hidden'
            );
        }
    }


    /* =========================================================
       FAIL STEP
    ========================================================= */

    function failStep(
        stepNumber
    ) {
        const step =
            document.getElementById(
                `ra-step-${stepNumber}`
            );

        if (!step) {
            return;
        }

        const parts =
            getStepParts(step);

        step.classList.remove(
            'text-neutral-500',
            'text-blue-400',
            'text-emerald-400'
        );

        step.classList.add(
            'text-red-400',
            'font-semibold'
        );

        if (parts?.spinner) {
            parts.spinner.classList.add(
                'hidden'
            );
        }

        if (parts?.check) {
            parts.check.classList.add(
                'hidden'
            );
        }

        if (parts?.dot) {
            parts.dot.classList.remove(
                'hidden'
            );

            parts.dot.classList.remove(
                'bg-neutral-700',
                'bg-blue-400',
                'bg-emerald-400'
            );

            parts.dot.classList.add(
                'bg-red-500'
            );
        }
    }


    /* =========================================================
       COMPLETE ALL STEPS
    ========================================================= */

    function completeAllSteps() {
        stopProgressAnimation();

        for (
            let i = 1;
            i <= 5;
            i++
        ) {
            completeStep(i);
        }
    }


    /* =========================================================
       VISUAL PROCESSING PROGRESS
       
       IMPORTANT:
       The backend only reports "processing".
       These are visual UI stages while Python is running.
    ========================================================= */

    function updateVisualProcessingStep() {
        if (
            processingStartedAt === null
        ) {
            processingStartedAt =
                Date.now();
        }

        const elapsed =
            Date.now() -
            processingStartedAt;

        let currentStep = 1;

        /*
         * 0 - 2.5 sec
         * Initializing dataset pipelines
         */
        if (
            elapsed < 2500
        ) {
            currentStep = 1;
        }

        /*
         * 2.5 - 6 sec
         * Schema validation
         */
        else if (
            elapsed < 6000
        ) {
            currentStep = 2;
        }

        /*
         * 6 - 12 sec
         * ML model
         */
        else if (
            elapsed < 12000
        ) {
            currentStep = 3;
        }

        /*
         * 12 - 20 sec
         * Fuzzy / Gateway
         */
        else if (
            elapsed < 20000
        ) {
            currentStep = 4;
        }

        /*
         * 20+ sec
         * Final classifications
         */
        else {
            currentStep = 5;
        }

        /*
         * Complete everything before
         * the active step.
         */
        for (
            let i = 1;
            i < currentStep;
            i++
        ) {
            completeStep(i);
        }

        activateStep(currentStep);
    }


    function startProgressAnimation() {
        if (
            progressAnimationTimer
        ) {
            return;
        }

        if (
            processingStartedAt === null
        ) {
            processingStartedAt =
                Date.now();
        }

        updateVisualProcessingStep();

        progressAnimationTimer =
            setInterval(
                () => {
                    updateVisualProcessingStep();
                },
                1000
            );
    }


    /* =========================================================
       UPDATE PROGRESS FROM SERVER STATUS
    ========================================================= */

    function updateProgressFromStatus(
        status
    ) {
        const normalizedStatus =
            String(status || '')
                .trim()
                .toLowerCase();

        console.log(
            'Updating progress UI:',
            normalizedStatus
        );

        /*
         * QUEUED
         */
        if (
            normalizedStatus ===
            'queued'
        ) {
            stopProgressAnimation();

            activateStep(1);

            return;
        }

        /*
         * PROCESSING
         */
        if (
            normalizedStatus ===
                'processing' ||
            normalizedStatus ===
                'running'
        ) {
            startProgressAnimation();

            return;
        }

        /*
         * COMPLETED
         */
        if (
            normalizedStatus ===
                'completed' ||
            normalizedStatus ===
                'complete' ||
            normalizedStatus ===
                'success'
        ) {
            completeAllSteps();

            return;
        }

        /*
         * FAILED
         */
        if (
            normalizedStatus ===
                'failed' ||
            normalizedStatus ===
                'error'
        ) {
            stopProgressAnimation();

            /*
             * If we know the current visual
             * stage, fail that one.
             */
            let failedStep = 2;

            if (
                processingStartedAt !== null
            ) {
                const elapsed =
                    Date.now() -
                    processingStartedAt;

                if (
                    elapsed >= 20000
                ) {
                    failedStep = 5;
                } else if (
                    elapsed >= 12000
                ) {
                    failedStep = 4;
                } else if (
                    elapsed >= 6000
                ) {
                    failedStep = 3;
                } else if (
                    elapsed >= 2500
                ) {
                    failedStep = 2;
                } else {
                    failedStep = 1;
                }
            }

            failStep(
                failedStep
            );
        }
    }


    /* =========================================================
       MODAL
    ========================================================= */

    function openModal() {
        if (!modalBackdrop) {
            console.warn(
                'Reconciliation modal #ra-recon-modal-backdrop was not found.'
            );

            return;
        }

        modalBackdrop.classList.remove(
            'hidden'
        );

        if (progressState) {
            progressState.classList.remove(
                'hidden'
            );
        }

        if (resultState) {
            resultState.classList.add(
                'hidden'
            );
        }

        resetProgressSteps();
    }


    function closeModal() {
        stopPolling();

        stopProgressAnimation();

        currentRunId = null;

        if (modalBackdrop) {
            modalBackdrop.classList.add(
                'hidden'
            );
        }

        if (progressState) {
            progressState.classList.remove(
                'hidden'
            );
        }

        if (resultState) {
            resultState.classList.add(
                'hidden'
            );
        }
    }


    function showResultState() {
        stopProgressAnimation();

        if (progressState) {
            progressState.classList.add(
                'hidden'
            );
        }

        if (resultState) {
            resultState.classList.remove(
                'hidden'
            );
        }
    }


    function showProgressState() {
        if (progressState) {
            progressState.classList.remove(
                'hidden'
            );
        }

        if (resultState) {
            resultState.classList.add(
                'hidden'
            );
        }
    }


    /* =========================================================
       RESULT DISPLAY
    ========================================================= */

    function findMetricValueElement(
        labelText
    ) {
        if (!resultState) {
            return null;
        }

        /*
         * Prefer explicit IDs.
         */
        const explicitMap = {
            'Match Rate':
                'ra-match-rate',

            'Matched':
                'ra-matched-count',

            'Unmatched':
                'ra-unmatched-count'
        };

        const explicitId =
            explicitMap[labelText];

        if (explicitId) {
            const explicitElement =
                document.getElementById(
                    explicitId
                );

            if (explicitElement) {
                return explicitElement;
            }
        }

        /*
         * Fallback to label lookup.
         */
        const labels =
            resultState.querySelectorAll(
                'span'
            );

        for (
            const label of labels
        ) {
            if (
                label.textContent
                    .trim()
                    .toLowerCase() ===
                labelText
                    .toLowerCase()
            ) {
                const container =
                    label.parentElement;

                if (!container) {
                    continue;
                }

                const value =
                    container.querySelector(
                        'span.font-mono'
                    );

                if (value) {
                    return value;
                }
            }
        }

        return null;
    }


    function setMetricByLabel(
        labelText,
        value
    ) {
        const element =
            findMetricValueElement(
                labelText
            );

        if (!element) {
            console.warn(
                `Could not find metric display element for "${labelText}".`
            );

            return;
        }

        element.textContent =
            value;
    }


    function findBreakdownValueElement(
        labelText
    ) {
        if (!resultState) {
            return null;
        }

        /*
         * Prefer explicit IDs.
         */
        const explicitMap = {
            'Fuzzy Matches':
                'ra-fuzzy-matches',

            'Gateway Fee Deductions':
                'ra-gateway-fee-deductions',

            'Split Payments':
                'ra-split-payments'
        };

        const explicitId =
            explicitMap[labelText];

        if (explicitId) {
            const explicitElement =
                document.getElementById(
                    explicitId
                );

            if (explicitElement) {
                return explicitElement;
            }
        }

        /*
         * Fallback to label lookup.
         */
        const elements =
            resultState.querySelectorAll(
                'span'
            );

        for (
            const element of elements
        ) {
            if (
                element.textContent
                    .trim()
                    .toLowerCase() ===
                labelText
                    .toLowerCase()
            ) {
                const container =
                    element.parentElement;

                if (!container) {
                    continue;
                }

                const value =
                    container.querySelector(
                        'strong.font-mono'
                    );

                if (value) {
                    return value;
                }
            }
        }

        return null;
    }


    function setBreakdownByLabel(
        labelText,
        value
    ) {
        const element =
            findBreakdownValueElement(
                labelText
            );

        if (!element) {
            console.warn(
                `Could not find breakdown display element for "${labelText}".`
            );

            return;
        }

        element.textContent =
            value !== null &&
            value !== undefined
                ? formatNumber(value)
                : '--';
    }


    function countValue(value) {
        if (
            value === null ||
            value === undefined
        ) {
            return null;
        }

        if (
            Array.isArray(value)
        ) {
            return value.length;
        }

        if (
            typeof value === 'object'
        ) {
            return Object.keys(
                value
            ).length;
        }

        const number =
            Number(value);

        return Number.isFinite(number)
            ? number
            : null;
    }


    /* =========================================================
       RENDER RESULTS
    ========================================================= */

    function renderResultsUI(
        resultData,
        runId
    ) {
        console.log(
            'RECON RESULTS:',
            resultData
        );

        const summary =
            resultData?.summary || {};

        const run =
            resultData?.run || {};

        /*
         * -----------------------------------------------------
         * MATCH RATE
         * -----------------------------------------------------
         */

        const matchRate =
            resultData?.match_rate ??
            run?.match_rate ??
            summary?.match_rate ??
            summary?.match_rate_source_a_percent ??
            summary?.match_percentage ??
            summary?.matched_percentage ??
            null;


        /*
         * -----------------------------------------------------
         * MATCHED
         * -----------------------------------------------------
         */

        const matchedCount =
            resultData?.matched_count ??
            run?.matched_count ??
            summary?.matched_count ??
            summary?.matched_transactions_or_groups ??
            summary?.matched ??
            null;


        /*
         * -----------------------------------------------------
         * UNMATCHED A
         * -----------------------------------------------------
         */

        const unmatchedA =
            resultData?.unmatched_a_count ??
            run?.unmatched_a_count ??
            summary?.unmatched_a_count ??
            summary?.unmatched_source_a ??
            resultData?.unmatched_a ??
            summary?.unmatched_a ??
            null;


        /*
         * -----------------------------------------------------
         * UNMATCHED B
         * -----------------------------------------------------
         */

        const unmatchedB =
            resultData?.unmatched_b_count ??
            run?.unmatched_b_count ??
            summary?.unmatched_b_count ??
            summary?.unmatched_source_b ??
            resultData?.unmatched_b ??
            summary?.unmatched_b ??
            null;


        /*
         * -----------------------------------------------------
         * COUNT UNMATCHED
         * -----------------------------------------------------
         */

        const unmatchedACount =
            countValue(
                unmatchedA
            );

        const unmatchedBCount =
            countValue(
                unmatchedB
            );

        let totalUnmatched =
            null;

        if (
            unmatchedACount !== null ||
            unmatchedBCount !== null
        ) {
            totalUnmatched =
                (unmatchedACount ?? 0) +
                (unmatchedBCount ?? 0);
        }


        /*
         * -----------------------------------------------------
         * DISPLAY METRICS
         * -----------------------------------------------------
         */

        setTextIfExists(
            'ra-match-rate',
            matchRate !== null
                ? formatPercentage(
                    matchRate
                )
                : '--'
        );

        setTextIfExists(
            'ra-matched-count',
            matchedCount !== null
                ? formatNumber(
                    matchedCount
                )
                : '--'
        );

        setTextIfExists(
            'ra-unmatched-count',
            totalUnmatched !== null
                ? formatNumber(
                    totalUnmatched
                )
                : '--'
        );


        /*
         * Fallback in case IDs are not present.
         */
        setMetricByLabel(
            'Match Rate',
            matchRate !== null
                ? formatPercentage(
                    matchRate
                )
                : '--'
        );

        setMetricByLabel(
            'Matched',
            matchedCount !== null
                ? formatNumber(
                    matchedCount
                )
                : '--'
        );

        setMetricByLabel(
            'Unmatched',
            totalUnmatched !== null
                ? formatNumber(
                    totalUnmatched
                )
                : '--'
        );


        /*
         * -----------------------------------------------------
         * ML BREAKDOWN
         * -----------------------------------------------------
         */

        const fuzzyCount =
        resultData?.fuzzy_matches ??
        summary?.fuzzy_matches ??
        summary?.fuzzy_match_count ??
        summary?.fuzzy_matches_count ??
        summary?.levenshtein_matches ??
        summary?.fuzzy_count ??
        0;

        const feeCount =
            resultData?.gateway_fee_deductions ??
            summary?.gateway_fee_deductions ??
            summary?.fee_deductions ??
            summary?.fee_matches ??
            summary?.fee_match_count ??
            summary?.gateway_matches ??
            summary?.gateway_match_count ??
            0;

        const splitCount =
            resultData?.split_payments ??
            summary?.split_payments ??
            summary?.split_payment_matches ??
            summary?.split_matches ??
            summary?.n_to_one_matches ??
            summary?.split_count ??
            0;


        /*
         * -----------------------------------------------------
         * DISPLAY ML BREAKDOWN
         * -----------------------------------------------------
         */

        setTextIfExists(
            'ra-fuzzy-matches',
            fuzzyCount !== null
                ? formatNumber(
                    fuzzyCount
                )
                : '--'
        );

        setTextIfExists(
            'ra-gateway-fee-deductions',
            feeCount !== null
                ? formatNumber(
                    feeCount
                )
                : '--'
        );

        setTextIfExists(
            'ra-split-payments',
            splitCount !== null
                ? formatNumber(
                    splitCount
                )
                : '--'
        );


        /*
         * Fallback in case IDs are not present.
         */
        setBreakdownByLabel(
            'Fuzzy Matches',
            fuzzyCount
        );

        setBreakdownByLabel(
            'Gateway Fee Deductions',
            feeCount
        );

        setBreakdownByLabel(
            'Split Payments',
            splitCount
        );


        /*
         * -----------------------------------------------------
         * DEBUG
         * -----------------------------------------------------
         */

        console.log(
            'RECON DISPLAY VALUES:',
            {
                runId,

                matchRate,

                matchedCount,

                unmatchedACount,

                unmatchedBCount,

                totalUnmatched,

                fuzzyCount,

                feeCount,

                splitCount
            }
        );
    }


    /* =========================================================
       TABLE RENDERING
    ========================================================= */

    function renderTableRows(
        tbody,
        rows,
        emptyMessage
    ) {
        if (!tbody) {
            return;
        }

        tbody.innerHTML = '';

        if (
            !Array.isArray(rows) ||
            rows.length === 0
        ) {
            const tr =
                document.createElement(
                    'tr'
                );

            tr.innerHTML = `
                <td
                    colspan="100"
                    class="px-4 py-6 text-center text-gray-500"
                >
                    ${escapeHtml(
                        emptyMessage
                    )}
                </td>
            `;

            tbody.appendChild(tr);

            return;
        }

        rows.forEach(row => {
            const tr =
                document.createElement(
                    'tr'
                );

            tr.className =
                'border-b border-gray-100';

            if (
                row &&
                typeof row === 'object'
            ) {
                Object.values(
                    row
                ).forEach(value => {
                    const td =
                        document.createElement(
                            'td'
                        );

                    td.className =
                        'px-4 py-3 text-sm text-gray-700 whitespace-nowrap';

                    if (
                        value !== null &&
                        typeof value === 'object'
                    ) {
                        td.textContent =
                            JSON.stringify(
                                value
                            );
                    } else {
                        td.textContent =
                            value === null ||
                            value === undefined
                                ? ''
                                : String(
                                    value
                                );
                    }

                    tr.appendChild(td);
                });
            } else {
                const td =
                    document.createElement(
                        'td'
                    );

                td.className =
                    'px-4 py-3 text-sm text-gray-700';

                td.textContent =
                    String(
                        row ?? ''
                    );

                tr.appendChild(td);
            }

            tbody.appendChild(tr);
        });
    }


    /* =========================================================
       FETCH RESULTS
    ========================================================= */

    async function fetchResults(
        runId
    ) {
        const url =
            `/reconciliation-runs/${runId}/results`;

        const response =
            await fetch(
                url,
                {
                    method: 'GET',

                    headers: {
                        'Accept':
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest'
                    },

                    credentials:
                        'same-origin',

                    cache:
                        'no-store'
                }
            );

        const text =
            await response.text();

        let data = {};

        try {
            data =
                text
                    ? JSON.parse(text)
                    : {};
        } catch (error) {
            throw new Error(
                `The results endpoint returned invalid JSON (HTTP ${response.status}).`
            );
        }

        if (!response.ok) {
            throw new Error(
                data.message ||
                `Could not load reconciliation results (HTTP ${response.status}).`
            );
        }

        return data;
    }


    /* =========================================================
       POLLING
    ========================================================= */

    async function pollStatus() {
        if (!currentRunId) {
            return;
        }

        try {
            const response =
                await fetch(
                    `/reconciliation-runs/${currentRunId}/status`,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        },

                        credentials:
                            'same-origin',

                        cache:
                            'no-store'
                    }
                );

            const text =
                await response.text();

            let data = {};

            try {
                data =
                    text
                        ? JSON.parse(
                            text
                        )
                        : {};
            } catch (error) {
                throw new Error(
                    `Status endpoint returned invalid JSON (HTTP ${response.status}).`
                );
            }

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    `Status request failed with HTTP ${response.status}.`
                );
            }

            const status =
                String(
                    data.status || ''
                )
                    .trim()
                    .toLowerCase();

            console.log(
                'Reconciliation status:',
                status,
                'run:',
                currentRunId
            );


            /*
             * Update visual progress.
             */
            updateProgressFromStatus(
                status
            );


            /* =================================================
               STILL PROCESSING
            ================================================= */

            if (
                status === 'queued' ||
                status === 'processing' ||
                status === 'running' ||
                status === 'pending'
            ) {
                pollTimer =
                    setTimeout(
                        pollStatus,
                        2500
                    );

                return;
            }


            /* =================================================
               SUCCESS
            ================================================= */

            if (
                status === 'completed' ||
                status === 'complete' ||
                status === 'success'
            ) {
                stopPolling();

                /*
                 * Make all steps green/check immediately.
                 */
                completeAllSteps();

                try {
                    const resultData =
                        await fetchResults(
                            currentRunId
                        );

                    renderResultsUI(
                        resultData,
                        currentRunId
                    );

                    showResultState();

                    showToast(
                        'Reconciliation Completed',
                        'Your reconciliation has completed successfully.',
                        'green',
                        5000
                    );

                    setSubmitButtonLoading(
                        false
                    );

                    isSubmitting =
                        false;

                } catch (resultError) {
                    console.error(
                        'Error loading reconciliation results:',
                        resultError
                    );

                    showToast(
                        'Results Error',
                        resultError.message ||
                        'The reconciliation completed, but the results could not be loaded.',
                        'red',
                        8000
                    );

                    setSubmitButtonLoading(
                        false
                    );

                    isSubmitting =
                        false;
                }

                return;
            }


            /* =================================================
               FAILED
            ================================================= */

            if (
                status === 'failed' ||
                status === 'error'
            ) {
                stopPolling();

                /*
                 * Determine current visual step.
                 */
                let failedStep = 2;

                if (
                    processingStartedAt !==
                    null
                ) {
                    const elapsed =
                        Date.now() -
                        processingStartedAt;

                    if (
                        elapsed >= 20000
                    ) {
                        failedStep = 5;
                    } else if (
                        elapsed >= 12000
                    ) {
                        failedStep = 4;
                    } else if (
                        elapsed >= 6000
                    ) {
                        failedStep = 3;
                    } else if (
                        elapsed >= 2500
                    ) {
                        failedStep = 2;
                    } else {
                        failedStep = 1;
                    }
                }

                failStep(
                    failedStep
                );

                const message =
                    data.message ||
                    data.error ||
                    data.error_message ||
                    'The reconciliation failed. Please check the application logs.';

                showToast(
                    'Reconciliation Failed',
                    message,
                    'red',
                    8000
                );

                setSubmitButtonLoading(
                    false
                );

                isSubmitting =
                    false;

                return;
            }


            /* =================================================
               UNKNOWN STATUS
            ================================================= */

            console.warn(
                'Unknown reconciliation status:',
                status
            );

            pollTimer =
                setTimeout(
                    pollStatus,
                    2500
                );

        } catch (error) {
            console.error(
                'Error while checking reconciliation status:',
                error
            );

            stopPolling();

            stopProgressAnimation();

            showToast(
                'Status Error',
                error.message ||
                'Unable to check reconciliation status.',
                'red',
                10000
            );

            setSubmitButtonLoading(
                false
            );

            isSubmitting =
                false;
        }
    }


    /* =========================================================
       FORM SUBMISSION
    ========================================================= */

    form.addEventListener(
        'submit',
        async event => {
            event.preventDefault();

            if (isSubmitting) {
                return;
            }

            isSubmitting = true;

            stopPolling();

            stopProgressAnimation();

            currentRunId = null;


            /*
             * -------------------------------------------------
             * FILE VALIDATION
             * -------------------------------------------------
             */

            const fileA =
                document.getElementById(
                    'file_source_a'
                ) ||
                form.querySelector(
                    '[name="file_source_a"]'
                );

            const fileB =
                document.getElementById(
                    'file_source_b'
                ) ||
                form.querySelector(
                    '[name="file_source_b"]'
                );

            if (
                !fileA ||
                !fileA.files ||
                fileA.files.length === 0
            ) {
                showToast(
                    'Validation Error',
                    'Please select Source A file.',
                    'red',
                    6000
                );

                isSubmitting = false;

                return;
            }

            if (
                !fileB ||
                !fileB.files ||
                fileB.files.length === 0
            ) {
                showToast(
                    'Validation Error',
                    'Please select Source B file.',
                    'red',
                    6000
                );

                isSubmitting = false;

                return;
            }


            /*
             * -------------------------------------------------
             * OPEN MODAL
             * -------------------------------------------------
             */

            openModal();

            showProgressState();

            setSubmitButtonLoading(
                true
            );


            try {
                const formData =
                    new FormData(
                        form
                    );

                console.log(
                    'Submitting reconciliation request...'
                );


                /*
                 * -------------------------------------------------
                 * SUBMIT
                 * -------------------------------------------------
                 */

                const response =
                    await fetch(
                        actionUrl,
                        {
                            method: 'POST',

                            headers: {
                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest'
                            },

                            body:
                                formData,

                            credentials:
                                'same-origin'
                        }
                    );


                /*
                 * -------------------------------------------------
                 * READ RESPONSE
                 * -------------------------------------------------
                 */

                const responseText =
                    await response.text();

                let initialResult = {};

                try {
                    initialResult =
                        responseText
                            ? JSON.parse(
                                responseText
                            )
                            : {};
                } catch (
                    parseError
                ) {
                    console.error(
                        'Server returned non-JSON response:',
                        responseText
                    );

                    throw new Error(
                        `Server returned an invalid response (HTTP ${response.status}).`
                    );
                }


                /*
                 * -------------------------------------------------
                 * HTTP ERROR
                 * -------------------------------------------------
                 */

                if (!response.ok) {
                    const serverMessage =
                        initialResult.message ||
                        initialResult.error ||
                        null;

                    if (
                        response.status ===
                        504
                    ) {
                        throw new Error(
                            serverMessage ||
                            'The server timed out before accepting the reconciliation job.'
                        );
                    }

                    throw new Error(
                        serverMessage ||
                        `Server returned status ${response.status}.`
                    );
                }


                /*
                 * -------------------------------------------------
                 * RUN ID
                 * -------------------------------------------------
                 */

                const runId =
                    initialResult.run_id ??
                    initialResult.id ??
                    initialResult.run?.id ??
                    null;

                if (!runId) {
                    console.error(
                        'Server response did not contain run_id:',
                        initialResult
                    );

                    throw new Error(
                        'The server accepted the request but did not return a reconciliation run ID.'
                    );
                }

                currentRunId =
                    runId;

                console.log(
                    'Reconciliation queued successfully.',
                    {
                        runId:
                            currentRunId,

                        response:
                            initialResult
                    }
                );


                /*
                 * -------------------------------------------------
                 * INITIAL STATUS
                 * -------------------------------------------------
                 */

                updateProgressFromStatus(
                    initialResult.status ||
                    'queued'
                );


                /*
                 * -------------------------------------------------
                 * START POLLING
                 * -------------------------------------------------
                 */

                pollStatus();

            } catch (error) {
                console.error(
                    'Reconciliation submission error:',
                    error
                );

                stopPolling();

                stopProgressAnimation();

                currentRunId = null;

                showToast(
                    'Execution Error',
                    error.message ||
                    'Unable to start reconciliation.',
                    'red',
                    8000
                );

                if (modalBackdrop) {
                    modalBackdrop.classList.add(
                        'hidden'
                    );
                }

                setSubmitButtonLoading(
                    false
                );

                isSubmitting =
                    false;
            }
        }
    );


    /* =========================================================
       MODAL CLOSE BUTTONS
    ========================================================= */

    if (modalCloseX) {
        modalCloseX.addEventListener(
            'click',
            () => {
                closeModal();
            }
        );
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener(
            'click',
            () => {
                closeModal();
            }
        );
    }


    if (modalBackdrop) {
        modalBackdrop.addEventListener(
            'click',
            event => {
                if (
                    event.target ===
                        modalBackdrop &&
                    !isSubmitting
                ) {
                    closeModal();
                }
            }
        );
    }


    /* =========================================================
       PRINT REPORT HELPERS
    ========================================================= */

    function reportEscape(value) {
        return escapeHtml(
            value === null || value === undefined
                ? ''
                : String(value)
        );
    }


    function reportFormatValue(value) {
        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '—';
        }

        if (typeof value === 'boolean') {
            return value ? 'Yes' : 'No';
        }

        if (typeof value === 'number') {
            return formatNumber(value);
        }

        if (typeof value === 'object') {
            return JSON.stringify(value);
        }

        return String(value);
    }


    function reportLabel(value) {
        return String(value || '')
            .replace(/_/g, ' ')
            .replace(/([a-z])([A-Z])/g, '$1 $2')
            .replace(/\b\w/g, character => character.toUpperCase());
    }


    function reportTable(rows, emptyMessage = 'No records available.') {
        if (
            !Array.isArray(rows) ||
            rows.length === 0
        ) {
            return `
                <div class="report-empty">
                    ${reportEscape(emptyMessage)}
                </div>
            `;
        }

        const objectRows =
            rows.filter(
                row =>
                    row &&
                    typeof row === 'object' &&
                    !Array.isArray(row)
            );

        if (objectRows.length === 0) {
            return `
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.map((row, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${reportEscape(reportFormatValue(row))}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        const columns = [
            ...new Set(
                objectRows.flatMap(
                    row => Object.keys(row)
                )
            )
        ];

        return `
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            ${columns.map(column => `
                                <th>${reportEscape(reportLabel(column))}</th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.map((row, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                ${columns.map(column => {
                                    const value =
                                        row &&
                                        typeof row === 'object'
                                            ? row[column]
                                            : '';

                                    return `
                                        <td>
                                            ${reportEscape(
                                                reportFormatValue(value)
                                            )}
                                        </td>
                                    `;
                                }).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }


    function reportSummaryFields(summary) {
        if (
            !summary ||
            typeof summary !== 'object' ||
            Array.isArray(summary)
        ) {
            return '';
        }

        const entries =
            Object.entries(summary)
                .filter(([key, value]) => {
                    if (
                        key === 'unmatched_source_a' ||
                        key === 'unmatched_source_b' ||
                        key === 'unmatched_a_rows' ||
                        key === 'unmatched_b_rows'
                    ) {
                        return false;
                    }

                    return (
                        value === null ||
                        typeof value !== 'object' ||
                        Array.isArray(value)
                    );
                });

        if (entries.length === 0) {
            return '';
        }

        return `
            <section class="report-section">
                <div class="section-heading">
                    <span class="section-kicker">Reconciliation Details</span>
                    <h2>Run Summary</h2>
                </div>

                <div class="report-detail-grid">
                    ${entries.map(([key, value]) => `
                        <div class="detail-card">
                            <div class="detail-label">
                                ${reportEscape(reportLabel(key))}
                            </div>
                            <div class="detail-value">
                                ${reportEscape(
                                    reportFormatValue(value)
                                )}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </section>
        `;
    }


    function reportObjectTables(summary) {
        if (
            !summary ||
            typeof summary !== 'object' ||
            Array.isArray(summary)
        ) {
            return '';
        }

        return Object.entries(summary)
            .filter(([key, value]) =>
                Array.isArray(value) &&
                value.length > 0 &&
                key !== 'unmatched_a_rows' &&
                key !== 'unmatched_b_rows'
            )
            .map(([key, value]) => `
                <section class="report-section">
                    <div class="section-heading">
                        <span class="section-kicker">Detailed Data</span>
                        <h2>${reportEscape(reportLabel(key))}</h2>
                    </div>

                    ${reportTable(value)}
                </section>
            `)
            .join('');
    }


    function createReportWindow(
        title,
        subtitle,
        bodyHtml
    ) {
        const reportWindow =
            window.open(
                '',
                '_blank',
                'width=1200,height=900'
            );

        if (!reportWindow) {
            showToast(
                'Print Window Blocked',
                'Please allow pop-ups for this site and try again.',
                'red',
                7000
            );

            return null;
        }

        const generatedAt =
            new Date().toLocaleString();

        reportWindow.document.open();

        reportWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta
                    name="viewport"
                    content="width=device-width, initial-scale=1.0"
                >
                <title>${reportEscape(title)}</title>

                <style>
                    @page {
                        size: A4;
                        margin: 14mm;
                    }

                    * {
                        box-sizing: border-box;
                    }

                    html,
                    body {
                        margin: 0;
                        padding: 0;
                        background: #f3f4f6;
                        color: #172033;
                        font-family:
                            Inter,
                            ui-sans-serif,
                            system-ui,
                            -apple-system,
                            BlinkMacSystemFont,
                            "Segoe UI",
                            sans-serif;
                    }

                    body {
                        padding: 32px;
                    }

                    .report {
                        max-width: 1100px;
                        margin: 0 auto;
                        background: #ffffff;
                        box-shadow:
                            0 18px 50px rgba(15, 23, 42, 0.10);
                    }

                    .report-header {
                        padding: 34px 38px 30px;
                        border-bottom: 1px solid #dbe2ea;
                        background: #ffffff;
                    }

                    .brand-line {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 24px;
                    }

                    .brand {
                        font-size: 12px;
                        font-weight: 800;
                        letter-spacing: 0.16em;
                        text-transform: uppercase;
                        color: #475569;
                    }

                    .report-type {
                        font-size: 11px;
                        font-weight: 700;
                        letter-spacing: 0.12em;
                        text-transform: uppercase;
                        color: #64748b;
                    }

                    .report-title {
                        margin: 20px 0 7px;
                        font-size: 30px;
                        line-height: 1.15;
                        font-weight: 800;
                        color: #0f172a;
                    }

                    .report-subtitle {
                        margin: 0;
                        color: #64748b;
                        font-size: 14px;
                        line-height: 1.6;
                    }

                    .report-meta {
                        display: grid;
                        grid-template-columns:
                            repeat(2, minmax(0, 1fr));
                        gap: 10px 28px;
                        margin-top: 24px;
                        padding-top: 18px;
                        border-top: 1px solid #e5e7eb;
                    }

                    .meta-item {
                        font-size: 12px;
                    }

                    .meta-label {
                        display: block;
                        margin-bottom: 3px;
                        color: #94a3b8;
                        font-size: 10px;
                        font-weight: 700;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                    }

                    .meta-value {
                        color: #334155;
                        font-weight: 600;
                    }

                    .report-body {
                        padding: 34px 38px 40px;
                    }

                    .report-section {
                        margin-bottom: 34px;
                        page-break-inside: avoid;
                    }

                    .section-heading {
                        margin-bottom: 16px;
                        padding-bottom: 10px;
                        border-bottom: 1px solid #e2e8f0;
                    }

                    .section-kicker {
                        display: block;
                        margin-bottom: 4px;
                        color: #64748b;
                        font-size: 10px;
                        font-weight: 800;
                        letter-spacing: 0.13em;
                        text-transform: uppercase;
                    }

                    .section-heading h2 {
                        margin: 0;
                        color: #0f172a;
                        font-size: 18px;
                        font-weight: 800;
                    }

                    .metrics-grid {
                        display: grid;
                        grid-template-columns:
                            repeat(4, minmax(0, 1fr));
                        gap: 12px;
                    }

                    .metric-card {
                        min-height: 94px;
                        padding: 16px;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        background: #f8fafc;
                    }

                    .metric-label {
                        color: #64748b;
                        font-size: 10px;
                        font-weight: 800;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                    }

                    .metric-value {
                        margin-top: 8px;
                        color: #0f172a;
                        font-size: 23px;
                        font-weight: 800;
                    }

                    .detail-grid {
                        display: grid;
                        grid-template-columns:
                            repeat(3, minmax(0, 1fr));
                        gap: 10px;
                    }

                    .detail-card {
                        padding: 13px 14px;
                        border: 1px solid #e2e8f0;
                        border-radius: 7px;
                        background: #ffffff;
                    }

                    .detail-label {
                        margin-bottom: 5px;
                        color: #64748b;
                        font-size: 10px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                    }

                    .detail-value {
                        color: #1e293b;
                        font-size: 13px;
                        line-height: 1.45;
                        font-weight: 600;
                        overflow-wrap: anywhere;
                    }

                    .report-table-wrap {
                        overflow: visible;
                    }

                    .report-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 10px;
                    }

                    .report-table th {
                        padding: 9px 8px;
                        border-bottom: 2px solid #cbd5e1;
                        background: #f8fafc;
                        color: #475569;
                        font-size: 9px;
                        font-weight: 800;
                        text-align: left;
                        text-transform: uppercase;
                        letter-spacing: 0.04em;
                    }

                    .report-table td {
                        padding: 8px;
                        border-bottom: 1px solid #e2e8f0;
                        color: #334155;
                        vertical-align: top;
                        overflow-wrap: anywhere;
                    }

                    .report-table tr {
                        page-break-inside: avoid;
                    }

                    .report-empty {
                        padding: 24px;
                        border: 1px dashed #cbd5e1;
                        border-radius: 8px;
                        color: #64748b;
                        text-align: center;
                        font-size: 13px;
                    }

                    .report-footer {
                        display: flex;
                        justify-content: space-between;
                        gap: 20px;
                        padding-top: 18px;
                        border-top: 1px solid #e2e8f0;
                        color: #94a3b8;
                        font-size: 10px;
                    }

                    .print-actions {
                        position: fixed;
                        right: 22px;
                        bottom: 22px;
                        display: flex;
                        gap: 8px;
                    }

                    .print-button {
                        border: 0;
                        border-radius: 7px;
                        padding: 11px 17px;
                        background: #0f172a;
                        color: #ffffff;
                        cursor: pointer;
                        font-size: 12px;
                        font-weight: 700;
                    }

                    .print-button.secondary {
                        background: #e2e8f0;
                        color: #334155;
                    }

                    @media (max-width: 760px) {
                        body {
                            padding: 0;
                        }

                        .report-header,
                        .report-body {
                            padding-left: 20px;
                            padding-right: 20px;
                        }

                        .metrics-grid,
                        .detail-grid,
                        .report-meta {
                            grid-template-columns: 1fr;
                        }

                        .brand-line {
                            display: block;
                        }

                        .report-type {
                            margin-top: 8px;
                        }
                    }

                    @media print {
                        html,
                        body {
                            background: #ffffff;
                        }

                        body {
                            padding: 0;
                        }

                        .report {
                            max-width: none;
                            box-shadow: none;
                        }

                        .print-actions {
                            display: none !important;
                        }

                        .report-header {
                            padding-top: 0;
                        }

                        .report-section {
                            page-break-inside: avoid;
                        }

                        .report-table thead {
                            display: table-header-group;
                        }
                    }
                </style>
            </head>

            <body>
                <main class="report">
                    <header class="report-header">
                        <div class="brand-line">
                            <div class="brand">
                                Reconciliation Report
                            </div>

                            <div class="report-type">
                                ${reportEscape(title)}
                            </div>
                        </div>

                        <h1 class="report-title">
                            ${reportEscape(title)}
                        </h1>

                        <p class="report-subtitle">
                            ${reportEscape(subtitle)}
                        </p>

                        <div class="report-meta">
                            <div class="meta-item">
                                <span class="meta-label">
                                    Run ID
                                </span>
                                <span class="meta-value">
                                    ${reportEscape(
                                        currentRunId
                                    )}
                                </span>
                            </div>

                            <div class="meta-item">
                                <span class="meta-label">
                                    Generated
                                </span>
                                <span class="meta-value">
                                    ${reportEscape(generatedAt)}
                                </span>
                            </div>
                        </div>
                    </header>

                    <div class="report-body">
                        ${bodyHtml}

                        <footer class="report-footer">
                            <span>
                                Confidential reconciliation report
                            </span>

                            <span>
                                Prepared for internal review
                            </span>
                        </footer>
                    </div>
                </main>

                <div class="print-actions">
                    <button
                        type="button"
                        class="print-button secondary"
                        onclick="window.close()"
                    >
                        Close
                    </button>

                    <button
                        type="button"
                        class="print-button"
                        onclick="window.print()"
                    >
                        Print / Save as PDF
                    </button>
                </div>
            </body>
            </html>
        `);

        reportWindow.document.close();

        return reportWindow;
    }


    async function openSummaryReport() {
        if (!currentRunId) {
            showToast(
                'No Run',
                'There is no active reconciliation run.',
                'red',
                5000
            );

            return;
        }

        const reportWindow =
            createReportWindow(
                'Reconciliation Summary',
                'Detailed reconciliation results prepared for review and printing.',
                `
                    <section class="report-section">
                        <div class="section-heading">
                            <span class="section-kicker">
                                Executive Overview
                            </span>
                            <h2>Reconciliation Performance</h2>
                        </div>

                        <div id="summary-report-content">
                            <div class="report-empty">
                                Preparing report...
                            </div>
                        </div>
                    </section>
                `
            );

        if (!reportWindow) {
            return;
        }

        try {
            const resultData =
                await fetchResults(
                    currentRunId
                );

            const summary =
                resultData?.summary || {};

            const matchRate =
                resultData?.match_rate ??
                summary?.match_rate ??
                summary?.match_rate_source_a_percent ??
                null;

            const matchedCount =
                resultData?.matched_count ??
                summary?.matched_count ??
                summary?.matched_transactions_or_groups ??
                null;

            const unmatchedA =
                resultData?.unmatched_a_count ??
                summary?.unmatched_source_a ??
                countValue(resultData?.unmatched_a);

            const unmatchedB =
                resultData?.unmatched_b_count ??
                summary?.unmatched_source_b ??
                countValue(resultData?.unmatched_b);

            const fuzzyCount =
                resultData?.fuzzy_matches ??
                summary?.fuzzy_matches ??
                summary?.fuzzy_match_count ??
                summary?.fuzzy_matches_count ??
                summary?.levenshtein_matches ??
                summary?.fuzzy_count ??
                0;

            const feeCount =
                resultData?.gateway_fee_deductions ??
                summary?.gateway_fee_deductions ??
                summary?.fee_deductions ??
                summary?.fee_matches ??
                summary?.fee_match_count ??
                summary?.gateway_matches ??
                summary?.gateway_match_count ??
                0;

            const splitCount =
                resultData?.split_payments ??
                summary?.split_payments ??
                summary?.split_payment_matches ??
                summary?.split_matches ??
                summary?.n_to_one_matches ??
                summary?.split_count ??
                0;

            const content =
                reportWindow.document
                    .getElementById(
                        'summary-report-content'
                    );

            if (!content) {
                return;
            }

            content.innerHTML = `
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-label">
                            Match Rate
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                matchRate !== null
                                    ? formatPercentage(matchRate)
                                    : '—'
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Matched
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                matchedCount !== null
                                    ? formatNumber(matchedCount)
                                    : '—'
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Unmatched Source A
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                unmatchedA !== null &&
                                unmatchedA !== undefined
                                    ? formatNumber(
                                        countValue(unmatchedA)
                                    )
                                    : '—'
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Unmatched Source B
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                unmatchedB !== null &&
                                unmatchedB !== undefined
                                    ? formatNumber(
                                        countValue(unmatchedB)
                                    )
                                    : '—'
                            )}
                        </div>
                    </div>
                </div>

                <div style="height: 14px;"></div>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-label">
                            Fuzzy Matches
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                formatNumber(fuzzyCount)
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Gateway Fee Deductions
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                formatNumber(feeCount)
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Split Payments
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                formatNumber(splitCount)
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Total Exceptions
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                formatNumber(
                                    (countValue(unmatchedA) || 0) +
                                    (countValue(unmatchedB) || 0)
                                )
                            )}
                        </div>
                    </div>
                </div>
            `;

            content.insertAdjacentHTML(
                'afterend',
                `
                    ${reportSummaryFields(summary)}
                    ${reportObjectTables(summary)}
                `
            );

            reportWindow.focus();

        } catch (error) {
            console.error(
                'Error preparing summary report:',
                error
            );

            reportWindow.close();

            showToast(
                'Report Error',
                error.message ||
                'Unable to prepare the summary report.',
                'red',
                8000
            );
        }
    }


    async function openUnmatchedReport() {
        if (!currentRunId) {
            showToast(
                'No Run',
                'There is no active reconciliation run.',
                'red',
                5000
            );

            return;
        }

        const reportWindow =
            createReportWindow(
                'Unmatched Transactions Report',
                'Detailed exception report containing transactions that could not be reconciled.',
                `
                    <section class="report-section">
                        <div class="section-heading">
                            <span class="section-kicker">
                                Exception Review
                            </span>
                            <h2>Unmatched Transactions</h2>
                        </div>

                        <div id="unmatched-report-content">
                            <div class="report-empty">
                                Preparing report...
                            </div>
                        </div>
                    </section>
                `
            );

        if (!reportWindow) {
            return;
        }

        try {
            const resultData =
                await fetchResults(
                    currentRunId
                );

            const unmatchedA =
                Array.isArray(resultData?.unmatched_a)
                    ? resultData.unmatched_a
                    : [];

            const unmatchedB =
                Array.isArray(resultData?.unmatched_b)
                    ? resultData.unmatched_b
                    : [];

            const summary =
                resultData?.summary || {};

            const content =
                reportWindow.document
                    .getElementById(
                        'unmatched-report-content'
                    );

            if (!content) {
                return;
            }

            content.innerHTML = `
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-label">
                            Source A Exceptions
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                formatNumber(
                                    unmatchedA.length
                                )
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Source B Exceptions
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                formatNumber(
                                    unmatchedB.length
                                )
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Total Exceptions
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                formatNumber(
                                    unmatchedA.length +
                                    unmatchedB.length
                                )
                            )}
                        </div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-label">
                            Match Rate
                        </div>
                        <div class="metric-value">
                            ${reportEscape(
                                resultData?.match_rate !== null &&
                                resultData?.match_rate !== undefined
                                    ? formatPercentage(
                                        resultData.match_rate
                                    )
                                    : '—'
                            )}
                        </div>
                    </div>
                </div>
            `;

            content.insertAdjacentHTML(
                'afterend',
                `
                    <section class="report-section">
                        <div class="section-heading">
                            <span class="section-kicker">
                                Source A
                            </span>
                            <h2>Unmatched Source A Transactions</h2>
                        </div>

                        ${reportTable(
                            unmatchedA,
                            'No unmatched Source A transactions were found.'
                        )}
                    </section>

                    <section class="report-section">
                        <div class="section-heading">
                            <span class="section-kicker">
                                Source B
                            </span>
                            <h2>Unmatched Source B Transactions</h2>
                        </div>

                        ${reportTable(
                            unmatchedB,
                            'No unmatched Source B transactions were found.'
                        )}
                    </section>
                `
            );

            reportWindow.focus();

        } catch (error) {
            console.error(
                'Error preparing unmatched report:',
                error
            );

            reportWindow.close();

            showToast(
                'Report Error',
                error.message ||
                'Unable to prepare the unmatched transactions report.',
                'red',
                8000
            );
        }
    }


    /* =========================================================
       SUMMARY BUTTON
    ========================================================= */

    const summaryBtn =
        document.getElementById(
            'ra-btn-summary'
        );

    if (summaryBtn) {
        summaryBtn.addEventListener(
            'click',
            () => {
                openSummaryReport();
            }
        );
    }


    /* =========================================================
       UNMATCHED EXPORT BUTTON
    ========================================================= */

    const unmatchedBtn =
        document.getElementById(
            'ra-btn-unmatched'
        );

    if (unmatchedBtn) {
        unmatchedBtn.addEventListener(
            'click',
            () => {
                openUnmatchedReport();
            }
        );
    }


    /* =========================================================
       INITIAL STATE
    ========================================================= */

    resetProgressSteps();

});


    /* =========================================================
       INCREMENT AND DECREMENT STATE
    ========================================================= */
    function increment(id, step) {
        const input = document.getElementById(id);
        const val = parseFloat(input.value) || 0;
        input.value = (val + step).toFixed(2);
    }

    function decrement(id, step) {
        const input = document.getElementById(id);
        const val = parseFloat(input.value) || 0;
        input.value = Math.max(0, val - step).toFixed(2);
    }
</script>

</body>
</html>