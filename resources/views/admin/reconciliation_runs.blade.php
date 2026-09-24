<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconciliation Runs - ReconAgent</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .bg-grid-pattern {
            background-size: 30px 30px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
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

        .delay-1 {
            animation-delay: 0.05s;
        }

        .delay-2 {
            animation-delay: 0.12s;
        }

        /* =========================================================
           VIEW LOG MODAL
        ========================================================= */

        .run-log-overlay {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition:
                opacity 180ms ease,
                visibility 180ms ease;
        }

        .run-log-overlay.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .run-log-modal {
            opacity: 0;
            transform: translateY(18px) scale(0.97);
            transition:
                opacity 180ms ease,
                transform 180ms ease;
        }

        .run-log-overlay.active .run-log-modal {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .run-log-scroll {
            scrollbar-width: thin;
            scrollbar-color: #404040 transparent;
        }

        .run-log-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .run-log-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .run-log-scroll::-webkit-scrollbar-thumb {
            background: #404040;
            border-radius: 999px;
        }

        /* =========================================================
           REPORT WINDOW
        ========================================================= */

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
            font-size: 12px;
        }

        .report-table th {
            background: #f8fafc;
            font-weight: 700;
        }
    </style>
</head>

<body class="bg-black text-slate-100 font-sans antialiased bg-grid-pattern min-h-screen">

    <div class="flex flex-col md:flex-row h-screen overflow-hidden">

        <!-- Mobile Header Bar -->
        <header class="md:hidden flex items-center justify-between p-4 bg-black/90 border-b border-neutral-800 shrink-0 backdrop-blur-md z-30">

            <div class="flex items-center gap-3">

                <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">

                    <svg class="w-5 h-5 text-black" viewBox="0 0 24 24" fill="none">
                        <path d="M12 4L20 8L12 12L4 8L12 4Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 12L12 16L20 12" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 16L12 20L20 16" stroke="currentColor" stroke-width="2"/>
                    </svg>

                </div>

                <span class="text-lg font-bold tracking-tight text-white">
                    ReconAgent
                </span>

            </div>

            <button
                id="menu-toggle"
                class="p-2 text-neutral-400 hover:text-white focus:outline-none"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    ></path>
                </svg>
            </button>

        </header>


        <!-- Sidebar Navigation -->
        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-20 w-64 bg-black/95 md:bg-black/80 border-r border-neutral-800 flex flex-col justify-between shrink-0 backdrop-blur-md -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:static"
        >

            <div>

                <div class="hidden md:block p-6 border-b border-neutral-800">

                    <div class="flex items-center gap-3">

                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">

                            <svg class="w-5 h-5 text-black" viewBox="0 0 24 24" fill="none">
                                <path d="M12 4L20 8L12 12L4 8L12 4Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M4 12L12 16L20 12" stroke="currentColor" stroke-width="2"/>
                                <path d="M4 16L12 20L20 16" stroke="currentColor" stroke-width="2"/>
                            </svg>

                        </div>

                        <span class="text-lg font-bold tracking-tight text-white">
                            ReconAgent
                        </span>

                    </div>

                </div>

                <x-admin.nav />

            </div>


            <div class="p-4 border-t border-neutral-800 flex items-center justify-between">

                <div>

                    <p class="text-xs font-semibold text-white">
                        System Administrator
                    </p>

                    <p class="text-[10px] text-neutral-500">
                        Admin Role
                    </p>

                </div>

                <form action="/logout" method="POST">

                    @csrf

                    <button
                        type="submit"
                        class="text-xs text-neutral-400 hover:text-white font-medium transition-colors"
                    >
                        Logout
                    </button>

                </form>

            </div>

        </aside>


        <div
            id="sidebar-overlay"
            class="fixed inset-0 bg-black/60 z-10 hidden md:hidden"
        ></div>


        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">

            <header class="animate-reveal flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 md:mb-8 pb-4 border-b border-neutral-800">

                <div>

                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                        Reconciliation Runs
                    </h1>

                    <p class="text-xs text-neutral-400 mt-1">
                        Audit log and automated matching engine history.
                    </p>

                </div>


                <button
                    class="w-full sm:w-auto justify-center px-4 py-2 bg-white text-black hover:bg-neutral-200 font-semibold rounded-lg text-xs transition-transform active:scale-95 duration-150 flex items-center space-x-2"
                >

                    <a href="/trigger-run">
                        <span>+ Trigger New Run</span>
                    </a>

                    <svg
                        class="w-3.5 h-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                        ></path>
                    </svg>

                </button>

            </header>


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


                                    <td class="p-3 text-right">

                                        <button
                                            type="button"
                                            class="view-log-button text-neutral-400 hover:text-white transition-colors"
                                            data-run-id="{{ $run->id }}"
                                            data-run-identifier="{{ $run->run_identifier }}"
                                            data-status="{{ $run->status }}"
                                            data-source-a="{{ $run->source_a_filename }}"
                                            data-source-b="{{ $run->source_b_filename }}"
                                            data-execution-speed="{{ $run->execution_speed }}"
                                            data-created-at="{{ optional($run->created_at)->format('Y-m-d H:i:s') }}"
                                            data-updated-at="{{ optional($run->updated_at)->format('Y-m-d H:i:s') }}"
                                        >
                                            View Log
                                        </button>

                                    </td>

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

        </main>

    </div>


    <!-- =========================================================
         VIEW LOG MODAL
    ========================================================= -->

    <div
        id="run-log-overlay"
        class="run-log-overlay fixed inset-0 z-[100] bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
    >

        <div
            id="run-log-modal"
            class="run-log-modal w-full max-w-2xl max-h-[90vh] bg-[#171717] border border-neutral-800 rounded-2xl shadow-2xl overflow-hidden"
        >

            <!-- Modal Header -->
            <div class="px-5 sm:px-7 pt-6 sm:pt-7">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <p class="text-[10px] uppercase tracking-[0.18em] text-neutral-500 font-mono mb-2">
                            Reconciliation Run
                        </p>

                        <h2
                            id="log-modal-title"
                            class="text-xl sm:text-2xl font-bold text-white tracking-tight"
                        >
                            Run Log
                        </h2>

                    </div>


                    <button
                        id="close-run-log"
                        type="button"
                        class="w-9 h-9 rounded-lg border border-neutral-800 bg-neutral-900 text-neutral-400 hover:text-white hover:bg-neutral-800 transition-colors flex items-center justify-center shrink-0"
                        aria-label="Close"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 6l12 12M18 6L6 18"
                            />
                        </svg>
                    </button>

                </div>


                <!-- Status Banner -->
                <div
                    id="log-status-banner"
                    class="mt-5 rounded-lg border border-emerald-900/80 bg-emerald-950/30 px-4 py-3 flex items-center gap-3"
                >

                    <div
                        id="log-status-icon"
                        class="w-8 h-8 rounded-full bg-emerald-950 border border-emerald-800 flex items-center justify-center shrink-0"
                    >

                        <svg
                            class="w-4 h-4 text-emerald-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>

                    </div>


                    <div>

                        <p
                            id="log-status-text"
                            class="text-sm font-semibold text-emerald-400"
                        >
                            Reconciliation Completed Successfully
                        </p>

                        <p
                            id="log-status-subtext"
                            class="text-[11px] text-neutral-500 mt-0.5"
                        >
                            Run execution details
                        </p>

                    </div>

                </div>

            </div>


            <!-- Modal Body -->
            <div class="run-log-scroll overflow-y-auto px-5 sm:px-7 py-5 max-h-[55vh]">

                <!-- Metrics -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                    <div class="bg-[#0d0d0d] border border-neutral-800 rounded-xl p-4">

                        <p class="text-[10px] uppercase tracking-wider text-neutral-500 font-mono">
                            Run ID
                        </p>

                        <p
                            id="log-run-id"
                            class="text-lg font-bold text-white mt-2 font-mono"
                        >
                            —
                        </p>

                    </div>


                    <div class="bg-[#0d0d0d] border border-neutral-800 rounded-xl p-4">

                        <p class="text-[10px] uppercase tracking-wider text-neutral-500 font-mono">
                            Execution
                        </p>

                        <p
                            id="log-execution-speed"
                            class="text-lg font-bold text-white mt-2 font-mono"
                        >
                            —
                        </p>

                    </div>


                    <div class="bg-[#0d0d0d] border border-neutral-800 rounded-xl p-4">

                        <p class="text-[10px] uppercase tracking-wider text-neutral-500 font-mono">
                            Status
                        </p>

                        <p
                            id="log-status-value"
                            class="text-lg font-bold text-white mt-2"
                        >
                            —
                        </p>

                    </div>

                </div>


                <!-- Dataset Information -->
                <div class="mt-4 bg-[#0d0d0d] border border-neutral-800 rounded-xl overflow-hidden">

                    <div class="px-4 py-3 border-b border-neutral-800">

                        <p class="text-[10px] uppercase tracking-[0.16em] text-neutral-500 font-mono">
                            Source Dataset(s)
                        </p>

                    </div>


                    <div class="divide-y divide-neutral-800">

                        <div class="px-4 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                            <div class="flex items-center gap-3">

                                <div class="w-7 h-7 rounded-md bg-neutral-900 border border-neutral-800 flex items-center justify-center">
                                    <span class="text-[10px] font-bold text-neutral-400">
                                        A
                                    </span>
                                </div>

                                <div>

                                    <p class="text-[10px] uppercase text-neutral-600 font-mono">
                                        Source A
                                    </p>

                                    <p
                                        id="log-source-a"
                                        class="text-sm text-neutral-200 break-all"
                                    >
                                        —
                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="px-4 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                            <div class="flex items-center gap-3">

                                <div class="w-7 h-7 rounded-md bg-neutral-900 border border-neutral-800 flex items-center justify-center">
                                    <span class="text-[10px] font-bold text-neutral-400">
                                        B
                                    </span>
                                </div>

                                <div>

                                    <p class="text-[10px] uppercase text-neutral-600 font-mono">
                                        Source B
                                    </p>

                                    <p
                                        id="log-source-b"
                                        class="text-sm text-neutral-200 break-all"
                                    >
                                        —
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Run Information -->
                <div class="mt-4 bg-[#0d0d0d] border border-neutral-800 rounded-xl overflow-hidden">

                    <div class="px-4 py-3 border-b border-neutral-800">

                        <p class="text-[10px] uppercase tracking-[0.16em] text-neutral-500 font-mono">
                            Run Information
                        </p>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-neutral-800">

                        <div class="p-4">

                            <p class="text-[10px] uppercase text-neutral-600 font-mono">
                                Run Identifier
                            </p>

                            <p
                                id="log-run-identifier"
                                class="text-sm text-neutral-200 mt-1 break-all font-mono"
                            >
                                —
                            </p>

                        </div>


                        <div class="p-4">

                            <p class="text-[10px] uppercase text-neutral-600 font-mono">
                                Created
                            </p>

                            <p
                                id="log-created-at"
                                class="text-sm text-neutral-200 mt-1 font-mono"
                            >
                                —
                            </p>

                        </div>


                        <div class="p-4 sm:border-t sm:border-neutral-800">

                            <p class="text-[10px] uppercase text-neutral-600 font-mono">
                                Last Updated
                            </p>

                            <p
                                id="log-updated-at"
                                class="text-sm text-neutral-200 mt-1 font-mono"
                            >
                                —
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 MODAL ACTIONS
            ====================================================== -->

            <div class="px-5 sm:px-7 py-4 border-t border-neutral-800 bg-[#141414]">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    <!-- Download Summary -->
                    <button
                        id="download-summary-button"
                        type="button"
                        class="group w-full px-4 py-3 bg-white hover:bg-neutral-200 text-black font-semibold rounded-lg text-sm transition-colors flex items-center justify-center gap-2"
                    >

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"
                            />
                        </svg>

                        <span>
                            Download Summary
                        </span>

                    </button>


                    <!-- Export Unmatched -->
                    <button
                        id="export-unmatched-button"
                        type="button"
                        class="group w-full px-4 py-3 bg-neutral-800 hover:bg-neutral-700 text-white font-semibold rounded-lg text-sm transition-colors border border-neutral-700 flex items-center justify-center gap-2"
                    >

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 7h16M4 12h16M4 17h10"
                            />
                        </svg>

                        <span>
                            Export Unmatched
                        </span>

                    </button>

                </div>


                <button
                    id="close-run-log-footer"
                    type="button"
                    class="w-full mt-3 px-4 py-3 bg-neutral-900 hover:bg-neutral-800 text-neutral-300 hover:text-white font-semibold rounded-lg text-sm transition-colors border border-neutral-800"
                >
                    Close
                </button>

            </div>

        </div>

    </div>


 <script>

        /* =========================================================
           SIDEBAR
        ========================================================= */

        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);


        /* =========================================================
           VIEW LOG MODAL
        ========================================================= */

        const runLogOverlay =
            document.getElementById('run-log-overlay');

        const closeRunLog =
            document.getElementById('close-run-log');

        const closeRunLogFooter =
            document.getElementById('close-run-log-footer');

        const logModalTitle =
            document.getElementById('log-modal-title');

        const logStatusBanner =
            document.getElementById('log-status-banner');

        const logStatusIcon =
            document.getElementById('log-status-icon');

        const logStatusText =
            document.getElementById('log-status-text');

        const logStatusSubtext =
            document.getElementById('log-status-subtext');

        const logRunId =
            document.getElementById('log-run-id');

        const logExecutionSpeed =
            document.getElementById('log-execution-speed');

        const logStatusValue =
            document.getElementById('log-status-value');

        const logSourceA =
            document.getElementById('log-source-a');

        const logSourceB =
            document.getElementById('log-source-b');

        const logRunIdentifier =
            document.getElementById('log-run-identifier');

        const logCreatedAt =
            document.getElementById('log-created-at');

        const logUpdatedAt =
            document.getElementById('log-updated-at');

        const downloadSummaryButton =
            document.getElementById('download-summary-button');

        const exportUnmatchedButton =
            document.getElementById('export-unmatched-button');


        /*
         * Keeps track of whichever run is currently open.
         */
        let activeRunId = null;


        function resetRunLogModal() {

            activeRunId = null;

            logModalTitle.textContent = 'Run Log';

            logRunId.textContent = '—';
            logExecutionSpeed.textContent = '—';
            logStatusValue.textContent = '—';

            logSourceA.textContent = '—';
            logSourceB.textContent = '—';

            logRunIdentifier.textContent = '—';
            logCreatedAt.textContent = '—';
            logUpdatedAt.textContent = '—';

            logStatusText.textContent =
                'Reconciliation Run';

            logStatusSubtext.textContent =
                'Run execution details';

            logStatusBanner.className =
                'mt-5 rounded-lg border border-neutral-800 bg-neutral-900/40 px-4 py-3 flex items-center gap-3';

            logStatusIcon.className =
                'w-8 h-8 rounded-full bg-neutral-900 border border-neutral-800 flex items-center justify-center shrink-0';

            logStatusIcon.innerHTML = `
                <svg
                    class="w-4 h-4 text-neutral-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M12 19a7 7 0 100-14 7 7 0 000 14z"
                    />
                </svg>
            `;

        }


        function setRunStatus(status) {

            const normalizedStatus =
                String(status || '')
                    .trim()
                    .toLowerCase();


            const isCompleted =
                normalizedStatus === 'completed' ||
                normalizedStatus === 'complete' ||
                normalizedStatus === 'success' ||
                normalizedStatus === 'successful';


            const isFailed =
                normalizedStatus === 'failed' ||
                normalizedStatus === 'error';


            const isProcessing =
                normalizedStatus === 'processing' ||
                normalizedStatus === 'running';


            if (isCompleted) {

                logStatusBanner.className =
                    'mt-5 rounded-lg border border-emerald-900/80 bg-emerald-950/30 px-4 py-3 flex items-center gap-3';

                logStatusIcon.className =
                    'w-8 h-8 rounded-full bg-emerald-950 border border-emerald-800 flex items-center justify-center shrink-0';

                logStatusIcon.innerHTML = `
                    <svg
                        class="w-4 h-4 text-emerald-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                `;

                logStatusText.className =
                    'text-sm font-semibold text-emerald-400';

                logStatusText.textContent =
                    'Reconciliation Completed Successfully';

                logStatusSubtext.textContent =
                    'The reconciliation run finished successfully.';

                logStatusValue.className =
                    'text-lg font-bold text-emerald-400 mt-2';


            } else if (isFailed) {

                logStatusBanner.className =
                    'mt-5 rounded-lg border border-red-900/80 bg-red-950/30 px-4 py-3 flex items-center gap-3';

                logStatusIcon.className =
                    'w-8 h-8 rounded-full bg-red-950 border border-red-800 flex items-center justify-center shrink-0';

                logStatusIcon.innerHTML = `
                    <svg
                        class="w-4 h-4 text-red-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                `;

                logStatusText.className =
                    'text-sm font-semibold text-red-400';

                logStatusText.textContent =
                    'Reconciliation Run Failed';

                logStatusSubtext.textContent =
                    'The reconciliation process did not complete successfully.';

                logStatusValue.className =
                    'text-lg font-bold text-red-400 mt-2';


            } else if (isProcessing) {

                logStatusBanner.className =
                    'mt-5 rounded-lg border border-amber-900/80 bg-amber-950/30 px-4 py-3 flex items-center gap-3';

                logStatusIcon.className =
                    'w-8 h-8 rounded-full bg-amber-950 border border-amber-800 flex items-center justify-center shrink-0';

                logStatusIcon.innerHTML = `
                    <svg
                        class="w-4 h-4 text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3"
                        />
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />
                    </svg>
                `;

                logStatusText.className =
                    'text-sm font-semibold text-amber-400';

                logStatusText.textContent =
                    'Reconciliation Run Processing';

                logStatusSubtext.textContent =
                    'The reconciliation process is currently running.';

                logStatusValue.className =
                    'text-lg font-bold text-amber-400 mt-2';


            } else {

                logStatusBanner.className =
                    'mt-5 rounded-lg border border-neutral-800 bg-neutral-900/40 px-4 py-3 flex items-center gap-3';

                logStatusText.className =
                    'text-sm font-semibold text-neutral-300';

                logStatusText.textContent =
                    'Reconciliation Run';

                logStatusSubtext.textContent =
                    'Run execution details.';

                logStatusValue.className =
                    'text-lg font-bold text-white mt-2';

            }


            logStatusValue.textContent =
                status || 'Unknown';

        }


        function openRunLog(button) {

            resetRunLogModal();


            const runId =
                button.dataset.runId || '';

            const runIdentifier =
                button.dataset.runIdentifier || '';

            const status =
                button.dataset.status || 'Unknown';

            const sourceA =
                button.dataset.sourceA || 'Not available';

            const sourceB =
                button.dataset.sourceB || 'Not available';

            const executionSpeed =
                button.dataset.executionSpeed || '—';

            const createdAt =
                button.dataset.createdAt || '—';

            const updatedAt =
                button.dataset.updatedAt || '—';


            /*
             * Remember the selected run.
             *
             * This is the important part that allows the
             * Download Summary / Export Unmatched buttons
             * to operate on an old run.
             */
            activeRunId = runId;


            logModalTitle.textContent =
                `Run #RUN-${runId}`;


            logRunId.textContent =
                `#RUN-${runId}`;


            logExecutionSpeed.textContent =
                executionSpeed
                    ? `${executionSpeed}ms`
                    : '—';


            logSourceA.textContent =
                sourceA;


            logSourceB.textContent =
                sourceB;


            logRunIdentifier.textContent =
                runIdentifier || '—';


            logCreatedAt.textContent =
                createdAt || '—';


            logUpdatedAt.textContent =
                updatedAt || '—';


            setRunStatus(status);


            /*
             * Only completed runs should normally have
             * reconciliation results available.
             */
            const normalizedStatus =
                String(status || '')
                    .trim()
                    .toLowerCase();


            const canExport =
                normalizedStatus === 'completed' ||
                normalizedStatus === 'complete' ||
                normalizedStatus === 'success' ||
                normalizedStatus === 'successful';


            downloadSummaryButton.disabled = !canExport;
            exportUnmatchedButton.disabled = !canExport;


            if (canExport) {

                downloadSummaryButton.classList.remove(
                    'opacity-40',
                    'cursor-not-allowed'
                );

                exportUnmatchedButton.classList.remove(
                    'opacity-40',
                    'cursor-not-allowed'
                );

            } else {

                downloadSummaryButton.classList.add(
                    'opacity-40',
                    'cursor-not-allowed'
                );

                exportUnmatchedButton.classList.add(
                    'opacity-40',
                    'cursor-not-allowed'
                );

            }


            runLogOverlay.classList.add('active');

            document.body.classList.add('overflow-hidden');

        }


        function closeRunLogModal() {

            runLogOverlay.classList.remove('active');

            document.body.classList.remove('overflow-hidden');

        }


        /* =========================================================
           FETCH EXISTING RESULTS
        ========================================================= */

        async function fetchRunResults(runId) {

            if (!runId) {
                throw new Error('No reconciliation run selected.');
            }


            const response =
                await fetch(
                    `/reconciliation-runs/${runId}/results`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    `Unable to retrieve results for run #${runId}.`
                );

            }


            return await response.json();

        }


        /* =========================================================
           REPORT HELPERS
        ========================================================= */

        function reportEscape(value) {

            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        }


        function reportFormatValue(value) {

            if (
                value === null ||
                value === undefined ||
                value === ''
            ) {
                return '—';
            }


            if (typeof value === 'object') {

                try {
                    return JSON.stringify(value);
                } catch (error) {
                    return String(value);
                }

            }


            return String(value);

        }


        function reportLabel(value) {

            return String(value)
                .replace(/_/g, ' ')
                .replace(/-/g, ' ')
                .replace(/\b\w/g, character =>
                    character.toUpperCase()
                );

        }


        function reportTable(rows) {

            if (!Array.isArray(rows) || rows.length === 0) {

                return `
                    <p style="
                        color:#64748b;
                        font-size:13px;
                        margin:12px 0;
                    ">
                        No records available.
                    </p>
                `;

            }


            const columns = [
                ...new Set(
                    rows.flatMap(row =>
                        row && typeof row === 'object'
                            ? Object.keys(row)
                            : []
                    )
                )
            ];


            if (!columns.length) {

                return `
                    <p style="
                        color:#64748b;
                        font-size:13px;
                        margin:12px 0;
                    ">
                        No structured records available.
                    </p>
                `;

            }


            let html = `
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>S/N</th>
            `;


            columns.forEach(column => {

                html += `
                    <th>
                        ${reportEscape(reportLabel(column))}
                    </th>
                `;

            });


            html += `
                        </tr>
                    </thead>
                    <tbody>
            `;


            rows.forEach((row, index) => {

                html += `
                    <tr>
                        <td>
                            ${index + 1}
                        </td>
                `;


                columns.forEach(column => {

                    html += `
                        <td>
                            ${reportEscape(
                                reportFormatValue(
                                    row?.[column]
                                )
                            )}
                        </td>
                    `;

                });


                html += '</tr>';

            });


            html += `
                    </tbody>
                </table>
            `;


            return html;

        }


        function reportMetric(label, value) {

            return `
                <div style="
                    border:1px solid #e5e7eb;
                    border-radius:10px;
                    padding:16px;
                    background:#ffffff;
                ">
                    <div style="
                        font-size:11px;
                        color:#64748b;
                        text-transform:uppercase;
                        letter-spacing:.08em;
                        font-weight:700;
                    ">
                        ${reportEscape(label)}
                    </div>

                    <div style="
                        font-size:22px;
                        font-weight:800;
                        color:#111827;
                        margin-top:6px;
                    ">
                        ${reportEscape(value)}
                    </div>
                </div>
            `;

        }


        function createReportWindow(
            title,
            subtitle,
            bodyHtml,
            existingWindow = null
        ) {

            const reportWindow =
                existingWindow ||
                window.open(
                    '',
                    '_blank',
                    'width=1200,height=900'
                );


            if (!reportWindow) {

                throw new Error(
                    'The report window was blocked by the browser. Please allow pop-ups for this site.'
                );

            }


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

                    <title>
                        ${reportEscape(title)}
                    </title>

                    <style>

                        @page {
                            size: A4;
                            margin: 16mm;
                        }

                        * {
                            box-sizing: border-box;
                        }

                        body {
                            margin: 0;
                            background: #f1f5f9;
                            color: #111827;
                            font-family:
                                Arial,
                                Helvetica,
                                sans-serif;
                        }

                        .report-shell {
                            max-width: 1100px;
                            margin: 30px auto;
                            background: white;
                            border-radius: 14px;
                            box-shadow:
                                0 10px 35px
                                rgba(15, 23, 42, .10);
                            overflow: hidden;
                        }

                        .report-header {
                            padding: 30px 34px;
                            border-bottom: 1px solid #e5e7eb;
                        }

                        .brand {
                            font-size: 11px;
                            font-weight: 800;
                            letter-spacing: .16em;
                            text-transform: uppercase;
                            color: #64748b;
                        }

                        h1 {
                            margin: 7px 0 5px;
                            font-size: 27px;
                            line-height: 1.2;
                        }

                        .subtitle {
                            color: #64748b;
                            font-size: 13px;
                        }

                        .report-body {
                            padding: 30px 34px;
                        }

                        .section {
                            margin-top: 28px;
                        }

                        .section:first-child {
                            margin-top: 0;
                        }

                        .section-title {
                            font-size: 12px;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: .10em;
                            color: #475569;
                            margin-bottom: 10px;
                        }

                        .metrics {
                            display: grid;
                            grid-template-columns:
                                repeat(4, minmax(0, 1fr));
                            gap: 12px;
                        }

                        .report-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 10px;
                        }

                        .report-table th,
                        .report-table td {
                            border: 1px solid #e5e7eb;
                            padding: 8px 10px;
                            text-align: left;
                            vertical-align: top;
                            font-size: 11px;
                        }

                        .report-table th {
                            background: #f8fafc;
                            font-weight: 700;
                            color: #334155;
                        }

                        .report-footer {
                            padding: 20px 34px;
                            border-top: 1px solid #e5e7eb;
                            color: #94a3b8;
                            font-size: 10px;
                        }

                        .report-actions {
                            position: sticky;
                            top: 0;
                            z-index: 10;
                            display: flex;
                            justify-content: flex-end;
                            gap: 8px;
                            padding: 12px 16px;
                            background: #0f172a;
                        }

                        .report-actions button {
                            border: 0;
                            border-radius: 7px;
                            padding: 9px 14px;
                            cursor: pointer;
                            font-weight: 700;
                            font-size: 12px;
                        }

                        .print-button {
                            background: white;
                            color: #111827;
                        }

                        .close-button {
                            background: #334155;
                            color: white;
                        }

                        @media (max-width: 700px) {

                            .report-shell {
                                margin: 0;
                                border-radius: 0;
                            }

                            .metrics {
                                grid-template-columns: 1fr 1fr;
                            }

                        }

                        @media print {

                            body {
                                background: white;
                            }

                            .report-shell {
                                margin: 0;
                                max-width: none;
                                box-shadow: none;
                                border-radius: 0;
                            }

                            .report-actions {
                                display: none;
                            }

                            .report-header,
                            .report-body,
                            .report-footer {
                                padding-left: 0;
                                padding-right: 0;
                            }

                            .report-table {
                                page-break-inside: auto;
                            }

                            tr {
                                page-break-inside: avoid;
                                page-break-after: auto;
                            }

                        }

                    </style>

                </head>


                <body>

                    <div class="report-actions">

                        <button
                            class="print-button"
                            onclick="window.print()"
                        >
                            Print / Save as PDF
                        </button>

                        <button
                            class="close-button"
                            onclick="window.close()"
                        >
                            Close
                        </button>

                    </div>


                    <div class="report-shell">

                        <div class="report-header">

                            <div class="brand">
                                ReconAgent
                            </div>

                            <h1>
                                ${reportEscape(title)}
                            </h1>

                            <div class="subtitle">
                                ${reportEscape(subtitle)}
                            </div>

                        </div>


                        <div class="report-body">

                            ${bodyHtml}

                        </div>


                        <div class="report-footer">

                            Generated:
                            ${reportEscape(
                                new Date().toLocaleString()
                            )}

                        </div>

                    </div>

                </body>

                </html>
            `);


            reportWindow.document.close();

            return reportWindow;

        }


        /* =========================================================
           DOWNLOAD SUMMARY
        ========================================================= */

        async function downloadSummaryForRun() {

            if (!activeRunId) {

                alert(
                    'No reconciliation run is currently selected.'
                );

                return;

            }


            /*
             * Open immediately so browsers do not treat the
             * report as a blocked popup after the async request.
             */
            let reportWindow = null;


            try {

                reportWindow =
                    window.open(
                        '',
                        '_blank',
                        'width=1200,height=900'
                    );


                if (!reportWindow) {

                    throw new Error(
                        'The report window was blocked by the browser.'
                    );

                }


                reportWindow.document.write(`
                    <html>
                        <body style="
                            font-family:Arial;
                            padding:40px;
                        ">
                            Preparing reconciliation report...
                        </body>
                    </html>
                `);


                const resultData =
                    await fetchRunResults(activeRunId);


                const summary =
                    resultData?.summary || {};


                const matched =
                    resultData?.matched_count ??
                    resultData?.matched ??
                    summary?.matched ??
                    0;


                const unmatchedA =
                    Array.isArray(resultData?.unmatched_a)
                        ? resultData.unmatched_a.length
                        : (
                            resultData?.unmatched_a_count ??
                            summary?.unmatched_a ??
                            0
                        );


                const unmatchedB =
                    Array.isArray(resultData?.unmatched_b)
                        ? resultData.unmatched_b.length
                        : (
                            resultData?.unmatched_b_count ??
                            summary?.unmatched_b ??
                            0
                        );


                const fuzzyMatches =
                    resultData?.fuzzy_matches ??
                    summary?.fuzzy_matches ??
                    0;


                const gatewayFeeDeductions =
                    resultData?.gateway_fee_deductions ??
                    summary?.gateway_fee_deductions ??
                    0;


                const splitPayments =
                    resultData?.split_payments ??
                    summary?.split_payments ??
                    0;


                const totalExceptions =
                    unmatchedA + unmatchedB;


                let matchRate =
                    resultData?.match_rate ??
                    summary?.match_rate;


                if (
                    matchRate === undefined &&
                    matched !== undefined
                ) {

                    const total =
                        Number(matched) +
                        Number(totalExceptions);

                    if (total > 0) {

                        matchRate =
                            (
                                Number(matched) /
                                total *
                                100
                            ).toFixed(2);

                    }

                }


                let scalarSummaryHtml = '';
                let scalarSummaryIndex = 0;


                Object.entries(summary)
                    .forEach(([key, value]) => {

                        if (
                            Array.isArray(value) ||
                            value === null ||
                            typeof value === 'object'
                        ) {
                            return;
                        }


                        scalarSummaryIndex += 1;

                        scalarSummaryHtml += `
                            <tr>
                                <td>
                                    ${scalarSummaryIndex}
                                </td>

                                <td>
                                    ${reportEscape(
                                        reportLabel(key)
                                    )}
                                </td>

                                <td>
                                    ${reportEscape(
                                        reportFormatValue(value)
                                    )}
                                </td>
                            </tr>
                        `;

                    });


                const summaryArrays =
                    Object.entries(summary)
                        .filter(
                            ([key, value]) =>
                                Array.isArray(value) &&
                                value.length > 0
                        );


                let detailedTablesHtml = '';


                summaryArrays.forEach(
                    ([key, value]) => {

                        detailedTablesHtml += `

                            <div class="section">

                                <div class="section-title">
                                    ${reportEscape(
                                        reportLabel(key)
                                    )}
                                </div>

                                ${reportTable(value)}

                            </div>

                        `;

                    }
                );


                const bodyHtml = `

                    <div class="section">

                        <div class="section-title">
                            Run Overview
                        </div>

                        <div class="metrics">

                            ${reportMetric(
                                'Match Rate',
                                matchRate !== undefined
                                    ? `${matchRate}%`
                                    : '—'
                            )}

                            ${reportMetric(
                                'Matched',
                                matched
                            )}

                            ${reportMetric(
                                'Unmatched',
                                totalExceptions
                            )}

                            ${reportMetric(
                                'Fuzzy Matches',
                                fuzzyMatches
                            )}

                        </div>

                    </div>


                    <div class="section">

                        <div class="section-title">
                            ML Match Breakdown
                        </div>

                        <div class="metrics">

                            ${reportMetric(
                                'Gateway Fee Deductions',
                                gatewayFeeDeductions
                            )}

                            ${reportMetric(
                                'Split Payments',
                                splitPayments
                            )}

                            ${reportMetric(
                                'Source A Exceptions',
                                unmatchedA
                            )}

                            ${reportMetric(
                                'Source B Exceptions',
                                unmatchedB
                            )}

                        </div>

                    </div>


                    ${
                        scalarSummaryHtml
                            ? `
                                <div class="section">

                                    <div class="section-title">
                                        Summary Details
                                    </div>

                                    <table class="report-table">

                                        <thead>
                                            <tr>
                                                <th>S/N</th>

                                                <th>
                                                    Field
                                                </th>

                                                <th>
                                                    Value
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            ${scalarSummaryHtml}
                                        </tbody>

                                    </table>

                                </div>
                            `
                            : ''
                    }


                    ${detailedTablesHtml}

                `;


                /*
                 * Replace the temporary report page with
                 * the complete report.
                 */
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

                        <title>
                            Reconciliation Summary - Run #${reportEscape(activeRunId)}
                        </title>

                        <style>

                            @page {
                                size: A4;
                                margin: 16mm;
                            }

                            * {
                                box-sizing: border-box;
                            }

                            body {
                                margin: 0;
                                background: #f1f5f9;
                                color: #111827;
                                font-family: Arial, Helvetica, sans-serif;
                            }

                            .report-actions {
                                position: sticky;
                                top: 0;
                                z-index: 10;
                                display: flex;
                                justify-content: flex-end;
                                gap: 8px;
                                padding: 12px 16px;
                                background: #0f172a;
                            }

                            .report-actions button {
                                border: 0;
                                border-radius: 7px;
                                padding: 9px 14px;
                                cursor: pointer;
                                font-weight: 700;
                                font-size: 12px;
                            }

                            .print-button {
                                background: white;
                                color: #111827;
                            }

                            .close-button {
                                background: #334155;
                                color: white;
                            }

                            .report-shell {
                                max-width: 1100px;
                                margin: 30px auto;
                                background: white;
                                border-radius: 14px;
                                box-shadow: 0 10px 35px rgba(15,23,42,.10);
                                overflow: hidden;
                            }

                            .report-header {
                                padding: 30px 34px;
                                border-bottom: 1px solid #e5e7eb;
                            }

                            .brand {
                                font-size: 11px;
                                font-weight: 800;
                                letter-spacing: .16em;
                                text-transform: uppercase;
                                color: #64748b;
                            }

                            h1 {
                                margin: 7px 0 5px;
                                font-size: 27px;
                            }

                            .subtitle {
                                color: #64748b;
                                font-size: 13px;
                            }

                            .report-body {
                                padding: 30px 34px;
                            }

                            .section {
                                margin-top: 28px;
                            }

                            .section:first-child {
                                margin-top: 0;
                            }

                            .section-title {
                                font-size: 12px;
                                font-weight: 800;
                                text-transform: uppercase;
                                letter-spacing: .10em;
                                color: #475569;
                                margin-bottom: 10px;
                            }

                            .metrics {
                                display: grid;
                                grid-template-columns:
                                    repeat(4, minmax(0, 1fr));
                                gap: 12px;
                            }

                            .report-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                            }

                            .report-table th,
                            .report-table td {
                                border: 1px solid #e5e7eb;
                                padding: 8px 10px;
                                text-align: left;
                                vertical-align: top;
                                font-size: 11px;
                            }

                            .report-table th {
                                background: #f8fafc;
                                font-weight: 700;
                                color: #334155;
                            }

                            .report-footer {
                                padding: 20px 34px;
                                border-top: 1px solid #e5e7eb;
                                color: #94a3b8;
                                font-size: 10px;
                            }

                            @media (max-width: 700px) {

                                .report-shell {
                                    margin: 0;
                                    border-radius: 0;
                                }

                                .metrics {
                                    grid-template-columns: 1fr 1fr;
                                }

                            }

                            @media print {

                                body {
                                    background: white;
                                }

                                .report-shell {
                                    margin: 0;
                                    max-width: none;
                                    box-shadow: none;
                                    border-radius: 0;
                                }

                                .report-actions {
                                    display: none;
                                }

                                .report-header,
                                .report-body,
                                .report-footer {
                                    padding-left: 0;
                                    padding-right: 0;
                                }

                                tr {
                                    page-break-inside: avoid;
                                }

                            }

                        </style>

                    </head>

                    <body>

                        <div class="report-actions">

                            <button
                                class="print-button"
                                onclick="window.print()"
                            >
                                Print / Save as PDF
                            </button>

                            <button
                                class="close-button"
                                onclick="window.close()"
                            >
                                Close
                            </button>

                        </div>


                        <div class="report-shell">

                            <div class="report-header">

                                <div class="brand">
                                    ReconAgent
                                </div>

                                <h1>
                                    Reconciliation Summary
                                </h1>

                                <div class="subtitle">
                                    Run #RUN-${reportEscape(activeRunId)}
                                </div>

                            </div>


                            <div class="report-body">

                                ${bodyHtml}

                            </div>


                            <div class="report-footer">

                                Generated:
                                ${reportEscape(
                                    new Date().toLocaleString()
                                )}

                            </div>

                        </div>

                    </body>

                    </html>
                `);

                reportWindow.document.close();

            } catch (error) {

                if (reportWindow) {
                    reportWindow.close();
                }

                alert(
                    error.message ||
                    'Unable to generate the reconciliation summary.'
                );

            }

        }


        /* =========================================================
           EXPORT UNMATCHED
        ========================================================= */

        async function exportUnmatchedForRun() {

            if (!activeRunId) {

                alert(
                    'No reconciliation run is currently selected.'
                );

                return;

            }


            let reportWindow = null;


            try {

                /*
                 * Open the report window immediately so the browser
                 * does not block it after the asynchronous request.
                 *
                 * The existing stored reconciliation results are reused.
                 * The reconciliation is NOT executed again and no CSV
                 * file is downloaded.
                 */
                reportWindow =
                    window.open(
                        '',
                        '_blank',
                        'width=1200,height=900'
                    );


                if (!reportWindow) {

                    throw new Error(
                        'The report window was blocked by the browser. Please allow pop-ups for this site.'
                    );

                }


                reportWindow.document.open();

                reportWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Preparing Unmatched Report...</title>
                        <style>
                            body {
                                margin: 0;
                                min-height: 100vh;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                background: #f1f5f9;
                                color: #111827;
                                font-family: Arial, Helvetica, sans-serif;
                            }
                            .loading-card {
                                background: white;
                                border: 1px solid #e5e7eb;
                                border-radius: 14px;
                                padding: 32px;
                                text-align: center;
                                box-shadow: 0 10px 35px rgba(15, 23, 42, .10);
                            }
                            .loading-title {
                                font-size: 18px;
                                font-weight: 800;
                            }
                            .loading-text {
                                margin-top: 8px;
                                color: #64748b;
                                font-size: 13px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="loading-card">
                            <div class="loading-title">
                                Preparing Unmatched Report
                            </div>
                            <div class="loading-text">
                                Retrieving existing reconciliation results...
                            </div>
                        </div>
                    </body>
                    </html>
                `);

                reportWindow.document.close();


                /*
                 * Retrieve the results already stored for this run.
                 * This avoids re-running the same source files.
                 */
                const resultData =
                    await fetchRunResults(
                        activeRunId
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
                    resultData?.summary &&
                    typeof resultData.summary === 'object'
                        ? resultData.summary
                        : {};


                const matched =
                    resultData?.matched ??
                    summary?.matched ??
                    0;


                const matchRate =
                    resultData?.match_rate ??
                    summary?.match_rate;


                const totalExceptions =
                    unmatchedA.length +
                    unmatchedB.length;


                const bodyHtml = `

                    <div class="section">

                        <div class="section-title">
                            Exception Overview
                        </div>

                        <div class="metrics">

                            ${reportMetric(
                                'Source A Unmatched',
                                unmatchedA.length
                            )}

                            ${reportMetric(
                                'Source B Unmatched',
                                unmatchedB.length
                            )}

                            ${reportMetric(
                                'Total Exceptions',
                                totalExceptions
                            )}

                            ${reportMetric(
                                'Match Rate',
                                matchRate !== undefined &&
                                matchRate !== null &&
                                matchRate !== ''
                                    ? `${matchRate}%`
                                    : '—'
                            )}

                        </div>

                    </div>


                    <div class="section">

                        <div class="section-title">
                            Reconciliation Context
                        </div>

                        <table class="report-table">

                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Field</th>
                                    <th>Value</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>
                                    <td>1</td>
                                    <td>Run ID</td>
                                    <td>#RUN-${reportEscape(activeRunId)}</td>
                                </tr>

                                <tr>
                                    <td>2</td>
                                    <td>Matched Records</td>
                                    <td>${reportEscape(matched)}</td>
                                </tr>

                                <tr>
                                    <td>3</td>
                                    <td>Source A Exceptions</td>
                                    <td>${reportEscape(unmatchedA.length)}</td>
                                </tr>

                                <tr>
                                    <td>4</td>
                                    <td>Source B Exceptions</td>
                                    <td>${reportEscape(unmatchedB.length)}</td>
                                </tr>

                                <tr>
                                    <td>5</td>
                                    <td>Total Exceptions</td>
                                    <td>${reportEscape(totalExceptions)}</td>
                                </tr>

                            </tbody>

                        </table>

                    </div>


                    <div class="section">

                        <div class="section-title">
                            Source A — Unmatched Records
                        </div>

                        ${reportTable(unmatchedA)}

                    </div>


                    <div class="section">

                        <div class="section-title">
                            Source B — Unmatched Records
                        </div>

                        ${reportTable(unmatchedB)}

                    </div>

                `;


                /*
                 * Use the same professional report window used by
                 * Download Summary. The user can review it immediately
                 * and use Print / Save as PDF when needed.
                 */
                reportWindow =
                    createReportWindow(
                        'Unmatched Transactions Report',
                        `Run #RUN-${activeRunId}`,
                        bodyHtml,
                        reportWindow
                    );


            } catch (error) {

                if (reportWindow) {
                    reportWindow.close();
                }

                alert(
                    error.message ||
                    'Unable to generate the unmatched transactions report.'
                );

            }

        }


        /* =========================================================
           VIEW LOG BUTTONS
        ========================================================= */

        document
            .querySelectorAll('.view-log-button')
            .forEach(button => {

                button.addEventListener(
                    'click',
                    () => {

                        openRunLog(button);

                    }
                );

            });


        /* =========================================================
           DOWNLOAD / EXPORT BUTTONS
        ========================================================= */

        downloadSummaryButton.addEventListener(
            'click',
            () => {

                if (
                    downloadSummaryButton.disabled
                ) {
                    return;
                }

                downloadSummaryForRun();

            }
        );


        exportUnmatchedButton.addEventListener(
            'click',
            () => {

                if (
                    exportUnmatchedButton.disabled
                ) {
                    return;
                }

                exportUnmatchedForRun();

            }
        );


        /* =========================================================
           CLOSE MODAL
        ========================================================= */

        closeRunLog.addEventListener(
            'click',
            closeRunLogModal
        );


        closeRunLogFooter.addEventListener(
            'click',
            closeRunLogModal
        );


        /* Click outside modal */

        runLogOverlay.addEventListener(
            'click',
            function (event) {

                if (
                    event.target ===
                    runLogOverlay
                ) {
                    closeRunLogModal();
                }

            }
        );


        /* Escape key */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    runLogOverlay.classList.contains('active')
                ) {

                    closeRunLogModal();

                }

            }
        );

    </script>

</body>
</html>