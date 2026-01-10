// jQuery Validation Plugin - Custom Configuration
// This file is for form validation, not Firebase configuration

// Remove any existing Firebase config to prevent conflicts
if (typeof firebase !== 'undefined' && firebase.apps.length > 0) {
    console.log('✅ Firebase already initialized in main layout');
} else {
    console.log('⚠️ Firebase not yet initialized - will be handled by main layout');
}

// Add any custom validation rules here if needed
// Example:
// $.validator.addMethod("customRule", function(value, element) {
//     return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
// }, "Please enter a valid value"); 