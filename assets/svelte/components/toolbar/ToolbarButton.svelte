<script>
    export let active = false;
    export let onClick;
    export let label;
    export let icon;
</script>

<button type="button" class:active={active} on:click={onClick}>
    {#if icon}
        <i class={icon}></i>
    {/if}
    {#if label}
        {label}
    {/if}
</button>

<style>
    .active {
        background-color: #ddd;
    }
</style>