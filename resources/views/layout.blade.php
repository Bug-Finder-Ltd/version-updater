<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Application Version Updater | BugFinder</title>
    <!-- BugFinder Favicon -->
    <link rel="icon" type="image/png" href="https://bug-finder.s3.ap-southeast-1.amazonaws.com/assets/v2/logo/favicon-bf.svg">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Animate.css for smooth modal animations -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <style>
        :root {
            --bf-primary: #4f46e5;
            --bf-primary-hover: #4338ca;
            --bf-secondary: #06b6d4;
            --bf-dark: #0f172a;
            --bf-bg: #f8fafc;
            --bf-card-bg: #ffffff;
            --bf-border: #e2e8f0;
        }

        body {
            background-color: var(--bf-bg);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #334155;
            min-height: 100vh;
        }

        .updater-container {
            max-width: 920px;
            margin: 0 auto;
        }

        .updater-card {
            border: 1px solid var(--bf-border);
            border-radius: 20px;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.07);
            background: var(--bf-card-bg);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .updater-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            color: white;
            padding: 32px 36px;
            position: relative;
            overflow: hidden;
        }

        .updater-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(99,102,241,0.25) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .version-badge {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 50rem;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--bf-dark);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--bf-primary);
            font-size: 1.2rem;
        }

        .req-card {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 14px 16px;
            transition: all 0.2s ease;
        }

        .req-card:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }

        .btn-bf-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: white;
            border: none;
            font-weight: 600;
            border-radius: 12px;
            padding: 12px 24px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            transition: all 0.25s ease;
        }

        .btn-bf-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
            color: white;
        }

        .btn-bf-primary:disabled {
            opacity: 0.95 !important;
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            cursor: wait !important;
            box-shadow: none !important;
            filter: none !important;
        }

        .btn-bf-primary:disabled * {
            opacity: 1 !important;
            color: #ffffff !important;
            filter: none !important;
        }

        .btn-bf-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            font-weight: 700;
            border-radius: 14px;
            padding: 14px 28px;
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
            transition: all 0.25s ease;
        }

        .btn-bf-success:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.45);
            color: white;
        }

        .step-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        .step-pending { background-color: #f1f5f9; color: #94a3b8; }
        .step-active { background-color: #e0e7ff; color: #4f46e5; border: 2px solid #818cf8; }
        .step-success { background-color: #d1fae5; color: #059669; }
        .step-error { background-color: #fee2e2; color: #dc2626; }

        .progress-bar-custom {
            height: 12px;
            border-radius: 50rem;
            background-color: #e2e8f0;
            overflow: hidden;
        }

        .changelog-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            max-height: 220px;
            overflow-y: auto;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header-custom {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 24px 28px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="updater-container">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle & jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @stack('scripts')
</body>
</html>
