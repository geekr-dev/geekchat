<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="https://image.gstatics.cn/icon/geekchat.png">

    <title inertia>{{ config('app.name', 'GeekChat') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
    <footer class="text-center sm:text-left">
        <div class="p-4 text-center text-neutral-700">
            <a href="https://twitter.com/geek_chatgpt" target="_blank" class="text-blue-300 dark:text-blue-500">Click here to provide feedback if you encounter any issues</a>.
        </div>
    </footer>
</body>

</html>