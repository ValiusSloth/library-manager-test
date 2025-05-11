export const UI = {
    /**
     * @param {HTMLElement} container - Container to show spinner in
     */
    showLoading(container) {
        container.innerHTML = `
            <div class="d-flex justify-content-center my-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
    },
    
    /**
     * @param {string} message - Message to display
     * @param {string} [type='info'] - Alert type (success, danger, warning, info)
     * @param {string} [containerId='alert-container'] - ID of container to insert alert
     * @param {boolean} [autoDismiss=true] - Whether to auto-dismiss the alert
     */
    showAlert(message, type = 'info', containerId = 'alert-container', autoDismiss = true) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        fetch(`/books/alert?type=${type}&message=${encodeURIComponent(message)}`)
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                
                if (autoDismiss) {
                    setTimeout(() => {
                        const alerts = container.querySelectorAll('.alert');
                        alerts.forEach(alert => {
                            alert.classList.remove('show');
                            setTimeout(() => alert.remove(), 150);
                        });
                    }, 5000);
                }
            })
            .catch(error => console.error('Error showing alert:', error));
    }
};