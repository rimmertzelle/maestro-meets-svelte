<script>
    import { auth } from '../lib/auth.svelte.js'
    import { navigate } from '../lib/router.svelte.js'
</script>

<div class="flex flex-col items-center justify-center min-h-[50vh] text-center">
    <h1 class="text-4xl font-semibold text-gray-900 tracking-tight mb-4">Welcome</h1>
    <p class="text-gray-500 text-base max-w-md mb-8">
        This is your project starter. Sign in to access your account, or contact an administrator to get an invite.
    </p>
    {#if auth.user}
        <div class="flex gap-3">
            {#if auth.user.role === 'admin'}
                <a href="/admin/users" onclick={(e) => { e.preventDefault(); navigate('/admin/users') }}
                    class="px-5 py-2.5 text-sm font-medium rounded-xl bg-gray-900 text-white hover:bg-gray-700 transition-colors">
                    Manage users
                </a>
            {/if}
            <a href="/profile" onclick={(e) => { e.preventDefault(); navigate('/profile') }}
                class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                My profile
            </a>
        </div>
    {:else}
        <a href="/login" onclick={(e) => { e.preventDefault(); navigate('/login') }}
            class="px-5 py-2.5 text-sm font-medium rounded-xl bg-gray-900 text-white hover:bg-gray-700 transition-colors">
            Sign in
        </a>
    {/if}
</div>
