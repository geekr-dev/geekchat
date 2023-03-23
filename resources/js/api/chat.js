import axios from 'axios'
import { CHAT_CONFIG } from '../config.js';

export default {
    // 获取所有消息
    getMessages: () => {
        return axios.get(CHAT_CONFIG.BASE_URL + '/messages', { responseType: 'json' })
    },

    // 发送文本消息
    chatMessage: (message, regen) => {
        const formData = new FormData();
        formData.append('prompt', message);
        formData.append('regen', regen);
        return fetch(CHAT_CONFIG.BASE_URL + '/chat', { method: 'POST', body: formData })
    },

    // 发送翻译消息
    translateMessage: (message, regen) => {
        const formData = new FormData();
        formData.append('prompt', message);
        formData.append('regen', regen);
        return fetch(CHAT_CONFIG.BASE_URL + '/translate', { method: 'POST', body: formData })
    },

    // 发送语音消息
    audioMessage: (blob) => {
        const formData = new FormData();
        formData.append('audio', blob);
        const api_key = window.localStorage.getItem('GEEKCHAT_API_KEY', '')
        if (api_key) {
            formData.append('api_key', api_key);
        }
        return fetch(CHAT_CONFIG.BASE_URL + '/audio', { method: 'POST', body: formData })
    },

    // 发送画图消息
    imageMessage: (message, regen) => {
        const formData = new FormData();
        formData.append('prompt', message);
        formData.append('regen', regen);
        const api_key = window.localStorage.getItem('GEEKCHAT_API_KEY', '')
        if (api_key) {
            formData.append('api_key', api_key);
        }
        return axios.post(CHAT_CONFIG.BASE_URL + '/image', formData, { responseType: 'json' })
    },

    // 验证API Key
    validApiKey: (api_key) => {
        const formData = new FormData();
        formData.append('api_key', api_key);
        return axios.post(CHAT_CONFIG.BASE_URL + '/valid', formData, { responseType: 'json' })
    },

    // 清空所有消息
    clearMessages: () => {
        return axios.get(CHAT_CONFIG.BASE_URL + '/reset')
    },
}