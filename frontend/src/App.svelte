<script>
    import { onMount } from 'svelte'
    import { router } from './lib/router.svelte.js'
    import { auth } from './lib/auth.svelte.js'
    import { apiFetch } from './lib/api.js'
    import Layout from './components/Layout.svelte'
    import Home from './pages/Home.svelte'
    import Login from './pages/Login.svelte'
    import Invite from './pages/Invite.svelte'
    import Profile from './pages/Profile.svelte'
    import AdminUsers from './pages/AdminUsers.svelte'
    import NotFound from './pages/NotFound.svelte'

    onMount(async () => {
        const handler = () => { router.path = window.location.pathname }
        window.addEventListener('popstate', handler)

        try {
            const data = await apiFetch('/api/auth/me')
            auth.user = data.user
        } catch {
            auth.user = null
        } finally {
            auth.loading = false
        }

        return () => window.removeEventListener('popstate', handler)
    })

    const inviteToken = $derived(
        router.path.startsWith('/invite/') ? router.path.slice('/invite/'.length) : null
    )
</script>

{#if auth.loading}
    <div class="flex items-center justify-center min-h-screen text-sm text-gray-400">Loading…</div>
{:else}
    <Layout>
        {#if router.path === '/'}
            <Home />
        {:else if router.path === '/login'}
            <Login />
        {:else if inviteToken}
            <Invite token={inviteToken} />
        {:else if router.path === '/profile'}
            <Profile />
        {:else if router.path === '/admin/users'}
            <AdminUsers />
        {:else}
            <NotFound />
        {/if}
    </Layout>
{/if}
