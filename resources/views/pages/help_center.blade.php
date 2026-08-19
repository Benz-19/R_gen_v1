<!-- docs.html -->
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReconAgent — Business Knowledge Base</title>
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
                    <span class="text-[10px] sm:text-xs font-semibold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-brand-cyan/10 border border-brand-cyan/20 text-brand-cyan truncate">Business Help Center</span>
                </div>
                
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="relative hidden sm:block">
                        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500"></i>
                        <input type="text" placeholder="Search guides & features..." class="bg-black/60 border border-white/10 rounded-lg pl-8 pr-3 py-1.5 text-xs text-white placeholder-neutral-500 focus:border-brand-cyan focus:outline-none w-44 md:w-56">
                    </div>
                    <a href="/" class="text-xs font-medium text-neutral-400 hover:text-white transition px-2.5 sm:px-3 py-1.5 rounded-lg border border-white/10 bg-white/5 shrink-0">
                        <span class="hidden sm:inline">&larr; Back to App</span>
                        <span class="sm:hidden">&larr; App</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8">
            <!-- Sidebar Navigation -->
            <aside class="md:col-span-4 lg:col-span-3 space-y-6">
                <div class="glass-panel p-4 rounded-2xl text-xs space-y-5 md:sticky md:top-24">
                    <div>
                        <span class="text-neutral-500 uppercase tracking-wider text-[10px] font-bold block mb-2.5">Getting Started</span>
                        <ul class="space-y-1">
                            <li>
                                <button onclick="showDoc('overview')" id="nav-overview" class="doc-nav-btn w-full text-left font-semibold px-3 py-2 rounded-xl text-brand-cyan bg-brand-cyan/10 transition flex items-center gap-2">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i> <span>Platform Overview</span>
                                </button>
                            </li>
                            <li>
                                <button onclick="showDoc('data-import')" id="nav-data-import" class="doc-nav-btn w-full text-left text-neutral-400 hover:text-white px-3 py-2 rounded-xl hover:bg-white/5 transition flex items-center gap-2">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 shrink-0"></i> <span>Importing Business Data</span>
                                </button>
                            </li>
                            <li>
                                <button onclick="showDoc('account-setup')" id="nav-account-setup" class="doc-nav-btn w-full text-left text-neutral-400 hover:text-white px-3 py-2 rounded-xl hover:bg-white/5 transition flex items-center gap-2">
                                    <i data-lucide="users" class="w-4 h-4 shrink-0"></i> <span>Team & Permissions</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <span class="text-neutral-500 uppercase tracking-wider text-[10px] font-bold block mb-2.5">Reconciliation & Analytics</span>
                        <ul class="space-y-1">
                            <li>
                                <button onclick="showDoc('matching-rules')" id="nav-matching-rules" class="doc-nav-btn w-full text-left text-neutral-400 hover:text-white px-3 py-2 rounded-xl hover:bg-white/5 transition flex items-center gap-2">
                                    <i data-lucide="sliders" class="w-4 h-4 shrink-0"></i> <span>Smart Matching Rules</span>
                                </button>
                            </li>
                            <li>
                                <button onclick="showDoc('discrepancies')" id="nav-discrepancies" class="doc-nav-btn w-full text-left text-neutral-400 hover:text-white px-3 py-2 rounded-xl hover:bg-white/5 transition flex items-center gap-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i> <span>Resolving Discrepancies</span>
                                </button>
                            </li>
                            <li>
                                <button onclick="showDoc('reports')" id="nav-reports" class="doc-nav-btn w-full text-left text-neutral-400 hover:text-white px-3 py-2 rounded-xl hover:bg-white/5 transition flex items-center gap-2">
                                    <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i> <span>Executive Reports</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>

            <!-- Content Area -->
            <section class="md:col-span-8 lg:col-span-9 space-y-6">
                <div class="glass-panel p-5 sm:p-8 rounded-2xl min-h-[400px] sm:min-h-[520px]">
                    
                    <!-- 1. PLATFORM OVERVIEW -->
                    <div id="doc-overview" class="doc-section space-y-6">
                        <div class="space-y-2">
                            <span class="text-xs font-semibold text-brand-cyan uppercase tracking-wider">Getting Started</span>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Automated Business Reconciliation</h1>
                            <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">
                                ReconAgent eliminates hours of manual data matching. Connect your payment gateways, bank feeds, and internal ERP systems to automatically spot missing payouts, fee mismatches, and revenue variances.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-4 border-t border-white/10">
                            <div class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                                <div class="w-8 h-8 rounded-lg bg-brand-cyan/10 border border-brand-cyan/20 flex items-center justify-center text-brand-cyan">
                                    <i data-lucide="zap" class="w-4 h-4"></i>
                                </div>
                                <h3 class="text-sm font-bold text-white">Instant Matching</h3>
                                <p class="text-xs text-neutral-400">Matches 98% of monthly transaction volumes automatically without manual intervention.</p>
                            </div>
                            <div class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                                <div class="w-8 h-8 rounded-lg bg-brand-accent/10 border border-brand-accent/20 flex items-center justify-center text-brand-accent">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                                </div>
                                <h3 class="text-sm font-bold text-white">Audit Ready</h3>
                                <p class="text-xs text-neutral-400">Complete transaction lineage and step-by-step logs ready for external audits.</p>
                            </div>
                            <div class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2 sm:col-span-2 lg:col-span-1">
                                <div class="w-8 h-8 rounded-lg bg-brand-amber/10 border border-brand-amber/20 flex items-center justify-center text-brand-amber">
                                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                                </div>
                                <h3 class="text-sm font-bold text-white">Revenue Control</h3>
                                <p class="text-xs text-neutral-400">Detect uncollected funds, bank chargebacks, and hidden payment processor fees.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. IMPORTING BUSINESS DATA -->
                    <div id="doc-data-import" class="doc-section hidden space-y-6">
                        <div class="space-y-2">
                            <span class="text-xs font-semibold text-brand-cyan uppercase tracking-wider">Getting Started</span>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Importing Business Data</h1>
                            <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">
                                Upload sales sheets, bank statements, or sync your accounting apps directly without formatting spreadsheets manually.
                            </p>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-white/10">
                            <h3 class="text-sm font-bold text-white">Supported Import Methods</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-4 rounded-xl border border-white/10 bg-black/40 space-y-2">
                                    <div class="flex items-center gap-2 text-white font-semibold text-xs">
                                        <i data-lucide="file-up" class="w-4 h-4 text-brand-cyan shrink-0"></i> CSV & Excel Spreadsheets
                                    </div>
                                    <p class="text-xs text-neutral-400">Drag and drop bank exports, QuickBooks CSVs, or internal sales reports. Smart mapping aligns column headers automatically.</p>
                                </div>
                                <div class="p-4 rounded-xl border border-white/10 bg-black/40 space-y-2">
                                    <div class="flex items-center gap-2 text-white font-semibold text-xs">
                                        <i data-lucide="refresh-cw" class="w-4 h-4 text-brand-accent shrink-0"></i> Automated Platform Integrations
                                    </div>
                                    <p class="text-xs text-neutral-400">Directly sync Stripe, Shopify, Bank Feeds, and ERP systems for daily, background data updates.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. TEAM & PERMISSIONS -->
                    <div id="doc-account-setup" class="doc-section hidden space-y-6">
                        <div class="space-y-2">
                            <span class="text-xs font-semibold text-brand-cyan uppercase tracking-wider">Getting Started</span>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Team & Permissions</h1>
                            <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">
                                Invite finance managers, auditors, and leadership teams with tailored access roles.
                            </p>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-white/10">
                            <div class="p-4 rounded-xl border border-white/5 bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                                <div>
                                    <span class="text-white font-semibold text-xs block">Finance Administrator</span>
                                    <span class="text-xs text-neutral-500">Full access to manage data sources, team roles, and match configurations.</span>
                                </div>
                                <span class="text-xs px-2.5 py-1 rounded-full bg-brand-cyan/10 text-brand-cyan border border-brand-cyan/20 self-start sm:self-center shrink-0">Full Control</span>
                            </div>

                            <div class="p-4 rounded-xl border border-white/5 bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                                <div>
                                    <span class="text-white font-semibold text-xs block">Reconciliation Specialist</span>
                                    <span class="text-xs text-neutral-500">Can review flagged transactions, approve matches, and export summary reports.</span>
                                </div>
                                <span class="text-xs px-2.5 py-1 rounded-full bg-brand-accent/10 text-brand-accent border border-brand-accent/20 self-start sm:self-center shrink-0">Reviewer Access</span>
                            </div>

                            <div class="p-4 rounded-xl border border-white/5 bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                                <div>
                                    <span class="text-white font-semibold text-xs block">Auditor / Executive</span>
                                    <span class="text-xs text-neutral-500">Read-only access to finished reconciliation reports, dashboards, and audit trails.</span>
                                </div>
                                <span class="text-xs px-2.5 py-1 rounded-full bg-neutral-800 text-neutral-400 border border-neutral-700 self-start sm:self-center shrink-0">Read Only</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. SMART MATCHING RULES -->
                    <div id="doc-matching-rules" class="doc-section hidden space-y-6">
                        <div class="space-y-2">
                            <span class="text-xs font-semibold text-brand-cyan uppercase tracking-wider">Reconciliation & Analytics</span>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Smart Matching Rules</h1>
                            <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">
                                Customize how strictly transactions are matched. Configure date tolerance windows, fee offsets, and text recognition rules to fit your business.
                            </p>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-white/10">
                            <div class="p-4 rounded-xl bg-black/60 border border-white/10 space-y-3">
                                <span class="text-xs font-semibold text-white block">Rule Controls Preview</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div class="p-3 bg-white/5 rounded-lg border border-white/5">
                                        <span class="text-neutral-400 block mb-1">Date Buffer</span>
                                        <span class="text-white font-semibold">Match within ±3 calendar days</span>
                                    </div>
                                    <div class="p-3 bg-white/5 rounded-lg border border-white/5">
                                        <span class="text-neutral-400 block mb-1">Processor Fee Deduction</span>
                                        <span class="text-white font-semibold">Auto-calculate 2.9% + $0.30 Stripe fee</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. RESOLVING DISCREPANCIES -->
                    <div id="doc-discrepancies" class="doc-section hidden space-y-6">
                        <div class="space-y-2">
                            <span class="text-xs font-semibold text-brand-cyan uppercase tracking-wider">Reconciliation & Analytics</span>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Resolving Discrepancies</h1>
                            <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">
                                When a record cannot be matched with 100% certainty, ReconAgent flags it for human review with actionable recommendations.
                            </p>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-white/10">
                            <div class="p-4 rounded-xl border border-brand-amber/30 bg-brand-amber/5 space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-0 text-xs">
                                    <span class="text-brand-amber font-semibold flex items-center gap-1.5">
                                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i> Flagged: Partial Payment Mismatch
                                    </span>
                                    <span class="text-neutral-400">Confidence: 82%</span>
                                </div>
                                <p class="text-xs text-neutral-300">Bank record shows $12,000 received vs invoice amount of $12,450. Difference identified as standard $450 wire transfer service fee.</p>
                                <div class="flex flex-wrap gap-2 pt-1">
                                    <button class="px-3 py-1 bg-brand-amber text-black font-semibold rounded-lg text-xs">Approve Offset</button>
                                    <button class="px-3 py-1 bg-white/10 text-white rounded-lg text-xs hover:bg-white/20">Request Investigation</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. EXECUTIVE REPORTS -->
                    <div id="doc-reports" class="doc-section hidden space-y-6">
                        <div class="space-y-2">
                            <span class="text-xs font-semibold text-brand-cyan uppercase tracking-wider">Reconciliation & Analytics</span>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Executive Reports & Insights</h1>
                            <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">
                                Generate clean, executive-ready balance summaries, month-end settlement reports, and discrepancy logs with a single click.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-white/10 text-xs">
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-2">
                                <div class="flex items-center gap-2 text-white font-semibold">
                                    <i data-lucide="pie-chart" class="w-4 h-4 text-brand-cyan shrink-0"></i> Month-End Settlement Summary
                                </div>
                                <p class="text-neutral-400">Comprehensive overview of total matched revenue, uncollected invoices, and bank fees for accounting lock.</p>
                            </div>

                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-2">
                                <div class="flex items-center gap-2 text-white font-semibold">
                                    <i data-lucide="file-check" class="w-4 h-4 text-brand-accent shrink-0"></i> Audit Export (PDF / Excel)
                                </div>
                                <p class="text-neutral-400">Export formatted financial proof sheets ready for internal CFO review or external tax auditors.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
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
                    <a href="/help_center.html" class="text-white">Help Center</a>
                    <a href="/system-status" class="hover:text-white transition">System Status</a>
                    <a href="/privacy" class="hover:text-white transition">Privacy & Terms</a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Interactive Navigation Script -->
    <script>
        lucide.createIcons();

        function showDoc(sectionId) {
            // Hide all document content sections
            const sections = document.querySelectorAll('.doc-section');
            sections.forEach(sec => sec.classList.add('hidden'));

            // Show selected section
            const targetSection = document.getElementById(`doc-${sectionId}`);
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }

            // Reset all navigation button styles
            const navButtons = document.querySelectorAll('.doc-nav-btn');
            navButtons.forEach(btn => {
                btn.className = "doc-nav-btn w-full text-left text-neutral-400 hover:text-white px-3 py-2 rounded-xl hover:bg-white/5 transition flex items-center gap-2";
            });

            // Highlight the active button
            const activeBtn = document.getElementById(`nav-${sectionId}`);
            if (activeBtn) {
                activeBtn.className = "doc-nav-btn w-full text-left font-semibold px-3 py-2 rounded-xl text-brand-cyan bg-brand-cyan/10 transition flex items-center gap-2";
            }
        }
    </script>
</body>
</html>