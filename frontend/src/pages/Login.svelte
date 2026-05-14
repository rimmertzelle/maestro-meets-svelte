<script>
    import { auth } from '../lib/auth.svelte.js'
    import { navigate } from '../lib/router.svelte.js'
    import { post } from '../lib/api.js'
    import ErrorAlert from '../components/ErrorAlert.svelte'

    let email = $state('')
    let password = $state('')
    let error = $state('')
    let loading = $state(false)

    async function submit(e) {
        e.preventDefault()
        error = ''
        loading = true
        try {
            const data = await post('/api/auth/login', { email, password })
            auth.user = data.user
            navigate('/')
        } catch (err) {
            error = err.message
        } finally {
            loading = false
        }
    }
</script>

<div class="max-w-sm mx-auto pt-16">
    <h1 class="text-2xl font-semibold text-gray-900 mb-8 tracking-tight">Sign in</h1>

    <ErrorAlert message={error} />

    <form onsubmit={submit} class="space-y-4">
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1.5">Email</span>
            <!-- svelte-ignore a11y_autofocus -->
            <input type="email" bind:value={email} required autofocus
                class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
        </label>
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1.5">Password</span>
            <input type="password" bind:value={password} required
                class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
        </label>
        <button type="submit" disabled={loading}
            class="w-full px-4 py-2.5 text-sm font-medium rounded-xl bg-gray-900 text-white hover:bg-gray-700 transition-colors mt-2 disabled:opacity-50">
            {loading ? 'Signing in…' : 'Sign in'}
        </button>
    </form>
</div>
