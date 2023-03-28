import axios from 'axios'
import { CHAT_CONFIG } from '../config.js';

export default {
    // Get all messages.
    getMessages: () => {
        return axios.get(CHAT_CONFIG.BASE_URL + '/messages', { responseType: 'json' })
    },

    // Send text message.
    chatMessage: (message, regen) => {
        const formData = new FormData();
        formData.append('prompt', message);
        formData.append('regen', regen);
        return fetch(CHAT_CONFIG.BASE_URL + '/chat', { method: 'POST', body: formData })
    },

    // Send translation message.
    translateMessage: (message, regen) => {
        const formData = new FormData();
        formData.append('prompt', message);
        formData.append('regen', regen);
        return fetch(CHAT_CONFIG.BASE_URL + '/translate', { method: 'POST', body: formData })
    },

    // Send voice message.
    audioMessage: (blob) => {
        const formData = new FormData();
        formData.append('audio', blob);
        const api_key = window.localStorage.getItem('GEEKCHAT_API_KEY', '')
        if (api_key) {
            formData.append('api_key', api_key);
        }
        return fetch(CHAT_CONFIG.BASE_URL + '/audio', { method: 'POST', body: formData })
    },

    // Send image create message.
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

    // Verify API Key.
    validApiKey: (api_key) => {
        const formData = new FormData();
        formData.append('api_key', api_key);
        return axios.post(CHAT_CONFIG.BASE_URL + '/valid', formData, { responseType: 'json' })
    },

    // Clear all messages.
    clearMessages: () => {
        return axios.get(CHAT_CONFIG.BASE_URL + '/reset')
    },
}