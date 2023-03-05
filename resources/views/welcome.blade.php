<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>GeekChat</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="antialiased">
    <div class="flex flex-col space-y-4 p-4">
        @foreach($messages as $message)
        <div class="flex rounded-lg p-4 @if ($message['role'] === 'assistant') bg-green-200 flex-reverse @else bg-blue-200 @endif ">
            <div class="ml-4">
                <div class="text-lg">
                    @if ($message['role'] === 'assistant')
                    <a href="#" class="font-medium text-gray-900">GeekChat</a>
                    @else
                    <a href="#" class="font-medium text-gray-900">你</a>
                    @endif
                </div>
                <div class="mt-1">
                    <p class="text-gray-600">
                        {!! \Illuminate\Mail\Markdown::parse($message['content']) !!}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <form class="p-4 flex space-x-4 justify-center items-center" action="/chat" method="post">
        @csrf
        <label for="message">问题:</label>
        <input id="message" type="text" name="message" autocomplete="off" class="border rounded-md  p-2 flex-1" />
        <a class="bg-gray-800 text-white p-2 rounded-md" href="/reset">重置会话</a>
    </form>
</body>

</html>