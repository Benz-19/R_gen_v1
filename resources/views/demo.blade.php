<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ReconAgent — Interactive Reconciliation Simulator</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
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
            background: rgba(10, 10, 10, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        @keyframes pageSlideRight {
            0% { 
                opacity: 0; 
                transform: translateX(-160px); 
            }
            100% { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
        .animate-page-entry {
            animation: pageSlideRight 1.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
    </style>
</head>
<body class="bg-black text-neutral-300 antialiased font-sans min-h-screen bg-grid flex flex-col overflow-x-hidden">

    <!-- Sliding Content Wrapper -->
    <div class="flex-1 flex flex-col opacity-0 animate-page-entry">

        <!-- Top Navigation Header -->
        <header class="border-b border-white/10 bg-black/60 backdrop-blur-xl sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="/" class="w-8 h-8 bg-white text-black font-bold flex items-center justify-center rounded-lg hover:scale-105 transition shrink-0">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </a>
                    <span class="font-bold text-base sm:text-lg text-white tracking-tight">ReconAgent</span>
                    <span class="text-[10px] sm:text-xs font-mono px-2 py-0.5 rounded bg-white/10 text-neutral-400">v1.0</span>
                </div>
                
                <!-- Navigation Actions (Desktop + Mobile Responsive) -->
                <div class="flex items-center gap-2">
                    <a href="/" class="text-xs font-medium text-neutral-400 hover:text-white transition px-2.5 sm:px-3 py-1.5 rounded-lg border border-white/10 bg-white/5 flex items-center gap-1.5">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Back to Landing Page</span>
                        <span class="sm:hidden">Back</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Application Interface -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Input Simulation Controls (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Quick Preset Selector -->
                <div class="glass-panel p-5 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono text-neutral-400 uppercase tracking-wider font-semibold">Pre-built Test Cases</span>
                        <i data-lucide="sparkles" class="w-4 h-4 text-brand-cyan"></i>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs font-medium">
                        <button onclick="loadPreset('exact')" class="p-2.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 text-left transition flex items-center justify-between">
                            <span>Exact Match</span>
                            <span class="w-2 h-2 rounded-full bg-brand-accent"></span>
                        </button>
                        <button onclick="loadPreset('fuzzy')" class="p-2.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 text-left transition flex items-center justify-between">
                            <span>Human Review</span>
                            <span class="w-2 h-2 rounded-full bg-brand-amber"></span>
                        </button>
                        <button onclick="loadPreset('mismatch')" class="p-2.5 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 text-left transition flex items-center justify-between">
                            <span>No Match</span>
                            <span class="w-2 h-2 rounded-full bg-brand-rose"></span>
                        </button>
                        <button onclick="generateRandom()" class="p-2.5 rounded-xl border border-brand-cyan/30 bg-brand-cyan/10 hover:bg-brand-cyan/20 text-brand-cyan text-left transition flex items-center justify-between font-mono">
                            <span>Random Pair</span>
                            <i data-lucide="shuffle" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Transaction Data Form -->
                <form id="reconcileForm" class="glass-panel p-6 rounded-2xl space-y-6">
                    
                    <!-- Bank Record Inputs -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-xs font-mono text-neutral-400 uppercase tracking-wider border-b border-white/10 pb-2">
                            <i data-lucide="building-2" class="w-4 h-4 text-white"></i>
                            <span>Source A: Bank Statement (CSV)</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="col-span-2">
                                <label class="block text-neutral-400 mb-1 font-mono">Description</label>
                                <input type="text" id="bank_desc" required class="w-full bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-white font-mono focus:border-white focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-neutral-400 mb-1 font-mono">Amount ($)</label>
                                <input type="number" step="0.01" id="bank_amount" required class="w-full bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-white font-mono focus:border-white focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-neutral-400 mb-1 font-mono">Date</label>
                                <input type="date" id="bank_date" required class="w-full bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-white font-mono focus:border-white focus:outline-none">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-neutral-400 mb-1 font-mono">Reference Code</label>
                                <input type="text" id="bank_ref" required class="w-full bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-white font-mono focus:border-white focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Ledger Record Inputs -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-xs font-mono text-neutral-400 uppercase tracking-wider border-b border-white/10 pb-2">
                            <i data-lucide="database" class="w-4 h-4 text-white"></i>
                            <span>Source B: Internal Ledger (ERP)</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="col-span-2">
                                <label class="block text-neutral-400 mb-1 font-mono">Description</label>
                                <input type="text" id="ledger_desc" required class="w-full bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-white font-mono focus:border-white focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-neutral-400 mb-1 font-mono">Amount ($)</label>
                                <input type="number" step="0.01" id="ledger_amount" required class="w-full bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-white font-mono focus:border-white focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-neutral-400 mb-1 font-mono">Date</label>
                                <input type="date" id="ledger_date" required class="w-full bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-white font-mono focus:border-white focus:outline-none">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-neutral-400 mb-1 font-mono">Reference Code</label>
                                <input type="text" id="ledger_ref" required class="w-full bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-white font-mono focus:border-white focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button type="submit" id="runBtn" class="w-full bg-white text-black font-semibold hover:bg-neutral-200 py-3 rounded-xl transition flex items-center justify-center gap-2 group text-sm">
                        <i data-lucide="cpu" class="w-4 h-4 group-hover:rotate-12 transition"></i>
                        <span>Execute Reconciliation Run</span>
                    </button>
                </form>
            </div>

            <!-- Right Column: Live Execution Pipeline & Analysis Visualizer (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Real-Time Inspection Terminal -->
                <div class="glass-panel p-6 rounded-2xl space-y-6 min-h-[580px] flex flex-col justify-between">
                    
                    <!-- Terminal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-white/20"></div>
                            <div class="w-3 h-3 rounded-full bg-white/20"></div>
                            <div class="w-3 h-3 rounded-full bg-white/20"></div>
                            <span class="text-xs font-mono text-neutral-400 ml-2">ReconEngine // Processing Pipeline</span>
                        </div>
                        <span id="pipeline-status" class="text-xs font-mono px-2.5 py-1 rounded bg-white/5 border border-white/10 text-neutral-400">
                            Idle
                        </span>
                    </div>

                    <!-- Animated Steps Visualizer -->
                    <div id="steps-container" class="space-y-3 font-mono text-xs my-auto">
                        
                        <!-- Step 1 -->
                        <div id="step-1" class="p-3.5 rounded-xl border border-white/5 bg-white/[0.02] flex items-center justify-between transition-all duration-300">
                            <div class="flex items-center gap-3">
                                <div class="step-icon w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-neutral-400">1</div>
                                <span class="step-label font-medium text-text-muted text-sm">Data Ingestion & Normalization</span>
                            </div>
                            <span class="step-state step-status text-neutral-500">Pending</span>
                        </div>

                        <!-- Step 2 -->
                        <div id="step-2" class="p-3.5 rounded-xl border border-white/5 bg-white/[0.02] flex items-center justify-between transition-all duration-300">
                            <div class="flex items-center gap-3">
                                <div class="step-icon w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-neutral-400">2</div>
                                <span class="step-label font-medium text-text-muted text-sm">Deterministic Match Verification</span>
                            </div>
                            <span class="step-state step-status text-neutral-500">Pending</span>
                        </div>

                        <!-- Step 3 -->
                        <div id="step-3" class="p-3.5 rounded-xl border border-white/5 bg-white/[0.02] flex items-center justify-between transition-all duration-300">
                            <div class="flex items-center gap-3">
                                <div class="step-icon w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-neutral-400">3</div>
                                <span class="step-label font-medium text-text-muted text-sm">ML Scoring & Distance Weights</span>
                            </div>
                            <span class="step-state step-status text-neutral-500">Pending</span>
                        </div>
                    </div>

                    <!-- Dynamic Output Results Box -->
                    <div id="results-panel" class="hidden space-y-4 pt-4 border-t border-white/10">
                        
                        <!-- Top Match Badge + Score -->
                        <div class="p-4 rounded-xl border border-white/10 bg-white/5 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 block">Prediction Verdict</span>
                                <span id="res-status" class="text-lg font-bold text-white tracking-wide">--</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 block">Confidence Score</span>
                                <span id="res-score" class="text-2xl font-mono font-bold text-brand-accent">0%</span>
                            </div>
                        </div>

                        <!-- Metrics Breakdown -->
                        <div class="grid grid-cols-3 gap-3 font-mono text-xs">
                            <div class="p-3 rounded-lg border border-white/5 bg-black/40">
                                <span class="text-neutral-500 text-[10px] block">Amount Delta</span>
                                <span id="res-amount-diff" class="text-white font-semibold">$0.00</span>
                            </div>
                            <div class="p-3 rounded-lg border border-white/5 bg-black/40">
                                <span class="text-neutral-500 text-[10px] block">Date Variance</span>
                                <span id="res-days-diff" class="text-white font-semibold">0 Days</span>
                            </div>
                            <div class="p-3 rounded-lg border border-white/5 bg-black/40">
                                <span class="text-neutral-500 text-[10px] block">Text Similarity</span>
                                <span id="res-text-sim" class="text-white font-semibold">0%</span>
                            </div>
                        </div>

                        <!-- Reason Output -->
                        <div class="p-3 rounded-lg border border-white/10 bg-white/5 text-xs">
                            <span class="text-[10px] font-mono text-neutral-400 block mb-1">Engine Explanation:</span>
                            <p id="res-explanation" class="text-neutral-300 leading-relaxed font-sans">---</p>
                        </div>

                        <!-- JSON Drawer -->
                        <details class="text-xs font-mono group">
                            <summary class="cursor-pointer text-neutral-500 hover:text-white transition flex items-center gap-1 py-1">
                                <i data-lucide="code" class="w-3.5 h-3.5"></i> View Raw JSON Response
                            </summary>
                            <pre id="raw-json" class="mt-2 p-3 rounded bg-black border border-white/10 text-[11px] text-brand-cyan overflow-x-auto max-h-40"></pre>
                        </details>
                    </div>

                </div>
            </div>
        </main>

        <!-- Application Footer -->
        <footer class="border-t border-white/10 bg-black/80 backdrop-blur-xl py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-mono text-neutral-500">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse"></span>
                    <span>ReconEngine Infrastructure Operational</span>
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

    <!-- Execution Logic JavaScript -->
    <script>
        lucide.createIcons();

        // Preset Test Cases
        const presets = {
            exact: {
                bank: { desc: "STRIPE PAYOUT #9482", amount: 12450.00, date: "2026-08-18", ref: "TXN-8812" },
                ledger: { desc: "Stripe Inc Transfer", amount: 12450.00, date: "2026-08-19", ref: "INV-9482-ST" }
            },
            fuzzy: {
                bank: { desc: "WIRE OUT: ACME CORP INC", amount: 4850.50, date: "2026-08-10", ref: "WIRE-7731" },
                ledger: { desc: "Acme Corporation Software Vendor", amount: 4820.00, date: "2026-08-14", ref: "PO-7731" }
            },
            mismatch: {
                bank: { desc: "AWS CLOUD SERVICES", amount: 1290.00, date: "2026-08-01", ref: "REF-0019" },
                ledger: { desc: "OFFICE DEPOT SUPPLIES", amount: 340.20, date: "2026-08-18", ref: "INV-8821" }
            }
        };

        function loadPreset(key) {
            const data = presets[key];
            populateForm(data.bank, data.ledger);
        }

        function populateForm(bank, ledger) {
            document.getElementById('bank_desc').value = bank.desc;
            document.getElementById('bank_amount').value = bank.amount;
            document.getElementById('bank_date').value = bank.date;
            document.getElementById('bank_ref').value = bank.ref;

            document.getElementById('ledger_desc').value = ledger.desc;
            document.getElementById('ledger_amount').value = ledger.amount;
            document.getElementById('ledger_date').value = ledger.date;
            document.getElementById('ledger_ref').value = ledger.ref;
        }

        function generateRandom() {
            const randId = Math.floor(1000 + Math.random() * 9000);
            const randAmount = (Math.random() * 5000 + 50).toFixed(2);
            
            const randomBank = {
                desc: `ACH TRANSFER TXN #${randId}`,
                amount: randAmount,
                date: "2026-08-15",
                ref: `REF-${randId}`
            };

            const variance = Math.random() > 0.5 ? 0 : (Math.random() * 20).toFixed(2);
            const randomLedger = {
                desc: `Vendor Payout #${randId}`,
                amount: (parseFloat(randAmount) + parseFloat(variance)).toFixed(2),
                date: "2026-08-17",
                ref: `INV-${randId}`
            };

            populateForm(randomBank, randomLedger);
        }

        // Initialize with default preset
        loadPreset('exact');

        const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

        // Helper function to reset a specific stage indicator
        function resetStep(stepNum) {
            const stepEl = document.getElementById(`step-${stepNum}`);
            if (!stepEl) return;
            
            stepEl.className = "p-3.5 rounded-xl border border-white/5 bg-white/[0.02] flex items-center justify-between transition-all duration-300";

            const icon = stepEl.querySelector('.step-icon');
            const label = stepEl.querySelector('.step-label');
            const badge = stepEl.querySelector('.step-status');

            if (icon) {
                icon.className = "step-icon w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-neutral-400 font-mono text-xs";
                icon.innerHTML = stepNum;
            }
            if (label) label.className = "step-label font-medium text-neutral-400 text-xs font-mono";
            if (badge) {
                badge.className = "step-status step-state text-xs font-mono text-neutral-500 opacity-50";
                badge.textContent = "Pending";
            }
        }

        // Helper function to mark a stage as active/processing
        async function activateStep(stepNum, statusText = "Processing...") {
            const stepEl = document.getElementById(`step-${stepNum}`);
            if (!stepEl) return;

            stepEl.className = "p-3.5 rounded-xl border border-brand-cyan/30 bg-brand-cyan/5 flex items-center justify-between transition-all duration-300";

            const icon = stepEl.querySelector('.step-icon');
            const label = stepEl.querySelector('.step-label');
            const badge = stepEl.querySelector('.step-status');

            if (icon) icon.className = "step-icon w-6 h-6 rounded-full bg-brand-cyan/20 border border-brand-cyan text-brand-cyan flex items-center justify-center font-mono text-xs animate-pulse";
            if (label) label.className = "step-label font-medium text-brand-cyan text-xs font-mono";
            if (badge) {
                badge.className = "step-status step-state text-xs font-mono text-brand-cyan animate-pulse font-semibold";
                badge.textContent = statusText;
            }
        }

        // Helper function to mark a stage as completed
        function completeStep(stepNum, statusText = "Done") {
            const stepEl = document.getElementById(`step-${stepNum}`);
            if (!stepEl) return;

            stepEl.className = "p-3.5 rounded-xl border border-brand-accent/30 bg-brand-accent/5 flex items-center justify-between transition-all duration-300";

            const icon = stepEl.querySelector('.step-icon');
            const label = stepEl.querySelector('.step-label');
            const badge = stepEl.querySelector('.step-status');

            if (icon) {
                icon.className = "step-icon w-6 h-6 rounded-full bg-brand-accent/20 border border-brand-accent text-brand-accent flex items-center justify-center font-mono text-xs";
                icon.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
            }
            if (label) label.className = "step-label font-medium text-white text-xs font-mono";
            if (badge) {
                badge.className = "step-status step-state text-xs font-mono text-brand-accent font-semibold";
                badge.textContent = statusText;
            }
        }

        // Helper function to populate the DOM with API execution results
        function renderResults(data) {
            const resultsPanel = document.getElementById('results-panel');
            if (!resultsPanel) return;

            const statusVal = data.status || data.match_type || 'MANUAL_REVIEW';
            const resStatus = document.getElementById('res-status');
            const resScore = document.getElementById('res-score');
            
            if (resStatus) resStatus.textContent = statusVal.replace(/_/g, ' ');

            const confidenceValue = data.confidence_score !== undefined 
                ? Math.round(data.confidence_score * 100) 
                : (data.match_score !== undefined ? data.match_score : 0);

            if (resScore) resScore.textContent = `${confidenceValue}%`;

            if (resScore && resStatus) {
                if (statusVal.includes('AUTOMATIC') || statusVal.includes('EXACT') || statusVal === 'exact') {
                    resScore.className = "text-2xl font-mono font-bold text-brand-accent";
                    resStatus.className = "text-lg font-bold text-brand-accent tracking-wide";
                } else if (statusVal.includes('HUMAN') || statusVal.includes('NEEDS') || statusVal.includes('FUZZY') || statusVal === 'fuzzy') {
                    resScore.className = "text-2xl font-mono font-bold text-brand-amber";
                    resStatus.className = "text-lg font-bold text-brand-amber tracking-wide";
                } else {
                    resScore.className = "text-2xl font-mono font-bold text-brand-rose";
                    resStatus.className = "text-lg font-bold text-brand-rose tracking-wide";
                }
            }

            const amountDiffEl = document.getElementById('res-amount-diff');
            if (amountDiffEl) {
                const diffVal = data.amount_diff !== undefined 
                    ? data.amount_diff 
                    : (data.breakdown && data.breakdown.amount !== undefined ? Math.abs(1 - data.breakdown.amount) : 0);
                amountDiffEl.textContent = `$${Number(diffVal).toFixed(2)}`;
            }

            const daysDiffEl = document.getElementById('res-days-diff');
            if (daysDiffEl) {
                const daysVal = data.days_diff !== undefined ? data.days_diff : 0;
                daysDiffEl.textContent = `${daysVal} Day(s)`;
            }

            const textSimEl = document.getElementById('res-text-sim');
            if (textSimEl) {
                const simVal = data.text_similarity !== undefined 
                    ? data.text_similarity 
                    : (data.breakdown && data.breakdown.description !== undefined ? Math.round(data.breakdown.description * 100) : 0);
                textSimEl.textContent = `${simVal}%`;
            }

            const explanationEl = document.getElementById('res-explanation');
            if (explanationEl) {
                explanationEl.textContent = data.explanation || data.reason || "Reconciliation pipeline run finished successfully.";
            }

            const rawJsonEl = document.getElementById('raw-json');
            if (rawJsonEl) {
                rawJsonEl.textContent = JSON.stringify(data, null, 2);
            }

            resultsPanel.classList.remove('hidden');
            resultsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Attach Form Submit Listener
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('reconcileForm');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const runBtn = document.getElementById('runBtn');
                const resultsPanel = document.getElementById('results-panel');
                const statusBadge = document.getElementById('pipeline-status');

                runBtn.disabled = true;
                runBtn.classList.add('opacity-50');
                if (resultsPanel) resultsPanel.classList.add('hidden');

                if (statusBadge) {
                    statusBadge.className = "text-xs font-mono px-2.5 py-1 rounded bg-brand-cyan/10 border border-brand-cyan/30 text-brand-cyan flex items-center gap-1.5";
                    statusBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-brand-cyan animate-ping"></span> Running Pipeline...`;
                }

                // Smooth scroll to steps container on mobile / smaller screens
                const stepsContainer = document.getElementById('steps-container');
                if (stepsContainer) {
                    stepsContainer.closest('.glass-panel')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }

                // Reset Pipeline Stage Badges
                resetStep(1);
                resetStep(2);
                resetStep(3);

                // Construct Request Payload from Input Fields
                const payload = {
                    bank_record: {
                        id: "BNK-" + Date.now(),
                        description: document.getElementById('bank_desc')?.value || "",
                        amount: parseFloat(document.getElementById('bank_amount')?.value || 0),
                        date: document.getElementById('bank_date')?.value || "",
                        reference: document.getElementById('bank_ref')?.value || ""
                    },
                    ledger_record: {
                        id: "LDG-" + Date.now(),
                        description: document.getElementById('ledger_desc')?.value || "",
                        amount: parseFloat(document.getElementById('ledger_amount')?.value || 0),
                        date: document.getElementById('ledger_date')?.value || "",
                        reference: document.getElementById('ledger_ref')?.value || ""
                    }
                };

                // Animated Step 1: Data Normalization
                await activateStep(1, "Parsing & Normalizing...");
                await delay(1200);
                completeStep(1, "Normalized");

                // Animated Step 2: Deterministic Rule Validation
                await activateStep(2, "Verifying Deterministic Rules...");
                await delay(1400);
                completeStep(2, "Rules Checked");

                // Animated Step 3 & API Fetch Execution
                await activateStep(3, "Calculating Weighted Scores...");
                await delay(1200);

                try {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                    const response = await fetch('/api/v1/reconcile/match-demo', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) {
                        throw new Error(`Server returned HTTP ${response.status}`);
                    }

                    const data = await response.json();

                    completeStep(3, "Scored");

                    if (statusBadge) {
                        statusBadge.className = "text-xs font-mono px-2.5 py-1 rounded bg-brand-accent/20 border border-brand-accent/30 text-brand-accent";
                        statusBadge.textContent = "Run Completed";
                    }

                    renderResults(data);

                } catch (err) {
                    if (statusBadge) {
                        statusBadge.className = "text-xs font-mono px-2.5 py-1 rounded bg-brand-rose/20 border border-brand-rose/30 text-brand-rose";
                        statusBadge.textContent = "Execution Failed";
                    }
                    alert("Error executing reconciliation pipeline: " + err.message);
                } finally {
                    runBtn.disabled = false;
                    runBtn.classList.remove('opacity-50');
                }
            });
        });
    </script>
</body>
</html>