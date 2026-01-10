<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title id="app_name"><?php echo @$_COOKIE['meta_title']; ?></title>
    <link rel="icon" id="favicon" type="image/x-icon" href="<?php echo str_replace('images/','images%2F',@$_COOKIE['favicon']); ?>">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <!-- Styles -->
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @yield('style')
</head>
<body>
<style type="text/css">
    .form-group.default-admin {
        padding: 10px;
        font-size: 14px;
        color: #000;
        font-weight: 600;
        border-radius: 10px;
        box-shadow: 0 0px 6px 0px rgba(0, 0, 0, 0.5);
        margin: 20px 10px 10px 10px;
    }
    .form-group.default-admin .crediantials-field {
        position: relative;
        padding-right: 15px;
        text-align: left;
        padding-top: 5px;
        padding-bottom: 5px;
    }
    .form-group.default-admin .crediantials-field > a {
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        margin: auto;
        height: 20px;
    }
    .login-register {
        background-color: #FF683A;
    }
    <?php if(isset($_COOKIE['admin_panel_color'])){ ?>
    a, a:hover, a:focus {
        color: <?php echo $_COOKIE['admin_panel_color']; ?>;
    }
    .btn-primary, .btn-primary.disabled, .btn-primary:hover, .btn-primary.disabled:hover {
        background: <?php echo $_COOKIE['admin_panel_color']; ?>;
        border: 1px solid<?php echo $_COOKIE['admin_panel_color']; ?>;
    }
    [type="checkbox"]:checked + label::before {
        border-right: 2px solid<?php echo $_COOKIE['admin_panel_color']; ?>;
        border-bottom: 2px solid<?php echo $_COOKIE['admin_panel_color']; ?>;
    }
    .form-material .form-control, .form-material .form-control.focus, .form-material .form-control:focus {
        background-image: linear-gradient(<?php echo $_COOKIE['admin_panel_color']; ?>, <?php echo $_COOKIE['admin_panel_color']; ?>), linear-gradient(rgba(120, 130, 140, 0.13), rgba(120, 130, 140, 0.13));
    }
    .btn-primary.active, .btn-primary:active, .btn-primary:focus, .btn-primary.disabled.active, .btn-primary.disabled:active, .btn-primary.disabled:focus, .btn-primary.active.focus, .btn-primary.active:focus, .btn-primary.active:hover, .btn-primary.focus:active, .btn-primary:active:focus, .btn-primary:active:hover, .open > .dropdown-toggle.btn-primary.focus, .open > .dropdown-toggle.btn-primary:focus, .open > .dropdown-toggle.btn-primary:hover, .btn-primary.focus, .btn-primary:focus, .btn-primary:not(:disabled):not(.disabled).active:focus, .btn-primary:not(:disabled):not(.disabled):active:focus, .show > .btn-primary.dropdown-toggle:focus {
        background: <?php echo $_COOKIE['admin_panel_color']; ?>;
        border-color: <?php echo $_COOKIE['admin_panel_color']; ?>;
        box-shadow: 0 0 0 0.2rem<?php echo $_COOKIE['admin_panel_color']; ?>;
    }
    .login-register {
        background-color: <?php echo $_COOKIE['admin_panel_color']; ?>;
    }
    <?php } ?>
