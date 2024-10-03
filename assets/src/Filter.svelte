<script>
    import { createEventDispatcher } from "svelte";

    export let data = {};
    export let label = "";
    export let tax = "";
    export let val = "";
    export let isPrePop = false;

    const dispatch = createEventDispatcher();
    let selectedFilter = "";

    function handleChange(e) {
        selectedFilter = e.target.value;
        let payload = {
            tax: tax,
            value: selectedFilter,
            name: data[selectedFilter].name,
        };
        dispatch("filterChange", payload);
    }
</script>

{#if !isPrePop}
    <select on:change={handleChange} bind:value={val}>
        <option value="">{label}</option>
        {#if Object.keys(data).length > 0}
            {#each Object.entries(data) as [key, val]}
                <option value={key} disabled={val.count === 0}
                    >{val.name} ({val.count})</option
                >
            {/each}
        {/if}
    </select>
{/if}
