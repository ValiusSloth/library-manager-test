export const Ajax = {
    /**
     * @param {string} url - URL to fetch from
     * @param {Object} [options] - Fetch options
     * @returns {Promise} - Fetch promise
     */
    fetch(url, options = {}) {
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {})
        };
        
        return fetch(url, { ...options, headers })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const contentType = response.headers.get('Content-Type') || '';
                if (contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text();
                }
            });
    },
    
    /**
     * @param {HTMLFormElement} form - Form to submit
     * @param {Function} [onSuccess] - Success callback
     * @param {Function} [onError] - Error callback
     * @param {Object} [options] - Additional options
     * @returns {Promise} - Fetch promise
     */
    submitForm(form, onSuccess, onError, options = {}) {
        const formData = new FormData(form);
        
        formData.append('ajax', '1');
        
        const submitButton = form.querySelector('button[type="submit"]');
        let originalButtonText = '';
        
        if (submitButton && !options.noLoadingState) {
            originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            submitButton.disabled = true;
        }
        
        return this.fetch(form.action, {
            method: form.method || 'POST',
            body: formData
        })
        .then(data => {
            if (onSuccess) onSuccess(data);
            return data;
        })
        .catch(error => {
            console.error('Form submission error:', error);
            if (onError) onError(error);
            return Promise.reject(error);
        })
        .finally(() => {
            if (submitButton && !options.noLoadingState) {
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            }
        });
    }
};