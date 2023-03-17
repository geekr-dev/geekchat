# GeekChat - Free ChatGPT With Support For Text, Voice And Stream

[中文文档](https://github.com/geekr-dev/geekchat/blob/main/README.zh.md)

### Introduction

This is a clone of ChatGPT with support for text, voice, stream and beautiful UI, made with Laravel 10 using the ChatGPT and Audio (Whisper model) API, as well as Inertia + Vue3 + Tailwind CSS:

<img width="658" alt="image" src="https://user-images.githubusercontent.com/114386672/224564157-2fb92c40-7a43-4156-9715-b76ea2cef46e.png">


### Quick Start

1、PHP HTTP Server

```bash
php artisan serve
npm run dev
```

2、Docker

```bash
git clone https://github.com/geekr-dev/geekchat.git
cd geekchat
cp .env.example .env
# config OPENAI_API_KEY
docker-compose up -d
docker exec -it geekchat_laravel.test_1 bash
composer install
npm install
npm run build
exit
docker-compose build --no-cache
docker-compose up -d
```

After that, you can view GeekChat by `http://localhost` in your local browser.

detail：<https://geekr.dev/posts/chatgpt-website-by-laravel-10>

### Stream Support

I implement stream with EventSource and curl, you can use it to receive stream response from OpenAI API.

Frontend:

```js
ChatAPI.chatMessage(message).then(response => {
    ...
    return response.json();
}).then(data => {
    ...
    const eventSource = new EventSource(`/stream?chat_id=${data.chat_id}`);
    eventSource.onmessage = function (e) {
        ...
        if (e.data == "[DONE]") {
            eventSource.close();
        } else {
            let word = JSON.parse(e.data).choices[0].delta.content
            if (word !== undefined) {
                state.messages[state.messages.length - 1].content += JSON.parse(e.data).choices[0].delta.content
            }
        }
    };
    eventSource.onerror = function (e) {
        eventSource.close();
        ...
    };
}).catch(error => {
    ...
});
```

Backend:

```php
use App\Facades\OpenAI;
use Symfony\Component\HttpFoundation\StreamedResponse;

...

$params = [
    'model' => 'gpt-3.5-turbo',
    'messages' => $messages,
    'stream' => true,
];

$response = new StreamedResponse(function () use ($request, $params) {
    $respData = '';
    OpenAI::chat($params, function ($ch, $data) use (&$respData) {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            echo "data: [ERROR] $httpCode";
        } else {
            echo $data;
        }
        ob_flush();
        flush();
        return strlen($data);
    });
    ...
});
$response->headers->set('Content-Type', 'text/event-stream');
$response->headers->set('Cache-Control', 'no-cache');
$response->headers->set('X-Accel-Buffering', 'no');
return $response;
```

