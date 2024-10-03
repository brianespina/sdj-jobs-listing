<script>
    import { onMount } from "svelte";
    import Filter from "./Filter.svelte";
    import Loader from "./loader.svelte";

    let posts = [];
    let errorMessage = "";
    let selectedFilters = {};
    let currentPage = 1;
    let allFilters = {};
    let showExpired = false;
    let isLoading = true;
    let perPage = 10;
    let total = 0;
    let moreIsLoading = false;
    let isPrePop = false;

    $: lastPage = Math.ceil(total / perPage) === currentPage;

    let url = new URL(window.location.href);
    let path = url.pathname
        .trim()
        .split("/")
        .filter((i) => i);

    async function fetchPosts(page = 1, append = false, load = true) {
        isLoading = load;
        if (!load) {
            moreIsLoading = true;
        }

        try {
            const formData = new FormData();
            formData.append("action", "get_jobs"); // This matches the action set in WordPress
            formData.append("page", page);
            formData.append("per_page", perPage);

            if (showExpired) {
                formData.append("show_expired", 1);
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
                total = parseInt(result.data.total);
            } else {
                errorMessage = "Failed to fetch posts";
            }
        } catch (error) {
            errorMessage = "An error occurred: " + error.message;
        }
        isLoading = false;
        moreIsLoading = false;
    }

    // Load more posts when user clicks "Load More" button12
    function loadMore() {
        currentPage += 1;
        fetchPosts(currentPage, true, false);
    }

    function handleFilterChange(e) {
        currentPage = 1;
        if (e.detail.value === "") {
            if (e.detail.tax in selectedFilters) {
                let temp = { ...selectedFilters };
                delete temp[e.detail.tax];
                selectedFilters = temp;
            }
        } else {
            selectedFilters = {
                ...selectedFilters,
                [e.detail.tax]: { value: e.detail.value, name: e.detail.name },
            };
        }
        fetchPosts();
    }
    function removeFilter(key) {
        currentPage = 1;
        selectedFilters = {
            ...selectedFilters,
            [key]: "",
        };
        fetchPosts(currentPage);
    }
    // Fetch posts when component is mounted
    onMount(() => {
        if (path[0] === "country") {
            isPrePop = true;
            selectedFilters = {
                country: path[1],
            };
        }
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
            bind:val={selectedFilters["country"]}
            {isPrePop}
        />
        <Filter
            on:filterChange={handleFilterChange}
            data={allFilters.job_listing_type}
            tax="job_listing_type"
            label="Type"
            bind:val={selectedFilters["job_listing_type"]}
        />
        <Filter
            on:filterChange={handleFilterChange}
            data={allFilters.job_industry}
            tax="job_industry"
            label="Industry"
            bind:val={selectedFilters["job_industry"]}
        />
        <Filter
            on:filterChange={handleFilterChange}
            data={allFilters.job_experience_level}
            tax="job_experience_level"
            label="Experience level"
            bind:val={selectedFilters["job_experience_level"]}
        />

        <label class="expired" for="expired"
            ><input
                id="expired"
                type="checkbox"
                bind:checked={showExpired}
                on:change={() => fetchPosts(1)}
            />
            Show Expired</label
        >

        <div class="active-filters">
            {#each Object.keys(selectedFilters) as key}
                {#if selectedFilters[key]}
                    {#if path[1] !== selectedFilters[key]}
                        <div on:click={() => removeFilter(key)}>
                            {selectedFilters[key].name} <span>x</span>
                        </div>
                    {/if}
                {/if}
            {/each}
        </div>
    </div>
    <div class="job-listing-loop">
        <div class="job-listing-heading">
            <div>
                {#if total}
                    {total} Job{total !== 1 ? "s" : ""}
                {/if}
            </div>
            <div>
                Display
                <select bind:value={perPage} on:change={fetchPosts}>
                    <option value={10}>10</option>
                    <option value={12}>12</option>
                    <option value={25}>25</option>
                    <option value={50}>50</option>
                    <option value={100}>100</option>
                </select>
            </div>
        </div>
        {#if isLoading}
            <div class="loader">
                <Loader />
            </div>
        {:else if posts.length > 0}
            <ul>
                {#each posts as post}
                    <li>
                        <div class="company-image">
                            {#if post.featured_image}
                                <img
                                    alt="The project logo"
                                    src={post.featured_image}
                                />
                            {/if}
                        </div>
                        <div class="job-content">
                            <div>
                                <h2 class="title">{@html post.title}</h2>
                                <div class="location">
                                    {@html post.company_name}
                                </div>
                            </div>
                            <div>
                                <img
                                    id="image-298-37-1"
                                    alt="job location"
                                    src="/wp-content/uploads/2022/11/location-icon.svg"
                                    class="ct-image"
                                    data-id="image-298-37"
                                />
                                {@html post.job_location}
                            </div>
                            <div>
                                <img
                                    id="image-440-37-1"
                                    alt="industry"
                                    src="/wp-content/uploads/2022/11/industry-icon-black.svg"
                                    class="ct-image"
                                    data-id="image-440-37"
                                />
                                {post.job_industry &&
                                post.job_industry.length > 0
                                    ? post.job_industry[0].name
                                    : "N/A"}
                            </div>
                            <div class="job-details">
                                <div>
                                    <img
                                        id="image-455-37-1"
                                        alt="job type"
                                        src="/wp-content/uploads/2022/11/briefacase-icon.svg"
                                        class="ct-image"
                                        data-id="image-455-37"
                                    />
                                    {post.type && post.type.length > 0
                                        ? post.type[0].name
                                        : "N/A"}
                                </div>
                                <div>
                                    <img
                                        id="image-470-37-3"
                                        alt="job level"
                                        src="/wp-content/uploads/2022/11/star-icon.svg"
                                        class="ct-image"
                                        data-id="image-470-37"
                                    />
                                    {post.job_experience_level &&
                                    post.job_experience_level.length > 0
                                        ? post.job_experience_level[0].name
                                        : "N/A"}
                                </div>
                            </div>
                        </div>
                        <div class="job-footer">
                            <div class="time">
                                <img
                                    id="image-748-37-3"
                                    alt="posted"
                                    src="/wp-content/uploads/2022/11/clock-icon-blue.svg"
                                    class="ct-image"
                                    data-id="image-748-37"
                                />{post.time}
                            </div>
                            <a
                                href={post.permalink}
                                class="ct-link-button text--uppercase btn--primary"
                                >Show Details</a
                            >
                        </div>
                    </li>
                {/each}
            </ul>
            <div class="load-more">
                {#if moreIsLoading}
                    <div class="loader">
                        <Loader />
                    </div>
                {/if}
                <button
                    class="ct-link-button text--uppercase btn--primary"
                    on:click={loadMore}
                    class:dissable={lastPage}
                >
                    Load More
                </button>
            </div>
        {:else if posts.length === 0}
            No Jobs Found
        {/if}
    </div>
</section>

<style>
    .expired {
        padding-top: 15px;
        font-size: 16px;
    }
    .active-filters {
        padding-top: 20px;
    }
    .active-filters div:hover {
        background-color: var(--primary-light);
    }
    .active-filters div {
        float: left;
        margin-right: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        border-radius: var(--radius-l);
        background: #e4e4e4;
        color: var(--base);
        font-size: var(--text-s);
        padding: 8px 18px;
    }
    .active-filters div span {
        font-size: 15px;
        font-weight: 600;
    }
    .job-listing {
        display: grid;
        grid-template-columns: 2fr 5fr; /* Define a 30% column and a 70% column */
        gap: 50px; /* Space between the columns */
        width: 100%;
    }

    .job-content img,
    .job-footer img {
        margin-right: 8px;
    }

    .job-filters {
        padding-inline: 30px;
        padding-block: 60px;
        padding-bottom: 40px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        box-sizing: border-box; /* Ensure padding is included in the element's width */
        border: 2px solid var(--base);
        border-radius: var(--radius-m);
        align-self: start;
        margin-top: 45px;
        background: #fff;
    }

    .job-listing-loop {
        box-sizing: border-box; /* Ensure content fits within the specified width */
    }

    .load-more {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .load-more .loader {
        padding: 20px;
    }

    ul {
        list-style: none;
        padding: 0;
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
    }

    li {
        background: #fff;
        border: solid thin var(--shade-light);
        border-radius: var(--radius-m);
        padding: 30px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
        grid-template-rows: 1fr;
        gap: 30px;
        font-family: "Nunito Sans";
        font-size: var(--text-s);
    }

    .job-content {
        grid-column: 2/4;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .job-footer {
        grid-column: 4/6;
        display: flex;
        flex-direction: column;
        align-items: end;
        justify-content: space-between;
    }

    .job-details {
        display: flex;
        gap: 30px;
    }

    .job-listing-heading {
        display: flex;
        justify-content: space-between;
    }

    .loader {
        width: 100px;
        margin: auto;
        padding-top: 80px;
    }

    .title {
        font-family: "DM Sans";
        font-size: 2.2rem;
    }

    .location {
        font-size: var(--text-s);
    }

    .time {
        color: var(--primary);
    }

    button.dissable {
        pointer-events: none;
        opacity: 0.4;
    }

    @media (max-width: 1024px) {
        .job-listing {
            grid-template-columns: 1fr; /* Define a 30% column and a 70% column */
            gap: 20px;
        }
        li {
            grid-template-columns: 1fr;
            grid-template-rows: auto;
            gap: 10px;
        }
        .job-footer,
        .job-content {
            grid-column: 1;
        }
        .company-image {
            max-width: 50px;
        }
        .job-footer {
            gap: 20px;
            align-items: start;
        }
    }
</style>
