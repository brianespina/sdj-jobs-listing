<script>
    import { onMount } from "svelte";
    import Filter from "./Filter.svelte";

    let posts = [];
    let errorMessage = "";
    let selectedFilters = {};
    let currentPage = 1;
    let allFilters = {};
    let hideExpired = false;
    let isLoading = true;
    let perPage = 10;

    async function fetchPosts(page = 1, append = false, load = true) {
        isLoading = load;
        try {
            const formData = new FormData();
            formData.append("action", "get_jobs"); // This matches the action set in WordPress
            formData.append("page", page);
            formData.append("per_page", perPage);

            if (hideExpired) {
                formData.append("hide_expired", 1);
            }

            if (Object.keys(selectedFilters).length > 0) {
                formData.append("filters", JSON.stringify(selectedFilters));
            }
            const res = await fetch(wordpress_object.ajax_url, {
                method: "POST",
                body: formData,
            });

            const result = await res.json();
            if (res.ok && result.success) {
                if (append) {
                    posts = [...posts, ...result.data.posts]; // Append new posts
                } else {
                    posts = result.data.posts;
                }
                allFilters = result.data.taxonomy_counts;
            } else {
                errorMessage = "Failed to fetch posts";
            }
        } catch (error) {
            errorMessage = "An error occurred: " + error.message;
        }
        isLoading = false;
    }

    // Load more posts when user clicks "Load More" button12
    function loadMore() {
        currentPage += 1;
        fetchPosts(currentPage, true, false);
    }

    function handleFilterChange(e) {
        if (e.detail.value === "") {
            if (e.detail.tax in selectedFilters) {
                let temp = { ...selectedFilters };
                delete temp[e.detail.tax];
                selectedFilters = temp;
            }
        } else {
            selectedFilters = {
                ...selectedFilters,
                [e.detail.tax]: e.detail.value,
            };
        }
        fetchPosts();
    }
    // Fetch posts when component is mounted
    onMount(() => {
        fetchPosts();
    });
</script>

<section class="job-listing">
    <div class="job-filters">
        <Filter
            on:filterChange={handleFilterChange}
            data={allFilters.country}
            tax="country"
            label="Country"
        />
        <Filter
            on:filterChange={handleFilterChange}
            data={allFilters.job_listing_type}
            tax="job_listing_type"
            label="Type"
        />
        <Filter
            on:filterChange={handleFilterChange}
            data={allFilters.job_industry}
            tax="job_industry"
            label="Industry"
        />
        <Filter
            on:filterChange={handleFilterChange}
            data={allFilters.job_experience_level}
            tax="job_experience_level"
            label="Experience level"
        />

        <label for="expired"
            ><input
                id="expired"
                type="checkbox"
                bind:checked={hideExpired}
                on:change={fetchPosts}
            />
            Hide Expired</label
        >

        {#each Object.keys(selectedFilters) as key}
            <div>{selectedFilters[key]}</div>
        {/each}
    </div>
    <div class="job-listing-loop">
        <select bind:value={perPage} on:change={fetchPosts}>
            <option value={10}>10</option>
            <option value={11}>11</option>
            <option value={12}>12</option>
            <option value={13}>13</option>
            <option value={14}>14</option>
            <option value={15}>15</option>
            <option value={16}>16</option>
            <option value={17}>17</option>
            <option value={18}>18</option>
            <option value={19}>19</option>
            <option value={20}>20</option>
        </select>

        {#if isLoading}
            Loading...
        {:else if posts.length > 0}
            <ul>
                {#each posts as post}
                    <li>
                        <a href={post.permalink}>{post.company_name}</a>
                        <div>{post.job_location}</div>
                        <div>
                            {post.type && post.type.length > 0
                                ? post.type[0].name
                                : "N/A"}
                        </div>
                        <div>
                            {post.country && post.country.length > 0
                                ? post.country[0].name
                                : "N/A"}
                        </div>
                        <div>
                            {post.job_industry && post.job_industry.length > 0
                                ? post.job_industry[0].name
                                : "N/A"}
                        </div>
                        <div>
                            {post.job_experience_level &&
                            post.job_experience_level.length > 0
                                ? post.job_experience_level[0].name
                                : "N/A"}
                        </div>
                        <div>{post.time}</div>
                        <img alt="The project logo" src={post.featured_image} />
                    </li>
                {/each}
            </ul>
            <button on:click={loadMore}> Load More </button>
        {/if}
    </div>
</section>

<style>
    .job-listing {
        display: grid;
        grid-template-columns: 1fr 2fr; /* Define a 30% column and a 70% column */
        gap: 30px; /* Space between the columns */
        width: 100%;
    }

    .job-filters {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        box-sizing: border-box; /* Ensure padding is included in the element's width */
    }

    .job-listing-loop {
        box-sizing: border-box; /* Ensure content fits within the specified width */
    }
</style>
