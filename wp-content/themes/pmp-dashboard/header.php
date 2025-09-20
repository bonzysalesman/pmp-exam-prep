<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#5b28b3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PMP Prep">
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/assets/pmp-exam-prep-logo.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: 'rgb(91, 40, 179)',
                        'primary-dark': 'rgb(71, 30, 139)',
                        secondary: '#333',
                        'light-gray': '#f5f5f5',
                        'border-color': '#eaeaea',
                        success: '#28a745'
                    },
                    fontFamily: {
                        'source': ['Source Sans Pro', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
    <style>
        p { line-height: 1.1618; }
    </style>
    
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-gray-50 font-source'); ?>>
