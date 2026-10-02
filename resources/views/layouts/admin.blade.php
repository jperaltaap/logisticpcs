<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Control') — {{ config('app.name', 'LogisticPCS') }}</title>

    <!-- Favicon del Sistema -->
    <link rel="icon" type="image/webp" href="{{ \App\Models\EmpresaConfig::instancia()->icono_url }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Modern Clean Enterprise & High-Contrast Style System -->
    <style>
        :root {
            /* Theme Tokens */
            --admin-primary: #0284c7;
            --admin-primary-gradient: #0369a1;
            --admin-primary-rgb: 2, 132, 199;
            --admin-primary-dark: #0f2b48;
            --admin-sidebar-width: 270px;
            --admin-sidebar-compact: 80px;

            /* Light Mode Variables (Crisp High Contrast, Solid SaaS Surfaces) */
            --body-bg: #f8fafc;
            --card-bg: #ffffff;
            --card-border: #cbd5e1;
            --card-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.06), 0 1px 2px -1px rgba(15, 23, 42, 0.04);
            --card-shadow-hover: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);

            --header-bg: #ffffff;
            --header-border: #e2e8f0;

            --dropdown-bg: #ffffff;
            --dropdown-border: #e2e8f0;
            --dropdown-header-bg: #f8fafc;
            --dropdown-hover: #f1f5f9;
            --dropdown-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.06);

            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;

            --input-bg: #f8fafc;
            --input-border: #cbd5e1;

            --body-mesh-gradient: 
                radial-gradient(at 0% 0%, rgba(var(--admin-primary-rgb), 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.04) 0px, transparent 50%),
                #f8fafc;
        }

        /* Dark Mode High-Contrast Overrides */
        [data-bs-theme="dark"] {
            --body-bg: #0b1320;
            --card-bg: #111c2e;
            --card-border: #334155;
            --card-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.35);
            --card-shadow-hover: 0 12px 28px -4px rgba(0, 0, 0, 0.5);

            --header-bg: #0d1726;
            --header-border: #1e293b;

            --dropdown-bg: #162235;
            --dropdown-border: #273549;
            --dropdown-header-bg: #0f1826;
            --dropdown-hover: #1e2e45;
            --dropdown-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.6);

            --text-heading: #f8fafc;
            --text-body: #cbd5e1;
            --text-muted: #94a3b8;

            --input-bg: #162235;
            --input-border: #273549;

            --body-mesh-gradient: 
                radial-gradient(at 0% 0%, rgba(var(--admin-primary-rgb), 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.9) 0px, transparent 50%),
                #0b1320;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--body-mesh-gradient) fixed;
            background-color: var(--body-bg);
            color: var(--text-body);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            min-height: 100vh;
            letter-spacing: -0.15px;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* Discreet Modern Scrollbars */
        .sidebar-menu {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.16) transparent;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.16);
            border-radius: 10px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.35);
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.35) transparent;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.35);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(148, 163, 184, 0.55);
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        /* Scoped High-Contrast Content Typography */
        .text-heading {
            color: var(--text-heading) !important;
        }

        .app-main h1, .app-main h2, .app-main h3, .app-main h4, .app-main h5, .app-main h6 {
            color: var(--text-heading);
            letter-spacing: -0.3px;
        }

        .app-main p, .app-main .text-body {
            color: var(--text-body);
        }

        .app-main .text-muted {
            color: var(--text-muted) !important;
        }

        /* Enterprise Cards: Permanent Noticeable Border + Enhanced Hover Resalte */
        .glass-card, .admin-card, .card {
            background-color: var(--card-bg) !important;
            border: 1.5px solid var(--card-border) !important;
            box-shadow: var(--card-shadow);
            border-radius: 0.95rem;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.2s ease;
            position: relative;
        }

        .glass-card:hover, .admin-card:hover, .card:hover {
            transform: translateY(-5px) scale(1.008);
            box-shadow: 0 16px 32px -4px rgba(15, 23, 42, 0.14), 
                        0 0 0 1.5px var(--admin-primary), 
                        0 6px 18px -2px rgba(var(--admin-primary-rgb), 0.3) !important;
            border-color: var(--admin-primary) !important;
            z-index: 3;
        }

        [data-bs-theme="dark"] .glass-card:hover, 
        [data-bs-theme="dark"] .admin-card:hover, 
        [data-bs-theme="dark"] .card:hover {
            box-shadow: 0 20px 38px -6px rgba(0, 0, 0, 0.65), 
                        0 0 0 1.5px var(--admin-primary), 
                        0 8px 24px -2px rgba(var(--admin-primary-rgb), 0.45) !important;
            border-color: var(--admin-primary) !important;
        }

        /* KPI Colored Cards (Text-White) with permanent visible border & vibrant hover pop */
        .kpi-card, .card.text-white {
            border: 1.5px solid rgba(255, 255, 255, 0.35) !important;
            border-radius: 16px !important;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.2s ease !important;
        }

        .kpi-card:hover, .card.text-white:hover {
            transform: translateY(-6px) scale(1.02) !important;
            box-shadow: 0 20px 38px -5px rgba(0, 0, 0, 0.4), 
                        0 0 0 2px rgba(255, 255, 255, 0.75),
                        0 8px 22px rgba(0, 0, 0, 0.25) !important;
            border-color: #ffffff !important;
            z-index: 4;
        }

        /* Modal Anti-Flicker & Enterprise Dialog Protection */
        .modal {
            z-index: 1060;
        }

        .modal-backdrop {
            z-index: 1055;
        }

        .modal-dialog {
            transition: transform 0.2s ease-out !important;
        }

        .modal-content,
        .modal-content.admin-card,
        .modal-content.glass-card {
            background-color: var(--card-bg) !important;
            border: 1px solid var(--card-border) !important;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.5) !important;
            border-radius: 1rem !important;
            transform: none !important;
            transition: none !important;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .modal-content:hover,
        .modal-content.admin-card:hover,
        .modal-content.glass-card:hover,
        .modal-dialog:hover,
        .modal-dialog:hover .modal-content,
        .modal-body:hover,
        .modal-header:hover,
        .modal-footer:hover {
            transform: none !important;
            transition: none !important;
            border-color: var(--card-border) !important;
        }

        /* Layout Structure */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Sidebar Styling (High-Contrast Deep Navy Tone) */
        .app-sidebar {
            width: var(--admin-sidebar-width);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            background: #081525 !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #cbd5e1;
        }

        .app-sidebar .sidebar-brand {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            padding: 1.15rem 1.25rem !important;
        }

        .app-sidebar .brand-title {
            color: #ffffff !important;
            font-size: 1.18rem;
            font-weight: 800;
            letter-spacing: -0.2px;
            line-height: 1.25;
            margin-bottom: 0.32rem; /* Clear separation from subtitle */
            display: block;
        }

        .app-sidebar .brand-subtitle {
            color: #94a3b8 !important;
            font-size: 0.66rem;
            letter-spacing: 0.9px;
            font-weight: 600;
            text-transform: uppercase;
            line-height: 1.2;
            opacity: 0.85;
        }

        .app-sidebar .sidebar-user {
            background: rgba(255, 255, 255, 0.03) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        .app-sidebar .sidebar-user-name {
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.88rem;
            line-height: 1.2;
        }

        .app-sidebar .sidebar-user-role-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(16, 185, 129, 0.15) !important;
            color: #34d399 !important;
            border: 1px solid rgba(52, 211, 153, 0.35) !important;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
        }

        .app-sidebar .nav-header {
            color: #64748b !important;
            font-weight: 700;
            font-size: 0.68rem;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .app-sidebar .nav-link {
            color: #94a3b8 !important;
            font-weight: 500;
            border-radius: 0.55rem;
            padding: 0.62rem 0.85rem;
            transition: all 0.2s ease;
        }

        .app-sidebar .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            transform: translateX(3px);
        }

        /* Active Navigation item styling with vibrant gradient & glow */
        .app-sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%) !important;
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 4px 18px 0 rgba(var(--admin-primary-rgb), 0.45);
        }

        .app-sidebar .hover-link {
            color: #94a3b8 !important;
        }

        .app-sidebar .hover-link:hover {
            color: #ffffff !important;
        }

        /* Main Content Wrapper */
        .app-main {
            flex-grow: 1;
            margin-left: var(--admin-sidebar-width);
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Solid Header Navbar */
        .app-header {
            background: var(--header-bg) !important;
            border-bottom: 1px solid var(--header-border) !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        /* Breadcrumb Bar */
        .app-content-header {
            background: transparent !important;
            border-bottom: 1px solid var(--card-border);
        }

        /* Sidebar Compact & Collapsed States */
        body.sidebar-collapsed .app-sidebar {
            margin-left: calc(-1 * var(--admin-sidebar-width));
        }

        body.sidebar-collapsed .app-main {
            margin-left: 0;
        }

        /* Mobile Overlay */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1035;
        }

        @media (max-width: 991.98px) {
            .app-sidebar {
                margin-left: calc(-1 * var(--admin-sidebar-width));
            }
            .app-main {
                margin-left: 0 !important;
            }
            body.sidebar-open .app-sidebar {
                margin-left: 0;
            }
            body.sidebar-open .sidebar-backdrop {
                display: block;
            }
        }

        /* Pulsing Status Indicator */
        .status-indicator-online {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Color Palette Picker */
        .color-picker-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 3px solid transparent;
            cursor: pointer;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }

        .color-picker-btn:hover {
            transform: scale(1.15);
        }

        .color-picker-btn.active {
            border-color: #ffffff;
            box-shadow: 0 0 0 2px var(--admin-primary), 0 4px 10px rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
        }

        /* Primary Dynamic Accent Utility Rules */
        .btn-primary {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%) !important;
            border-color: var(--admin-primary) !important;
            color: #ffffff !important;
            box-shadow: 0 3px 10px 0 rgba(var(--admin-primary-rgb), 0.28);
        }

        .btn-primary:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: 0 5px 14px 0 rgba(var(--admin-primary-rgb), 0.38);
            color: #ffffff !important;
        }

        .btn-outline-primary {
            color: var(--admin-primary) !important;
            border-color: var(--admin-primary) !important;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 3px 10px 0 rgba(var(--admin-primary-rgb), 0.28);
        }

        /* Pure solid bg-primary elements (card headers, solid indicators) */
        .bg-primary:not([class*="bg-opacity"]):not([class*="-subtle"]) {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%) !important;
            color: #ffffff !important;
        }

        .text-primary {
            color: var(--admin-primary) !important;
        }

        [data-bs-theme="dark"] .text-primary {
            color: var(--admin-primary-light, #93c5fd) !important;
        }

        .border-primary {
            border-color: var(--admin-primary) !important;
        }

        /* High-Contrast Dynamic Badge System */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            vertical-align: middle;
            line-height: 1.25;
            padding: 0.32em 0.65em;
            border-radius: 6px;
            transition: all 0.15s ease-in-out;
        }

        .badge i, 
        .badge svg {
            font-size: 0.9em;
            line-height: 1;
            display: inline-block;
            vertical-align: -0.05em;
        }

        a.badge {
            text-decoration: none !important;
            cursor: pointer;
        }

        a.badge:hover {
            filter: brightness(0.92);
            transform: translateY(-1px);
        }

        [data-bs-theme="dark"] a.badge:hover {
            filter: brightness(1.25);
        }

        /* Universal Soft Badge Classes & High-Contrast Subtle Support */
        .badge-soft-primary,
        .bg-primary-subtle,
        .badge.bg-primary.bg-opacity-10 {
            background-color: rgba(var(--admin-primary-rgb), 0.12) !important;
            color: var(--admin-primary) !important;
            border: 1px solid rgba(var(--admin-primary-rgb), 0.32) !important;
            box-shadow: none !important;
        }

        .badge-soft-success,
        .bg-success-subtle,
        .badge.bg-success.bg-opacity-10 {
            background-color: rgba(16, 185, 129, 0.12) !important;
            color: #047857 !important;
            border: 1px solid rgba(16, 185, 129, 0.32) !important;
            box-shadow: none !important;
        }

        .badge-soft-info,
        .bg-info-subtle,
        .badge.bg-info.bg-opacity-10 {
            background-color: rgba(6, 182, 212, 0.12) !important;
            color: #0e7490 !important;
            border: 1px solid rgba(6, 182, 212, 0.32) !important;
            box-shadow: none !important;
        }

        .badge-soft-warning,
        .bg-warning-subtle,
        .badge.bg-warning.bg-opacity-10 {
            background-color: rgba(245, 158, 11, 0.14) !important;
            color: #b45309 !important;
            border: 1px solid rgba(245, 158, 11, 0.38) !important;
            box-shadow: none !important;
        }

        .badge-soft-danger,
        .bg-danger-subtle,
        .badge.bg-danger.bg-opacity-10 {
            background-color: rgba(239, 68, 68, 0.12) !important;
            color: #b91c1c !important;
            border: 1px solid rgba(239, 68, 68, 0.32) !important;
            box-shadow: none !important;
        }

        .badge-soft-secondary,
        .bg-secondary-subtle,
        .badge.bg-secondary.bg-opacity-10 {
            background-color: rgba(100, 116, 139, 0.12) !important;
            color: #334155 !important;
            border: 1px solid rgba(100, 116, 139, 0.28) !important;
            box-shadow: none !important;
        }

        /* Dark Mode High-Contrast Vibrant Badges */
        [data-bs-theme="dark"] .badge-soft-primary,
        [data-bs-theme="dark"] .bg-primary-subtle,
        [data-bs-theme="dark"] .badge.bg-primary.bg-opacity-10 {
            background-color: rgba(var(--admin-primary-rgb), 0.22) !important;
            color: #93c5fd !important;
            border-color: rgba(var(--admin-primary-rgb), 0.45) !important;
        }

        [data-bs-theme="dark"] .badge-soft-success,
        [data-bs-theme="dark"] .bg-success-subtle,
        [data-bs-theme="dark"] .badge.bg-success.bg-opacity-10 {
            background-color: rgba(16, 185, 129, 0.2) !important;
            color: #6ee7b7 !important;
            border-color: rgba(16, 185, 129, 0.42) !important;
        }

        [data-bs-theme="dark"] .badge-soft-info,
        [data-bs-theme="dark"] .bg-info-subtle,
        [data-bs-theme="dark"] .badge.bg-info.bg-opacity-10 {
            background-color: rgba(6, 182, 212, 0.2) !important;
            color: #7dd3fc !important;
            border-color: rgba(6, 182, 212, 0.42) !important;
        }

        [data-bs-theme="dark"] .badge-soft-warning,
        [data-bs-theme="dark"] .bg-warning-subtle,
        [data-bs-theme="dark"] .badge.bg-warning.bg-opacity-10 {
            background-color: rgba(245, 158, 11, 0.22) !important;
            color: #fde047 !important;
            border-color: rgba(245, 158, 11, 0.45) !important;
        }

        [data-bs-theme="dark"] .badge-soft-danger,
        [data-bs-theme="dark"] .bg-danger-subtle,
        [data-bs-theme="dark"] .badge.bg-danger.bg-opacity-10 {
            background-color: rgba(239, 68, 68, 0.22) !important;
            color: #fca5a5 !important;
            border-color: rgba(239, 68, 68, 0.45) !important;
        }

        [data-bs-theme="dark"] .badge-soft-secondary,
        [data-bs-theme="dark"] .bg-secondary-subtle,
        [data-bs-theme="dark"] .badge.bg-secondary.bg-opacity-10 {
            background-color: rgba(148, 163, 184, 0.18) !important;
            color: #cbd5e1 !important;
            border-color: rgba(148, 163, 184, 0.35) !important;
        }

        /* Action Buttons & Icon Harmonization */
        .btn-sm i, 
        .btn-group-sm > .btn i {
            font-size: 0.85rem;
            vertical-align: -1px;
            display: inline-block;
        }

        .btn-group-sm > .btn {
            padding: 0.25rem 0.52rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        /* Enterprise Pagination Design System */
        .pagination {
            margin-bottom: 0;
            gap: 5px;
            flex-wrap: wrap;
        }

        .page-item .page-link {
            border-radius: 8px !important;
            border: 1px solid var(--card-border);
            background-color: var(--input-bg);
            color: var(--text-body);
            font-size: 0.84rem;
            font-weight: 500;
            padding: 0.38rem 0.72rem;
            min-width: 35px;
            height: 35px;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.18s ease;
            box-shadow: none;
        }

        .page-item .page-link:hover {
            background-color: rgba(var(--admin-primary-rgb), 0.12);
            border-color: var(--admin-primary);
            color: var(--admin-primary);
            transform: translateY(-1px);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%) !important;
            border-color: var(--admin-primary) !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(var(--admin-primary-rgb), 0.32) !important;
        }

        .page-item.disabled .page-link {
            background-color: transparent !important;
            border-color: var(--card-border) !important;
            color: var(--text-muted) !important;
            opacity: 0.45;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Clean Enterprise Table Styling */
        .table-glass {
            --bs-table-bg: transparent;
            --bs-table-hover-bg: rgba(var(--admin-primary-rgb), 0.04);
            color: var(--text-body);
        }

        [data-bs-theme="dark"] .table-glass {
            --bs-table-hover-bg: rgba(255, 255, 255, 0.04);
        }

        .table-glass th {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-size: 0.72rem;
            color: var(--text-muted);
            border-bottom: 2px solid var(--card-border);
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .table-glass td {
            color: var(--text-body);
            border-bottom: 1px solid var(--card-border);
            font-size: 0.86rem;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        /* Solid Enterprise Inputs (Crisp, High Contrast) */
        .form-control-glass, .form-control {
            background-color: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            color: var(--text-body) !important;
            border-radius: 0.5rem;
        }

        .form-control-glass:focus, .form-control:focus {
            background-color: var(--card-bg) !important;
            border-color: var(--admin-primary) !important;
            box-shadow: 0 0 0 3px rgba(var(--admin-primary-rgb), 0.2) !important;
            color: var(--text-heading) !important;
        }

        /* 100% Solid & Opaque Dropdowns (NO glass distortion, NO bleed-through) */
        .dropdown-menu,
        .dropdown-menu-glass {
            background-color: var(--dropdown-bg) !important;
            border: 1px solid var(--dropdown-border) !important;
            box-shadow: var(--dropdown-shadow) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border-radius: 0.75rem;
            z-index: 1070 !important;
            padding: 0;
            overflow: hidden;
        }

        .dropdown-header-bg {
            background-color: var(--dropdown-header-bg) !important;
            border-bottom: 1px solid var(--dropdown-border) !important;
        }

        .dropdown-item {
            color: var(--text-body) !important;
            font-size: 0.85rem;
            transition: all 0.15s ease;
        }

        .dropdown-item:hover, .dropdown-item:focus {
            background-color: var(--dropdown-hover) !important;
            color: var(--admin-primary) !important;
        }

        .dropdown-item.text-danger:hover {
            background-color: rgba(225, 29, 72, 0.08) !important;
            color: #e11d48 !important;
        }

        .dropdown-divider {
            border-color: var(--card-border) !important;
            margin: 0.25rem 0;
            opacity: 0.7;
        }

        /* User Profile Trigger Button Pill */
        .user-profile-btn {
            border: 1px solid var(--card-border);
            background-color: var(--input-bg);
            border-radius: 9999px;
            padding: 3px 10px 3px 4px !important;
            transition: all 0.2s ease;
        }

        .user-profile-btn:hover {
            background-color: var(--dropdown-hover);
            border-color: var(--admin-primary);
        }

        /* Offcanvas Drawer (100% Solid & Opaque) */
        .offcanvas {
            background-color: var(--card-bg) !important;
            color: var(--text-body) !important;
            border-left: 1px solid var(--card-border) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15) !important;
        }

        /* Gradient icon containers */
        .gradient-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }

        .max-w-400 {
            max-width: 400px;
        }

        /* SweetAlert2 Theme Integration */
        .swal2-popup.swal2-toast {
            font-family: inherit !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1rem !important;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1) !important;
            border: 1px solid var(--card-border) !important;
        }
        [data-bs-theme="dark"] .swal2-popup {
            background: #0f172a !important;
            color: #f1f5f9 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6) !important;
        }
        [data-bs-theme="dark"] .swal2-title,
        [data-bs-theme="dark"] .swal2-html-container {
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .swal2-close {
            color: #94a3b8 !important;
        }

        /* Accessibility (a11y) & Focus Indicators (WCAG 2.2 AA) */
        .skip-link {
            position: fixed;
            top: -100px;
            left: 1rem;
            z-index: 1080;
            transition: top 0.2s ease-in-out;
        }
        .skip-link:focus {
            top: 1rem;
        }
        :focus-visible {
            outline: 2px solid var(--admin-primary) !important;
            outline-offset: 2px !important;
        }
        .table-responsive:focus-visible {
            outline: 2px dashed var(--admin-primary) !important;
            outline-offset: 2px !important;
            border-radius: 6px;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Skip to Main Content Link (WCAG 2.2 AA 2.4.1) -->
    <a href="#main-content" class="btn btn-primary shadow-lg skip-link">
        <i class="bi bi-box-arrow-in-down me-1" aria-hidden="true"></i> Saltar al contenido principal
    </a>

    <!-- Mobile Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        @include('layouts.partials.sidebar')

        <!-- Main Wrapper & Content Container (WCAG 2.2 Landmark) -->
        <main class="app-main" id="main-content" tabindex="-1">
            <!-- Header Navbar -->
            @include('layouts.partials.navbar')

            <!-- Page Header / Breadcrumb -->
            <div class="app-content-header py-3 px-4">
                <div class="container-fluid p-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h3 class="mb-0 fw-bold">
                                @yield('page_title', 'Dashboard')
                            </h3>
                            <div class="small mt-1">
                                @yield('breadcrumb')
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @yield('page_actions')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="app-content-body flex-grow-1 p-4">
                <div class="container-fluid p-0">
                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            @include('layouts.partials.footer')
        </main>
    </div>

    <!-- Theme Customizer Offcanvas -->
    @include('layouts.partials.theme-offcanvas')

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 Library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Theme & UI Manager Script -->
    <script>
        (function() {
            // Keys for LocalStorage
            const THEME_MODE_KEY = 'logisticpcs_theme_mode';
            const ACCENT_COLOR_KEY = 'logisticpcs_accent_color';
            const SIDEBAR_COLLAPSED_KEY = 'logisticpcs_sidebar_collapsed';

            // Color Themes Map (Color, Gradient End, RGB)
            const COLOR_PRESETS = {
                '#0284c7': { gradient: '#0369a1', rgb: '2, 132, 199' },   // Azul Océano
                '#059669': { gradient: '#047857', rgb: '5, 150, 105' },   // Verde Esmeralda
                '#4f46e5': { gradient: '#3730a3', rgb: '79, 70, 229' },  // Índigo Tech
                '#d97706': { gradient: '#b45309', rgb: '217, 119, 6' },   // Ámbar Industrial
                '#7c3aed': { gradient: '#5b21b6', rgb: '124, 58, 237' },  // Púrpura Real
                '#e11d48': { gradient: '#be123c', rgb: '225, 29, 72' }   // Carmesí Operativo
            };

            // Elements
            const htmlElement = document.documentElement;
            const bodyElement = document.body;
            const themeModeToggle = document.getElementById('themeModeToggle');
            const themeModeIcon = document.getElementById('themeModeIcon');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const resetThemeBtn = document.getElementById('resetThemeBtn');
            const colorButtons = document.querySelectorAll('.color-picker-btn');

            // 1. Initial State Loading
            const savedMode = localStorage.getItem(THEME_MODE_KEY) || 'light';
            const savedAccent = localStorage.getItem(ACCENT_COLOR_KEY) || '#0284c7';
            const savedSidebarCollapsed = localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === 'true';

            // Apply Mode
            applyThemeMode(savedMode);

            // Apply Accent Color
            applyAccentColor(savedAccent);

            if (savedSidebarCollapsed && window.innerWidth >= 992) {
                bodyElement.classList.add('sidebar-collapsed');
            }

            // 2. Mode Toggle (Light / Dark)
            function applyThemeMode(mode) {
                htmlElement.setAttribute('data-bs-theme', mode);
                localStorage.setItem(THEME_MODE_KEY, mode);

                if (mode === 'dark') {
                    if (themeModeIcon) themeModeIcon.className = 'bi bi-sun-fill text-warning';
                    const darkRadio = document.getElementById('themeDark');
                    if (darkRadio) darkRadio.checked = true;
                } else {
                    if (themeModeIcon) themeModeIcon.className = 'bi bi-moon-stars-fill text-secondary';
                    const lightRadio = document.getElementById('themeLight');
                    if (lightRadio) lightRadio.checked = true;
                }
            }

            if (themeModeToggle) {
                themeModeToggle.addEventListener('click', () => {
                    const currentMode = htmlElement.getAttribute('data-bs-theme');
                    applyThemeMode(currentMode === 'dark' ? 'light' : 'dark');
                });
            }

            document.querySelectorAll('input[name="themeModeRadio"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    applyThemeMode(e.target.value);
                });
            });

            // 3. Accent Color Picker & Gradient Generator
            function applyAccentColor(color) {
                const config = COLOR_PRESETS[color] || { gradient: color, rgb: '2, 132, 199' };
                document.documentElement.style.setProperty('--admin-primary', color);
                document.documentElement.style.setProperty('--admin-primary-gradient', config.gradient);
                document.documentElement.style.setProperty('--admin-primary-rgb', config.rgb);
                localStorage.setItem(ACCENT_COLOR_KEY, color);

                colorButtons.forEach(btn => {
                    if (btn.getAttribute('data-color') === color) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            colorButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const selectedColor = btn.getAttribute('data-color');
                    applyAccentColor(selectedColor);
                });
            });

            // 4. Desktop & Mobile Sidebar Toggles
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    if (window.innerWidth < 992) {
                        bodyElement.classList.toggle('sidebar-open');
                    } else {
                        bodyElement.classList.toggle('sidebar-collapsed');
                        localStorage.setItem(SIDEBAR_COLLAPSED_KEY, bodyElement.classList.contains('sidebar-collapsed'));
                    }
                });
            }

            if (sidebarCloseBtn) {
                sidebarCloseBtn.addEventListener('click', () => {
                    bodyElement.classList.remove('sidebar-open');
                });
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', () => {
                    bodyElement.classList.remove('sidebar-open');
                });
            }

            // 5. Reset Theme
            if (resetThemeBtn) {
                resetThemeBtn.addEventListener('click', () => {
                    applyThemeMode('light');
                    applyAccentColor('#0284c7');
                    bodyElement.classList.remove('sidebar-collapsed');
                    localStorage.removeItem(SIDEBAR_COLLAPSED_KEY);
                });
            }

            // 6. Global Search Shortcut (Ctrl+K)
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    const searchInput = document.getElementById('globalSearch');
                    if (searchInput) {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.select();
                    }
                }
            });

            // 7. SweetAlert2 Toast Mixin Helper
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                showCloseButton: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            window.Toast = Toast;

            // 8. Global SweetAlert2 Confirmation for Deletion and Destructive Actions
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form || !(form instanceof HTMLFormElement)) return;

                const methodInput = form.querySelector('input[name="_method"]');
                const isDelete = (methodInput && methodInput.value.toUpperCase() === 'DELETE') || form.classList.contains('form-delete');

                if (isDelete && !form.dataset.confirmed) {
                    e.preventDefault();
                    const message = form.getAttribute('data-confirm-text') 
                        || form.getAttribute('data-confirm') 
                        || '¿Está seguro de eliminar este registro? Esta acción no se puede deshacer.';

                    Swal.fire({
                        title: '¿Confirmar Eliminación?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        focusCancel: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                    });
                }
            });
        })();
    </script>

    <!-- SweetAlert2 Server Session Flash Notifications (Modal Style) -->
    @if (session('success') || session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Operación exitosa!',
                        text: {!! json_encode(session('success') ?? session('status')) !!},
                        confirmButtonColor: 'var(--admin-primary, #0284c7)',
                        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Aceptar',
                        timer: 3500,
                        timerProgressBar: true
                    });
                }
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Operación no completada',
                        text: {!! json_encode(session('error')) !!},
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: '<i class="bi bi-x-lg me-1"></i> Entendido'
                    });
                }
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: {!! json_encode(session('warning')) !!},
                        confirmButtonColor: '#f59e0b',
                        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Entendido',
                        timer: 5000,
                        timerProgressBar: true
                    });
                }
            });
        </script>
    @endif

    @if (session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Información',
                        text: {!! json_encode(session('info')) !!},
                        confirmButtonColor: 'var(--admin-primary, #0284c7)',
                        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Aceptar',
                        timer: 5000,
                        timerProgressBar: true
                    });
                }
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof Swal !== 'undefined') {
                    let errorsList = {!! json_encode($errors->all()) !!}.map(err => `<li>${err}</li>`).join('');
                    Swal.fire({
                        icon: 'error',
                        title: 'Errores en el Formulario',
                        html: `<ul class="text-start mb-0 ps-3 small">${errorsList}</ul>`,
                        confirmButtonColor: 'var(--admin-primary, #0284c7)',
                        confirmButtonText: '<i class="bi bi-pencil me-1"></i> Corregir'
                    });
                }
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
