# GeekChat - A ChatGPT clone

This is a simple clone of ChatGPT made with Laravel using the OpenAI PHP client

这是使用 Laravel 10 + Tailwind + OpenAI PHP 客户端实现的 ChatGPT 网页版：

![](https://image.gstatics.cn/2023/03/05/image-20230305225252997.png)

本地启动：

```bash
git clone https://github.com/geekr-dev/geekchat.git
cd geekchat
cp .env.example .env
# 设置 OPENAI_API_KEY
docker-compose up -d
docker exec -it geekchat_laravel.test_1 bash
cd /var/www/html
composer install
# 未使用 Octane 不需要执行以下操作
./vendor/bin/rr get-binary
exit
./vendor/bin/sail artisan sail:publish
# 调整 context/supervisord.conf 中 http 服务器启动配置
chmod +x ./rr
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d
```

启动成功后，就可以通过 `http://localhost` 在浏览器访问 GeekChat 了。

开发流程详细文档在这里：<https://geekr.dev/posts/chatgpt-website-by-laravel-10>。

