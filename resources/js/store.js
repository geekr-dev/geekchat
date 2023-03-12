// store.js
import { createStore } from 'vuex';
import ChatAPI from './api/chat';

const store = createStore({
    state() {
        return {
            messages: []
        }
    },
    mutations: {
        initMessages(state, messages) {
            state.messages = messages;
        },
        addMessage(state, message) {
            state.messages.push(message)
        },
        deleteMessage(state) {
            state.messages.pop()
        },
        clearMessages(state) {
            state.messages = []
        }
    },
    actions: {
        // 初始化消息
        initMessages({ commit }) {
            ChatAPI.getMessages().then(response => {
                commit('initMessages', response.data);
            }).catch(error => {
                console.log(error);
                commit('initMessages', []);
            });
        },
        chatMessage({ commit }, message) {
            commit('addMessage', { 'role': 'user', 'content': message })
            commit('addMessage', { 'role': 'assistant', 'content': '正在思考如何回答您的问题，请稍候...' })
            ChatAPI.chatMessage(message).then(response => {
                commit('deleteMessage')
                commit('addMessage', response.data);
            }).catch(error => {
                commit('deleteMessage')
                if (error.response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': '请求过于频繁，请稍后再试' });
                } else {
                    commit('addMessage', { 'role': 'assistant', 'content': '请求处理失败，请重试' });
                }
            });
        },
        audioMessage({ commit }, blob) {
            commit('addMessage', { 'role': 'assistant', 'content': '正在识别语音并思考如何回答您的问题，请稍候...' })
            ChatAPI.audioMessage(blob).then(response => {
                commit('deleteMessage');
                console.log(response.data);
                response.data.forEach((_data, index) => {
                    commit('addMessage', { 'role': _data.role, 'content': _data.content });
                });
            }).catch(error => {
                commit('deleteMessage')
                if (error.response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': '请求过于频繁，请稍后再试' });
                } else {
                    commit('addMessage', { 'role': 'assistant', 'content': '处理语音失败，可能没录音成功（按下话筒图标->开始讲话->讲完按下终止图标，操作不要太快），再来一次试试吧' });
                }
            });
        },
        clearMessages({ commit }) {
            ChatAPI.clearMessages().then(response => {
                commit('clearMessages');
            }).catch(error => {
                console.log(error);
            });
        },
    },
    getters: {
        // 获取所有消息
        allMessages(state) {
            return state.messages;
        },
    },
})

export default store
