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
    <fieldset>
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
    </fieldset>
{/if}

<style>
    select {
        border: none;
        background: #fff;
        padding-left: 0;
        width: 100%;
        cursor: pointer;
        font-size: 16px;
    }
    select:focus-visible {
        border: none;
        outline: none;
    }
    fieldset {
        border: none;
        border-bottom: 1px solid var(--shade-light);
        padding-left: 0;
        padding-bottom: 20px;
    }
</style>
