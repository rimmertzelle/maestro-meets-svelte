<script>
    import { navigate } from '../lib/router.svelte.js'
</script>

<div class="flex flex-col items-center justify-center min-h-[50vh] text-center">
    <h1 class="text-4xl font-semibold text-gray-900 tracking-tight mb-4">404</h1>
    <p class="text-gray-500 mb-6">Page not found.</p>
    <a href="/" onclick={(e) => { e.preventDefault(); navigate('/') }}
        class="text-sm font-medium text-gray-900 hover:text-gray-600 transition-colors">
        ← Go home
    </a>
</div>
