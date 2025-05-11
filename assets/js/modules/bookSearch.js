export const BookSearch = {
    /**
     * @param {string} [inputSelector='#book-search'] - Selector for search input
     * @param {string} [tableSelector='#books-table'] - Selector for the table to filter
     */
    init(inputSelector = '#book-search', tableSelector = '#books-table') {
        if (window.bookSearchInitialized) {
            console.info('BookSearch already initialized, skipping duplicate initialization');
            return;
        }
        window.bookSearchInitialized = true;
        
        const searchInput = document.querySelector(inputSelector);
        const table = document.querySelector(tableSelector);
        
        if (!searchInput || !table) {
            console.warn('Search input or table not found:', { searchInput, table });
            return;
        }
        
        const tbody = table.querySelector('tbody');
        if (!tbody) {
            console.warn('Table body not found');
            return;
        }
        
        const originalRows = Array.from(tbody.querySelectorAll('tr'));
        const originalHTML = tbody.innerHTML;
        
        let currentSearchTerm = '';
        
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'btn btn-sm btn-outline-secondary position-absolute end-0 top-0 mt-2 me-2';
        clearButton.innerHTML = '<i class="fas fa-times"></i>';
        clearButton.style.display = 'none';
        
        const inputContainer = searchInput.parentElement;
        inputContainer.style.position = 'relative';
        inputContainer.appendChild(clearButton);
        
        let searchTimeout = null;
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search')) {
            const searchFromUrl = urlParams.get('search');
            if (searchFromUrl.trim()) {
                searchInput.value = searchFromUrl;
                clearButton.style.display = 'block';
                currentSearchTerm = searchFromUrl.trim().toLowerCase();
                
                this.performServerSearch(currentSearchTerm, tbody);
            }
        }
        
        searchInput.addEventListener('input', () => {
            if (searchTimeout) clearTimeout(searchTimeout);
            
            const searchTerm = searchInput.value.trim();
            clearButton.style.display = searchTerm ? 'block' : 'none';
            
            searchTimeout = setTimeout(() => {
                currentSearchTerm = searchTerm.toLowerCase();
                
                this.updateSearchUrlParam(currentSearchTerm);
                
                if (!currentSearchTerm) {
                    tbody.innerHTML = originalHTML;
                    
                    const pagination = document.getElementById('pagination-container');
                    if (pagination) pagination.style.display = '';
                } else {
                    this.performServerSearch(currentSearchTerm, tbody);
                }
            }, 300);
        });
        
        clearButton.addEventListener('click', () => {
            searchInput.value = '';
            currentSearchTerm = '';
            tbody.innerHTML = originalHTML;
            clearButton.style.display = 'none';
            
            const pagination = document.getElementById('pagination-container');
            if (pagination) pagination.style.display = '';
            
            this.updateSearchUrlParam('');
            
            searchInput.focus();
        });
        
        document.addEventListener('click', (event) => {
            if (!event.target.matches('.pagination .page-link') && 
                !event.target.closest('.pagination .page-link')) {
                return;
            }
            
            if (currentSearchTerm) {
                event.preventDefault();
                
                const link = event.target.matches('.pagination .page-link') ? 
                    event.target : event.target.closest('.pagination .page-link');
                
                const page = link.getAttribute('data-page');
                if (!page) return;
                
                const url = new URL(window.location);
                url.searchParams.set('page', page);
                url.searchParams.set('search', currentSearchTerm);
                
                window.location.href = url.toString();
            }
        });
    },
    
    /**
     * @param {string} searchTerm - The search term to add to URL
     */
    updateSearchUrlParam(searchTerm) {
        const url = new URL(window.location);
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        window.history.replaceState({}, '', url);
    },
    
    /**
     * @param {string} searchTerm - The search term
     * @param {HTMLElement} tbody - Table body element
     */
    performServerSearch(searchTerm, tbody) {
        const currentSearchTerm = searchTerm;
        
        if (!tbody || !tbody.parentNode) {
            console.error('Table body is no longer valid. Attempting to find it again.');
            const table = document.querySelector('#books-table');
            if (table) {
                tbody = table.querySelector('tbody');
                if (!tbody) {
                    console.error('Could not find table body for search results.');
                    return;
                }
            } else {
                console.error('Could not find table for search results.');
                return;
            }
        }
        
        this.showLoadingState(tbody);
        
        const pagination = document.getElementById('pagination-container');
        if (pagination) pagination.style.display = 'none';
        
        fetch(`/books/search?q=${encodeURIComponent(currentSearchTerm)}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Search request failed with status ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!tbody || !tbody.parentNode) {
                console.error('Table body is no longer valid after search response.');
                return;
            }
            
            if (data.success) {
                if (data.results && data.results.length > 0) {
                    this.renderSearchResults(data.results, tbody);
                } else {
                    this.showNoResultsMessage(tbody, currentSearchTerm);
                }
            } else {
                throw new Error(data.message || 'Unknown error occurred during search');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            
            if (tbody && tbody.parentNode) {
                this.showErrorMessage(tbody, currentSearchTerm, error.message);
            }
        });
    },
    
    /**
     * @param {HTMLElement} tbody - Table body element
     */
    showLoadingState(tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Searching...</p>
                </td>
            </tr>
        `;
    },
    
    /**
     * @param {Array} books - Array of book objects
     * @param {HTMLElement} tbody - Table body element
     */
    renderSearchResults(books, tbody) {
        const currentTbody = document.querySelector('#books-table tbody');
        if (!currentTbody) {
            console.error('Could not find table body at rendering time');
            return;
        }
        
        tbody = currentTbody;
        
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }
        
        if (!Array.isArray(books) || books.length === 0) {
            this.showNoResultsMessage(tbody, '');
            return;
        }
        
        let rowsHtml = '';
        
        books.forEach(book => {
            if (!book || !book.id) {
                console.warn('Invalid book data:', book);
                return;
            }
            
            let actionsHtml = '';
            try {
                actionsHtml = `
                    <div class="btn-group btn-group-sm">
                        <a href="/books/${book.id}" class="btn btn-info" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="/books/${book.id}/edit" class="btn btn-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="post" action="/books/${book.id}" class="d-inline">
                            <input type="hidden" name="_token" value="${book.csrfToken || ''}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-danger delete-book-btn" title="Delete" 
                                    data-confirm="Are you sure you want to delete this book?">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                `;
            } catch (e) {
                console.error('Error generating actions HTML:', e);
                actionsHtml = '<div class="alert alert-warning small p-1">Error loading actions</div>';
            }
            
            rowsHtml += `
                <tr>
                    <td>${book.title || ''}</td>
                    <td>${book.author || ''}</td>
                    <td>${book.isbn || ''}</td>
                    <td>${book.publicationDate || ''}</td>
                    <td>${actionsHtml}</td>
                </tr>
            `;
        });
        
        try {
            tbody.innerHTML = rowsHtml;
        } catch (error) {
            console.error('Error setting innerHTML:', error);
        }
    },
    
    /**
     * @param {Object} book - Book object
     * @returns {string} HTML for actions
     */
    generateActionsHTML(book) {
        return `
            <div class="btn-group btn-group-sm">
                <a href="/books/${book.id}" class="btn btn-info" title="View">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="/books/${book.id}/edit" class="btn btn-primary" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <form method="post" action="/books/${book.id}" class="d-inline">
                    <input type="hidden" name="_token" value="${book.csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger delete-book-btn" title="Delete" 
                            data-confirm="Are you sure you want to delete this book?">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        `;
    },
    
    /**
     * @param {HTMLElement} tbody - Table body element
     * @param {string} searchTerm - The search term
     */
    showNoResultsMessage(tbody, searchTerm = '') {
        tbody.innerHTML = `
            <tr class="no-results-row">
                <td colspan="5" class="text-center py-3">
                    <div class="alert alert-info mb-0">
                        No books found${searchTerm ? ` matching "${searchTerm}"` : ''}
                    </div>
                </td>
            </tr>
        `;
    },
    
    /**
     * @param {HTMLElement} tbody - Table body element
     */
    showLoadingState(tbody) {
        const currentTbody = document.querySelector('#books-table tbody');
        if (!currentTbody) {
            console.error('Could not find table body when showing loading state');
            return;
        }
        
        currentTbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Searching...</p>
                </td>
            </tr>
        `;
    },

    /**
     * @param {HTMLElement} tbody - Table body element
     * @param {string} searchTerm - The search term
     */
    showNoResultsMessage(tbody, searchTerm = '') {
        const currentTbody = document.querySelector('#books-table tbody');
        if (!currentTbody) {
            console.error('Could not find table body when showing no results');
            return;
        }
        
        currentTbody.innerHTML = `
            <tr class="no-results-row">
                <td colspan="5" class="text-center py-3">
                    <div class="alert alert-info mb-0">
                        No books found${searchTerm ? ` matching "${searchTerm}"` : ''}
                    </div>
                </td>
            </tr>
        `;
    },

    /**
     * @param {HTMLElement} tbody - Table body element
     * @param {string} searchTerm - The search term
     * @param {string} errorMessage - Error message
     */
    showErrorMessage(tbody, searchTerm, errorMessage) {
        const currentTbody = document.querySelector('#books-table tbody');
        if (!currentTbody) {
            console.error('Could not find table body when showing error');
            return;
        }
        
        currentTbody.innerHTML = `
            <tr class="error-row">
                <td colspan="5" class="text-center py-3">
                    <div class="alert alert-danger mb-0">
                        Error searching for "${searchTerm}": ${errorMessage}
                    </div>
                </td>
            </tr>
        `;
    }
};