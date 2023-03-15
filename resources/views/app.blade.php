<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="https://image.gstatics.cn/icon/geekchat.png">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

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
            GeekChat 由
            <a href="https://geekr.dev" target="_blank" class="text-neutral-800 dark:text-neutral-400">极客书房</a>
            开发维护，合作咨询、定制开发、问题反馈请<a href="https://image.gstatics.cn/wechat.webp" target="_blank" class="text-blue-300 dark:text-blue-500">点这里</a>。
        </div>
    </footer>
</body>

</html>