</style>
<section id="wrapper">
    <div class="login-register">
        <div class="login-logo text-center py-3">
            <a href="#" style="display: inline-block;background: #fff;padding: 10px;border-radius: 5px;"><img
                        src="{{ asset('images/logo_web.png') }}"> </a>
        </div>
        <div class="login-box card" style="margin-bottom:0%;">
            <div class="card-body">
                @if(count($errors) > 0)
                    @foreach( $errors->all() as $message )
                        <div class="alert alert-danger display-hide">
                            <button class="close" data-close="alert"></button>
                            <span>{{ $message }}</span>
                        </div>
                    @endforeach
                @endif
                <form class="form-horizontal form-material" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="box-title m-b-20">{{ __('Login') }}</div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control" placeholder="{{ __('Email Address') }}" id="email" type="email"
                                   class="form-control @error('email') is-invalid @enderror" name="email"
                                   value="{{ old('email') }}" required autocomplete="email" autofocus></div>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input id="password" placeholder="{{ __('Password') }}" type="password"
                                   class="form-control @error('password') is-invalid @enderror" name="password" required
                                   autocomplete="current-password"></div>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                        @enderror
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember')
                            ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                    </div>
                    <div class="form-group text-center m-t-20 mb-0">
                        <div class="col-xs-12">
                            <button type="submit"
                                    class="btn btn-dark btn-lg btn-block text-uppercase waves-effect waves-light btn btn-primary">
                                {{ __('Login') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script src="{{asset('assets/plugins/jquery/jquery.min.js')}}"></script>
<!-- Firebase 9.0.0 Compat SDKs - Same as main layout -->
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-firestore-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-storage-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-database-compat.js"></script>
<script src="https://unpkg.com/geofirestore/dist/geofirestore.js"></script>
<script src="https://cdn.firebase.com/libs/geofire/5.0.1/geofire.min.js"></script>
<script src="{{ asset('js/crypto-js.js') }}"></script>
<script src="{{ asset('js/jquery.cookie.js') }}"></script>
<script src="{{ asset('js/jquery.validate.js') }}"></script>
<script type="text/javascript">
    // Firebase configuration - Same as main layout
    const firebaseConfig = {
        apiKey: "{{ env('FIREBASE_APIKEY', 'AIzaSyAf_lICoxPh8qKE1QnVkmQYTFJXKkYmRXU') }}",
        authDomain: "{{ env('FIREBASE_AUTH_DOMAIN', 'jippymart-27c08.firebaseapp.com') }}",
        databaseURL: "{{ env('FIREBASE_DATABASE_URL', 'https://jippymart-27c08-default-rtdb.firebaseio.com') }}",
        projectId: "{{ env('FIREBASE_PROJECT_ID', 'jippymart-27c08') }}",
        storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET', 'jippymart-27c08.firebasestorage.app') }}",
        messagingSenderId: "{{ env('FIREBASE_MESSAAGING_SENDER_ID', '592427852800') }}",
        appId: "{{ env('FIREBASE_APP_ID', '1:592427852800:web:f74df8ceb2a4b597d1a4e5') }}",
        measurementId: "{{ env('FIREBASE_MEASUREMENT_ID', 'G-ZYBQYPZWCF') }}"
    };

    // Initialize Firebase only if not already initialized
    if (!firebase.apps.length) {
        try {
            firebase.initializeApp(firebaseConfig);
            console.log('✅ Firebase initialized successfully');
            
            // Initialize Firestore database globally
            window.database = firebase.firestore();
            window.storage = firebase.storage();
            window.auth = firebase.auth();
            
            console.log('✅ Firebase services initialized');
        } catch (error) {
            console.error('❌ Firebase initialization error:', error);
        }
    } else {
        console.log('✅ Firebase already initialized in main layout');
    }

    function copyToClipboard(text) {
        const elem = document.createElement('textarea');
        elem.value = text;
        document.body.appendChild(elem);
        elem.select();
        document.execCommand('copy');
        document.body.removeChild(elem);
    }
    
    var database = firebase.firestore();
    var ref = database.collection('settings').doc("globalSettings");
    $(document).ready(function () {
        ref.get().then(async function (snapshots) {
            var globalSettings = snapshots.data();
            setCookie('application_name', globalSettings.applicationName, 365);
            setCookie('meta_title', globalSettings.meta_title, 365);
            setCookie('favicon', globalSettings.favicon, 365);
            admin_panel_color = globalSettings.admin_panel_color;
            setCookie('admin_panel_color', admin_panel_color, 365);
            $('.login-register').css({'background-color': admin_panel_color});
            document.title = globalSettings.meta_title;
            var favicon = '<?php echo @$_COOKIE['favicon'] ?>';
        })
    });
    function setCookie(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }
</script>

<!-- Auto-login script for Admin Impersonation -->
<script>
// Auto-login script for Admin Impersonation
(function() {
    console.log('🔍 Auto-login script started');
    
    // Check URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const impersonationToken = urlParams.get('impersonation_token');
    const restaurantUid = urlParams.get('restaurant_uid');
    const autoLogin = urlParams.get('auto_login');
    
    console.log('🔍 Parameters:', {
        token: !!impersonationToken,
        uid: !!restaurantUid,
        autoLogin: autoLogin
    });
    
    // Only proceed if we have all required parameters
    if (impersonationToken && restaurantUid && autoLogin === 'true') {
        console.log('🔐 Starting auto-login process...');
        
        // Show loading immediately
        showLoading();
        
        // Wait for Firebase to be ready
        setTimeout(function() {
            if (typeof firebase !== 'undefined' && firebase.auth) {
                startAutoLogin();
            } else {
                console.error('❌ Firebase not available');
                showError('Firebase not loaded. Please refresh the page.');
            }
        }, 2000); // Wait for Firebase to be ready
    } else {
        console.log('ℹ️ No impersonation parameters, showing normal login');
    }
    
    function startAutoLogin() {
        console.log('🚀 Starting auto-login...');
        
        const auth = firebase.auth();
        
        // Sign in with custom token
        auth.signInWithCustomToken(impersonationToken)
            .then(function(userCredential) {
                console.log('✅ Login successful!');
                console.log('User UID:', userCredential.user.uid);
                console.log('Expected UID:', restaurantUid);
                
                // Verify UID matches
                if (userCredential.user.uid !== restaurantUid) {
                    throw new Error('UID mismatch - security violation');
                }
                
                // Store impersonation info
                localStorage.setItem('restaurant_impersonation', JSON.stringify({
                    isImpersonated: true,
                    restaurantUid: restaurantUid,
                    impersonatedAt: new Date().toISOString()
                }));
                
                console.log('🔄 Redirecting to dashboard...');
                
                // Redirect to dashboard
                setTimeout(function() {
                    window.location.href = '/dashboard';
                }, 1000);
            })
            .catch(function(error) {
                console.error('❌ Login failed:', error);
                showError('Auto-login failed: ' + error.message);
                
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            });
    }
    
    function showLoading() {
        const loading = document.createElement('div');
        loading.id = 'auto-login-loading';
        loading.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; justify-content: center; align-items: center;">
                <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; max-width: 400px;">
                    <div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                    <h3>🔐 Admin Impersonation</h3>
                    <p>Logging you in as restaurant owner...</p>
                    <p style="font-size: 12px; color: #666;">Please wait while we authenticate you.</p>
                </div>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `;
        document.body.appendChild(loading);
    }
    
    function showError(message) {
        // Remove loading first
        const loading = document.getElementById('auto-login-loading');
        if (loading) {
            loading.remove();
        }
        
        const error = document.createElement('div');
        error.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <strong>❌ Auto-login Failed:</strong><br>
                ${message}
                <button onclick="this.parentElement.parentElement.remove()" style="float: right; background: none; border: none; font-size: 18px; cursor: pointer; margin-left: 10px;">&times;</button>
            </div>
        `;
        document.body.appendChild(error);
    }
})();
</script>
</body>
</html>
