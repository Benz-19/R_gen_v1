<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee Dashboard - ReconAgent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-grid-pattern {
            background-size: 30px 30px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="bg-black text-slate-100 font-sans antialiased bg-grid-pattern min-h-screen relative">
    <!-- Modal for Workspace Access -->
    <div id="verificationModal" class="{{ $isVerified ? 'hidden' : 'flex' }} fixed inset-0 z-50 items-center justify-center bg-black/80 backdrop-blur-xl p-4">
        <div class="bg-neutral-900 border border-neutral-800 p-6 sm:p-8 rounded-2xl max-w-md w-full shadow-2xl space-y-6">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-white tracking-tight">Workspace Access Required</h2>
                <p class="text-xs text-neutral-400">Enter your organization details to link your employee account and unlock dashboard features.</p>
            </div>

            <div id="modalAlert" class="hidden p-3 rounded-lg text-xs font-medium border"></div>

            <form id="verificationForm" onsubmit="event.preventDefault(); verifyEmployeeAccount();" class="space-y-4">
                <div>
                    <label class="block text-xs text-neutral-400 font-medium mb-1">Company Name</label>
                    <input type="text" id="companyName" class="w-full px-3 py-2 bg-black border border-neutral-800 rounded-lg text-sm text-white focus:outline-none focus:border-white transition-colors" placeholder="e.g. Acme Corp" required>
                </div>
                <div>
                    <label class="block text-xs text-neutral-400 font-medium mb-1">Join Code</label>
                    <input type="text" id="joinCode" class="w-full px-3 py-2 bg-black border border-neutral-800 rounded-lg text-sm text-white focus:outline-none focus:border-white transition-colors font-mono" placeholder="e.g. 123456" required>
                </div>
                <button type="submit" id="submitBtn" class="w-full py-2.5 bg-white text-black font-semibold rounded-lg text-xs hover:bg-neutral-200 transition-colors">
                    Verify & Join Workspace
                </button>
            </form>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-black/80 border-r border-neutral-800 flex flex-col justify-between shrink-0 backdrop-blur-md">
            <div>
                <div class="p-6 border-b border-neutral-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-neutral-900 border border-neutral-700 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4L18 7.5L12 11L6 7.5L12 4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M6 10.5L12 14L18 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 14L12 17.5L18 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 17.5L12 21L18 17.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold tracking-tight text-white">ReconAgent</span>
                    </div>
                </div>

                <x-employee.nav :total_unmatched_discrepancies="$total_unmatched_discrepancies" />
            
            </div>
            <div class="p-4 border-t border-neutral-800 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-white">Organization Staff</p>
                    <p class="text-[10px] text-neutral-500">Employee Role</p>
                </div>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-neutral-400 hover:text-white font-medium transition-colors">Logout</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto p-8">
            <header class="flex justify-between items-center mb-8 pb-4 border-b border-neutral-800">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Employee Workspace</h1>
                    <p class="text-xs text-neutral-400 mt-1">Tenant: <span class="text-white font-medium">{{ $metrics['active_workspace'] ?? 'Organization Workspace' }}</span></p>
                </div>
                    <button class="w-full sm:w-auto justify-center px-4 py-2 bg-white text-black hover:bg-neutral-200 font-semibold rounded-lg text-xs transition-transform active:scale-95 duration-150 flex items-center space-x-2">
                        <a href="/execute-recon-runs"><span>+ Run Reconciliation</span></a>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
            </header>

            <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Matched Records</p>
                    <p class="text-2xl font-bold mt-2 text-white font-mono">0</p>
                </div>
                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Assigned Exceptions</p>
                    <p class="text-2xl font-bold mt-2 text-emerald-400 font-mono">0</p>
                </div>
                <div class="bg-black/40 border border-neutral-800 p-5 rounded-xl backdrop-blur-sm">
                    <p class="text-xs text-neutral-400 font-medium uppercase tracking-wider">Resolved Discrepancies</p>
                    <p class="text-2xl font-bold mt-2 text-neutral-300 font-mono">0</p>
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
        function showAlert(message, isError = true) {
            const alertBox = document.getElementById('modalAlert');
            alertBox.classList.remove('hidden', 'bg-red-500/10', 'border-red-500/30', 'text-red-400', 'bg-emerald-500/10', 'border-emerald-500/30', 'text-emerald-400');
            
            if (isError) {
                alertBox.classList.add('bg-red-500/10', 'border-red-500/30', 'text-red-400');
            } else {
                alertBox.classList.add('bg-emerald-500/10', 'border-emerald-500/30', 'text-emerald-400');
            }
            
            alertBox.textContent = message;
        }

        async function verifyEmployeeAccount() {
            const joinCode = document.getElementById('joinCode')?.value.trim();
            const companyName = document.getElementById('companyName')?.value.trim();
            const submitBtn = document.getElementById('submitBtn');

            if (!joinCode || !companyName) {
                showAlert("Please provide both join code and company name.");
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying...';

            try {
                const response = await fetch('/api/v1/verify-employee-account', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        join_code: joinCode,
                        company_name: companyName
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.state) {
                    showAlert(data.message || 'Verification failed. Check your credentials.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Verify & Join Workspace';
                    return;
                }

                showAlert("Verified successfully! Loading dashboard...", false);
                setTimeout(() => window.location.reload(), 1000);

            } catch (error) {
                showAlert("A server error occurred. Please try again.");
                submitBtn.disabled = false;
                submitBtn.textContent = 'Verify & Join Workspace';
            }
        }
    </script>
</body>
</html>