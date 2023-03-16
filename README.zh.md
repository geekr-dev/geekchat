# GeekChat - 支持语音和流式响应的免费体验版 ChatGPT

### 项目介绍

这是使用 Laravel 10 + Inertia + Vue3 + Tailwind + ChatGPT + Audio API 实现的 ChatGPT 网页版，在文字基础上增加了对语音的支持（基于 Whisper）：

<img width="658" alt="image" src="https://user-images.githubusercontent.com/114386672/224564157-2fb92c40-7a43-4156-9715-b76ea2cef46e.png">


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

