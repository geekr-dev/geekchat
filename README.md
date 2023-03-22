# GeekChat - Free ChatGPT With Support For Text, Voice And Stream

[中文文档](https://github.com/geekr-dev/geekchat/blob/main/README.zh.md)

### Introduction

This is a clone of ChatGPT with support for text, voice, translate, image, stream and beautiful UI, made with Laravel 10 using the ChatGPT and Audio (Whisper model) API, as well as Inertia + Vue3 + Tailwind CSS:

support **text** && **voice**

<img width="598" alt="fa89bd30dfc71ef7312ca9dc8d079f3" src="https://user-images.githubusercontent.com/114386672/226559170-22e49847-b8b4-4c2a-856b-0325bb884f6e.png">

support **stream**

<img width="649" alt="db5b094fbc9e739302693c8728dd236" src="https://user-images.githubusercontent.com/114386672/226559792-50f0fd09-2062-46e8-a38e-4ba329a8e26e.png">

support **image**

<img width="705" alt="953ccb2083f34156fa2d4f18417ffc1" src="https://user-images.githubusercontent.com/114386672/226559494-b26aef63-8d32-4ee0-bfc0-bc5ab7a82193.png">

and support **translate**

<img width="915" alt="2a2bf9f1a8b2b7ec2b1b925eb5e9e75" src="https://user-images.githubusercontent.com/114386672/226558517-bf534744-c8d6-4a9f-9f3c-282aa8e96330.png">

final, support custom openai api key:

<img width="753" alt="d0f87acf563e0a1c7798c0088d1b961" src="https://user-images.githubusercontent.com/114386672/226852264-fce3eeaf-5f77-4ac3-b841-2198da21a890.png">

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

detail tutorial：<https://geekr.dev/posts/chatgpt-website-by-laravel-10>

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

> Warning: Due to the use of functions such as `header`, `ob_flush`, and `flush`, this does not support PHP HTTP servers driven by persistent memory mechanisms like Swoole or RoadRunner. It's important to keep this in mind when deploying to a production environment.
