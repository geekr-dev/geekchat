# GeekChat - A ChatGPT clone

This is a simple clone of ChatGPT made with Laravel using the OpenAI PHP client

这是使用 Laravel 10 + Tailwind + OpenAI PHP 客户端实现的 ChatGPT 网页版：

![](https://image.gstatics.cn/2023/03/05/image-20230305225252997.png)

本地启动：

```bash
cp .env.example .env
# 设置 OPENAI_API_KEY
docker-compose up -d
```

启动成功后，就可以通过 `http://localhost` 在浏览器访问 GeekChat 了。

开发流程详细文档在这里：<https://geekr.dev/posts/chatgpt-website-by-laravel-10>。

