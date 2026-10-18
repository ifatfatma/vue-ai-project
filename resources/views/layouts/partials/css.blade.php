
<!-- Plugins CSS -->
<link rel="stylesheet" href="{{ asset('admin/assets/vendors/iconfonts/mdi/css/materialdesignicons.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/vendors/iconfonts/ionicons/dist/css/ionicons.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/vendors/iconfonts/flag-icon-css/css/flag-icon.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/vendor.bundle.base.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/vendor.bundle.addons.css') }}">

<!-- Admin Template CSS -->
<link rel="stylesheet" href="{{ asset('admin/assets/css/shared/style.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/css/demo_1/style.css') }}">

<!-- Favicon -->
 <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

<style>
    /* Dashboard spacing */
    .main-panel {
        padding-top: 70px;
    }

    /* Prompt cards */
    .prompts-container {
        column-count: 3;
        column-gap: 16px;
        width: 100%;
    }

    .prompt-card {
        break-inside: avoid;
        margin-bottom: 16px;
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaeaea;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .06);
        display: inline-block;
        width: 100%;
        transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
    }

    .prompt-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, .12);
    }

    .prompt-content-text {
        background: #f8fafc;
        font-family: monospace;
        font-size: 14px;
        color: #4b5563;
        line-height: 1.5;
        max-height: 4.5em;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    @media (max-width: 1024px) {
        .prompts-container {
            column-count: 2;
        }
    }

    @media (max-width: 640px) {
        .prompts-container {
            column-count: 1;
        }
    }
</style>