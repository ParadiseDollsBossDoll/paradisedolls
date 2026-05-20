<div class="elysian-profile-menu" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
    <button type="button" class="elysian-topbar-avatar" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="menu" aria-label="{{ __('Open account menu') }}">
        <span>{{ $initials }}</span>
        @if ($profilePhotoUrl)
            <img src="{{ $profilePhotoUrl }}" alt="{{ __('Profile photo') }}" loading="lazy" decoding="async" onerror="this.remove()">
        @endif
    </button>
    <div x-cloak x-show="open" x-transition class="elysian-profile-dropdown" role="menu">
        <div class="elysian-profile-dropdown-head">
            <p>{{ $user->name }}</p>
            <span>{{ __('Member account') }}</span>
        </div>
        <a href="{{ route('profile.edit') }}" role="menuitem">{{ __('Profile settings') }}</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" role="menuitem">{{ __('Sign Out') }}</button>
        </form>
    </div>
</div>

