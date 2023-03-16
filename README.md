# GeekChat - Free ChatGPT With Support For Text, Voice And Stream

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

