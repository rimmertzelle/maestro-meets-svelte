<script>
    import { onMount } from 'svelte'
    import { auth } from '../lib/auth.svelte.js'
    import { navigate } from '../lib/router.svelte.js'
    import { apiFetch, post } from '../lib/api.js'
    import ErrorAlert from '../components/ErrorAlert.svelte'

    let users = $state([])
    let roles = $state([])
    let loading = $state(true)
    let error = $state('')
    let inviteToken = $state(null)

    // Inline edit state
    let editingId = $state(null)
    let editName = $state('')
    let editEmail = $state('')
    let editRoleId = $state(2)

    // Add form state
    let addOpen = $state(false)
    let addName = $state('')
    let addEmail = $state('')
    let addRoleId = $state(2)
    let addLoading = $state(false)
    let addError = $state('')

    onMount(async () => {
        if (!auth.user || auth.user.role !== 'admin') { navigate('/'); return }
        await loadUsers()
    })

    async function loadUsers() {
        try {
            const data = await apiFetch('/api/users')
            users = data.users
            roles = data.roles
        } catch (err) {
            error = err.message
        } finally {
            loading = false
        }
    }

    function startEditing(user) {
        editingId = user.id
        editName = user.name
        editEmail = user.email
        editRoleId = user.role_id
    }

    async function saveUser(userId, e) {
        e.preventDefault()
        try {
            const data = await post(`/api/users/${userId}`, {
                name: editName,
                email: editEmail,
                role_id: editRoleId,
            })
            users = users.map(u => u.id === userId ? data.user : u)
            editingId = null
        } catch (err) {
            error = err.message
        }
    }

    async function createUser(e) {
        e.preventDefault()
        addError = ''
        addLoading = true
        try {
            const data = await post('/api/users', {
                name: addName,
                email: addEmail,
                role_id: addRoleId,
            })
            inviteToken = data.token
            addName = ''
            addEmail = ''
            addRoleId = 2
            addOpen = false
            await loadUsers()
        } catch (err) {
            addError = err.message
        } finally {
            addLoading = false
        }
    }

    async function resendInvite(userId) {
        try {
            const data = await post(`/api/users/${userId}/invite`, {})
            inviteToken = data.token
            await loadUsers()
        } catch (err) {
            error = err.message
        }
    }

    const inviteUrl = $derived(
        inviteToken ? `${window.location.origin}/invite/${inviteToken}` : null
    )
</script>

<div>
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">Users</h1>
    </div>

    <ErrorAlert message={error} />

    {#if inviteUrl}
        <div class="mb-6 px-4 py-4 rounded-xl bg-teal-50 border border-teal-100">
            <p class="text-sm font-medium text-teal-800 mb-2">User created. Share this invite link:</p>
            <code class="block text-xs text-teal-700 break-all bg-teal-100 rounded-lg px-3 py-2">{inviteUrl}</code>
            <button onclick={() => navigator.clipboard.writeText(inviteUrl)}
                class="mt-2 text-xs text-teal-600 hover:text-teal-800 font-medium underline">
                Copy to clipboard
            </button>
        </div>
    {/if}

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-8">
        {#if loading}
            <p class="px-4 py-6 text-sm text-gray-400 text-center">Loading…</p>
        {:else}
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-medium text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    {#each users as user (user.id)}
                        {#if editingId === user.id}
                            <tr>
                                <td colspan="5" class="px-4 py-3 bg-gray-50/80">
                                    <form onsubmit={(e) => saveUser(user.id, e)} class="flex flex-wrap gap-2 items-end">
                                        <label class="block flex-1 min-w-32">
                                            <span class="block text-xs text-gray-400 mb-0.5">Name</span>
                                            <input type="text" bind:value={editName} required
                                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
                                        </label>
                                        <label class="block flex-1 min-w-40">
                                            <span class="block text-xs text-gray-400 mb-0.5">Email</span>
                                            <input type="email" bind:value={editEmail} required
                                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
                                        </label>
                                        <label class="block">
                                            <span class="block text-xs text-gray-400 mb-0.5">Role</span>
                                            <select bind:value={editRoleId}
                                                class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
                                                {#each roles as role}
                                                    <option value={role.id}>{role.name}</option>
                                                {/each}
                                            </select>
                                        </label>
                                        <div class="flex gap-2">
                                            <button type="submit"
                                                class="px-4 py-1.5 text-xs font-medium rounded-full bg-gray-900 text-white hover:bg-gray-700 transition-colors">
                                                Save
                                            </button>
                                            <button type="button" onclick={() => editingId = null}
                                                class="px-4 py-1.5 text-xs font-medium rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-800">{user.name}</td>
                                <td class="px-4 py-3 text-gray-500">{user.email}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                                        {user.role === 'admin' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700'}">
                                        {user.role}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    {#if !user.has_password}
                                        <span class="text-xs text-amber-600 font-medium">Pending</span>
                                        {#if user.invite_token}
                                            <button
                                                onclick={() => navigator.clipboard.writeText(`${window.location.origin}/invite/${user.invite_token}`)}
                                                class="ml-2 text-xs text-gray-400 hover:text-gray-700 underline">
                                                Copy link
                                            </button>
                                        {:else}
                                            <button onclick={() => resendInvite(user.id)}
                                                class="ml-2 text-xs text-gray-400 hover:text-gray-700 underline">
                                                New invite
                                            </button>
                                        {/if}
                                    {:else}
                                        <span class="text-xs text-teal-600 font-medium">Active</span>
                                    {/if}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button onclick={() => startEditing(user)}
                                        class="text-xs text-gray-400 hover:text-gray-700 transition-colors">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        {/if}
                    {:else}
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-sm text-gray-400 text-center">No users yet.</td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        {/if}
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <button onclick={() => addOpen = !addOpen}
            class="text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
            {addOpen ? 'Cancel' : '+ Add user'}
        </button>

        {#if addOpen}
            <div class="mt-4">
                <ErrorAlert message={addError} />
                <form onsubmit={createUser}>
                    <div class="flex flex-wrap gap-3 items-end">
                        <label class="block flex-1 min-w-32">
                            <span class="block text-xs text-gray-400 mb-0.5">Name <span class="text-red-400">*</span></span>
                            <input type="text" bind:value={addName} required
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
                        </label>
                        <label class="block flex-1 min-w-40">
                            <span class="block text-xs text-gray-400 mb-0.5">Email <span class="text-red-400">*</span></span>
                            <input type="email" bind:value={addEmail} required
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
                        </label>
                        <label class="block">
                            <span class="block text-xs text-gray-400 mb-0.5">Role</span>
                            <select bind:value={addRoleId}
                                class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-gray-900">
                                {#each roles as role}
                                    <option value={role.id}>{role.name}</option>
                                {/each}
                            </select>
                        </label>
                        <button type="submit" disabled={addLoading}
                            class="px-4 py-1.5 text-xs font-medium rounded-full bg-gray-900 text-white hover:bg-gray-700 transition-colors disabled:opacity-50">
                            {addLoading ? 'Creating…' : 'Create & get invite link'}
                        </button>
                    </div>
                </form>
            </div>
        {/if}
    </div>
</div>
