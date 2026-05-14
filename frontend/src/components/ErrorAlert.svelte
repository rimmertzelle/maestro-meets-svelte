<script>
    let { message = '' } = $props()
</script>

{#if message}
    <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-sm text-red-700">
        {message}
    </div>
{/if}
