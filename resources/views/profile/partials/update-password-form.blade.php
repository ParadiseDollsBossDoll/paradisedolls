<div
    x-data="{
        open: {{ $errors->updatePassword->isNotEmpty() ? 'true' : 'false' }},
        saving: false,
        justUpdated: {{ session('status') === 'password-updated' ? 'true' : 'false' }},
        show: { current: false, password: false, confirm: false },
        newPassword: '',

        get strengthScore() {
            const p = this.newPassword;
            if (!p || p.length < 6) return 0;
            let s = 0;
            if (p.length >= 8)  s++;
            if (p.length >= 14) s++;
            if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++;
            if (/[0-9]/.test(p)) s++;
            if (/[^A-Za-z0-9]/.test(p)) s++;
            return Math.min(s, 4);
        },
        get strengthLabel() {
            if (!this.newPassword) return '';
            if (this.newPassword.length < 6) return '{{ __('Too short') }}';
            return ['', '{{ __('Weak') }}', '{{ __('Fair') }}', '{{ __('Good') }}', '{{ __('Strong') }}'][this.strengthScore] || '';
        },
        get strengthColor() {
            return ['', '#ef4444', '#f59e0b', '#84cc16', '#22c55e'][this.strengthScore] || '#ef4444';
        },

        toggle() {
            this.open = !this.open;
            if (!this.open) {
                this.newPassword = '';
                this.show = { current: false, password: false, confirm: false };
                this.justUpdated = false;
            }
        },
    }"
