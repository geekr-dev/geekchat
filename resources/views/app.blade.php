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
    <div class="flex items-center justify-center">
        <div class="my-4 grid sm:grid-cols-2 gap-y-2 gap-x-6">
            <div class="flex items-center justify-start space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="#3B82F6" d="M13 3C9.23 3 6.19 5.95 6 9.66l-1.92 2.53c-.24.31 0 .81.42.81H6v3c0 1.11.89 2 2 2h1v3h7v-4.69c2.37-1.12 4-3.51 4-6.31c0-3.86-3.12-7-7-7m1 11h-2v-2h2v2m1.75-5.19c-.29.4-.66.69-1.11.93c-.25.16-.42.33-.51.52c-.09.18-.13.43-.13.74h-2c0-.5.11-.92.31-1.18c.19-.27.54-.57 1.05-.91c.26-.16.47-.36.61-.59c.16-.23.23-.5.23-.82c0-.3-.08-.56-.26-.75c-.18-.18-.44-.28-.75-.28a1 1 0 0 0-.66.23c-.18.16-.27.39-.28.69h-1.93l-.01-.03c-.01-.79.25-1.36.77-1.77c.54-.39 1.24-.59 2.11-.59c.93 0 1.66.23 2.19.68c.54.45.81 1.06.81 1.82c0 .5-.15.91-.44 1.31Z">
                    </path>
                </svg>
                <div class="text-sm">
                    <a href="https://geekr.dev/posts/chatgpt-prompt-guide-1" class="hover:text-blue-500" target="_blank">如何更好地提问</a>
                </div>
            </div>
            <div class="flex items-center justify-start space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="#3B82F6" d="M17 16.47V7.39l-6 4.54M2.22 9.19a.858.858 0 0 1-.02-1.15l1.2-1.11c.2-.18.69-.26 1.05 0l3.42 2.61l7.93-7.25c.32-.32.87-.45 1.5-.12l4 1.91c.36.21.7.54.7 1.15v13.5c0 .4-.29.83-.6 1l-4.4 2.1c-.32.13-.92.01-1.13-.2l-8.02-7.3l-3.4 2.6c-.38.26-.85.19-1.05 0l-1.2-1.1c-.32-.33-.28-.87.05-1.2l3-2.7">
                    </path>
                </svg>
                <div class="text-sm">
                    <a href="https://geekr.dev/posts/chatgpt-oriented-programming" class="hover:text-blue-500" target="_blank">念咒工程师指南</a>
                </div>
            </div>
        </div>
    </div>
    <footer class="text-center sm:text-left">
        <div class="p-4 text-center text-neutral-700">
            GeekChat 由
            <a href="https://geekr.dev" target="_blank" class="text-neutral-800 dark:text-neutral-400">极客书房</a>
            开发维护，合作咨询、定制开发、独立部署、问题反馈请<a href="https://image.gstatics.cn/wechat.webp" target="_blank" class="text-blue-300 dark:text-blue-500">点这里</a>。
        </div>
    </footer>
</body>

</html>