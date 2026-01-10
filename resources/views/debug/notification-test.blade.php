<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firebase Notification Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>🔥 Firebase Push Notification Test</h3>
                        <p class="mb-0">Test your Firebase push notification setup</p>
                    </div>
                    <div class="card-body">
                        <div id="status" class="alert alert-info">
                            Ready to test notifications
                        </div>
                        
                        <form id="notificationForm">
                            <div class="mb-3">
                                <label for="role" class="form-label">Send To:</label>
                                <select class="form-select" id="role" required>
                                    <option value="">Select recipient type</option>
                                    <option value="vendor">Vendors (restaurant topic)</option>
                                    <option value="customer">Customers (customer topic)</option>
                                    <option value="driver">Drivers (driver topic)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject:</label>
                                <input type="text" class="form-control" id="subject" value="Test Notification" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message:</label>
                                <textarea class="form-control" id="message" rows="3" required>This is a test notification from your admin panel. If you receive this, your Firebase setup is working correctly!</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                Send Test Notification
                            </button>
                            
                            <a href="/notification" class="btn btn-secondary ms-2">Back to Notifications</a>
                        </form>
                        
                        <hr>
                        
                        <div class="mt-4">
                            <h5>📋 Debug Information</h5>
                            <ul class="list-unstyled">
                                <li><strong>Project ID:</strong> jippymart-27c08</li>
                                <li><strong>Service Account:</strong> storage/app/firebase/serviceAccount.json</li>
                                <li><strong>Topics:</strong> restaurant, customer, driver</li>
                                <li><strong>API Endpoint:</strong> /broadcastnotification</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4">
                            <h5>🔍 Common Issues & Solutions</h5>
                            <div class="accordion" id="troubleshooting">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                            No devices receiving notifications
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#troubleshooting">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Ensure your mobile apps are subscribed to the correct topics</li>
                                                <li>Check if FCM tokens are valid and not expired</li>
                                                <li>Verify app is in foreground or has proper background notification handling</li>
                                                <li>Test with a real device, not simulator</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                            Authentication errors
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#troubleshooting">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Verify serviceAccount.json file exists and is valid</li>
                                                <li>Check Firebase project permissions</li>
                                                <li>Ensure FCM API is enabled in Google Cloud Console</li>
                                                <li>Verify project ID matches your Firebase project</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('notificationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const statusDiv = document.getElementById('status');
            const submitBtn = document.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            // Show loading state
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            statusDiv.className = 'alert alert-info';
            statusDiv.textContent = 'Sending notification...';
            
            try {
                const formData = {
                    role: document.getElementById('role').value,
                    subject: document.getElementById('subject').value,
                    message: document.getElementById('message').value,
                    _token: '{{ csrf_token() }}'
                };
                
                const response = await fetch('/broadcastnotification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusDiv.className = 'alert alert-success';
                    statusDiv.innerHTML = `
                        <strong>✅ Success!</strong><br>
                        ${result.message}<br>
                        <small class="text-muted">Response: ${JSON.stringify(result.result, null, 2)}</small>
                    `;
                } else {
                    statusDiv.className = 'alert alert-danger';
                    statusDiv.innerHTML = `
                        <strong>❌ Error!</strong><br>
                        ${result.message}<br>
                        <small class="text-muted">Response: ${JSON.stringify(result.result, null, 2)}</small>
                    `;
                }
            } catch (error) {
                statusDiv.className = 'alert alert-danger';
                statusDiv.innerHTML = `
                    <strong>❌ Network Error!</strong><br>
                    ${error.message}
                `;
            } finally {
                // Hide loading state
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        });
    </script>
</body>
</html>

