import axios from 'axios'
import { CHAT_CONFIG } from '../config.js';

export default {
    // 获取所有消息
    getMessages: () => {
        return axios.get(CHAT_CONFIG.BASE_URL + '/messages', { responseType: 'json' })
    },

    // 发送文本消息
    chatMessage: (message) => {
        const formData = new FormData();
        formData.append('prompt', message);
        return fetch(CHAT_CONFIG.BASE_URL + '/chat', { method: 'POST', body: formData })
    },

    // 发送翻译消息
    translateMessage: (message) => {
        const formData = new FormData();
        formData.append('prompt', message);
        return fetch(CHAT_CONFIG.BASE_URL + '/translate', { method: 'POST', body: formData })
    },

    // 发送语音消息
    audioMessage: (blob) => {
        const formData = new FormData();
        formData.append('audio', blob);
        return fetch(CHAT_CONFIG.BASE_URL + '/audio', { method: 'POST', body: formData })
    },

    // 发送画图消息
    imageMessage: (message) => {
        const formData = new FormData();
        formData.append('prompt', message);
        return axios.post(CHAT_CONFIG.BASE_URL + '/image', formData, { responseType: 'json' })
    },

    // 清空所有消息
    clearMessages: () => {
        return axios.get(CHAT_CONFIG.BASE_URL + '/reset')
    },
}