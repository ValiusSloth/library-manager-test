import { Validator } from './validator.js';
import { Ajax } from './ajax.js';
import { UI } from './ui.js';

export const BookForm = {
    /**
     * @param {string} [formSelector='form[name="book"]'] - Selector for the book form
     */
    init(formSelector = 'form[name="book"]') {
        const form = document.querySelector(formSelector);
        if (!form) return;
        
        this.setupValidation(form);
        this.setupAjaxSubmission(form);
    },
    
    /**
     * @param {HTMLFormElement} form - The form element
     */
    setupValidation(form) {
        form.addEventListener('submit', (event) => {
            let isValid = true;
            
            const titleInput = form.querySelector('#book_title');
            if (titleInput) {
                const titleResult = Validator.required(titleInput.value, 'Title cannot be empty');
                if (titleResult !== true) {
                    Validator.showError(titleInput, titleResult);
                    isValid = false;
                } else {
                    Validator.clearError(titleInput);
                }
            }
            
            const authorInput = form.querySelector('#book_author');
            if (authorInput) {
                const authorResult = Validator.required(authorInput.value, 'Author cannot be empty');
                if (authorResult !== true) {
                    Validator.showError(authorInput, authorResult);
                    isValid = false;
                } else {
                    Validator.clearError(authorInput);
                }
            }
            
            const isbnInput = form.querySelector('#book_isbn');
            if (isbnInput) {
                const isbnValue = isbnInput.value.trim();
                const isbnPattern = /^(?:\d[- ]?){9}[\dXx]$|^(?:\d[- ]?){13}$/;
                
                const isbnRequiredResult = Validator.required(isbnValue, 'ISBN cannot be empty');
                if (isbnRequiredResult !== true) {
                    Validator.showError(isbnInput, isbnRequiredResult);
                    isValid = false;
                } 
                else if (isbnValue) {
                    const isbnPatternResult = Validator.pattern(
                        isbnValue.replace(/[- ]/g, ''), 
                        isbnPattern,
                        'Please enter a valid ISBN'
                    );
                    
                    if (isbnPatternResult !== true) {
                        Validator.showError(isbnInput, isbnPatternResult);
                        isValid = false;
                    } else {
                        Validator.clearError(isbnInput);
                    }
                }
            }
            
            const publishedInput = form.querySelector('#book_published');
            if (publishedInput && publishedInput.value) {
                const dateResult = Validator.notFutureDate(publishedInput.value);
                if (dateResult !== true) {
                    Validator.showError(publishedInput, dateResult);
                    isValid = false;
                } else {
                    Validator.clearError(publishedInput);
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    },
    
    /**
     * @param {HTMLFormElement} form - The form element
     */
    setupAjaxSubmission(form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            
            Ajax.submitForm(form, 
                (data) => {
                    if (data.success) {
                        UI.showAlert('Book saved successfully!', 'success');
                        
                        if (window.location.href.includes('/books/new')) {
                            form.reset();
                        } else {
                            setTimeout(() => {
                                window.location.href = '/books/';
                            }, 10000);
                        }
                    } else {
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const input = form.querySelector(`#book_${field}`);
                                if (input) {
                                    Validator.showError(input, data.errors[field]);
                                }
                            });
                        }
                        UI.showAlert('There were errors in your submission. Please check the form.', 'danger');
                    }
                },
                (error) => {
                    UI.showAlert('An error occurred. Please try again.', 'danger');
                }
            );
        });
    }
};