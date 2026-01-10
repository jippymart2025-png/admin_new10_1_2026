/**
 * Activity Logger - Reusable JavaScript module for logging user activities
 * 
 * Usage:
 * 1. Include this file in your Blade template
 * 2. Call logActivity(module, action, description) from anywhere in your JavaScript
 */

// Function to log activity
function logActivity(module, action, description) {
    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!token) {
        console.error('CSRF token not found. Activity logging disabled.');
        return;
    }
    
    $.ajax({
        url: '/api/activity-logs/log',
        method: 'POST',
        data: {
            module: module,
            action: action,
            description: description,
            _token: token
        },
        success: function(response) {
            if (!response.success) {
                console.error('Failed to log activity:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error logging activity:', error);
        }
    });
}

// Function to log activity with additional data
function logActivityWithData(module, action, description, additionalData = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!token) {
        console.error('CSRF token not found. Activity logging disabled.');
        return;
    }
    
    const data = {
        module: module,
        action: action,
        description: description,
        _token: token,
        ...additionalData
    };
    
    $.ajax({
        url: '/api/activity-logs/log',
        method: 'POST',
        data: data,
        success: function(response) {
            if (!response.success) {
                console.error('Failed to log activity:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error logging activity:', error);
        }
    });
}

// Auto-log page views
function logPageView(module) {
    const pageTitle = document.title || 'Unknown Page';
    logActivity(module, 'viewed', `Viewed page: ${pageTitle}`);
}

// Log form submissions
function logFormSubmission(module, formId, action = 'submitted') {
    const form = document.getElementById(formId);
    if (form) {
        const formName = form.getAttribute('name') || formId;
        logActivity(module, action, `Form ${action}: ${formName}`);
    }
}

// Log button clicks
function logButtonClick(module, buttonText, action = 'clicked') {
    logActivity(module, action, `Button ${action}: ${buttonText}`);
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        logActivity,
        logActivityWithData,
        logPageView,
        logFormSubmission,
        logButtonClick
    };
}
