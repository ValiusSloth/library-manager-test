export const Validator = {
    /**
     * @param {HTMLElement} input - The input element
     * @param {string} message - Error message to display
     */
    showError(input, message) {
        this.clearError(input);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        
        input.classList.add('is-invalid');
        input.parentNode.appendChild(errorDiv);
    },
    
    /**
     * @param {HTMLElement} input - The input element
     */
    clearError(input) {
        input.classList.remove('is-invalid');
        
        const existingError = input.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    },
    
    /**
     * @param {string} value - Value to check
     * @param {string} [errorMessage] - Custom error message
     * @returns {boolean|string} true if valid, error message if invalid
     */
    required(value, errorMessage = 'This field is required') {
        return value.trim() !== '' ? true : errorMessage;
    },
    
    /**
     * @param {string} value - Value to check
     * @param {RegExp} pattern - Regular expression to match
     * @param {string} [errorMessage] - Custom error message
     * @returns {boolean|string} true if valid, error message if invalid
     */
    pattern(value, pattern, errorMessage = 'Invalid format') {
        return pattern.test(value) ? true : errorMessage;
    },
    
    /**
     * @param {string} value - Value to check
     * @param {string} [errorMessage] - Custom error message
     * @returns {boolean|string} true if valid, error message if invalid
     */
    notFutureDate(value, errorMessage = 'Date cannot be in the future') {
        if (!value) return true;
        
        const inputDate = new Date(value);
        const currentDate = new Date();
        
        return inputDate <= currentDate ? true : errorMessage;
    }
};