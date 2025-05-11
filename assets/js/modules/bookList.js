import { Ajax } from './ajax.js';
import { UI } from './ui.js';

export const BookList = {
    /**
     * @param {string} [containerSelector='#books-container'] - Books container selector
     * @param {string} [paginationSelector='#pagination-container'] - Pagination container selector
     * @param {string} [pageSizeSelector='#page-size'] - Page size selector
     */
    init(containerSelector = '#books-container', paginationSelector = '#pagination-container', pageSizeSelector = '#page-size') {
        const booksContainer = document.querySelector(containerSelector);
        const paginationContainer = document.querySelector(paginationSelector);
        const pageSizeSelect = document.querySelector(pageSizeSelector);
        
        if (!booksContainer || !paginationContainer) return;
        
        this.setupPagination(booksContainer, paginationContainer);
        
        if (pageSizeSelect) {
            this.setupPageSizeChange(booksContainer, paginationContainer, pageSizeSelect);
        }
        
        this.setupDeleteButtons();
    },
    
    /**
     * @param {HTMLElement} booksContainer - Container for book list
     * @param {HTMLElement} paginationContainer - Container for pagination
     */
    setupPagination(booksContainer, paginationContainer) {
        paginationContainer.addEventListener('click', (event) => {
            const target = event.target.closest('.page-link');
            
            if (target && !target.parentElement.classList.contains('disabled') && !target.parentElement.classList.contains('active')) {
                event.preventDefault();
                
                const page = target.getAttribute('data-page');
                const limit = document.getElementById('page-size')?.value || 10;
                
                this.loadBooks(booksContainer, paginationContainer, page, limit);
            }
        });
    },
    
    /**
     * @param {HTMLElement} booksContainer - Container for book list
     * @param {HTMLElement} paginationContainer - Container for pagination
     * @param {HTMLElement} pageSizeSelect - Page size select element
     */
    setupPageSizeChange(booksContainer, paginationContainer, pageSizeSelect) {
        pageSizeSelect.addEventListener('change', () => {
            const page = 1;
            const limit = pageSizeSelect.value;
            
            this.loadBooks(booksContainer, paginationContainer, page, limit);
        });
    },
    
    /**
     * @param {HTMLElement} booksContainer - Container for book list
     * @param {HTMLElement} paginationContainer - Container for pagination
     * @param {number} page - Page number
     * @param {number} limit - Number of items per page
     */
    loadBooks(booksContainer, paginationContainer, page, limit) {
        UI.showLoading(booksContainer);
        
        Ajax.fetch(`/books/list?page=${page}&limit=${limit}&ajax=1`)
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                const newBooks = tempDiv.querySelector('#books-container');
                if (newBooks) {
                    booksContainer.innerHTML = newBooks.innerHTML;
                }
                
                const newPagination = tempDiv.querySelector('#pagination-container');
                if (newPagination) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                }
                
                const url = new URL(window.location);
                url.searchParams.set('page', page);
                url.searchParams.set('limit', limit);
                window.history.pushState({}, '', url);
                
                this.setupDeleteButtons();
                
                booksContainer.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                console.error('Error loading books:', error);
                booksContainer.innerHTML = `<div class="alert alert-danger">An error occurred. Please try again.</div>`;
            });
    },
    
    setupDeleteButtons() {
        document.addEventListener('click', (event) => {
            const deleteButton = event.target.closest('.delete-book-btn');
            
            if (deleteButton) {
                event.preventDefault();
                
                if (confirm('Are you sure you want to delete this book?')) {
                    const form = deleteButton.closest('form');
                    const formData = new FormData(form);
                    
                    Ajax.fetch(form.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(data => {
                        if (data.success) {
                            const currentPage = document.querySelector('.pagination .active .page-link')?.getAttribute('data-page') || 1;
                            const limit = document.getElementById('page-size')?.value || 10;
                            const booksContainer = document.getElementById('books-container');
                            const paginationContainer = document.getElementById('pagination-container');
                            
                            if (booksContainer && paginationContainer) {
                                this.loadBooks(booksContainer, paginationContainer, currentPage, limit);
                                UI.showAlert('Book deleted successfully!', 'success');
                            }
                        } else {
                            UI.showAlert('Failed to delete book.', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting book:', error);
                        UI.showAlert('An error occurred. Please try again.', 'danger');
                    });
                }
            }
        }, { passive: false });
    }
};