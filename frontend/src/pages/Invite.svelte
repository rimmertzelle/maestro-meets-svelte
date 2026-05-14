<script>
    import { onMount } from 'svelte'
    import { auth } from '../lib/auth.svelte.js'
    import { navigate } from '../lib/router.svelte.js'
    import { apiFetch, post } from '../lib/api.js'
    import ErrorAlert from '../components/ErrorAlert.svelte'

    let { token } = $props()

    let invitedUser = $state(null)
    let invalid = $state(false)
    let error = $state('')
    let loading = $state(false)
    let password = $state('')
    let passwordConfirm = $state('')

    onMount(async () => {
        try {
            const data = await apiFetch(`/api/auth/invite/${token}`)
            invitedUser = data.user
        } catch (err) {
            invalid = true
            error = err.message
        }
    })

    async function submit(e) {
        e.preventDefault()
        error = ''
        loading = true
        try {
            const data = await post(`/api/auth/invite/${token}`, {
                password,
                password_confirm: passwordConfirm,
            })
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
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 tracking-tight">Set your password</h1>

    {#if invitedUser}
        <p class="text-sm text-gray-500 mb-8">
            Welcome, <strong class="text-gray-900">{invitedUser.name}</strong>.
            Choose a password to activate your account.
        </p>
    {/if}

    <ErrorAlert message={error} />

    {#if !invalid}
        <form onsubmit={submit} class="space-y-4">
            <label class="block">
                <span class="block text-xs font-medium text-gray-500 mb-1.5">
                    New password <span class="text-gray-400">(min. 8 characters)</span>
                </span>
                <!-- svelte-ignore a11y_autofocus -->
                <input type="password" bind:value={password} required minlength="8" autofocus
                    class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
            </label>
            <label class="block">
                <span class="block text-xs font-medium text-gray-500 mb-1.5">Confirm password</span>
                <input type="password" bind:value={passwordConfirm} required
                    class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
            </label>
            <button type="submit" disabled={loading}
                class="w-full px-4 py-2.5 text-sm font-medium rounded-xl bg-gray-900 text-white hover:bg-gray-700 transition-colors mt-2 disabled:opacity-50">
                {loading ? 'Activating…' : 'Activate account'}
            </button>
        </form>
    {:else}
        <a href="/login" onclick={(e) => { e.preventDefault(); navigate('/login') }}
            class="text-sm font-medium text-gray-900 hover:text-gray-600 transition-colors">
            ← Go to login
        </a>
    {/if}
</div>
