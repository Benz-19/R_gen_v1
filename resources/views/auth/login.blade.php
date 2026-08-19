<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReconAgent — Sign In</title>
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
            background: rgba(10, 10, 10, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        @keyframes slideFromBottom {
            0% {
                opacity: 0;
                transform: translateY(120px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-box-slide-up {
            animation: slideFromBottom 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-black text-neutral-300 antialiased font-sans min-h-screen bg-grid flex flex-col justify-between overflow-x-hidden">

    <!-- Header Navigation -->
    <header class="border-b border-white/10 bg-black/60 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <a href="/" class="w-8 h-8 bg-white text-black font-bold flex items-center justify-center rounded-lg hover:scale-105 transition shrink-0">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </a>
                <span class="font-bold text-base sm:text-lg text-white tracking-tight">ReconAgent</span>
            </div>
            
            <div>
                <a href="/" class="text-xs font-medium text-neutral-400 hover:text-white transition px-2.5 sm:px-3 py-1.5 rounded-lg border border-white/10 bg-white/5 flex items-center gap-1">
                    <span>&larr;</span>
                    <span class="hidden sm:inline">Back to Home</span>
                    <span class="sm:hidden">Home</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Centered Form Container -->
    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:py-12">
        
        <!-- Smooth Slide-Up Box (0.9s duration) -->
        <div class="w-full max-w-md glass-panel p-5 sm:p-8 rounded-2xl shadow-2xl relative overflow-hidden animate-box-slide-up">
            
            <!-- Glow Accent Header Border -->
            <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-brand-cyan to-transparent opacity-80"></div>

            <!-- Header Text -->
            <div class="text-center mb-6 sm:mb-8">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-3 sm:mb-4 text-white">
                    <i data-lucide="lock" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-cyan"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Welcome Back</h1>
                <p class="text-[11px] sm:text-xs text-neutral-400 mt-1 font-mono">Sign in to access your ReconEngine workspace</p>
            </div>

            <!-- Login Form -->
            <form action="#" method="POST" class="space-y-4 sm:space-y-5">
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-xs font-mono text-neutral-400 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-neutral-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" id="email" name="email" required placeholder="name@company.com" 
                            class="w-full bg-black/60 border border-white/10 rounded-xl pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm text-white placeholder-neutral-600 font-mono focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan focus:outline-none transition">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-mono text-neutral-400">Password</label>
                        <a href="#" class="text-xs text-brand-cyan hover:underline font-mono">Forgot?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-neutral-500">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                        </div>
                        <input type="password" id="password" name="password" required placeholder="••••••••••••" 
                            class="w-full bg-black/60 border border-white/10 rounded-xl pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm text-white placeholder-neutral-600 font-mono focus:border-brand-cyan focus:ring-1 focus:ring-brand-cyan focus:outline-none transition">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-white text-black font-semibold hover:bg-neutral-200 py-2.5 sm:py-3 rounded-xl transition flex items-center justify-center gap-2 group text-xs sm:text-sm mt-2">
                    <span>Sign In to Dashboard</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition"></i>
                </button>
            </form>

            <!-- Bottom Redirect Link -->
            <div class="mt-5 sm:mt-6 pt-5 sm:pt-6 border-t border-white/10 text-center text-xs text-neutral-400">
                Don't have an account? 
                <a href="#" class="text-white hover:text-brand-cyan font-semibold transition ml-1 inline-block">Create one &rarr;</a>
            </div>

        </div>
    </main>

    <!-- Application Footer -->
    <footer class="border-t border-white/10 bg-black/80 backdrop-blur-xl py-4 sm:py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 text-xs font-mono text-neutral-500 text-center sm:text-left">
            <div class="flex items-center justify-center sm:justify-start gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse shrink-0"></span>
                <span class="truncate">ReconEngine Infrastructure Operational</span>
            </div>
            <p>&copy; 2026 ReconAgent Inc. All rights reserved.</p>
            <div class="flex items-center justify-center gap-3 sm:gap-4 flex-wrap">
                <a href="/help-center" class="hover:text-white transition">Help Center</a>
                <a href="/system-status" class="hover:text-white transition">System Status</a>
                <a href="/privacy" class="hover:text-white transition">Privacy & Terms</a>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>