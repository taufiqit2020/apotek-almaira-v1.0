{{-- TOPBAR --}}
<header class="topbar no-print">
    {{-- Kiri: toggle + judul --}}
    <div class="topbar-left">
        <button type="button" @click="toggle()" class="topbar-icon-btn lg:hidden" aria-label="Buka menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="topbar-title-block min-w-0">
            <p class="topbar-eyebrow hidden sm:block">Apotek Almaira</p>
            <h1 class="topbar-title truncate">@yield('page-title', 'Dashboard')</h1>
        </div>
    </div>

    {{-- Kanan: aksi --}}
    <div class="topbar-right">
        {{-- Jam --}}
        <div class="topbar-clock hidden md:inline-flex" title="Waktu saat ini">
            <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-data="topbarClock()" x-text="time" class="tabular-nums"></span>
        </div>

        <span class="topbar-divider hidden md:block" aria-hidden="true"></span>

        {{-- CTA Kasir: di halaman POS = reset transaksi; selain itu buka kasir --}}
        @if(request()->routeIs('pos.*'))
        <button type="button"
                class="topbar-cta hidden sm:inline-flex"
                onclick="window.dispatchEvent(new CustomEvent('pos-new-transaction'))"
                title="Mulai transaksi baru (kosongkan keranjang)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Transaksi Baru</span>
        </button>
        @else
        <a href="{{ route('pos.index') }}" wire:navigate class="topbar-cta hidden sm:inline-flex">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Transaksi Baru</span>
        </a>
        @endif

        {{-- Notifikasi --}}
        <div class="topbar-notif">
            <livewire:shared.notification-badge />
        </div>

        <span class="topbar-divider hidden sm:block" aria-hidden="true"></span>

        {{-- User --}}
        <div class="relative" x-data="{ open: false }">
            <button type="button"
                    @click="open = !open"
                    class="topbar-user-btn"
                    :class="open ? 'is-open' : ''"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()">
                <div class="topbar-avatar">
                    @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                        <img src="{{ asset(auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <span>{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="topbar-user-meta hidden md:block">
                    <p class="topbar-user-name truncate" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
                    <p class="topbar-user-role truncate">{{ auth()->user()->role?->name ?? 'User' }}</p>
                </div>
                <svg class="topbar-chevron hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="open ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open"
                 @click.away="open = false"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1 scale-[0.98]"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="topbar-dropdown">
                <div class="topbar-dropdown-head">
                    <div class="topbar-avatar topbar-avatar-lg">
                        @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                            <img src="{{ asset(auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            <span>{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700/80">{{ auth()->user()->role?->name ?? 'User' }}</p>
                        <p class="text-sm font-bold text-slate-800 leading-snug truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" wire:navigate class="topbar-dropdown-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profil Saya
                </a>
                <div class="topbar-dropdown-foot">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="topbar-dropdown-item is-danger w-full">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
