<script>
    import { onMount } from 'svelte'
    import { auth } from '../lib/auth.svelte.js'
    import { navigate } from '../lib/router.svelte.js'
    import { post } from '../lib/api.js'
    import ErrorAlert from '../components/ErrorAlert.svelte'

    let name = $state('')
    let email = $state('')
    let password = $state('')
    let passwordConfirm = $state('')
    let error = $state('')
    let success = $state(false)
    let loading = $state(false)

    onMount(() => {
        if (!auth.user) { navigate('/login'); return }
        name = auth.user.name
        email = auth.user.email
    })

    async function submit(e) {
        e.preventDefault()
        error = ''
        success = false
        loading = true
        try {
            const data = await post('/api/auth/profile', {
                name,
                email,
                password,
                password_confirm: passwordConfirm,
            })
            auth.user = data.user
            password = ''
            passwordConfirm = ''
            success = true
        } catch (err) {
            error = err.message
        } finally {
            loading = false
        }
    }
</script>

<div class="max-w-sm mx-auto pt-8">
    <h1 class="text-2xl font-semibold text-gray-900 mb-8 tracking-tight">My profile</h1>

    <ErrorAlert message={error} />

    {#if success}
        <div class="mb-5 px-4 py-3 rounded-xl bg-teal-50 border border-teal-100 text-sm text-teal-700">
            Changes saved.
        </div>
    {/if}

    <form onsubmit={submit} class="space-y-4">
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1.5">Name</span>
            <input type="text" bind:value={name} required
                class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
        </label>
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1.5">Email</span>
            <input type="email" bind:value={email} required
                class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
        </label>

        <hr class="border-gray-100 my-2">
        <p class="text-xs text-gray-400">Leave password fields blank to keep your current password.</p>

        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1.5">
                New password <span class="text-gray-400">(min. 8 characters)</span>
            </span>
            <input type="password" bind:value={password} minlength="8"
                class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
        </label>
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1.5">Confirm new password</span>
            <input type="password" bind:value={passwordConfirm}
                class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
        </label>

        <button type="submit" disabled={loading}
            class="w-full px-4 py-2.5 text-sm font-medium rounded-xl bg-gray-900 text-white hover:bg-gray-700 transition-colors disabled:opacity-50">
            {loading ? 'Saving…' : 'Save changes'}
        </button>
    </form>
</div>
