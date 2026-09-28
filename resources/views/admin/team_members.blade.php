<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Team Members - ReconAgent</title>

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
            animation: revealCard 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .delay-1 {
            animation-delay: 0.05s;
        }

        .delay-2 {
            animation-delay: 0.12s;
        }

        /* ------------------------------------------------------------
         * Toast
         * ------------------------------------------------------------ */

        .toast {
            opacity: 0;
            transform: translateY(-12px) scale(0.97);
            pointer-events: none;
            transition:
                opacity 220ms ease,
                transform 220ms ease;
        }

        .toast.toast-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .toast-progress {
            transform-origin: left;
        }

        @keyframes toastProgress {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        .toast-progress.animate {
            animation: toastProgress 4s linear forwards;
        }

        /* ------------------------------------------------------------
         * Modal
         * ------------------------------------------------------------ */

        .modal-root {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition:
                opacity 200ms ease,
                visibility 200ms ease;
        }

        .modal-root.modal-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .modal-panel {
            opacity: 0;
            transform: translateY(18px) scale(0.97);
            transition:
                opacity 220ms cubic-bezier(0.16, 1, 0.3, 1),
                transform 220ms cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal-open .modal-panel {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        /* ------------------------------------------------------------
         * Confirmation modal
         * ------------------------------------------------------------ */

        .confirm-modal-root {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition:
                opacity 180ms ease,
                visibility 180ms ease;
        }

        .confirm-modal-root.confirm-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .confirm-panel {
            opacity: 0;
            transform: translateY(12px) scale(0.96);
            transition:
                opacity 200ms cubic-bezier(0.16, 1, 0.3, 1),
                transform 200ms cubic-bezier(0.16, 1, 0.3, 1);
        }

        .confirm-open .confirm-panel {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        /* ------------------------------------------------------------
         * Custom select
         * ------------------------------------------------------------ */

        .clean-select {
            appearance: none;
            -webkit-appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, #737373 50%),
                linear-gradient(135deg, #737373 50%, transparent 50%);
            background-position:
                calc(100% - 18px) 50%,
                calc(100% - 13px) 50%;
            background-size:
                5px 5px,
                5px 5px;
            background-repeat: no-repeat;
            padding-right: 2.75rem;
        }

        .clean-select:hover {
            border-color: #404040;
        }

        .clean-select:focus {
            border-color: #525252;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.04);
        }

        /* ------------------------------------------------------------
         * Loading spinner
         * ------------------------------------------------------------ */

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-spinner {
            animation: spin 700ms linear infinite;
        }

        /* ------------------------------------------------------------
         * Scrollbar
         * ------------------------------------------------------------ */

        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #262626;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #404040;
        }
    </style>
</head>

<body class="bg-black text-slate-100 font-sans antialiased bg-grid-pattern min-h-screen">

<div class="flex flex-col md:flex-row h-screen overflow-hidden">

    <!-- ========================================================= -->
    <!-- Mobile Header                                             -->
    <!-- ========================================================= -->

    <header
        class="md:hidden flex items-center justify-between p-4 bg-black/90 border-b border-neutral-800 shrink-0 backdrop-blur-md z-30"
    >

        <div class="flex items-center gap-3">

            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">

                <svg
                    class="w-5 h-5 text-black"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M12 4L20 8L12 12L4 8L12 4Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M4 12L12 16L20 12"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M4 16L12 20L20 16"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

            </div>

            <span class="text-lg font-bold tracking-tight text-white">
                ReconAgent
            </span>

        </div>


        <button
            id="menu-toggle"
            type="button"
            class="p-2 text-neutral-400 hover:text-white focus:outline-none"
            aria-label="Toggle navigation"
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


    <!-- ========================================================= -->
    <!-- Sidebar                                                    -->
    <!-- ========================================================= -->

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
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M12 4L20 8L12 12L4 8L12 4Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M4 12L12 16L20 12"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M4 16L12 20L20 16"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                    </div>

                    <span class="text-lg font-bold tracking-tight text-white">
                        ReconAgent
                    </span>

                </div>

            </div>


            <x-admin.nav
                :total_unmatched_discrepancies="$total_unmatched_discrepancies"
            />

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


    <!-- Sidebar Overlay -->

    <div
        id="sidebar-overlay"
        class="fixed inset-0 bg-black/60 z-10 hidden md:hidden"
    ></div>


    <!-- ========================================================= -->
    <!-- Main                                                       -->
    <!-- ========================================================= -->

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">

        <!-- Page Header -->

        <header
            class="animate-reveal flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 md:mb-8 pb-4 border-b border-neutral-800"
        >

            <div>

                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                    Reconciliation Operations
                </h1>

                <p class="text-xs text-neutral-400 mt-1">

                    Active Environment:

                    <span class="text-white font-medium">
                        {{ $metrics['active_workspace'] ?? 'Production Workspace' }}
                    </span>

                </p>

            </div>

        </header>


        <!-- ===================================================== -->
        <!-- Metrics                                                -->
        <!-- ===================================================== -->

        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 md:mb-8">

            <div
                class="animate-reveal delay-1 bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm hover:border-neutral-700 transition-colors"
            >

                <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">
                    Active Team Members
                </p>

                <p class="text-2xl font-bold mt-2 text-white font-mono">
                    {{ $metrics['total_users'] ?? 0 }}
                </p>

            </div>

        </section>


        <!-- ===================================================== -->
        <!-- Team Members                                           -->
        <!-- ===================================================== -->

        <section
            class="animate-reveal delay-2 bg-black/40 border border-neutral-800 rounded-xl p-4 sm:p-6 backdrop-blur-sm"
        >

            <div class="flex justify-between items-center mb-6">

                <div>

                    <h2 class="text-base font-semibold text-white">
                        Team Access & Roles
                    </h2>

                    <p class="text-xs text-neutral-400 mt-0.5">
                        Manage authorized developers and system operators.
                    </p>

                </div>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full text-left text-xs min-w-[600px]">

                    <thead
                        class="bg-neutral-900/60 text-neutral-400 uppercase tracking-wider font-mono border-b border-neutral-800"
                    >

                        <tr>

                            <th class="p-3">
                                S/N
                            </th>

                            <th class="p-3">
                                User
                            </th>

                            <th class="p-3">
                                Role
                            </th>

                            <th class="p-3">
                                Status
                            </th>

                            <th class="p-3 text-right">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-neutral-800 text-neutral-300">

                    @if(blank($user_management))

                        <tr>

                            <td
                                colspan="5"
                                class="p-4 text-center text-neutral-500 font-mono"
                            >
                                No Record Found!
                            </td>

                        </tr>

                    @else

                        @foreach($user_management as $user)

                            @php
                                $userId = $user->id;
                                $isAdmin = (bool) $user->is_admin;
                                $isActive = (bool) $user->account_status;
                            @endphp

                            <tr class="hover:bg-white/[0.02] transition-colors">

                                <td class="p-3 font-medium text-white font-mono">
                                    {{ $loop->iteration }}
                                </td>


                                <td class="p-3 font-medium text-white">

                                    {{ $user->username }}

                                    <br>

                                    <span class="text-neutral-500 text-[11px] font-normal">
                                        {{ $user->email }}
                                    </span>

                                </td>


                                <td class="p-3">

                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] bg-white/10 text-white border border-white/20 font-mono"
                                    >
                                        {{ $isAdmin ? 'ADMIN' : 'EMPLOYEE' }}
                                    </span>

                                </td>


                                <td
                                    class="p-3 font-medium {{ $isActive ? 'text-emerald-400' : 'text-amber-400' }}"
                                >
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </td>


                                <td class="p-3 text-right">

                                    <button
                                        type="button"
                                        class="manage-user-btn text-neutral-400 hover:text-white transition-colors"
                                        data-user-id="{{ $userId }}"
                                        data-username="{{ $user->username }}"
                                        data-email="{{ $user->email }}"
                                        data-role="{{ $isAdmin ? 'ADMIN' : 'EMPLOYEE' }}"
                                        data-status="{{ $isActive ? 'Active' : 'Inactive' }}"
                                    >
                                        Manage
                                    </button>

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


<!-- ========================================================= -->
<!-- Toast Notification                                         -->
<!-- ========================================================= -->

<div
    id="toast-container"
    class="fixed top-5 right-5 z-[100] w-[calc(100%-2.5rem)] max-w-sm"
    aria-live="polite"
    aria-atomic="true"
>

    <div
        id="toast"
        class="toast bg-neutral-950/95 border border-neutral-800 rounded-xl shadow-2xl backdrop-blur-xl overflow-hidden"
    >

        <div class="p-4">

            <div class="flex items-start gap-3">

                <div
                    id="toast-icon"
                    class="mt-0.5 w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                >

                    <svg
                        id="toast-success-icon"
                        class="w-4 h-4 hidden"
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


                    <svg
                        id="toast-error-icon"
                        class="w-4 h-4 hidden"
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

                </div>


                <div class="min-w-0 flex-1">

                    <p
                        id="toast-title"
                        class="text-sm font-semibold text-white"
                    >
                        Success
                    </p>

                    <p
                        id="toast-message"
                        class="text-xs text-neutral-400 mt-1 leading-relaxed"
                    ></p>

                </div>


                <button
                    id="toast-close"
                    type="button"
                    class="text-neutral-600 hover:text-white transition-colors"
                    aria-label="Close notification"
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
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>

                </button>

            </div>

        </div>


        <div
            id="toast-progress"
            class="toast-progress h-[2px] bg-emerald-400"
        ></div>

    </div>

</div>


<!-- ========================================================= -->
<!-- Manage User Modal                                         -->
<!-- ========================================================= -->

<div
    id="manage-user-modal"
    class="modal-root fixed inset-0 z-50"
    aria-hidden="true"
>

    <div
        id="manage-user-overlay"
        class="absolute inset-0 bg-black/80 backdrop-blur-sm"
    ></div>


    <div class="relative min-h-screen flex items-center justify-center p-4">

        <div
            class="modal-panel w-full max-w-2xl bg-neutral-950 border border-neutral-800 rounded-2xl shadow-2xl overflow-hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="manage-user-title"
        >

            <!-- Header -->

            <div
                class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-neutral-800"
            >

                <div>

                    <p class="text-[10px] uppercase tracking-widest text-neutral-500 font-mono">
                        Account Management
                    </p>

                    <h2
                        id="manage-user-title"
                        class="text-lg font-semibold text-white mt-1"
                    >
                        Manage User
                    </h2>

                </div>


                <button
                    type="button"
                    id="close-manage-user"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-neutral-500 hover:text-white hover:bg-white/5 transition-colors"
                    aria-label="Close"
                >

                    <svg
                        class="w-5 h-5"
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

                </button>

            </div>


            <!-- Body -->

            <div class="p-5 sm:p-6 max-h-[75vh] overflow-y-auto">

                <!-- Account Summary -->

                <div class="bg-black/50 border border-neutral-800 rounded-xl p-4 mb-6">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <div>

                            <p class="text-xs text-neutral-500 uppercase tracking-wider">
                                Account
                            </p>

                            <p
                                id="manage-username"
                                class="text-base font-semibold text-white mt-1"
                            >
                                —
                            </p>

                            <p
                                id="manage-email"
                                class="text-xs text-neutral-500 mt-1"
                            >
                                —
                            </p>

                        </div>


                        <div class="flex items-center gap-2">

                            <span
                                id="manage-role-badge"
                                class="px-2 py-1 rounded text-[10px] bg-white/10 text-white border border-white/20 font-mono"
                            >
                                —
                            </span>

                            <span
                                id="manage-status-badge"
                                class="px-2 py-1 rounded text-[10px] border font-mono"
                            >
                                —
                            </span>

                        </div>

                    </div>

                </div>


                <!-- Account Details -->

                <div class="mb-6">

                    <div class="mb-3">

                        <h3 class="text-sm font-semibold text-white">
                            Account Details
                        </h3>

                        <p class="text-xs text-neutral-500 mt-1">
                            Update the user's account information and access level.
                        </p>

                    </div>


                    <form
                        id="update-user-form"
                        method="POST"
                        class="space-y-4"
                    >

                        @csrf
                        @method('PUT')

                        <input
                            type="hidden"
                            id="manage-user-id"
                            name="user_id"
                        >


                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <!-- Username -->

                            <div>

                                <label
                                    for="manage-username-input"
                                    class="block text-xs text-neutral-400 mb-2"
                                >
                                    Username
                                </label>

                                <input
                                    type="text"
                                    id="manage-username-input"
                                    name="username"
                                    class="w-full bg-black border border-neutral-800 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-neutral-600 transition-colors"
                                    required
                                >

                            </div>


                            <!-- Email -->

                            <div>

                                <label
                                    for="manage-email-input"
                                    class="block text-xs text-neutral-400 mb-2"
                                >
                                    Email
                                </label>

                                <input
                                    type="email"
                                    id="manage-email-input"
                                    name="email"
                                    class="w-full bg-black border border-neutral-800 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-neutral-600 transition-colors"
                                    required
                                >

                            </div>

                        </div>


                        <!-- Role -->

                        <div>

                            <label
                                for="manage-role-input"
                                class="block text-xs text-neutral-400 mb-2"
                            >
                                Role
                            </label>

                            <div class="relative">

                                <select
                                    id="manage-role-input"
                                    name="is_admin"
                                    class="clean-select w-full bg-black border border-neutral-800 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none transition-colors cursor-pointer"
                                >

                                    <option value="0">
                                        EMPLOYEE
                                    </option>

                                    <option value="1">
                                        ADMIN
                                    </option>

                                </select>

                            </div>

                        </div>


                        <!-- Save -->

                        <div class="flex justify-end">

                            <button
                                type="submit"
                                class="px-4 py-2.5 rounded-lg bg-white text-black text-xs font-semibold hover:bg-neutral-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Save Account Changes
                            </button>

                        </div>

                    </form>

                </div>


                <!-- Access Control -->

                <div class="border-t border-neutral-800 pt-6 mb-6">

                    <div class="mb-3">

                        <h3 class="text-sm font-semibold text-white">
                            Access Control
                        </h3>

                        <p class="text-xs text-neutral-500 mt-1">
                            Control whether this account can access ReconAgent.
                        </p>

                    </div>


                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-black/40 border border-neutral-800 rounded-xl p-4"
                    >

                        <div>

                            <p class="text-sm text-white font-medium">
                                Account Status
                            </p>

                            <p
                                id="access-status-description"
                                class="text-xs text-neutral-500 mt-1"
                            >
                                —
                            </p>

                        </div>


                        <button
                            type="button"
                            id="toggle-user-status"
                            class="px-4 py-2 rounded-lg text-xs font-semibold border transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            —
                        </button>

                    </div>

                </div>


                <!-- Password -->

                <div class="border-t border-neutral-800 pt-6 mb-6">

                    <div class="mb-3">

                        <h3 class="text-sm font-semibold text-white">
                            Password Management
                        </h3>

                        <p class="text-xs text-neutral-500 mt-1">
                            Generate a password reset request for this account.
                        </p>

                    </div>


                    <button
                        type="button"
                        id="reset-user-password"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-lg border border-neutral-700 text-xs font-medium text-neutral-300 hover:text-white hover:border-neutral-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Send Password Reset
                    </button>

                </div>


                <!-- Danger Zone -->

                <div class="border-t border-red-900/40 pt-6">

                    <div class="mb-3">

                        <h3 class="text-sm font-semibold text-red-400">
                            Danger Zone
                        </h3>

                        <p class="text-xs text-neutral-500 mt-1">
                            These actions can affect the user's ability to access the system.
                        </p>

                    </div>


                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-red-950/10 border border-red-900/30 rounded-xl p-4"
                    >

                        <div>

                            <p class="text-sm text-white font-medium">
                                Delete Account
                            </p>

                            <p class="text-xs text-neutral-500 mt-1">
                                Permanently remove this user account.
                            </p>

                        </div>


                        <button
                            type="button"
                            id="delete-user-account"
                            class="px-4 py-2 rounded-lg border border-red-900/60 text-red-400 text-xs font-semibold hover:bg-red-950/30 hover:text-red-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Delete Account
                        </button>

                    </div>

                </div>

            </div>


            <!-- Footer -->

            <div
                class="flex justify-end px-5 sm:px-6 py-4 border-t border-neutral-800 bg-black/30"
            >

                <button
                    type="button"
                    id="close-manage-user-footer"
                    class="px-4 py-2 rounded-lg border border-neutral-800 text-xs text-neutral-400 hover:text-white hover:border-neutral-700 transition-colors"
                >
                    Close
                </button>

            </div>

        </div>

    </div>

</div>


<!-- ========================================================= -->
<!-- Confirmation Modal                                         -->
<!-- ========================================================= -->

<div
    id="confirmation-modal"
    class="confirm-modal-root fixed inset-0 z-[80]"
    aria-hidden="true"
>

    <div
        id="confirmation-overlay"
        class="absolute inset-0 bg-black/80 backdrop-blur-sm"
    ></div>


    <div class="relative min-h-screen flex items-center justify-center p-4">

        <div
            class="confirm-panel w-full max-w-md bg-neutral-950 border border-neutral-800 rounded-2xl shadow-2xl overflow-hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirmation-title"
        >

            <div class="p-5 sm:p-6">

                <div class="flex items-start gap-4">

                    <div
                        id="confirmation-icon"
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-white/5 border border-white/10"
                    >

                        <svg
                            class="w-5 h-5 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v3.75m0 3.25h.01M10.29 3.86l-7.4 12.83A2 2 0 004.63 19.7h14.74a2 2 0 001.74-3.01L13.71 3.86a2 2 0 00-3.42 0z"
                            />
                        </svg>

                    </div>


                    <div class="flex-1">

                        <h3
                            id="confirmation-title"
                            class="text-base font-semibold text-white"
                        >
                            Confirm Change
                        </h3>

                        <p
                            id="confirmation-message"
                            class="text-sm text-neutral-400 mt-2 leading-relaxed"
                        >
                            Are you sure you want to continue?
                        </p>

                    </div>

                </div>


                <div class="flex justify-end gap-3 mt-7">

                    <button
                        type="button"
                        id="confirmation-cancel"
                        class="px-4 py-2.5 rounded-lg border border-neutral-800 text-xs font-medium text-neutral-400 hover:text-white hover:border-neutral-700 transition-colors"
                    >
                        Cancel
                    </button>


                    <button
                        type="button"
                        id="confirmation-confirm"
                        class="px-4 py-2.5 rounded-lg bg-white text-black text-xs font-semibold hover:bg-neutral-200 transition-colors"
                    >
                        Confirm Change
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value;


    /*
    |--------------------------------------------------------------------------
    | Mobile Sidebar
    |--------------------------------------------------------------------------
    */

    const menuToggle =
        document.getElementById('menu-toggle');

    const sidebar =
        document.getElementById('sidebar');

    const sidebarOverlay =
        document.getElementById('sidebar-overlay');


    function toggleSidebar() {

        if (!sidebar || !sidebarOverlay) {
            return;
        }

        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    }


    menuToggle?.addEventListener(
        'click',
        toggleSidebar
    );

    sidebarOverlay?.addEventListener(
        'click',
        toggleSidebar
    );


    /*
    |--------------------------------------------------------------------------
    | Toast Notification
    |--------------------------------------------------------------------------
    */

    const toast =
        document.getElementById('toast');

    const toastTitle =
        document.getElementById('toast-title');

    const toastMessage =
        document.getElementById('toast-message');

    const toastIcon =
        document.getElementById('toast-icon');

    const toastSuccessIcon =
        document.getElementById('toast-success-icon');

    const toastErrorIcon =
        document.getElementById('toast-error-icon');

    const toastProgress =
        document.getElementById('toast-progress');

    const toastClose =
        document.getElementById('toast-close');


    let toastTimer = null;


    function showToast(
        message,
        type = 'success',
        title = null
    ) {

        if (!toast) {
            return;
        }

        clearTimeout(toastTimer);

        const isSuccess =
            type === 'success';


        toastTitle.textContent =
            title ||
            (isSuccess ? 'Success' : 'Action Failed');


        toastMessage.textContent =
            message;


        toastIcon.className =
            isSuccess
                ? 'mt-0.5 w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-emerald-950/50 border border-emerald-900/50 text-emerald-400'
                : 'mt-0.5 w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-red-950/50 border border-red-900/50 text-red-400';


        toastSuccessIcon.classList.toggle(
            'hidden',
            !isSuccess
        );

        toastErrorIcon.classList.toggle(
            'hidden',
            isSuccess
        );


        toastProgress.className =
            isSuccess
                ? 'toast-progress h-[2px] bg-emerald-400'
                : 'toast-progress h-[2px] bg-red-400';


        /*
         * Force the animation to restart.
         */
        void toastProgress.offsetWidth;

        toastProgress.classList.add('animate');


        toast.classList.add('toast-visible');


        toastTimer = setTimeout(() => {

            hideToast();

        }, 4000);
    }


    function hideToast() {

        clearTimeout(toastTimer);

        toast?.classList.remove(
            'toast-visible'
        );
    }


    toastClose?.addEventListener(
        'click',
        hideToast
    );


    /*
    |--------------------------------------------------------------------------
    | Manage User Elements
    |--------------------------------------------------------------------------
    */

    const manageUserModal =
        document.getElementById('manage-user-modal');

    const manageUserOverlay =
        document.getElementById('manage-user-overlay');

    const closeManageUser =
        document.getElementById('close-manage-user');

    const closeManageUserFooter =
        document.getElementById('close-manage-user-footer');

    const manageUserId =
        document.getElementById('manage-user-id');

    const manageUsername =
        document.getElementById('manage-username');

    const manageEmail =
        document.getElementById('manage-email');

    const manageUsernameInput =
        document.getElementById('manage-username-input');

    const manageEmailInput =
        document.getElementById('manage-email-input');

    const manageRoleInput =
        document.getElementById('manage-role-input');

    const manageRoleBadge =
        document.getElementById('manage-role-badge');

    const manageStatusBadge =
        document.getElementById('manage-status-badge');

    const accessStatusDescription =
        document.getElementById('access-status-description');

    const toggleUserStatus =
        document.getElementById('toggle-user-status');

    const resetUserPassword =
        document.getElementById('reset-user-password');

    const deleteUserAccount =
        document.getElementById('delete-user-account');

    const updateUserForm =
        document.getElementById('update-user-form');


    /*
    |--------------------------------------------------------------------------
    | Confirmation Modal
    |--------------------------------------------------------------------------
    */

    const confirmationModal =
        document.getElementById('confirmation-modal');

    const confirmationOverlay =
        document.getElementById('confirmation-overlay');

    const confirmationTitle =
        document.getElementById('confirmation-title');

    const confirmationMessage =
        document.getElementById('confirmation-message');

    const confirmationConfirm =
        document.getElementById('confirmation-confirm');

    const confirmationCancel =
        document.getElementById('confirmation-cancel');


    let pendingConfirmation = null;


    function openConfirmation({
        title = 'Confirm Change',
        message = 'Are you sure you want to continue?',
        confirmText = 'Confirm Change',
        danger = false,
        action = null
    }) {

        confirmationTitle.textContent =
            title;

        confirmationMessage.textContent =
            message;

        confirmationConfirm.textContent =
            confirmText;


        if (danger) {

            confirmationConfirm.className =
                'px-4 py-2.5 rounded-lg bg-red-500 text-white text-xs font-semibold hover:bg-red-400 transition-colors';

        } else {

            confirmationConfirm.className =
                'px-4 py-2.5 rounded-lg bg-white text-black text-xs font-semibold hover:bg-neutral-200 transition-colors';
        }


        pendingConfirmation =
            action;


        confirmationModal.classList.add(
            'confirm-open'
        );

        confirmationModal.setAttribute(
            'aria-hidden',
            'false'
        );
    }


    function closeConfirmation() {

        confirmationModal.classList.remove(
            'confirm-open'
        );

        confirmationModal.setAttribute(
            'aria-hidden',
            'true'
        );

        pendingConfirmation =
            null;
    }


    confirmationCancel?.addEventListener(
        'click',
        closeConfirmation
    );


    confirmationOverlay?.addEventListener(
        'click',
        closeConfirmation
    );


    confirmationConfirm?.addEventListener(
        'click',
        async function () {

            if (
                typeof pendingConfirmation !== 'function'
            ) {
                closeConfirmation();
                return;
            }


            const action =
                pendingConfirmation;


            pendingConfirmation =
                null;


            this.disabled = true;
            this.textContent = 'Processing...';


            try {

                await action();

            } finally {

                this.disabled = false;
                this.textContent = 'Confirm Change';

                closeConfirmation();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Current User
    |--------------------------------------------------------------------------
    */

    let currentUser = {
        id: null,
        username: null,
        email: null,
        role: null,
        status: null
    };


    /*
    |--------------------------------------------------------------------------
    | Manage User Modal
    |--------------------------------------------------------------------------
    */

    function openManageUser(button) {

        currentUser.id =
            button.dataset.userId;

        currentUser.username =
            button.dataset.username || '';

        currentUser.email =
            button.dataset.email || '';

        currentUser.role =
            button.dataset.role || 'EMPLOYEE';

        currentUser.status =
            button.dataset.status || 'Inactive';


        manageUserId.value =
            currentUser.id;


        manageUsername.textContent =
            currentUser.username;

        manageEmail.textContent =
            currentUser.email;


        manageUsernameInput.value =
            currentUser.username;

        manageEmailInput.value =
            currentUser.email;


        manageRoleInput.value =
            currentUser.role === 'ADMIN'
                ? '1'
                : '0';


        manageRoleBadge.textContent =
            currentUser.role;


        updateStatusUI(
            currentUser.status
        );


        manageUserModal.classList.add(
            'modal-open'
        );

        manageUserModal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'overflow-hidden'
        );
    }


    function closeManageUserModal() {

        manageUserModal.classList.remove(
            'modal-open'
        );

        manageUserModal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'overflow-hidden'
        );


        currentUser = {
            id: null,
            username: null,
            email: null,
            role: null,
            status: null
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Status UI
    |--------------------------------------------------------------------------
    */

    function updateStatusUI(status) {

        currentUser.status =
            status;


        manageStatusBadge.textContent =
            status;


        if (status === 'Active') {

            manageStatusBadge.className =
                'px-2 py-1 rounded text-[10px] border font-mono text-emerald-400 border-emerald-900/50 bg-emerald-950/20';


            accessStatusDescription.textContent =
                'This account currently has access to ReconAgent.';


            toggleUserStatus.textContent =
                'Deactivate Account';


            toggleUserStatus.className =
                'px-4 py-2 rounded-lg text-xs font-semibold border border-amber-900/60 text-amber-400 hover:bg-amber-950/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed';

        } else {

            manageStatusBadge.className =
                'px-2 py-1 rounded text-[10px] border font-mono text-amber-400 border-amber-900/50 bg-amber-950/20';


            accessStatusDescription.textContent =
                'This account is currently prevented from accessing ReconAgent.';


            toggleUserStatus.textContent =
                'Activate Account';


            toggleUserStatus.className =
                'px-4 py-2 rounded-lg text-xs font-semibold border border-emerald-900/60 text-emerald-400 hover:bg-emerald-950/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed';
        }


        toggleUserStatus.dataset.status =
            status;
    }


    /*
    |--------------------------------------------------------------------------
    | Generic API Request
    |--------------------------------------------------------------------------
    */

    async function apiRequest(
        url,
        options = {}
    ) {

        const response =
            await fetch(url, {

                ...options,

                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',

                    ...(options.headers || {})
                }

            });


        const contentType =
            response.headers.get(
                'content-type'
            ) || '';


        let data = {};


        if (
            contentType.includes(
                'application/json'
            )
        ) {

            data =
                await response.json();
        }


        if (!response.ok) {

            let message =
                data.message ||
                'The request could not be completed.';


            if (
                response.status === 422 &&
                data.errors
            ) {

                const firstError =
                    Object.values(
                        data.errors
                    ).flat()[0];


                if (firstError) {
                    message =
                        firstError;
                }
            }


            throw new Error(
                message
            );
        }


        return data;
    }


    /*
    |--------------------------------------------------------------------------
    | Open User
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.manage-user-btn')
        .forEach(button => {

            button.addEventListener(
                'click',
                function () {

                    openManageUser(this);

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Close Manage Modal
    |--------------------------------------------------------------------------
    */

    closeManageUser?.addEventListener(
        'click',
        closeManageUserModal
    );


    closeManageUserFooter?.addEventListener(
        'click',
        closeManageUserModal
    );


    manageUserOverlay?.addEventListener(
        'click',
        closeManageUserModal
    );


    /*
    |--------------------------------------------------------------------------
    | Escape Key
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key !== 'Escape'
            ) {
                return;
            }


            if (
                confirmationModal.classList.contains(
                    'confirm-open'
                )
            ) {

                closeConfirmation();
                return;
            }


            if (
                manageUserModal.classList.contains(
                    'modal-open'
                )
            ) {

                closeManageUserModal();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    updateUserForm?.addEventListener(
        'submit',
        function (event) {

            event.preventDefault();


            const userId =
                manageUserId.value;


            if (!userId) {

                showToast(
                    'No user is currently selected.',
                    'error'
                );

                return;
            }


            const username =
                manageUsernameInput.value.trim();

            const email =
                manageEmailInput.value.trim();

            const selectedRole =
                manageRoleInput.value === '1'
                    ? 'ADMIN'
                    : 'EMPLOYEE';


            const currentRole =
                currentUser.role;


            const roleChanged =
                selectedRole !== currentRole;

            const usernameChanged =
                username !== currentUser.username;

            const emailChanged =
                email !== currentUser.email;


            if (
                !roleChanged &&
                !usernameChanged &&
                !emailChanged
            ) {

                showToast(
                    'No changes were made to this account.',
                    'error',
                    'No Changes'
                );

                return;
            }


            let changes = [];


            if (usernameChanged) {

                changes.push(
                    'the username'
                );
            }


            if (emailChanged) {

                changes.push(
                    'the email address'
                );
            }


            if (roleChanged) {

                changes.push(
                    `the role to ${selectedRole}`
                );
            }


            const changeDescription =
                changes.length === 1
                    ? changes[0]
                    : changes.slice(0, -1).join(', ') +
                      ' and ' +
                      changes[changes.length - 1];


            openConfirmation({

                title: 'Confirm Account Changes',

                message:
                    `Are you sure you want to change ${changeDescription} for ${currentUser.username}?`,

                confirmText:
                    'Save Changes',

                action:
                    async function () {

                        const submitButton =
                            updateUserForm.querySelector(
                                'button[type="submit"]'
                            );


                        const originalText =
                            submitButton.textContent;


                        submitButton.disabled =
                            true;

                        submitButton.textContent =
                            'Saving...';


                        try {

                            const formData =
                                new FormData(
                                    updateUserForm
                                );


                            const data =
                                await apiRequest(
                                    `/admin/users/${encodeURIComponent(userId)}`,
                                    {
                                        method: 'POST',
                                        body: formData
                                    }
                                );


                            showToast(
                                data.message ||
                                'User account updated successfully.'
                            );


                            window.setTimeout(
                                function () {
                                    window.location.reload();
                                },
                                900
                            );

                        } catch (error) {

                            console.error(
                                error
                            );


                            showToast(
                                error.message ||
                                'Something went wrong while updating the account.',
                                'error'
                            );


                            submitButton.disabled =
                                false;

                            submitButton.textContent =
                                originalText;

                            throw error;
                        }

                    }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Activate / Deactivate
    |--------------------------------------------------------------------------
    */

    toggleUserStatus?.addEventListener(
        'click',
        function () {

            const userId =
                manageUserId.value;


            const currentStatus =
                this.dataset.status;


            if (!userId) {

                showToast(
                    'No user is currently selected.',
                    'error'
                );

                return;
            }


            const isActive =
                currentStatus === 'Active';


            const action =
                isActive
                    ? 'deactivate'
                    : 'activate';


            const actionText =
                isActive
                    ? 'deactivate this account'
                    : 'activate this account';


            openConfirmation({

                title:
                    isActive
                        ? 'Deactivate Account'
                        : 'Activate Account',

                message:
                    `Are you sure you want to ${actionText} for ${currentUser.username}?`,

                confirmText:
                    isActive
                        ? 'Deactivate Account'
                        : 'Activate Account',

                danger:
                    isActive,

                action:
                    async function () {

                        const button =
                            toggleUserStatus;


                        button.disabled =
                            true;


                        try {

                            const data =
                                await apiRequest(
                                    `/admin/users/${encodeURIComponent(userId)}/${action}`,
                                    {
                                        method: 'POST'
                                    }
                                );


                            showToast(
                                data.message ||
                                'Account status updated successfully.'
                            );


                            window.setTimeout(
                                function () {
                                    window.location.reload();
                                },
                                900
                            );

                        } catch (error) {

                            console.error(
                                error
                            );


                            showToast(
                                error.message ||
                                'Unable to update account status.',
                                'error'
                            );


                            button.disabled =
                                false;

                            throw error;
                        }

                    }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */

    resetUserPassword?.addEventListener(
        'click',
        function () {

            const userId =
                manageUserId.value;


            if (!userId) {

                showToast(
                    'No user is currently selected.',
                    'error'
                );

                return;
            }


            openConfirmation({

                title:
                    'Send Password Reset',

                message:
                    `Send a password reset request to ${currentUser.email}?`,

                confirmText:
                    'Send Reset Request',

                action:
                    async function () {

                        const button =
                            resetUserPassword;


                        const originalText =
                            button.textContent;


                        button.disabled =
                            true;

                        button.textContent =
                            'Sending...';


                        try {

                            const data =
                                await apiRequest(
                                    `/admin/users/${encodeURIComponent(userId)}/password-reset`,
                                    {
                                        method: 'POST'
                                    }
                                );


                            showToast(
                                data.message ||
                                'Password reset request sent successfully.'
                            );

                        } catch (error) {

                            console.error(
                                error
                            );


                            showToast(
                                error.message ||
                                'Unable to send password reset request.',
                                'error'
                            );


                            throw error;

                        } finally {

                            button.disabled =
                                false;

                            button.textContent =
                                originalText;
                        }

                    }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Delete User
    |--------------------------------------------------------------------------
    */

    deleteUserAccount?.addEventListener(
        'click',
        function () {

            const userId =
                manageUserId.value;


            const username =
                manageUsername.textContent;


            if (!userId) {

                showToast(
                    'No user is currently selected.',
                    'error'
                );

                return;
            }


            openConfirmation({

                title:
                    'Delete User Account',

                message:
                    `This will permanently delete the account "${username}". This action cannot be undone. Are you sure you want to continue?`,

                confirmText:
                    'Delete Account',

                danger:
                    true,

                action:
                    async function () {

                        const button =
                            deleteUserAccount;


                        const originalText =
                            button.textContent;


                        button.disabled =
                            true;

                        button.textContent =
                            'Deleting...';


                        try {

                            const data =
                                await apiRequest(
                                    `/admin/users/${encodeURIComponent(userId)}`,
                                    {
                                        method: 'DELETE'
                                    }
                                );


                            showToast(
                                data.message ||
                                'User account deleted successfully.'
                            );


                            closeManageUserModal();


                            window.setTimeout(
                                function () {
                                    window.location.reload();
                                },
                                900
                            );

                        } catch (error) {

                            console.error(
                                error
                            );


                            showToast(
                                error.message ||
                                'Unable to delete account.',
                                'error'
                            );


                            button.disabled =
                                false;

                            button.textContent =
                                originalText;

                            throw error;
                        }

                    }

            });

        }
    );

});
</script>

</body>
</html>