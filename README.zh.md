# GeekChat - 支持语音和流式响应的免费体验版 ChatGPT

### 项目介绍

这是使用 Laravel 10 + Inertia + Vue3 + Tailwind + OpenAI API 实现的 ChatGPT 网页版，在文字基础上增加了对语音的支持（基于 Whisper）、绘图的支持（DALL·E）、翻译的支持。

支持 **文字** && **语音**

<img width="598" alt="fa89bd30dfc71ef7312ca9dc8d079f3" src="https://user-images.githubusercontent.com/114386672/226559170-22e49847-b8b4-4c2a-856b-0325bb884f6e.png">

支持 **stream**

<img width="649" alt="db5b094fbc9e739302693c8728dd236" src="https://user-images.githubusercontent.com/114386672/226559792-50f0fd09-2062-46e8-a38e-4ba329a8e26e.png">

支持 **绘图**

<img width="705" alt="953ccb2083f34156fa2d4f18417ffc1" src="https://user-images.githubusercontent.com/114386672/226559494-b26aef63-8d32-4ee0-bfc0-bc5ab7a82193.png">

支持 **翻译**

<img width="915" alt="2a2bf9f1a8b2b7ec2b1b925eb5e9e75" src="https://user-images.githubusercontent.com/114386672/226558517-bf534744-c8d6-4a9f-9f3c-282aa8e96330.png">

支持设置自己的 OpenAI API KEY：

<img width="753" alt="d0f87acf563e0a1c7798c0088d1b961" src="https://user-images.githubusercontent.com/114386672/226852264-fce3eeaf-5f77-4ac3-b841-2198da21a890.png">

### 快速启动

1、方式1 —— PHP HTTP 服务器（需要本地安装 PHP 和 NPM）

```bash
php artisan serve
npm run dev
```

2、方式2 —— Docker（无需额外依赖）

```bash
git clone https://github.com/geekr-dev/geekchat.git
cd geekchat
cp .env.example .env
# 设置 OPENAI_API_KEY
docker-compose up -d
docker exec -it geekchat_laravel.test_1 bash
composer install
npm install
npm run build
./vendor/bin/rr get-binary
exit
chmod +x ./rr
docker-compose build --no-cache
docker-compose up -d
```

启动成功后，就可以通过 `http://localhost` 在浏览器访问 GeekChat 了。

开发流程详细文档在这里：<https://geekr.dev/posts/chatgpt-website-by-laravel-10>。

### Stream 实现

我使用 EventSource 和 curl 实现了流式响应，你可以使用这个管道组合来接收 OpenAI API 的流式响应：

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

> 注意事项：由于用到了 header、ob_flush、flush 这些函数，所以不支持基于 Swoole 、RoadRunner 之类常驻内存机制驱动的 PHP HTTP 服务器，在部署到生产环境的时候需要注意这一点。 
