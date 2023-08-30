<!DOCTYPE >
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" html style="direction: rtl;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title inertia>Car Sale</title>
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">
        <script type="module" src="https://cdn.jsdelivr.net/npm/@duetds/date-picker@1.3.0/dist/duet/duet.esm.js"></script>
<script nomodule src="https://cdn.jsdelivr.net/npm/@duetds/date-picker@1.3.0/dist/duet/duet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@revolist/revo-dropdown@latest/dist/revo-dropdown/revo-dropdown.js"></script>
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
