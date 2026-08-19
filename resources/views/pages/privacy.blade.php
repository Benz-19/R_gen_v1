<!-- privacy.html -->
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReconAgent — Privacy Policy & Data Processing Terms</title>
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
                    <span class="text-[10px] sm:text-xs font-semibold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-brand-cyan/10 border border-brand-cyan/20 text-brand-cyan shrink-0">
                        <span class="hidden md:inline">Privacy Policy & Machine Learning Terms</span>
                        <span class="md:hidden">Privacy & Terms</span>
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
            
            <div class="space-y-2 sm:space-y-3">
                <span class="text-[10px] sm:text-xs font-semibold text-brand-cyan uppercase tracking-wider block">Legal Terms & Machine Learning Privacy</span>
                <h1 class="text-xl sm:text-3xl font-bold text-white tracking-tight">Privacy Policy & ML Data Terms</h1>
                <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed">
                    This document details how machine learning models process business data, clarifies data persistence versus mathematical feature weights, and sets usage terms.
                </p>
            </div>

            <!-- Prominent Warning / Disclaimer Banner -->
            <div class="glass-panel p-4 sm:p-6 rounded-2xl border-brand-rose/40 bg-brand-rose/5 space-y-3">
                <div class="flex items-center gap-2.5 text-brand-rose font-bold text-xs sm:text-base">
                    <i data-lucide="alert-octagon" class="w-4 h-4 sm:w-5 sm:h-5 shrink-0"></i>
                    <span>CRITICAL DISCLAIMER & LIMITATION OF LIABILITY</span>
                </div>
                <div class="text-[11px] sm:text-xs text-neutral-300 leading-relaxed space-y-2">
                    <p>
                        <strong class="text-white">Prohibition of Illicit Activities:</strong> Users are strictly prohibited from using this platform to process, reconcile, or facilitate illegal transactions, fraudulent activity, money laundering, or any other illicit conduct.
                    </p>
                    <p>
                        <strong class="text-white">Absolute Release of Liability:</strong> ReconAgent, its operators, developers, and affiliates assume <strong class="text-brand-rose underline">zero legal or financial responsibility</strong> for any uploaded data, ML-driven flagged operations, output accuracy, financial losses, regulatory non-compliance, or illegal activities conducted by users. By using this software, you explicitly acknowledge and agree that you use the platform entirely at your own risk and hold ReconAgent completely harmless against any legal actions or claims.
                    </p>
                </div>
            </div>

            <div class="glass-panel p-4 sm:p-8 rounded-2xl space-y-6 sm:space-y-8">
                
                <!-- Section 1 -->
                <div class="space-y-3">
                    <h2 class="text-sm sm:text-lg font-bold text-white flex items-center gap-2">
                        <i data-lucide="brain-circuit" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-cyan shrink-0"></i>
                        <span>1. Machine Learning Training & Data Persistence</span>
                    </h2>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        ReconAgent utilizes Machine Learning (ML) to train custom workspace models designed to flag subsequent operations, detect partial payment mismatches, and identify fee anomalies. To balance ML capability with strict user privacy:
                    </p>
                    <ul class="space-y-2 text-xs text-neutral-300 list-disc list-inside pl-1 sm:pl-2">
                        <li><strong class="text-white">Raw File Deletion:</strong> Raw bank exports, CSVs, and transaction spreadsheets are deleted from active runtime memory immediately following processing and feature extraction.</li>
                        <li><strong class="text-white">Mathematical Model Weights:</strong> Extracted data features and historical feedback (e.g., approved offsets or flagged variances) are converted into non-reconstructible, anonymized mathematical weights and vector embeddings.</li>
                        <li><strong class="text-white">Subsequent Operation Flagging:</strong> These localized model weights are stored securely in your dedicated company workspace to continuously train and evaluate subsequent transactions without storing raw customer document records.</li>
                    </ul>
                </div>

                <div class="border-t border-white/10"></div>

                <!-- Section 2 -->
                <div class="space-y-3">
                    <h2 class="text-sm sm:text-lg font-bold text-white flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-cyan shrink-0"></i>
                        <span>2. Supported Data Methods</span>
                    </h2>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Data ingestion mechanisms utilized for feature mapping and machine learning execution include:
                    </p>
                    <ul class="space-y-2 text-xs text-neutral-300 list-disc list-inside pl-1 sm:pl-2">
                        <li><strong class="text-white">CSV & Excel Spreadsheets:</strong> Ingestion of bank exports, QuickBooks CSVs, or internal sales reports for smart column alignment and pattern extraction.</li>
                        <li><strong class="text-white">Direct Integrations:</strong> API connectivity with payment processors, bank feeds, and enterprise ERP systems to stream operational entries directly to the evaluation engine.</li>
                        <li><strong class="text-white">Discrepancy Evaluation Rules:</strong> Application of date tolerance windows, confidence thresholds, and processor fee logic.</li>
                    </ul>
                </div>

                <div class="border-t border-white/10"></div>

                <!-- Section 3 -->
                <div class="space-y-3">
                    <h2 class="text-sm sm:text-lg font-bold text-white flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-cyan shrink-0"></i>
                        <span>3. Access Roles & Permitted Operations</span>
                    </h2>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Internal workspace controls enforce strict authorization levels regarding who can train models or resolve ML flags:
                    </p>
                    <ul class="space-y-2 text-xs text-neutral-300 list-disc list-inside pl-1 sm:pl-2">
                        <li><strong class="text-white">Finance Administrator:</strong> Full control to manage data sources, team roles, and match configurations.</li>
                        <li><strong class="text-white">Reconciliation Specialist:</strong> Reviewer access to evaluate ML-flagged transactions, approve matches, and export summary reports.</li>
                        <li><strong class="text-white">Auditor / Executive:</strong> Read-only access to finished reconciliation reports, dashboards, and audit trails.</li>
                    </ul>
                </div>

                <div class="border-t border-white/10"></div>

                <!-- Section 4 -->
                <div class="space-y-3">
                    <h2 class="text-sm sm:text-lg font-bold text-white flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-cyan shrink-0"></i>
                        <span>4. Audit Logs & System Outputs</span>
                    </h2>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Outputs generated by the system—including month-end settlement summaries and PDF/Excel audit exports—are compiled solely for internal CFO review, accounting locks, and external auditing purposes under direct user supervision.
                    </p>
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
                    <a href="/system-status" class="hover:text-white transition">System Status</a>
                    <a href="/privacy" class="text-white">Privacy & Terms</a>
                </div>
            </div>
        </footer>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>