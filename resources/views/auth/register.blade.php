<!DOCTYPE html>
<html lang="en" class="h-full bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black text-slate-100 font-mono">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReconAgent - Account Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        @keyframes spinBorder {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .circle-running {
            position: relative;
        }
        .circle-running::after {
            content: '';
            position: absolute;
            top: -3px; left: -3px; right: -3px; bottom: -3px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: #10b981;
            animation: spinBorder 1s linear infinite;
        }

        /* Smooth Radio Card Styles */
        .radio-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .radio-card:hover {
            transform: translateY(-2px);
            border-color: rgba(16, 185, 129, 0.4);
            box-shadow: 0 4px 20px -2px rgba(16, 185, 129, 0.1);
        }
        .radio-card:has(input:checked) {
            border-color: #10b981;
            background-color: rgba(16, 185, 129, 0.08);
            box-shadow: 0 0 25px -5px rgba(16, 185, 129, 0.15);
        }

        /* Step Panels Transition */
        .step-panel {
            transition: opacity 0.3s ease, transform 0.3s ease;
            will-change: opacity, transform;
        }
        .step-enter {
            opacity: 0;
            transform: translateY(10px) scale(0.99);
            pointer-events: none;
            position: absolute;
            width: 100%;
            visibility: hidden;
        }
        .step-active {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
            position: relative;
            visibility: visible;
        }
        .step-exit {
            opacity: 0;
            transform: translateY(-10px) scale(0.99);
            pointer-events: none;
            position: absolute;
            width: 100%;
            visibility: hidden;
        }

        /* Smooth Box Transitions */
        .smooth-box {
            transition: all 0.3s ease-in-out;
            max-height: 500px;
            opacity: 1;
            overflow: hidden;
        }
        .smooth-box.box-hidden {
            max-height: 0;
            opacity: 0;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            border-width: 0 !important;
            pointer-events: none;
        }

        .box-hidden{
            display: none !important;
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-grid antialiased">

    <!-- Top Navigation Header -->
    <header class="w-full border-b border-slate-800/60 bg-slate-950/80 backdrop-blur-md px-6 py-4 flex items-center justify-between z-10">
        <a href="/" class="flex items-center space-x-3 group transition-transform duration-200 active:scale-95">
            <div class="w-7 h-7 rounded bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 group-hover:border-emerald-400 group-hover:bg-emerald-500/20 transition-all duration-300">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6L12 2L5 6L12 10L19 6ZM19 12L12 16L5 12M19 18L12 22L5 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
            </div>
            <span class="font-bold text-lg tracking-tight text-white font-sans group-hover:text-emerald-400 transition-colors duration-300">ReconAgent</span>
        </a>
        <a href="/login" class="text-xs px-3.5 py-1.5 border border-slate-800 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white rounded-md transition-all duration-200 hover:shadow-lg active:scale-95 flex items-center space-x-1.5">
            <span>&larr; Back to Login</span>
        </a>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 z-10 my-auto">
        <div class="w-full max-w-xl bg-slate-900/90 border border-slate-800/80 rounded-2xl shadow-2xl shadow-emerald-950/20 backdrop-blur-xl overflow-hidden relative transition-all duration-300">
            
            <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-emerald-500/50 to-transparent"></div>

            <!-- In-App Notification Banner -->
            <div id="inline-alert" class="smooth-box box-hidden p-3.5 bg-rose-950/80 border-b border-rose-800/80 text-rose-300 text-xs flex items-start space-x-2.5">
                <svg class="w-4 h-4 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div id="inline-alert-text" class="flex-1"></div>
            </div>

            <!-- Stepper Header -->
            <div id="stepper-nav" class="p-5 border-b border-slate-800/80 bg-slate-950/40 transition-all duration-300">
                <div class="flex items-center justify-between max-w-md mx-auto">
                    
                    <!-- Step 1 Circle -->
                    <div class="flex flex-col items-center flex-1">
                        <div id="circle-1" class="w-8 h-8 rounded-full border border-emerald-500 bg-emerald-950/40 text-emerald-400 text-xs font-bold flex items-center justify-center transition-all duration-500">
                            1
                        </div>
                        <span class="text-[10px] mt-1.5 font-medium text-emerald-400 uppercase tracking-wider text-center hidden sm:block transition-colors duration-300">Type</span>
                    </div>
                    
                    <div id="line-1" class="h-[1px] flex-1 bg-slate-800 mx-2 transition-all duration-500"></div>

                    <!-- Step 2 Circle -->
                    <div class="flex flex-col items-center flex-1">
                        <div id="circle-2" class="w-8 h-8 rounded-full border border-slate-800 bg-slate-950 text-slate-500 text-xs font-bold flex items-center justify-center transition-all duration-500">
                            2
                        </div>
                        <span class="text-[10px] mt-1.5 font-medium text-slate-500 uppercase tracking-wider text-center hidden sm:block transition-colors duration-300">Verify</span>
                    </div>

                    <div id="line-2" class="h-[1px] flex-1 bg-slate-800 mx-2 transition-all duration-500"></div>

                    <!-- Step 3 Circle -->
                    <div class="flex flex-col items-center flex-1">
                        <div id="circle-3" class="w-8 h-8 rounded-full border border-slate-800 bg-slate-950 text-slate-500 text-xs font-bold flex items-center justify-center transition-all duration-500">
                            3
                        </div>
                        <span class="text-[10px] mt-1.5 font-medium text-slate-500 uppercase tracking-wider text-center hidden sm:block transition-colors duration-300">Workspace</span>
                    </div>

                    <div id="line-3" class="h-[1px] flex-1 bg-slate-800 mx-2 transition-all duration-500"></div>

                    <!-- Step 4 Circle -->
                    <div class="flex flex-col items-center flex-1">
                        <div id="circle-4" class="w-8 h-8 rounded-full border border-slate-800 bg-slate-950 text-slate-500 text-xs font-bold flex items-center justify-center transition-all duration-500">
                            4
                        </div>
                        <span class="text-[10px] mt-1.5 font-medium text-slate-500 uppercase tracking-wider text-center hidden sm:block transition-colors duration-300">Review</span>
                    </div>

                </div>
            </div>

            <!-- Form Body -->
            <div class="p-6 sm:p-7 relative min-h-[220px]">

                <!-- STEP 1: Account Type Selection -->
                <div id="step-1" class="step-panel step-active space-y-5">
                    <div class="text-center">
                        <h1 class="text-2xl font-bold tracking-tight text-white font-sans">Get Started</h1>
                        <p class="text-xs text-slate-400 mt-1">Select your account structure to initiate workspace creation</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 pt-1">
                        <label class="radio-card border border-slate-800 bg-slate-950/60 rounded-xl p-4 cursor-pointer block">
                            <div class="flex items-start space-x-3">
                                <input type="radio" name="accountType" value="organization" class="mt-1 text-emerald-500 focus:ring-emerald-500/20 bg-slate-900 border-slate-700 transition-colors" onchange="handleAccountTypeChange('organization')" checked>
                                <div>
                                    <span class="font-bold text-sm text-slate-200 block font-sans">Company / Enterprise</span>
                                    <span class="text-xs text-slate-400 mt-1 block leading-relaxed">Establish or join an organizational workspace built for multi-entity ledger matching.</span>
                                </div>
                            </div>
                        </label>

                        <label class="radio-card border border-slate-800 bg-slate-950/60 rounded-xl p-4 cursor-pointer block">
                            <div class="flex items-start space-x-3">
                                <input type="radio" name="accountType" value="individual" class="mt-1 text-emerald-500 focus:ring-emerald-500/20 bg-slate-900 border-slate-700 transition-colors" onchange="handleAccountTypeChange('individual')">
                                <div>
                                    <span class="font-bold text-sm text-slate-200 block font-sans">Individual / Sole Operator</span>
                                    <span class="text-xs text-slate-400 mt-1 block leading-relaxed">Single-operator setup for independent financial record analysis and CSV reconciliation.</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- STEP 2: Personal Details & Email Verification & Password Validation -->
                <div id="step-2" class="step-panel step-enter space-y-4">
                    <div class="text-center">
                        <h2 class="text-xl font-bold text-white font-sans">User Authentication</h2>
                        <p class="text-xs text-slate-400 mt-1">Enter your credentials and verify email ownership</p>
                    </div>

                    <div class="space-y-3.5 pt-1">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Full Name</label>
                            <input type="text" id="fullName" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 transition-all duration-200" placeholder="Jane Doe" oninput="registrationState.fullName = this.value; hideAlert();">
                        </div>

                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Work Email Address</label>
                            <div class="flex gap-2">
                                <input type="email" id="email" class="flex-1 px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 transition-all duration-200" placeholder="jane@company.com" oninput="registrationState.email = this.value; hideAlert();">
                                <button type="button" onclick="sendVerificationCode()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 active:scale-95 border border-slate-700 text-slate-200 font-medium text-xs rounded-lg transition-all duration-200 whitespace-nowrap">Send Code</button>
                            </div>
                        </div>

                        <div id="code-drawer" class="smooth-box box-hidden p-3 bg-slate-950/80 border border-slate-800 rounded-lg space-y-2">
                            <label class="block text-[10px] uppercase font-semibold text-slate-400 tracking-wider">Verification Code</label>
                            <div class="flex gap-2">
                                <input type="text" id="verificationCode" maxlength="4" class="w-28 px-3 py-1.5 bg-black border border-slate-800 rounded text-center tracking-widest text-emerald-400 font-mono text-sm focus:outline-none focus:border-emerald-500 transition-colors" placeholder="0000">
                                <button type="button" onclick="verifyCode()" class="px-3.5 py-1.5 bg-emerald-600/20 border border-emerald-500/40 text-emerald-400 hover:bg-emerald-600/30 active:scale-95 font-medium text-xs rounded transition-all duration-200">Verify</button>
                            </div>
                            <p class="text-[10px] text-slate-500">Enter the code sent to your <span class="text-emerald-400">email</span> to pass verification.</p>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Password</label>
                            <input type="password" id="password" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 transition-all duration-200" placeholder="••••••••••••" oninput="validatePassword(this.value)">
                            
                            <!-- Real-time Password Rules Checklist -->
                            <div class="mt-2.5 p-2.5 bg-slate-950/70 border border-slate-800/80 rounded-lg grid grid-cols-2 gap-1.5 text-[11px]">
                                <div id="rule-length" class="flex items-center space-x-1.5 text-slate-500 transition-colors duration-200">
                                    <span class="rule-icon font-bold">✕</span>
                                    <span>At least 8 characters</span>
                                </div>
                                <div id="rule-letter" class="flex items-center space-x-1.5 text-slate-500 transition-colors duration-200">
                                    <span class="rule-icon font-bold">✕</span>
                                    <span>Contains letters</span>
                                </div>
                                <div id="rule-number" class="flex items-center space-x-1.5 text-slate-500 transition-colors duration-200">
                                    <span class="rule-icon font-bold">✕</span>
                                    <span>Contains numbers</span>
                                </div>
                                <div id="rule-special" class="flex items-center space-x-1.5 text-slate-500 transition-colors duration-200">
                                    <span class="rule-icon font-bold">✕</span>
                                    <span>At least 1 special char</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Workspace Configuration -->
                <div id="step-3" class="step-panel step-enter space-y-4">
                    <div class="text-center">
                        <h2 class="text-xl font-bold text-white font-sans">Workspace Configuration</h2>
                        <p class="text-xs text-slate-400 mt-1">Configure workspace parameters and tenant association</p>
                    </div>

                    <!-- Individual Flow -->
                    <div id="individual-workspace-flow" class="smooth-box box-hidden space-y-3 pt-1">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Workspace Label / Name</label>
                            <input type="text" id="indWorkspaceName" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 transition-all duration-200" placeholder="Personal Workspace" oninput="registrationState.companyName = this.value; hideAlert();">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Primary Data Source</label>
                            <select id="indDataSource" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all duration-200" onchange="registrationState.primaryDataSource = this.value; hideAlert();">
                                <option value="" class="bg-slate-900">Select source structure...</option>
                                <option value="CSV File Uploads" class="bg-slate-900">CSV File Uploads</option>
                                <option value="Manual Spreadsheet Exports" class="bg-slate-900">Manual Spreadsheet Exports</option>
                            </select>
                        </div>
                    </div>

                    <!-- Organization Flow -->
                    <div id="org-workspace-flow" class="smooth-box space-y-3 pt-1">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Company / Organization Name</label>
                            <div class="flex gap-2">
                                <input type="text" id="companyName" class="flex-1 px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 transition-all duration-200" placeholder="Acme Financials Inc." oninput="registrationState.companyName = this.value; registrationState.isCompanyVerified = false; hideAlert();">
                                <button type="button" onclick="checkCompanyDatabase()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 active:scale-95 border border-slate-700 text-slate-200 font-medium text-xs rounded-lg transition-all duration-200 whitespace-nowrap">Verify Name</button>
                            </div>
                        </div>

                        <div id="org-status-box" class="smooth-box box-hidden p-3 rounded-lg border text-xs leading-relaxed"></div>

                        <div id="role-selection-box" class="smooth-box box-hidden">
                            <label class="block text-xs text-slate-400 mb-1">Select Initial Administrative Role</label>
                            <select id="selectedRole" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all duration-200" onchange="registrationState.selectedRole = this.value; hideAlert();">
                                <option value="" class="bg-slate-900">Select role assignment...</option>
                                <option value="Organization Administrator" class="bg-slate-900">Organization Administrator</option>
                                <option value="Finance Lead / Controller" class="bg-slate-900">Finance Lead / Controller</option>
                            </select>
                        </div>

                        <!-- Workspace Code Container -->
                        <div id="workspace-code-box" class="box-hidden space-y-2 mt-4">
                            <label for="workspaceCodeInput" class="block text-xs font-medium text-slate-300">Workspace Join Code</label>
                            <div class="flex space-x-2">
                                <input 
                                    type="text" 
                                    id="workspaceCodeInput" 
                                    class="flex-1 bg-slate-900 border border-slate-800 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-emerald-500" 
                                    placeholder="Enter join code provided by your admin"
                                    oninput="registrationState.workspaceCode = this.value; registrationState.isWorkspaceCodeVerified = false;"
                                />
                                <button 
                                    type="button" 
                                    onclick="verifyCompanyJoinCode()" 
                                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-semibold rounded-lg text-xs transition-colors shrink-0"
                                >
                                    Verify Code
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Review, Disclaimer & Terms -->
                <div id="step-4" class="step-panel step-enter space-y-4">
                    <div class="text-center">
                        <h2 class="text-xl font-bold text-white font-sans">Review Registration</h2>
                        <p class="text-xs text-slate-400 mt-1">Confirm configuration prior to tenant allocation</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        <div class="p-2.5 bg-slate-950 border border-slate-800/80 rounded-lg flex justify-between items-center text-xs transition-all hover:border-slate-700">
                            <div>
                                <span class="text-[10px] uppercase font-semibold text-slate-500 block">Account Type</span>
                                <span id="summary-type" class="font-medium text-slate-200 capitalize">-</span>
                            </div>
                            <button type="button" onclick="goToStep(1)" class="px-2.5 py-1 bg-slate-900 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800 active:scale-95 text-slate-400 hover:text-emerald-400 font-sans text-xs font-medium rounded transition-all duration-200">
                                Edit
                            </button>
                        </div>

                        <div class="p-2.5 bg-slate-950 border border-slate-800/80 rounded-lg flex justify-between items-center text-xs transition-all hover:border-slate-700">
                            <div>
                                <span class="text-[10px] uppercase font-semibold text-slate-500 block">Identity</span>
                                <span id="summary-user" class="font-medium text-slate-200">-</span>
                            </div>
                            <button type="button" onclick="goToStep(2)" class="px-2.5 py-1 bg-slate-900 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800 active:scale-95 text-slate-400 hover:text-emerald-400 font-sans text-xs font-medium rounded transition-all duration-200">
                                Edit
                            </button>
                        </div>

                        <div class="p-2.5 bg-slate-950 border border-slate-800/80 rounded-lg flex justify-between items-center text-xs transition-all hover:border-slate-700">
                            <div>
                                <span class="text-[10px] uppercase font-semibold text-slate-500 block">Workspace Target</span>
                                <span id="summary-workspace" class="font-medium text-slate-200">-</span>
                            </div>
                            <button type="button" onclick="goToStep(3)" class="px-2.5 py-1 bg-slate-900 border border-slate-800 hover:border-emerald-500/50 hover:bg-slate-800 active:scale-95 text-slate-400 hover:text-emerald-400 font-sans text-xs font-medium rounded transition-all duration-200">
                                Edit
                            </button>
                        </div>
                    </div>

                    <div class="p-3 bg-amber-950/30 border border-amber-500/30 text-amber-300 rounded-lg text-xs leading-relaxed">
                        <strong class="font-sans font-semibold text-amber-200 block mb-0.5">Tenant Isolation Notice</strong>
                        ReconAgent enforces strict organizational boundaries. All uploaded datasets are isolated to your workspace.
                    </div>

                    <div class="flex items-center space-x-2.5 pt-1">
                        <input type="checkbox" id="termsAgreed" onchange="toggleRegisterButton(this.checked)" class="w-4 h-4 text-emerald-500 bg-slate-950 border-slate-800 rounded focus:ring-0 focus:ring-offset-0 transition-all cursor-pointer">
                        <label for="termsAgreed" class="text-xs text-slate-400 cursor-pointer select-none">
                            I agree to the <a href="/privacy" target="_blank" class="text-emerald-400 hover:underline">Privacy Policy & Terms</a>.
                        </label>
                    </div>
                </div>

                <!-- STEP 5: Animated Onboarding Simulation -->
                <div id="step-5" class="step-panel step-enter space-y-4 py-2">
                    <div class="text-center space-y-1">
                        <h2 class="text-xl font-bold text-white font-sans">Provisioning Workspace</h2>
                        <p class="text-xs text-slate-400">Configuring ReconEngine isolated environment</p>
                    </div>

                    <div class="space-y-2.5 pt-1 max-w-md mx-auto" id="pipeline-steps-container">
                        <div id="phase-0" class="p-2.5 bg-slate-950 border border-slate-800 rounded-lg flex items-center justify-between text-xs transition-all duration-500">
                            <span class="text-slate-400">Processing account registration...</span>
                            <div class="status-icon w-5 h-5 rounded-full border border-slate-700 flex items-center justify-center text-[10px] text-slate-500 font-bold transition-all duration-500 shrink-0">1</div>
                        </div>
                        <div id="phase-1" class="p-2.5 bg-slate-950 border border-slate-800 rounded-lg flex items-center justify-between text-xs transition-all duration-500 opacity-40">
                            <span class="text-slate-400">Checking authenticity & domain permissions...</span>
                            <div class="status-icon w-5 h-5 rounded-full border border-slate-700 flex items-center justify-center text-[10px] text-slate-500 font-bold transition-all duration-500 shrink-0">2</div>
                        </div>
                        <div id="phase-2" class="p-2.5 bg-slate-950 border border-slate-800 rounded-lg flex items-center justify-between text-xs transition-all duration-500 opacity-40">
                            <span class="text-slate-400">Syncing tenant isolation & workspace keys...</span>
                            <div class="status-icon w-5 h-5 rounded-full border border-slate-700 flex items-center justify-center text-[10px] text-slate-500 font-bold transition-all duration-500 shrink-0">3</div>
                        </div>
                        <div id="phase-3" class="p-2.5 bg-slate-950 border border-slate-800 rounded-lg flex items-center justify-between text-xs transition-all duration-500 opacity-40">
                            <span class="text-slate-400">Calling on ReconAgent baseline engine...</span>
                            <div class="status-icon w-5 h-5 rounded-full border border-slate-700 flex items-center justify-center text-[10px] text-slate-500 font-bold transition-all duration-500 shrink-0">4</div>
                        </div>
                    </div>
                </div>

                <!-- Final Completion Screen - Perfectly Centered Flexbox Alignment -->
                <div id="step-completion-screen" class="step-panel step-enter py-10 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="w-12 h-12 bg-emerald-950/60 border border-emerald-500/40 text-emerald-400 rounded-full flex items-center justify-center transition-transform duration-500 scale-110 shadow-lg shadow-emerald-500/10">
                        <svg class="w-6 h-6 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="space-y-1">
                        <h2 class="text-xl font-bold text-white font-sans tracking-tight">Workspace Ready</h2>
                        <p class="text-slate-400 text-xs">Redirecting to sign in interface...</p>
                    </div>
                </div>

                <!-- Navigation Controls -->
                <div id="nav-buttons" class="flex justify-between items-center pt-4 border-t border-slate-800/80 mt-4 transition-all duration-300">
                    <button type="button" id="btn-back" onclick="previousStep()" class="px-4 py-2 bg-slate-950 hover:bg-slate-800 active:scale-95 border border-slate-800 text-slate-300 font-medium text-xs rounded-lg transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed disabled:active:scale-100">Back</button>
                    <button type="button" id="btn-next" onclick="nextStep()" class="px-5 py-2 bg-white text-slate-950 font-sans font-bold text-xs rounded-lg hover:bg-slate-200 active:scale-95 transition-all duration-200 disabled:bg-slate-800 disabled:text-slate-600 disabled:cursor-not-allowed disabled:active:scale-100 shadow-md">Next &rarr;</button>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-800/60 bg-slate-950/80 backdrop-blur-md px-6 py-4 z-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 space-y-3 md:space-y-0">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>ReconEngine Infrastructure Operational</span>
            </div>
            <div>
                &copy; 2026 ReconAgent Inc. All rights reserved.
            </div>
            <div class="flex items-center space-x-4">
                <a href="#" class="hover:text-slate-400 transition-colors">Help Center</a>
                <a href="#" class="hover:text-slate-400 transition-colors">System Status</a>
                <a href="/privacy" class="hover:text-slate-400 transition-colors">Privacy & Terms</a>
            </div>
        </div>
    </footer>

<script>
    const registrationState = {
        accountType: "organization",
        fullName: "",
        email: "",
        isEmailVerified: false,
        verificationCode: "",
        password: "",
        isPasswordValid: false,
        companyName: "",
        isCompanyVerified: false,
        companyStatus: null,
        workspaceCode: "",
        isWorkspaceCodeVerified: false,
        selectedRole: "",
        primaryDataSource: ""
    };

    let currentStep = 1;
    let isAnimating = false;

    function showAlert(msg) {
        const alertBox = document.getElementById('inline-alert');
        const alertText = document.getElementById('inline-alert-text');
        if (alertText) alertText.innerText = msg;
        if (alertBox) alertBox.classList.remove('box-hidden');
    }

    function hideAlert() {
        const alertBox = document.getElementById('inline-alert');
        if (alertBox) alertBox.classList.add('box-hidden');
    }

    function handleAccountTypeChange(type) {
        hideAlert();
        registrationState.accountType = type;
        const orgBox = document.getElementById('org-workspace-flow');
        const indBox = document.getElementById('individual-workspace-flow');

        if (type === 'individual') {
            if (orgBox) orgBox.classList.add('box-hidden');
            if (indBox) indBox.classList.remove('box-hidden');
        } else {
            if (orgBox) orgBox.classList.remove('box-hidden');
            if (indBox) indBox.classList.add('box-hidden');
        }
    }

    function handleCompanyNameInput(val) {
        hideAlert();
        registrationState.companyName = val;
        registrationState.isCompanyVerified = false;
        registrationState.companyStatus = null;
        registrationState.workspaceCode = "";
        registrationState.isWorkspaceCodeVerified = false;

        // Ensure sub-boxes are hidden whenever company name input changes
        const statusBox = document.getElementById('org-status-box');
        const roleBox = document.getElementById('role-selection-box');
        const codeBox = document.getElementById('workspace-code-box');

        if (statusBox) statusBox.classList.add('box-hidden');
        if (roleBox) roleBox.classList.add('box-hidden');
        if (codeBox) codeBox.classList.add('box-hidden');
    }

    function validatePassword(val) {
        hideAlert();
        registrationState.password = val;

        const rules = {
            length: val.length >= 8,
            letter: /[a-zA-Z]/.test(val),
            number: /[0-9]/.test(val),
            special: /[^a-zA-Z0-9]/.test(val)
        };

        updateRuleUI('rule-length', rules.length);
        updateRuleUI('rule-letter', rules.letter);
        updateRuleUI('rule-number', rules.number);
        updateRuleUI('rule-special', rules.special);

        registrationState.isPasswordValid = rules.length && rules.letter && rules.number && rules.special;
    }

    function updateRuleUI(id, isPassed) {
        const el = document.getElementById(id);
        if (!el) return;
        const icon = el.querySelector('.rule-icon');
        if (isPassed) {
            el.className = "flex items-center space-x-1.5 text-emerald-400 font-medium transition-colors duration-200";
            if (icon) icon.innerText = "✓";
        } else {
            el.className = "flex items-center space-x-1.5 text-slate-500 transition-colors duration-200";
            if (icon) icon.innerText = "✕";
        }
    }

    async function sendVerificationCode() {
        hideAlert();
        if(!registrationState.email.trim()) {
            showAlert("Please provide a valid work email address first.");
            return;
        }

        try {
            const response = await fetch('/api/v1/send-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ email: registrationState.email })
            });

            const data = await response.json();

            if (!response.ok) {
                showAlert(data.message || "Failed to send verification code.");
                return;
            }

            const codeDrawer = document.getElementById('code-drawer');
            if (codeDrawer) codeDrawer.classList.remove('box-hidden');
        } catch (error) {
            showAlert("An error occurred while sending the code. Please try again.");
        }
    }

    async function verifyCode() {
        hideAlert();
        const codeInputElement = document.getElementById('verificationCode');
        const codeInput = codeInputElement ? codeInputElement.value.trim() : '';
        
        if (!codeInput) {
            showAlert("Please enter the verification code.");
            return;
        }

        try {
            const response = await fetch('/api/v1/verify-registration-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    email: registrationState.email,
                    code: codeInput
                })
            });

            const data = await response.json();

            if (!response.ok || !data.valid) {
                registrationState.isEmailVerified = false;
                showAlert(data.message || "Invalid verification code.");
                return;
            }

            registrationState.verificationCode = codeInput;
            registrationState.isEmailVerified = true;
            const codeDrawer = document.getElementById('code-drawer');
            if (codeDrawer) codeDrawer.classList.add('box-hidden');
            showAlert("Email verified successfully!");
        } catch (error) {
            showAlert("An error occurred while validating the verification code.");
        }
    }

    async function checkCompanyDatabase() {
        hideAlert();
        const name = registrationState.companyName.trim();
        if(!name) {
            showAlert("Please enter an organization name before verifying.");
            return;
        }

        const statusBox = document.getElementById('org-status-box');
        const roleBox = document.getElementById('role-selection-box');
        const codeBox = document.getElementById('workspace-code-box');

        try {
            const response = await fetch('/api/v1/check-company', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ companyName: name })
            });

            const data = await response.json();

            if (!response.ok) {
                showAlert(data.message || "Failed to check organization.");
                return;
            }

            registrationState.isCompanyVerified = true;
            registrationState.isWorkspaceCodeVerified = false;
            registrationState.workspaceCode = "";

            if (data.exists) {
                registrationState.companyStatus = 'existing';
                if (statusBox) {
                    statusBox.className = "smooth-box p-3 bg-emerald-950/40 border border-emerald-800/60 text-emerald-300 rounded-lg text-xs leading-relaxed";
                    statusBox.innerHTML = `<strong class="font-sans block text-emerald-200">Organization Verified</strong> Workspace exists. Provide your Join Code below to proceed.`;
                    statusBox.classList.remove('box-hidden');
                }
                
                if (roleBox) roleBox.classList.add('box-hidden');
                if (codeBox) codeBox.classList.remove('box-hidden');
            } else {
                registrationState.companyStatus = 'new';
                if (statusBox) {
                    statusBox.className = "smooth-box p-3 bg-emerald-950/40 border border-emerald-800/60 text-emerald-300 rounded-lg text-xs leading-relaxed";
                    statusBox.innerHTML = `<strong class="font-sans block text-emerald-200">New Organization Verified</strong> You will be designated as the primary Organization Administrator.`;
                    statusBox.classList.remove('box-hidden');
                }
                
                if (roleBox) roleBox.classList.remove('box-hidden');
                if (codeBox) codeBox.classList.add('box-hidden');
            }
        } catch (error) {
            showAlert("An error occurred while verifying the company name.");
        }
    }

    async function verifyCompanyJoinCode() {
        hideAlert();
        const codeInputElement = document.getElementById('workspaceCodeInput');
        const joinCodeInput = codeInputElement ? codeInputElement.value.trim() : registrationState.workspaceCode.trim();

        if (!joinCodeInput) {
            showAlert("Please enter a workspace join code.");
            return;
        }

        try {
            const response = await fetch('/api/v1/verify-company-join-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    companyName: registrationState.companyName,
                    joinCode: joinCodeInput
                })
            });

            const data = await response.json();

            if (!response.ok || !data.valid) {
                registrationState.isWorkspaceCodeVerified = false;
                showAlert(data.message || "Invalid join code. Please contact your organization administrator.");
                return;
            }

            registrationState.workspaceCode = joinCodeInput;
            registrationState.isWorkspaceCodeVerified = true;
            showAlert("Company link has been established successfully!");
        } catch (error) {
            showAlert("An error occurred while verifying the join code.");
        }
    }

    function transitionStep(fromStep, toStep) {
        if (isAnimating || fromStep === toStep) return;
        isAnimating = true;

        const currentEl = typeof fromStep === 'string' ? document.getElementById(fromStep) : document.getElementById(`step-${fromStep}`);
        const nextEl = typeof toStep === 'string' ? document.getElementById(toStep) : document.getElementById(`step-${toStep}`);

        if (!currentEl || !nextEl) {
            isAnimating = false;
            return;
        }

        currentEl.className = `step-panel step-exit`;
        nextEl.className = `step-panel step-enter`;

        setTimeout(() => {
            if (typeof toStep === 'number') {
                currentStep = toStep;
            }
            nextEl.className = `step-panel step-active`;
            updateUI();
            isAnimating = false;
        }, 300);
    }

    function goToStep(targetStep) {
        hideAlert();
        transitionStep(currentStep, targetStep);
    }

    function nextStep() {
        hideAlert();

        if (currentStep === 2) {
            if (!registrationState.fullName.trim()) {
                showAlert("Full Name is required.");
                return;
            }
            if (!registrationState.email.trim()) {
                showAlert("Work Email Address is required.");
                return;
            }
            if (!registrationState.isEmailVerified) {
                showAlert("Please verify your email address before proceeding.");
                return;
            }
            if (!registrationState.isPasswordValid) {
                showAlert("Password must be at least 8 characters and contain letters, numbers, and at least 1 special character.");
                return;
            }
        }

        if (currentStep === 3) {
            if (registrationState.accountType === 'organization') {
                if (!registrationState.companyName.trim()) {
                    showAlert("Please enter your company/organization name.");
                    return;
                }
                if (!registrationState.isCompanyVerified) {
                    showAlert("You must click 'Verify Name' to check if your organization already exists.");
                    return;
                }
                
                if (registrationState.companyStatus === 'existing') {
                    const codeInputElement = document.getElementById('workspaceCodeInput');
                    const inputCode = codeInputElement ? codeInputElement.value.trim() : registrationState.workspaceCode.trim();

                    if (!inputCode) {
                        showAlert("Workspace Join Code is required for joining an existing company.");
                        return;
                    }

                    if (!registrationState.isWorkspaceCodeVerified) {
                        showAlert("Please verify your join code before proceeding.");
                        return;
                    }
                }

                if (registrationState.companyStatus === 'new' && !registrationState.selectedRole) {
                    showAlert("Please select an administrative role.");
                    return;
                }
            } else {
                if (!registrationState.primaryDataSource) {
                    showAlert("Please select a primary data source.");
                    return;
                }
            }
        }

        if (currentStep === 4) {
            startOnboardingPipeline();
            return;
        }

        if (currentStep < 4) {
            const currentCircle = document.getElementById(`circle-${currentStep}`);
            if (currentCircle) currentCircle.classList.add('circle-running');

            setTimeout(() => {
                if (currentCircle) currentCircle.classList.remove('circle-running');
                transitionStep(currentStep, currentStep + 1);
            }, 300);
        }
    }

    function previousStep() {
        hideAlert();
        if (currentStep > 1) {
            transitionStep(currentStep, currentStep - 1);
        }
    }

    function updateUI() {
        for(let i = 1; i <= 4; i++) {
            const circle = document.getElementById(`circle-${i}`);
            if (circle) {
                if (i < currentStep) {
                    circle.className = "w-8 h-8 rounded-full bg-emerald-500 text-slate-950 font-bold flex items-center justify-center text-xs transition-all duration-500 scale-100";
                    circle.innerHTML = "✓";
                } else if (i === currentStep) {
                    circle.className = "w-8 h-8 rounded-full border border-emerald-500 bg-emerald-950/40 text-emerald-400 font-bold flex items-center justify-center text-xs shadow-lg shadow-emerald-500/20 transition-all duration-500 scale-105";
                    circle.innerHTML = i;
                } else {
                    circle.className = "w-8 h-8 rounded-full border border-slate-800 bg-slate-950 text-slate-500 font-bold flex items-center justify-center text-xs transition-all duration-500 scale-100";
                    circle.innerHTML = i;
                }
            }
        }

        for(let i = 1; i <= 3; i++) {
            const line = document.getElementById(`line-${i}`);
            if (line) {
                if (i < currentStep) {
                    line.className = "h-[1px] flex-1 bg-emerald-500 mx-2 transition-all duration-500";
                } else {
                    line.className = "h-[1px] flex-1 bg-slate-800 mx-2 transition-all duration-500";
                }
            }
        }

        const btnBack = document.getElementById('btn-back');
        const btnNext = document.getElementById('btn-next');
        if (btnBack) btnBack.disabled = (currentStep === 1);
        
        if (btnNext) {
            if (currentStep === 4) {
                populateSummary();
                btnNext.innerHTML = "Register &rarr;";
                const termsCheck = document.getElementById('termsAgreed');
                btnNext.disabled = termsCheck ? !termsCheck.checked : true;
            } else {
                btnNext.innerHTML = "Next &rarr;";
                btnNext.disabled = false;
            }
        }
    }

    function toggleRegisterButton(checked) {
        if(currentStep === 4) {
            const btnNext = document.getElementById('btn-next');
            if (btnNext) btnNext.disabled = !checked;
        }
    }

    function populateSummary() {
        const typeEl = document.getElementById('summary-type');
        const userEl = document.getElementById('summary-user');
        const wsEl = document.getElementById('summary-workspace');

        if (typeEl) typeEl.innerText = registrationState.accountType;
        if (userEl) userEl.innerText = `${registrationState.fullName || 'N/A'} (${registrationState.email || 'N/A'})`;
        
        if (wsEl) {
            if(registrationState.accountType === 'organization') {
                wsEl.innerText = `${registrationState.companyName || 'N/A'} [${registrationState.companyStatus === 'existing' ? 'Joining via Code' : 'New Org Admin'}]`;
            } else {
                wsEl.innerText = `${registrationState.companyName || 'Personal Workspace'} (${registrationState.primaryDataSource || 'CSV Uploads'})`;
            }
        }
    }

    async function startOnboardingPipeline() {
        transitionStep(4, 5);

        setTimeout(() => {
            const navBtns = document.getElementById('nav-buttons');
            const stepNav = document.getElementById('stepper-nav');
            if (navBtns) navBtns.classList.add('opacity-0', 'pointer-events-none');
            if (stepNav) stepNav.classList.add('opacity-0', 'pointer-events-none');
        }, 300);

        try {
            const response = await fetch('/api/v1/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(registrationState)
            });

            const data = await response.json();

            if (!response.ok) {
                transitionStep(5, 4);
                const navBtns = document.getElementById('nav-buttons');
                const stepNav = document.getElementById('stepper-nav');
                if (navBtns) navBtns.classList.remove('opacity-0', 'pointer-events-none');
                if (stepNav) stepNav.classList.remove('opacity-0', 'pointer-events-none');
                showAlert(data.message || "Registration failed. Please review your details.");
                return;
            }

            let phaseIndex = 0;

            function runPhase() {
                if (phaseIndex < 4) {
                    const phaseEl = document.getElementById(`phase-${phaseIndex}`);
                    if (phaseEl) {
                        phaseEl.classList.remove('opacity-40');
                        phaseEl.classList.add('border-emerald-500/50', 'bg-emerald-950/20');
                        
                        const iconBox = phaseEl.querySelector('.status-icon');
                        if (iconBox) {
                            iconBox.className = "status-icon w-5 h-5 rounded-full bg-emerald-500 text-slate-950 font-bold flex items-center justify-center text-[10px] transition-all duration-500 scale-110 shrink-0";
                            iconBox.innerHTML = "✓";
                        }
                    }

                    phaseIndex++;
                    setTimeout(runPhase, 1200);
                } else {
                    setTimeout(() => {
                        transitionStep('step-5', 'step-completion-screen');
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 2000);
                    }, 400);
                }
            }

            setTimeout(runPhase, 600);

        } catch (error) {
            transitionStep(5, 4);
            const navBtns = document.getElementById('nav-buttons');
            const stepNav = document.getElementById('stepper-nav');
            if (navBtns) navBtns.classList.remove('opacity-0', 'pointer-events-none');
            if (stepNav) stepNav.classList.remove('opacity-0', 'pointer-events-none');
            showAlert("An unexpected error occurred during registration. Please try again.");
        }
    }
</script>
</body>
</html>