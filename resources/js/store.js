// store.js
import { createStore } from 'vuex';
import ChatAPI from './api/chat';

const store = createStore({
    state() {
        return {
            messages: [],
            isTyping: false,
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
        },
        toggleTyping(state) {
            state.isTyping = !state.isTyping
        },
    },
    actions: {
        // 初始化消息
        initMessages({ commit }) {
            ChatAPI.getMessages().then(response => {
                commit('initMessages', response.data);
            }).catch(error => {
                commit('initMessages', []);
            });
        },
        chatMessage({ state, commit }, message) {
            commit('addMessage', { 'role': 'user', 'content': message })
            ChatAPI.chatMessage(message).then(response => {
                if (response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': '请求过于频繁，请稍后再试' })
                    throw new Error('请求过于频繁，请稍后再试');  // 抛出异常，中断后续操作
                } else if (response.status >= 400) {
                    commit('addMessage', { 'role': 'assistant', 'content': '服务端异常，请稍后再试' })
                    throw new Error('服务端异常，请稍后再试');
                }
                return response.json();
            }).then(data => {
                commit('addMessage', { 'role': 'assistant', 'content': '正在思考如何回答您的问题，请稍候...' })
                const eventSource = new EventSource(`/stream/${data.chat_id}`);
                eventSource.onmessage = function (e) {
                    if (state.messages[state.messages.length - 1].content === '正在思考如何回答您的问题，请稍候...') {
                        state.messages[state.messages.length - 1].content = '';
                    }
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
                    console.log(e);
                    eventSource.close();
                    commit('deleteMessage');
                    commit('addMessage', { 'role': 'assistant', 'content': '接收回复出错，请重试' })
                };
            }).catch(error => {
                console.log(error);
            });
        },
        audioMessage({ state, commit }, blob) {
            commit('addMessage', { 'role': 'user', 'content': '正在识别语音，请稍候...' })
            ChatAPI.audioMessage(blob).then(response => {
                commit('deleteMessage');
                if (response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': '请求过于频繁，请稍后再试' })
                    throw new Error('请求过于频繁，请稍后再试');  // 抛出异常，中断后续操作
                } else if (response.status >= 400) {
                    commit('addMessage', { 'role': 'assistant', 'content': '服务端异常，请稍后再试' })
                    throw new Error('服务端异常，请稍后再试');
                }
                return response.json();
            }).then(data => {
                commit('addMessage', data.message); // 将语音识别结果作为用户文本信息
                commit('addMessage', { 'role': 'assistant', 'content': '正在思考如何回答您的问题，请稍候...' })
                const eventSource = new EventSource(`/stream/${data.chat_id}`);
                let buffer = '';
                eventSource.onmessage = function (e) {
                    if (state.messages[state.messages.length - 1].content === '正在思考如何回答您的问题，请稍候...') {
                        state.messages[state.messages.length - 1].content = '';
                    }
                    if (e.data == "[DONE]") {
                        eventSource.close();
                    } else {
                        // e.data 是否以 \n\n 结尾
                        if (e.data.endsWith('\n\n')) {
                            if (!e.data.startsWith('data: ')) {
                                e.data = buffer + e.data;
                                buffer = '';
                            }
                            let word = JSON.parse(e.data).choices[0].delta.content
                            if (word !== undefined) {
                                state.messages[state.messages.length - 1].content += JSON.parse(e.data).choices[0].delta.content
                            }
                        } else {
                            buffer += e.data;
                        }
                    }
                };
                eventSource.onerror = function (e) {
                    eventSource.close();
                    commit('deleteMessage');
                    commit('addMessage', { 'role': 'assistant', 'content': '接收回复出错，请重试' })
                    throw new Error(e);
                };
            }).catch(error => {
                if (state.messages[messages.length - 1].content === '正在识别语音，请稍候...') {
                    commit('deleteMessage');
                    commit('addMessage', { 'role': 'assistant', 'content': '网络请求失败，请重试' })
                }
                console.log(error);
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
        isTyping(state) {
            return state.isTyping;
        }
    },
})

export default store
