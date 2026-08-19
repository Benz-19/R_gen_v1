<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReconAgent — Autonomous Financial Reconciliation</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
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
                            accent: '#10b981', // Emerald precision
                            cyan: '#06b6d4',   // ML processing status
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Ambient Backgrounds */
        .bg-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
        .radial-glow {
            background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.12) 0%, rgba(16, 185, 129, 0.05) 30%, rgba(0, 0, 0, 0) 70%);
        }
        /* Glass Cards */
        .glass-card {
            background: rgba(10, 10, 10, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.7);
        }
        /* Scroll Reveal Animation Styles */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-black text-neutral-300 antialiased selection:bg-white selection:text-black font-sans bg-grid overflow-hidden" id="body-root">

    <!-- 5-Second Preloader Screen -->
    <div id="preloader" class="fixed inset-0 z-[100] bg-black flex flex-col items-center justify-center transition-opacity duration-700 px-4 text-center">
        <!-- App Name First -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-white text-black font-bold flex items-center justify-center rounded-xl shadow-lg">
                <i data-lucide="layers" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </div>
            <span class="font-bold text-2xl sm:text-3xl tracking-tight text-white font-sans">ReconAgent</span>
        </div>
        <!-- Rotating Loading Circle -->
        <div class="w-8 h-8 border-2 border-white/10 border-t-brand-accent rounded-full animate-spin"></div>
    </div>

    <!-- Navigation -->
    <header class="fixed top-0 left-0 w-full z-50 bg-black/60 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between">
            <a href="#" class="flex items-center gap-2.5 sm:gap-3 group">
                <div class="w-8 h-8 sm:w-9 sm:h-9 bg-white text-black font-bold flex items-center justify-center rounded-lg group-hover:scale-105 transition duration-300">
                    <i data-lucide="layers" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                </div>
                <span class="font-bold text-lg sm:text-xl tracking-tight text-white">ReconAgent</span>
            </a>

            <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-sm font-medium text-neutral-400">
                <a href="#features" class="hover:text-white transition-colors">Capabilities</a>
                <a href="/demo" class="hover:text-white transition-colors">Interactive Demo</a>
                <a href="#workflow" class="hover:text-white transition-colors">Architecture</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="/login" class="text-xs sm:text-sm font-medium text-neutral-400 hover:text-white transition px-2.5 sm:px-4 py-2">Sign In</a>
                <a href="/register" class="text-xs sm:text-sm font-semibold bg-white text-black hover:bg-neutral-200 transition px-3 sm:px-5 py-2 sm:py-2.5 rounded-lg flex items-center gap-1.5 sm:gap-2 group">
                    <span>Launch App</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4 group-hover:translate-x-1 transition"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-28 sm:pt-36 pb-16 sm:pb-20 md:pt-48 md:pb-32 overflow-hidden radial-glow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">
            
            <div class="reveal inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 rounded-full border border-white/10 bg-white/5 text-[11px] sm:text-xs font-mono text-neutral-300 mb-6 sm:mb-8 backdrop-blur-md max-w-full">
                <span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse shrink-0"></span>
                <span class="truncate">Deterministic Precision + Human-in-the-Loop ML</span>
            </div>

            <h1 class="reveal text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-bold tracking-tight text-white leading-[1.1] sm:leading-[1.05] mb-6 sm:mb-8">
                Reconciliation <br class="hidden sm:inline"/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-neutral-200 to-neutral-600">
                    Without the Friction.
                </span>
            </h1>

            <p class="reveal text-base sm:text-lg md:text-xl text-neutral-400 max-w-2xl mx-auto font-normal leading-relaxed mb-8 sm:mb-10 px-2">
                Automate cross-source ledger matching, highlight discrepancies instantly, and build custom ML models directly from expert decisions.
            </p>

            <div class="reveal flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 mb-12 sm:mb-20 w-full max-w-md sm:max-w-none mx-auto">
                <a href="/login" class="w-full sm:w-auto bg-white text-black hover:bg-neutral-200 font-semibold px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl transition-all flex items-center justify-center gap-2.5 sm:gap-3 text-sm sm:text-base group">
                    <span>Get Started Free</span>
                    <i data-lucide="arrow-up-right" class="w-4 h-4 sm:w-5 sm:h-5 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition"></i>
                </a>
                <a href="/demo" class="w-full sm:w-auto border border-white/10 bg-black/40 text-neutral-300 hover:text-white hover:border-white/20 font-medium px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl transition-all backdrop-blur-md flex items-center justify-center gap-2 text-sm sm:text-base">
                    <i data-lucide="play" class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-current"></i>
                    <span>Try Interactive Matcher</span>
                </a>
            </div>

            <!-- Interactive Terminal Preview -->
            <div id="demo" class="reveal max-w-5xl mx-auto rounded-2xl border border-white/10 bg-neutral-950/80 backdrop-blur-2xl p-3 sm:p-4 shadow-2xl text-left relative overflow-hidden group">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 sm:pb-4 mb-4 border-b border-white/10 px-2 gap-2 sm:gap-0">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-neutral-800"></div>
                        <div class="w-3 h-3 rounded-full bg-neutral-800"></div>
                        <div class="w-3 h-3 rounded-full bg-neutral-800"></div>
                        <span class="text-[11px] sm:text-xs font-mono text-neutral-500 ml-1 sm:ml-2 truncate">ReconEngine // Live Interactive Simulator</span>
                    </div>
                    <div class="flex items-center gap-3 self-start sm:self-auto">
                        <span id="engine-status" class="text-[11px] sm:text-xs font-mono px-2.5 py-1 rounded-md bg-brand-accent/10 text-brand-accent border border-brand-accent/20 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-accent animate-ping"></span>
                            Engine Ready
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs font-mono">
                    <div class="p-3 sm:p-4 rounded-xl border border-white/5 bg-black/50">
                        <div class="flex justify-between text-neutral-500 mb-3 uppercase tracking-wider font-semibold">
                            <span>Bank Statement (CSV)</span>
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-2 text-neutral-300">
                            <div class="p-2.5 rounded bg-white/5 border border-white/5 flex justify-between items-center gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-white truncate">STRIPE PAYOUT #9482</p>
                                    <p class="text-[10px] text-neutral-500 truncate">2026-08-18 • REF: TXN-8812</p>
                                </div>
                                <span class="text-white font-bold shrink-0">$12,450.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 sm:p-4 rounded-xl border border-white/5 bg-black/50">
                        <div class="flex justify-between text-neutral-500 mb-3 uppercase tracking-wider font-semibold">
                            <span>Internal Ledger (ERP)</span>
                            <i data-lucide="database" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-2 text-neutral-300">
                            <div class="p-2.5 rounded bg-white/5 border border-white/5 flex justify-between items-center gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-white truncate">Stripe Inc Transfer</p>
                                    <p class="text-[10px] text-neutral-500 truncate">2026-08-19 • REF: INV-9482-ST</p>
                                </div>
                                <span class="text-white font-bold shrink-0">$12,450.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 sm:mt-4 p-3 sm:p-4 rounded-xl border border-white/10 bg-white/5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-brand-cyan/10 border border-brand-cyan/30 flex items-center justify-center text-brand-cyan shrink-0">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-white">Predicted Match Score: <span id="match-score" class="text-brand-accent">99.2%</span></p>
                            <p id="match-desc" class="text-[10px] sm:text-[11px] text-neutral-400">Deterministic exact amount match + High text similarity index.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button onclick="simulateAction('confirm')" class="flex-1 sm:flex-none px-3.5 sm:px-4 py-2 rounded-lg bg-brand-accent hover:bg-emerald-600 text-black font-semibold text-xs transition flex items-center justify-center gap-1.5">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Confirm Match
                        </button>
                        <button onclick="simulateAction('flag')" class="flex-1 sm:flex-none px-3.5 sm:px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-semibold text-xs transition flex items-center justify-center gap-1.5">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Flag Discrepancy
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Capabilities Grid -->
    <section id="features" class="py-16 sm:py-24 md:py-32 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="reveal flex flex-col md:flex-row md:items-end justify-between mb-10 sm:mb-16">
                <div>
                    <span class="text-xs font-mono text-neutral-500 uppercase tracking-widest block mb-2 sm:mb-3">Core Engine</span>
                    <h2 class="text-2xl sm:text-4xl md:text-5xl font-bold text-white tracking-tight">Built for Total Accuracy.</h2>
                </div>
                <p class="text-neutral-400 max-w-md mt-3 md:mt-0 text-xs sm:text-sm">
                    ReconAgent never makes unverified financial decisions. It combines deterministic speed with learning algorithms.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                <!-- Card 1 -->
                <div class="reveal glass-card p-6 sm:p-8 rounded-2xl group">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white mb-5 sm:mb-6 group-hover:bg-white group-hover:text-black transition duration-300">
                        <i data-lucide="sliders" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-white mb-2 sm:mb-3">Deterministic Rules First</h3>
                    <p class="text-neutral-400 text-xs sm:text-sm leading-relaxed">
                        Execute immediate exact matching across amounts, dates, and reference codes with configured tolerances before passing to ML.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="reveal glass-card p-6 sm:p-8 rounded-2xl group">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white mb-5 sm:mb-6 group-hover:bg-white group-hover:text-black transition duration-300">
                        <i data-lucide="user-check" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-white mb-2 sm:mb-3">Human-in-the-Loop</h3>
                    <p class="text-neutral-400 text-xs sm:text-sm leading-relaxed">
                        Uncertain transactions are routed to your finance team. Human decisions automatically generate labeled examples for continuous training.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="reveal glass-card p-6 sm:p-8 rounded-2xl group">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white mb-5 sm:mb-6 group-hover:bg-white group-hover:text-black transition duration-300">
                        <i data-lucide="file-text" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-white mb-2 sm:mb-3">Natural Explanations</h3>
                    <p class="text-neutral-400 text-xs sm:text-sm leading-relaxed">
                        Get automated, clear explanations detailing why a match was suggested or why a record was flagged for review.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Architecture Tabs Section -->
    <section id="workflow" class="py-16 sm:py-24 md:py-32 bg-black/40 border-t border-white/5 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="reveal text-center max-w-3xl mx-auto mb-10 sm:mb-16">
                <span class="text-xs font-mono text-neutral-500 uppercase tracking-widest block mb-2 sm:mb-3">Platform Architecture</span>
                <h2 class="text-2xl sm:text-4xl md:text-5xl font-bold text-white tracking-tight">Structured Pipeline</h2>
            </div>

            <!-- Tab Buttons -->
            <div class="reveal flex justify-center mb-8 sm:mb-12">
                <div class="flex flex-col sm:flex-row p-1.5 rounded-xl border border-white/10 bg-neutral-950 font-mono text-xs w-full sm:w-auto gap-1 sm:gap-0">
                    <button onclick="switchTab(1)" id="tab-btn-1" class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg text-white bg-white/10 font-semibold transition text-center">1. Import & Validate</button>
                    <button onclick="switchTab(2)" id="tab-btn-2" class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg text-neutral-400 hover:text-white transition text-center">2. Match & Predict</button>
                    <button onclick="switchTab(3)" id="tab-btn-3" class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg text-neutral-400 hover:text-white transition text-center">3. Resolve & Learn</button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="reveal max-w-4xl mx-auto glass-card p-6 sm:p-8 rounded-2xl">
                <div id="tab-content-1" class="space-y-3 sm:space-y-4">
                    <div class="flex items-center gap-2.5 sm:gap-3 text-brand-accent font-mono text-xs sm:text-sm">
                        <i data-lucide="file-spreadsheets" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        <span>Stage 01 — Normalized Data Ingestion</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-white">Multi-Source Import Pipeline</h3>
                    <p class="text-neutral-400 leading-relaxed text-xs sm:text-sm">
                        Upload raw CSV or Excel bank statements and internal accounting exports. The system automatically normalizes dates, handles currencies, maps dynamic columns, and isolates dataset records per organization.
                    </p>
                </div>

                <div id="tab-content-2" class="hidden space-y-3 sm:space-y-4">
                    <div class="flex items-center gap-2.5 sm:gap-3 text-brand-cyan font-mono text-xs sm:text-sm">
                        <i data-lucide="cpu" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        <span>Stage 02 — Hybrid Reconciliation Engine</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-white">Rule Execution + ML Inference</h3>
                    <p class="text-neutral-400 leading-relaxed text-xs sm:text-sm">
                        First-pass deterministic rules filter out exact matches. Remaining candidate pairs are processed by the ML engine to compute confidence scores based on text similarity, date variance, and amount delta.
                    </p>
                </div>

                <div id="tab-content-3" class="hidden space-y-3 sm:space-y-4">
                    <div class="flex items-center gap-2.5 sm:gap-3 text-white font-mono text-xs sm:text-sm">
                        <i data-lucide="refresh-cw" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        <span>Stage 03 — Human Exception Loop</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-white">Continuous Model Retraining</h3>
                    <p class="text-neutral-400 leading-relaxed text-xs sm:text-sm">
                        Finance personnel review flagged exceptions inside an intuitive UI. Approved and rejected predictions are logged to an auditable database, continuously generating training data for future model versions.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-8 sm:py-12 bg-black text-xs text-neutral-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col md:flex-row justify-between items-center gap-4 sm:gap-6 text-center md:text-left">
            <div class="flex items-center gap-3 flex-wrap justify-center md:justify-start">
                <div class="w-6 h-6 bg-white text-black font-bold flex items-center justify-center rounded shrink-0">
                    R
                </div>
                <span class="font-bold text-white text-sm">ReconAgent</span>
                <span class="hidden sm:inline">— Financial Reconciliation Platform</span>
            </div>
            <div>
                © 2026 ReconAgent. All rights reserved.
            </div>
            <div class="flex items-center gap-4 flex-wrap justify-center">
                <a href="/help-center" class="hover:text-white transition">Help Center</a>
                <a href="/system-status" class="hover:text-white transition">System Status</a>
                <a href="/privacy" class="hover:text-white transition">Privacy & Terms</a>
            </div>
        </div>
    </footer>

    <!-- Interactive & Animation Scripts -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // 1. Preloader Timer Logic (5 Seconds)
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const preloader = document.getElementById('preloader');
                const body = document.getElementById('body-root');
                
                preloader.classList.add('opacity-0', 'pointer-events-none');
                body.classList.remove('overflow-hidden');
                
                // Initialize Scroll Observer after preloader clears
                initScrollObserver();
                
                setTimeout(() => preloader.remove(), 700);
            }, 5000);
        });

        // 2. Scroll Observer for Continuous Appear/Disappear Effect
        function initScrollObserver() {
            const revealElements = document.querySelectorAll('.reveal');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    } else {
                        entry.target.classList.remove('active'); // Disappears when out of view
                    }
                });
            }, {
                threshold: 0.15
            });

            revealElements.forEach(el => observer.observe(el));
        }

        // Simulator Logic
        function simulateAction(type) {
            const status = document.getElementById('engine-status');
            const score = document.getElementById('match-score');
            const desc = document.getElementById('match-desc');

            if (type === 'confirm') {
                status.className = "text-[11px] sm:text-xs font-mono px-2.5 py-1 rounded-md bg-brand-accent/20 text-brand-accent border border-brand-accent/30 flex items-center gap-1.5";
                status.innerHTML = `<i data-lucide="check" class="w-3 h-3"></i> Match Recorded & Saved to Audit Log`;
                desc.innerText = "Decision saved. Labeled example logged for v1.3 retraining cycle.";
                lucide.createIcons();
            } else {
                status.className = "text-[11px] sm:text-xs font-mono px-2.5 py-1 rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-1.5";
                status.innerHTML = `<i data-lucide="alert-circle" class="w-3 h-3"></i> Routed to Exception Queue`;
                desc.innerText = "Discrepancy assigned to Finance Analyst for manual review.";
                lucide.createIcons();
            }
        }

        // Tab Switcher Logic
        function switchTab(tabIndex) {
            for (let i = 1; i <= 3; i++) {
                document.getElementById(`tab-content-${i}`).classList.add('hidden');
                const btn = document.getElementById(`tab-btn-${i}`);
                btn.className = "w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg text-neutral-400 hover:text-white transition text-center";
            }
            
            document.getElementById(`tab-content-${tabIndex}`).classList.remove('hidden');
            const activeBtn = document.getElementById(`tab-btn-${tabIndex}`);
            activeBtn.className = "w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-lg text-white bg-white/10 font-semibold transition text-center";
        }
    </script>
</body>
</html>