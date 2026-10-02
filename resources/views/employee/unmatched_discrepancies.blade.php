<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Unmatched Discrepancies - ReconAgent</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .bg-grid-pattern {
            background-size: 30px 30px;
            background-image:
                linear-gradient(
                    to right,
                    rgba(255, 255, 255, 0.03) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    to bottom,
                    rgba(255, 255, 255, 0.03) 1px,
                    transparent 1px
                );
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

        <!-- =====================================================
             MOBILE HEADER
        ====================================================== -->

        <header
            class="md:hidden flex items-center justify-between p-4 bg-black/90 border-b border-neutral-800 shrink-0 backdrop-blur-md z-30"
        >

            <div class="flex items-center gap-3">

                <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">

                    <svg
                        class="w-5 h-5 text-black"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M12 4L20 8L12 12L4 8L12 4Z"
                            stroke="currentColor"
                            stroke-width="2"
                        />

                        <path
                            d="M4 12L12 16L20 12"
                            stroke="currentColor"
                            stroke-width="2"
                        />

                        <path
                            d="M4 16L12 20L20 16"
                            stroke="currentColor"
                            stroke-width="2"
                        />
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
                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>
            </button>

        </header>


        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <aside
            id="sidebar"
            class="fixed inset-y-0 left-0 z-20 w-64 bg-black/95 md:bg-black/80 border-r border-neutral-800 flex flex-col justify-between shrink-0 backdrop-blur-md -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:static"
        >

            <div>

                <div class="hidden md:block p-6 border-b border-neutral-800">

                    <div class="flex items-center gap-3">

                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">

                            <svg
                                class="w-5 h-5 text-black"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M12 4L20 8L12 12L4 8L12 4Z"
                                    stroke="currentColor"
                                    stroke-width="2"
                                />

                                <path
                                    d="M4 12L12 16L20 12"
                                    stroke="currentColor"
                                    stroke-width="2"
                                />

                                <path
                                    d="M4 16L12 20L20 16"
                                    stroke="currentColor"
                                    stroke-width="2"
                                />
                            </svg>

                        </div>

                        <span class="text-lg font-bold tracking-tight text-white">
                            ReconAgent
                        </span>

                    </div>

                </div>

                <x-employee.nav :total_unmatched_discrepancies="$total_unmatched_discrepancies" />

            </div>


            <div class="p-4 border-t border-neutral-800 flex items-center justify-between">

                <div>

                    <p class="text-xs font-semibold text-white">
                        System Tenant
                    </p>

                    <p class="text-[10px] text-neutral-500">
                        Employee Role
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

        
        <x-unmatched_discrepancies.discrepancies
            :runs="$runs"
            :total_unmatched_discrepancies="$total_unmatched_discrepancies"
        />
    </div>
</body>
</html>
