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
            commit('toggleTyping')
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
                const eventSource = new EventSource(`/stream?chat_id=${data.chat_id}`);
                eventSource.onmessage = function (e) {
                    if (state.messages[state.messages.length - 1].content === '正在思考如何回答您的问题，请稍候...') {
                        state.messages[state.messages.length - 1].content = '';
                    }
                    if (e.data == "[DONE]") {
                        eventSource.close();
                        commit('toggleTyping')
                    } else {
                        let word = JSON.parse(e.data).choices[0].delta.content
                        if (word !== undefined) {
                            state.messages[state.messages.length - 1].content += JSON.parse(e.data).choices[0].delta.content
                        }
                    }
                };
                eventSource.onerror = function (e) {
                    eventSource.close();
                    commit('deleteMessage');
                    commit('addMessage', { 'role': 'assistant', 'content': '请求频率太高，请稍后再试' })
                };
            }).catch(error => {
                console.log(error);
                commit('toggleTyping')
            });
        },
        translateMessage({ state, commit }, message) {
            commit('toggleTyping')
            commit('addMessage', { 'role': 'user', 'content': message })
            ChatAPI.translateMessage(message).then(response => {
                if (response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': '请求过于频繁，请稍后再试' })
                    throw new Error('请求过于频繁，请稍后再试');  // 抛出异常，中断后续操作
                } else if (response.status >= 400) {
                    commit('addMessage', { 'role': 'assistant', 'content': '服务端异常，请稍后再试' })
                    throw new Error('服务端异常，请稍后再试');
                }
                return response.json();
            }).then(data => {
                commit('addMessage', { 'role': 'assistant', 'content': '正在自动翻译你提交的内容，请稍候...' })
                const eventSource = new EventSource(`/stream?chat_id=${data.chat_id}`);
                eventSource.onmessage = function (e) {
                    if (state.messages[state.messages.length - 1].content === '正在自动翻译你提交的内容，请稍候...') {
                        state.messages[state.messages.length - 1].content = '';
                    }
                    if (e.data == "[DONE]") {
                        eventSource.close();
                        commit('toggleTyping')
                    } else {
                        let word = JSON.parse(e.data).choices[0].delta.content
                        if (word !== undefined) {
                            state.messages[state.messages.length - 1].content += JSON.parse(e.data).choices[0].delta.content
                        }
                    }
                };
                eventSource.onerror = function (e) {
                    eventSource.close();
                    commit('deleteMessage');
                    commit('addMessage', { 'role': 'assistant', 'content': '请求频率太高，请稍后再试' })
                };
            }).catch(error => {
                console.log(error);
                commit('toggleTyping')
            });
        },
        audioMessage({ state, commit }, blob) {
            commit('toggleTyping')
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
                const eventSource = new EventSource(`/stream?chat_id=${data.chat_id}`);
                eventSource.onmessage = function (e) {
                    if (state.messages[state.messages.length - 1].content === '正在思考如何回答您的问题，请稍候...') {
                        state.messages[state.messages.length - 1].content = '';
                    }
                    if (e.data == "[DONE]") {
                        eventSource.close();
                        commit('toggleTyping')
                    } else {
                        let word = JSON.parse(e.data).choices[0].delta.content
                        if (word !== undefined) {
                            state.messages[state.messages.length - 1].content += JSON.parse(e.data).choices[0].delta.content
                        }
                    }
                };
                eventSource.onerror = function (e) {
                    eventSource.close();
                    commit('deleteMessage');
                    commit('addMessage', { 'role': 'assistant', 'content': '请求频率太高，请稍后再试' })
                };
            }).catch(error => {
                if (state.messages[state.messages.length - 1].content === '正在识别语音，请稍候...') {
                    commit('deleteMessage');
                    commit('addMessage', { 'role': 'assistant', 'content': '网络请求失败，请重试' })
                }
                console.log(error);
                commit('toggleTyping')
            });
        },
        imageMessage({ commit }, message) {
            commit('toggleTyping')
            commit('addMessage', { 'role': 'user', 'content': message })
            commit('addMessage', { 'role': 'assistant', 'content': '正在根据你提供的信息绘图，请稍候...' })
            ChatAPI.imageMessage(message).then(response => {
                commit('deleteMessage')
                commit('addMessage', response.data);
                commit('toggleTyping')
            }).catch(error => {
                commit('deleteMessage')
                if (error.response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': '请求过于频繁，请稍后再试' });
                } else {
                    commit('addMessage', { 'role': 'assistant', 'content': '请求处理失败，请重试' });
                }
                commit('toggleTyping')
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
            return state.messages
        }
    },
})

export default store
