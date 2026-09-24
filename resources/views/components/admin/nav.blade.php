<nav class="p-4 space-y-1 text-sm font-medium pt-20 md:pt-4">
    <a href="/dashboard" 
       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('dashboard') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-sm' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
        Dashboard
    </a>
    
    <a href="/execute-recon-runs" 
       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('execute-recon-runs*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-sm' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
        Reconciliation Runs
    </a>
    
    <a href="/unmatched-discrepancies" 
       class="flex items-center justify-between px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('unmatched-discrepancies*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-sm' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
        <span>Unmatched Discrepancies</span>
        <span class="px-2 py-0.5 text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 rounded-full font-mono">{{ $metrics['pending_exceptions'] ?? 0 }}</span>
    </a>
    
    <a href="/data-sources" 
       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('data-sources*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-sm' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
        Data Sources & APIs
    </a>
    
    <a href="/team-members" 
       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->is('team-members*') ? 'bg-white/10 text-white font-semibold border border-white/10 shadow-sm' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
        Team Members
    </a>
</nav>