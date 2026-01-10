/**
 * Global Activity Logger
 * Provides logActivity function for all pages
 */

// Global logActivity function
window.logActivity = function(module, action, description) {
    console.log('🔍 logActivity called with:', { module, action, description });
    
    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    console.log('🔍 CSRF Token found:', token ? 'YES' : 'NO');
    
    // Prepare data object
    const data = {
        module: module,
        action: action,
        description: description
    };
    
    // Add CSRF token if available
    if (token) {
        data._token = token;
    }
    
    console.log('🔍 Sending AJAX request to /api/activity-logs/log');
    
    // Return a Promise so the calling code can await it
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/api/activity-logs/log',
            method: 'POST',
            data: data,
            success: function(response) {
                console.log('🔍 AJAX Success Response:', response);
                if (!response.success) {
                    console.error('Failed to log activity:', response.message);
                    reject(new Error(response.message));
                } else {
                    console.log('✅ Activity logged successfully:', module, action, description);
                    resolve(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Error logging activity:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                console.error('Status Code:', xhr.status);
                reject(new Error(error || 'Unknown error'));
            }
        });
    });
};

// Log when the script loads
console.log('Global Activity Logger loaded successfully');

// Add a test function for debugging
window.testLogActivity = function() {
    console.log('🧪 Testing logActivity function...');
    if (typeof logActivity === 'function') {
        logActivity('test', 'test_action', 'Test from testLogActivity function')
            .then(response => {
                console.log('✅ testLogActivity: logActivity function is available and called successfully');
            })
            .catch(error => {
                console.error('❌ testLogActivity: logActivity function failed:', error);
            });
    } else {
        console.error('❌ testLogActivity: logActivity function is not available');
    }
};

// Auto-test on page load (for debugging)
setTimeout(function() {
    console.log('🔍 Auto-testing logActivity availability...');
    if (typeof logActivity === 'function') {
        console.log('✅ logActivity function is available globally');
    } else {
        console.error('❌ logActivity function is NOT available globally');
    }
}, 1000);