>
    {{-- ── Always-visible header ──────────────────────────────────── --}}
    <div class="flex items-center gap-3 px-5 py-4 sm:px-6">
        {{-- Icon --}}
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl transition-colors"
              :style="open
                  ? 'background:rgba(196,104,122,0.14);border:1px solid rgba(196,104,122,0.25);color:#C4687A;'
                  : 'background:rgba(196,104,122,0.08);border:1px solid rgba(196,104,122,0.15);color:#C4687A;'">
            <svg viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.6]">
                <rect x="3" y="7" width="10" height="7" rx="1.5"/>
                <path d="M5.5 7V5.2a2.5 2.5 0 015 0V7"/>
            </svg>
        </span>

        {{-- Title + subtitle + success badge --}}
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <p class="font-display text-[0.93rem] font-semibold text-boss-ivory">{{ __('Password & Security') }}</p>
                {{-- Success badge --}}
                <span
                    x-show="justUpdated"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-90"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="inline-flex items-center gap-1 rounded-full border border-emerald-400/20 bg-emerald-400/[0.08] px-2 py-0.5 text-[0.6rem] text-emerald-300"
                >
                    <svg viewBox="0 0 16 16" class="h-2.5 w-2.5 fill-none stroke-current stroke-[2.2]"><path d="M3 8.5l3.5 3.5L13 5"/></svg>
                    {{ __('Updated') }}
                </span>
            </div>
            <p class="text-[0.61rem] text-boss-ivory/33">{{ __('Keep your account secure') }}</p>
        </div>

        {{-- Expand/collapse toggle --}}
        <button
            type="button"
            @click="toggle()"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[0.71rem] font-medium transition-all"
            :class="open
                ? 'border-white/[0.08] bg-white/[0.04] text-boss-ivory/50 hover:text-boss-ivory'
                : 'border-[rgba(196,104,122,0.22)] bg-[rgba(196,104,122,0.08)] text-[#C4687A] hover:bg-[rgba(196,104,122,0.14)]'"
        >
            <span x-text="open ? '{{ __('Cancel') }}' : '{{ __('Change Password') }}'"></span>
            <svg viewBox="0 0 16 16" class="h-3.5 w-3.5 fill-none stroke-current stroke-[2] transition-transform duration-200"
                 :class="open ? 'rotate-180' : ''"
            >
                <path d="M4 6l4 4 4-4"/>
            </svg>
        </button>
    </div>

    {{-- ── Collapsible body (CSS grid trick for smooth height animation) --}}
    <div class="grid transition-all duration-300 ease-in-out"
         :style="open ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'">
        <div class="overflow-hidden">
            <div class="border-t border-white/[0.05] p-5 sm:p-6">
                <form
                    method="POST"
                    action="{{ route('password.update') }}"
                    class="space-y-4"
                    @submit="saving = true"
                >
                    @csrf
                    @method('PUT')

                    {{-- Current password --}}
                    <div>
                        <label for="update_password_current_password"
                               class="block text-[0.67rem] uppercase tracking-[0.13em] text-boss-ivory/45">
                            {{ __('Current Password') }}
                        </label>
                        <div class="relative mt-1.5">
                            <x-text-input
                                id="update_password_current_password"
                                name="current_password"
                                class="block w-full pr-10"
                                autocomplete="current-password"
                                x-bind:type="show.current ? 'text' : 'password'"
                            />
                            <button type="button" @click="show.current = !show.current"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-boss-ivory/22 transition-colors hover:text-boss-ivory/50"
                                tabindex="-1">
                                <svg x-show="!show.current" viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.5]"><ellipse cx="8" cy="8" rx="6" ry="4"/><circle cx="8" cy="8" r="1.5"/></svg>
                                <svg x-show="show.current" x-cloak viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.5]"><ellipse cx="8" cy="8" rx="6" ry="4"/><circle cx="8" cy="8" r="1.5"/><path d="M2 2l12 12"/></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
                    </div>

                    {{-- New password + strength --}}
                    <div>
                        <label for="update_password_password"
                               class="block text-[0.67rem] uppercase tracking-[0.13em] text-boss-ivory/45">
                            {{ __('New Password') }}
                        </label>
                        <div class="relative mt-1.5">
                            <x-text-input
                                id="update_password_password"
                                name="password"
                                class="block w-full pr-10"
                                autocomplete="new-password"
                                x-bind:type="show.password ? 'text' : 'password'"
                                x-model="newPassword"
                            />
                            <button type="button" @click="show.password = !show.password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-boss-ivory/22 transition-colors hover:text-boss-ivory/50"
                                tabindex="-1">
                                <svg x-show="!show.password" viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.5]"><ellipse cx="8" cy="8" rx="6" ry="4"/><circle cx="8" cy="8" r="1.5"/></svg>
                                <svg x-show="show.password" x-cloak viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.5]"><ellipse cx="8" cy="8" rx="6" ry="4"/><circle cx="8" cy="8" r="1.5"/><path d="M2 2l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Strength indicator --}}
                        <div x-show="newPassword.length > 0" x-cloak class="mt-2 space-y-1.5">
                            <div class="flex gap-1">
                                <template x-for="i in 4" :key="i">
                                    <div class="h-1 flex-1 rounded-full transition-all duration-300"
                                         :style="i <= strengthScore
                                             ? `background-color: ${strengthColor}`
                                             : 'background-color: rgba(255,255,255,0.08)'">
                                    </div>
                                </template>
                            </div>
                            <p class="text-[0.63rem] transition-colors duration-200"
                               :style="`color: ${strengthColor}; opacity: 0.85;`"
                               x-text="strengthLabel">
                            </p>
                        </div>

                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
                    </div>

                    {{-- Confirm password --}}
                    <div>
                        <label for="update_password_password_confirmation"
                               class="block text-[0.67rem] uppercase tracking-[0.13em] text-boss-ivory/45">
                            {{ __('Confirm New Password') }}
                        </label>
                        <div class="relative mt-1.5">
                            <x-text-input
                                id="update_password_password_confirmation"
                                name="password_confirmation"
                                class="block w-full pr-10"
                                autocomplete="new-password"
                                x-bind:type="show.confirm ? 'text' : 'password'"
                            />
                            <button type="button" @click="show.confirm = !show.confirm"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-boss-ivory/22 transition-colors hover:text-boss-ivory/50"
                                tabindex="-1">
                                <svg x-show="!show.confirm" viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.5]"><ellipse cx="8" cy="8" rx="6" ry="4"/><circle cx="8" cy="8" r="1.5"/></svg>
                                <svg x-show="show.confirm" x-cloak viewBox="0 0 16 16" class="h-4 w-4 fill-none stroke-current stroke-[1.5]"><ellipse cx="8" cy="8" rx="6" ry="4"/><circle cx="8" cy="8" r="1.5"/><path d="M2 2l12 12"/></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap items-center gap-3 pt-0.5">
                        <button
                            type="submit"
                            :disabled="saving || newPassword.length < 6"
                            class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-[0.75rem] font-medium transition-all disabled:opacity-40"
                            style="border-color:rgba(196,104,122,0.30);background:rgba(196,104,122,0.09);color:#C4687A;"
                            onmouseover="if(!this.disabled)this.style.background='rgba(196,104,122,0.17)'"
                            onmouseout="this.style.background='rgba(196,104,122,0.09)'"
                        >
                            <svg x-show="saving" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12"/>
                            </svg>
                            <span x-text="saving ? '{{ __('Updating…') }}' : '{{ __('Update Password') }}'"></span>
                        </button>

                        <button type="button" @click="toggle()"
                            class="rounded-lg border border-white/[0.07] bg-white/[0.03] px-4 py-2 text-[0.75rem] text-boss-ivory/38 transition-all hover:text-boss-ivory/70">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
