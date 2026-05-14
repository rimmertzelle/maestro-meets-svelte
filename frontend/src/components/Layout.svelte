<script>
    import { auth } from '../lib/auth.svelte.js'
    import { navigate } from '../lib/router.svelte.js'
    import { post } from '../lib/api.js'

    let { children } = $props()

    async function logout() {
        await post('/api/auth/logout', {})
        auth.user = null
        navigate('/login')
    }

    function link(e, to) {
        e.preventDefault()
        navigate(to)
    }
</script>

<div class="flex flex-col min-h-screen">
    <header class="bg-white/90 backdrop-blur-sm border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-12">
                <a href="/" onclick={(e) => link(e, '/')}
                    class="text-sm font-semibold tracking-tight text-gray-900">Starter</a>
                <nav>
                    <ul class="flex gap-7 text-sm items-center">
                        {#if auth.user}
                            {#if auth.user.role === 'admin'}
                                <li>
                                    <a href="/admin/users" onclick={(e) => link(e, '/admin/users')}
                                        class="text-gray-500 hover:text-gray-900 transition-colors font-medium">Users</a>
                                </li>
                            {/if}
                            <li class="pl-4 border-l border-gray-200 flex items-center gap-3">
                                <a href="/profile" onclick={(e) => link(e, '/profile')}
                                    class="text-xs text-gray-500 hover:text-gray-900 transition-colors">
                                    {auth.user.name}
                                </a>
                                <button onclick={logout}
                                    class="text-xs text-gray-400 hover:text-gray-700 transition-colors">
                                    Sign out
                                </button>
                            </li>
                        {:else}
                            <li>
                                <a href="/login" onclick={(e) => link(e, '/login')}
                                    class="text-sm font-medium text-gray-900 hover:text-gray-600 transition-colors">
                                    Sign in
                                </a>
                            </li>
                        {/if}
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-5xl w-full mx-auto px-6 lg:px-8 py-12">
        {@render children()}
    </main>

    <footer class="border-t border-gray-100 py-6">
        <div class="max-w-5xl mx-auto px-6 lg:px-8 text-xs text-gray-400 text-center">
            &copy; {new Date().getFullYear()} Starter
        </div>
    </footer>
</div>
