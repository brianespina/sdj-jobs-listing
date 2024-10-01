<script>
    import { createEventDispatcher } from "svelte";

    export let data = {};
    export let label = "";
    export let tax = "";

    const dispatch = createEventDispatcher();
    let errorMessage = "";
    let selectedFilter = "";

    function handleChange(e) {
        selectedFilter = e.target.value;
        let payload = {
            tax: tax,
            value: selectedFilter,
        };
        dispatch("filterChange", payload);
    }
</script>

{#if Object.keys(data).length > 0}
    <select on:change={handleChange}>
        <option value="">{label}</option>
        {#each Object.entries(data) as [key, count]}
            <option value={key} disabled={count === 0}>{key} ({count})</option>
        {/each}
    </select>
{:else}
    <p>{errorMessage || "No filters available."}</p>
{/if}

