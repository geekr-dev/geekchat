/**
 * Defines the API route we are using.
 */
var base_url = '';

switch (process.env.NODE_ENV) {
    case 'development':
        base_url = 'http://127.0.0.1:8000';
        break;
    case 'production':
        base_url = 'https://gpt.geekr.dev';
        break;
}

export const CHAT_CONFIG = {
    BASE_URL: base_url,
};