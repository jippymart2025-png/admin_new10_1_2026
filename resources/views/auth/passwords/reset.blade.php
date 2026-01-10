<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Restaurant Admin') }} — {{ __('Reset Password') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #0f172a;
        }
        .reset-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 18px;
            padding: 40px 36px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
        }
        .reset-card h1 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 600;
            color: #0f172a;
        }
        .reset-card p {
            margin: 0 0 32px;
            color: #475569;
            line-height: 1.5;
        }
        label {
            display: block;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
            color: #0f172a;
        }
        .input-group {
            margin-bottom: 22px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #cbd5f5;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .error-text {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }
        .submit-btn {
            width: 100%;
            padding: 14px 16px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb, #4338ca);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 30px -12px rgba(37, 99, 235, 0.8);
        }
        .brand {
            text-align: center;
            font-size: 14px;
            color: #94a3b8;
            margin-top: 24px;
        }
    </style>
</head>
<body>
<div class="reset-card">
    <h1>{{ __('Reset Password') }}</h1>
    <p>{{ __('Choose a new password to access your restaurant account again.') }}</p>
    <div id="status-banner" style="display:none;border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:14px;font-weight:500;"></div>
    <form id="restaurant-reset-form">
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="input-group">
            <label for="email">{{ __('Email Address') }}</label>
            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
            @error('email')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group">
            <label for="password-confirm">{{ __('Confirm Password') }}</label>
            <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button type="submit" class="submit-btn" id="reset-submit">
            {{ __('Update Password') }}
        </button>
        <input type="hidden" name="email_hidden" value="{{ $email ?? old('email') }}">
    </form>
    <div class="brand">{{ config('app.name', 'Restaurant Admin') }}</div>
</div>
<script>
    (function () {
        const form = document.getElementById('restaurant-reset-form');
        const statusBanner = document.getElementById('status-banner');
        const submitBtn = document.getElementById('reset-submit');

        const showBanner = (message, isError = false) => {
            statusBanner.style.display = 'block';
            statusBanner.style.backgroundColor = isError ? '#fee2e2' : '#dcfce7';
            statusBanner.style.color = isError ? '#b91c1c' : '#15803d';
            statusBanner.innerText = message;
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            showBanner('{{ __("Processing request...") }}');
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.65';

            const payload = {
                email: form.email.value,
                token: form.token.value,
                password: form.password.value,
                password_confirmation: form['password_confirmation'].value
            };

            try {
                const response = await fetch('{{ url('/api/restaurant/reset-password') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    showBanner(data.message || '{{ __("Password updated successfully.") }}');
                    form.reset();
                } else {
                    showBanner(data.message || '{{ __("Unable to update password. Please try again.") }}', true);
                }
            } catch (error) {
                showBanner('{{ __("Something went wrong. Please retry in a moment.") }}', true);
            } finally {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            }
        });
    })();
</script>
</body>
</html>
