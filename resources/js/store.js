// store.js
import { createStore } from 'vuex';
import ChatAPI from './api/chat';

const store = createStore({
    state() {
        return {
            messages: [],
            apiKey: '',
            isTyping: false,
            lastAction: '',
            lastMessage: '',
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
        setApiKey(state, apiKey) {
            state.apiKey = apiKey;
        },
        setLastLog(state, { action, message }) {
            state.lastAction = action;
            state.lastMessage = message;
        }
    },
    actions: {
        // Initialize messages
        initMessages({ commit }) {
            commit('setApiKey', window.localStorage.getItem('GEEKCHAT_API_KEY', ''));
            commit('setLastLog', { action: window.localStorage.getItem('GEEKCHAT_LAST_ACTION', ''), message: window.localStorage.getItem('GEEKCHAT_LAST_MESSAGE', '') });
            ChatAPI.getMessages().then(response => {
                commit('initMessages', response.data);
            }).catch(error => {
                commit('initMessages', []);
            });
        },
        chatMessage({ state, commit }, { message, regen = false }) {
            commit('toggleTyping')
            console.log(regen)
            if (!regen) {
                // Output user message and record log only for the first time.
                commit('addMessage', { 'role': 'user', 'content': message })
                window.localStorage.setItem('GEEKCHAT_LAST_ACTION', 'chat');
                window.localStorage.setItem('GEEKCHAT_LAST_MESSAGE', message);
                commit('setLastLog', { action: 'chat', message: message });
            } else if (state.messages[state.messages.length - 1].role === 'assistant') {
                // Consistent with server logic, delete the last reply first.
                commit('deleteMessage');
            }
            ChatAPI.chatMessage(message, regen).then(response => {
                if (response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': 'Request frequency is too high, please try again later.' })
                    throw new Error('Request frequency is too high, please try again later.');  // Throw an exception and interrupt subsequent operations.
                } else if (response.status >= 400) {
                    commit('addMessage', { 'role': 'assistant', 'content': 'Server exception, please try again later.' })
                    throw new Error('Server exception, please try again later.');
                }
                return response.json();
            }).then(data => {
                commit('addMessage', { 'role': 'assistant', 'content': 'Thinking about how to answer your question, please wait...' })
                const apiKey = state.apiKey ? btoa(state.apiKey) : '';
                const eventSource = new EventSource(`/stream?chat_id=${data.chat_id}&api_key=${apiKey}`);
                eventSource.onmessage = function (e) {
                    if (state.messages[state.messages.length - 1].content === 'Thinking about how to answer your question, please wait...') {
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
                    commit('addMessage', { 'role': 'assistant', 'content': 'Request frequency is too high, please try again later.' })
                    commit('toggleTyping')
                };
            }).catch(error => {
                console.log(error);
                if (state.isTyping) {
                    commit('toggleTyping')
                }
            });
        },
        translateMessage({ state, commit }, { message, regen = false }) {
            commit('toggleTyping')
            if (!regen) {
                commit('addMessage', { 'role': 'user', 'content': message })
                window.localStorage.setItem('GEEKCHAT_LAST_ACTION', 'translate');
                window.localStorage.setItem('GEEKCHAT_LAST_MESSAGE', message);
                commit('setLastLog', { action: 'translate', message: message });
            } else if (state.messages[state.messages.length - 1].role === 'assistant') {
                commit('deleteMessage');
            }
            ChatAPI.translateMessage(message, regen).then(response => {
                if (response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': 'Request frequency is too high, please try again later.' })
                    throw new Error('Request frequency is too high, please try again later.');
                } else if (response.status >= 400) {
                    commit('addMessage', { 'role': 'assistant', 'content': 'Server exception, please try again later.' })
                    throw new Error('Server exception, please try again later.');
                }
                return response.json();
            }).then(data => {
                commit('addMessage', { 'role': 'assistant', 'content': 'Automatically translating the content you submitted, please wait...' })
                const apiKey = state.apiKey ? btoa(state.apiKey) : '';
                const eventSource = new EventSource(`/stream?chat_id=${data.chat_id}&api_key=${apiKey}`);
                eventSource.onmessage = function (e) {
                    if (state.messages[state.messages.length - 1].content === 'Automatically translating the content you submitted, please wait...') {
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
                    commit('addMessage', { 'role': 'assistant', 'content': 'Request frequency is too high, please try again later.' })
                    commit('toggleTyping')
                };
            }).catch(error => {
                console.log(error);
                if (state.isTyping) {
                    commit('toggleTyping')
                }
            });
        },
        audioMessage({ state, commit }, blob) {
            commit('toggleTyping')
            commit('addMessage', { 'role': 'user', 'content': 'Identifying the voice, please wait...' })
            ChatAPI.audioMessage(blob).then(response => {
                commit('deleteMessage');
                if (response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': 'Request frequency is too high, please try again later.' })
                    throw new Error('Request frequency is too high, please try again later.');
                } else if (response.status >= 400) {
                    commit('addMessage', { 'role': 'assistant', 'content': 'Server exception, please try again later.' })
                    throw new Error('Server exception, please try again later.');
                }
                return response.json();
            }).then(data => {
                commit('addMessage', data.message); // Use the speech recognition result as the user text information.
                window.localStorage.setItem('GEEKCHAT_LAST_ACTION', 'audio');
                window.localStorage.setItem('GEEKCHAT_LAST_MESSAGE', data.message.content);
                commit('setLastLog', { action: 'audio', message: data.message.content });
                if (data.message.role === 'assistant') {
                    throw new Error('Speech recognition failed, please try again.');
                }
                commit('addMessage', { 'role': 'assistant', 'content': 'Thinking about how to answer your question, please wait...' })
                const apiKey = state.apiKey ? btoa(state.apiKey) : '';
                const eventSource = new EventSource(`/stream?chat_id=${data.chat_id}&api_key=${apiKey}`);
                eventSource.onmessage = function (e) {
                    if (state.messages[state.messages.length - 1].content === 'Thinking about how to answer your question, please wait...') {
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
                    commit('addMessage', { 'role': 'assistant', 'content': 'Requests are too frequent, please try again later.' })
                    commit('toggleTyping')
                };
            }).catch(error => {
                if (state.messages[state.messages.length - 1].content === 'Recognizing speech, please wait...') {
                    commit('deleteMessage');
                    commit('addMessage', { 'role': 'assistant', 'content': 'Network request failed, please try again.' })
                }
                console.log(error);
                if (state.isTyping) {
                    commit('toggleTyping')
                }
            });
        },
        imageMessage({ state, commit }, { message, regen = false }) {
            commit('toggleTyping')
            if (!regen) {
                commit('addMessage', { 'role': 'user', 'content': message })
                window.localStorage.setItem('GEEKCHAT_LAST_ACTION', 'image');
                window.localStorage.setItem('GEEKCHAT_LAST_MESSAGE', message);
                commit('setLastLog', { action: 'image', message: message });
            } else if (state.messages[state.messages.length - 1].role === 'assistant') {
                commit('deleteMessage');
            }
            commit('addMessage', { 'role': 'assistant', 'content': 'Drawing based on the information you provided, please wait...' })
            ChatAPI.imageMessage(message, regen).then(response => {
                commit('deleteMessage')
                commit('addMessage', response.data);
                commit('toggleTyping')
            }).catch(error => {
                commit('deleteMessage')
                if (error.response.status === 429) {
                    commit('addMessage', { 'role': 'assistant', 'content': 'Requests are too frequent, please try again later.' });
                } else {
                    commit('addMessage', { 'role': 'assistant', 'content': 'Request processing failed, please try again.' });
                }
                commit('toggleTyping')
            });
        },
        validAndSetApiKey({ commit }, apiKey) {
            if (apiKey === null || apiKey === undefined || apiKey === '') {
                // Set to null to delete the locally stored apikey.
                commit('setApiKey', apiKey);
                window.localStorage.removeItem('GEEKCHAT_API_KEY');
                return;
            }
            ChatAPI.validApiKey(apiKey).then(response => {
                if (response.data.valid != true) {
                    alert(response.data.error);
                    return;
                }
                commit('setApiKey', apiKey);
                window.localStorage.setItem('GEEKCHAT_API_KEY', apiKey);
            }).catch(error => {
                alert('Network request failed, please refresh the page and try again.');
            });
        },
        clearMessages({ commit }) {
            ChatAPI.clearMessages().then(response => {
                commit('clearMessages');
                window.localStorage.removeItem('GEEKCHAT_LAST_ACTION');
                window.localStorage.removeItem('GEEKCHAT_LAST_MESSAGE');
                commit('setLastLog', { action: '', message: '' });
            }).catch(error => {
                console.log(error);
            });
        },
    },
    getters: {
        // Get all messages.
        allMessages(state) {
            return state.messages
        }
    },
})

export default store
