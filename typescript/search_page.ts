const filterTypes = ['condition', 'category', 'size'];

function matchesFilters(post: {[key: string]: string}, searchFilters: {[key: string]: Array<string>}): boolean {
    return filterTypes.every(filterType => {
        if (searchFilters[filterType].length === 0 || searchFilters[filterType].includes(post[filterType])) {
            return true;
        }
    });
}

function updateProducts(
    posts: Array<{[key: string]: string}>,
    searchedProducts: HTMLElement,
    filters: {[key: string]: Array<string>},
): void {
    searchedProducts.innerHTML = '';
    const filteredPosts = posts.filter(post => matchesFilters(post, filters));

    const productSectionTitle = document.createElement('h1');
    productSectionTitle.innerHTML = filteredPosts.length === 0 ? 'No results found' : `Found ${posts.length} results`;
    searchedProducts.appendChild(productSectionTitle);

    filteredPosts.forEach((post: {[key: string]: string}) => {
        const productCard = drawProductCard(post);
        searchedProducts.appendChild(productCard);
    });
}

async function performSearch(searchedProducts: HTMLElement, searchQuery: string): Promise<Array<{[key: string]: string}>> {
    return getData(`../actions/action_search.php?query=${searchQuery}`)
        .then(response => response.json())
        .then(json => {
            if (json.success) {
                return json.posts;
            } else {
                sendToastMessage('An unexpected error occurred', 'error');
                console.error(json.error);
            }
        })
        .catch(error => {
            sendToastMessage('An unexpected error occurred', 'error');
            console.error(error);
        });
}

const searchDrawer: HTMLElement | null = document.querySelector('#search-drawer');
const searchResults: HTMLElement | null = document.querySelector('#search-results');
const searchedProducts: HTMLElement | null = searchResults?.querySelector('#product-section') ?? null;

if (searchDrawer && searchResults && searchedProducts) {
    const searchInput: HTMLInputElement | null = document.querySelector('#search-input');
    const searchButton: HTMLElement | null = document.querySelector('#search-button');
    const searchFilterElems: NodeListOf<HTMLElement> = document.querySelectorAll('.search-filter');
    const searchFilters: {[key: string]: Array<string>} =
        filterTypes.reduce((acc, filterType) => ({...acc, [filterType]: []}), {});
    let posts: Array<{[key: string]: string}> = [];

    const urlParams = new URLSearchParams(window.location.search);
    performSearch(searchedProducts, urlParams.get('query') ?? '')
        .then(result => {
            posts = result;
            updateProducts(result, searchedProducts, searchFilters);
        });
    
    if (searchButton && searchInput) {
        searchButton.addEventListener('click', event => {
            event.preventDefault();
            window.history.pushState({}, '', `search?query=${searchInput.value}`);
            performSearch(searchedProducts, searchInput.value)
            .then(result => {
                posts = result;
                updateProducts(result, searchedProducts, searchFilters);
            });
        });

        searchInput.value = urlParams.get('query') ?? '';
        searchInput.addEventListener('input', () => {
            window.history.pushState({}, '', `search?query=${searchInput.value}`);
            performSearch(searchedProducts, searchInput.value)
            performSearch(searchedProducts, searchInput.value)
            .then(result => {
                posts = result;
                updateProducts(result, searchedProducts, searchFilters);
            });
        });
    }
    
    searchFilterElems.forEach(filterElem => {
        const filterInput: HTMLInputElement | null = filterElem.querySelector('input');
        if (!filterInput)
            return;
        filterInput.addEventListener('click', () => {
            const filterType = filterElem.dataset.type;
            const filterValue = filterElem.dataset.value;

            if (filterType && filterValue) {
                if (filterInput!.checked)
                    searchFilters[filterType].push(filterValue);
                else
                    searchFilters[filterType] = searchFilters[filterType].filter(value => value !== filterValue);
                updateProducts(posts, searchedProducts, searchFilters);
            }
        });
    });
}


