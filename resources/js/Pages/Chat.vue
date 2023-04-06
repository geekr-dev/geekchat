<script setup>
import AudioWidget from '@/Components/AudioWidget.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useStore } from 'vuex';
import { computed, onMounted } from 'vue';

// 消息列表初始化
const store = useStore()
onMounted(() => {
    store.dispatch('initMessages')
})

const messages = computed(() => store.state.messages.filter(message => message != undefined))
const isTyping = computed(() => store.state.isTyping)
const apiKey = computed(() => store.state.apiKey ? '*'.repeat(4) + store.state.apiKey.substr(length - 4, 4) : '')
const lastMessage = computed(() => store.state.lastMessage)
const lastAction = computed(() => store.state.lastAction)

const form = useForm({
    prompt: null
})

const chat = () => {
    if (apiKey.value == '') {
        alert('请先输入 API KEY')
        return
    }
    if (isTyping.value) {
        return;
    }
    store.dispatch('chatMessage', { message: form.prompt })
    form.reset()
}

const reset = () => {
    store.dispatch('clearMessages')
    form.reset()
}

const audio = (blob) => {
    if (apiKey.value == '') {
        alert('请先输入 API KEY')
        return
    }
    if (isTyping.value) {
        return;
    }
    store.dispatch('audioMessage', blob)
}

const audioFailed = (error) => {
    store.commit('addMessage', {
        role: 'assistant',
        content: error
    })
}

const image = () => {
    if (!form.prompt) {
        alert('请在输入框输入图片描述信息')
        return
    }
    if (apiKey.value == '') {
        alert('请先输入 API KEY')
        return
    }
    if (isTyping.value) {
        return;
    }
    store.dispatch('imageMessage', { message: form.prompt })
    form.reset()
}

const translate = () => {
    if (!form.prompt) {
        alert('请在输入框输入待翻译内容')
        return
    }
    if (apiKey.value == '') {
        alert('请先输入 API KEY')
        return
    }
    if (isTyping.value) {
        return;
    }
    store.dispatch('translateMessage', { message: form.prompt })
    form.reset()
}

const enterApiKey = () => {
    const api_key = prompt("输入你的 OpenAI API KEY：");
    store.dispatch('validAndSetApiKey', api_key)
}

const regenerate = () => {
    if (!lastMessage.value || !lastAction.value) {
        return
    }
    switch (lastAction.value) {
        case "chat":
            store.dispatch('chatMessage', { message: lastMessage.value, regen: true })
            break
        case "image":
            store.dispatch('imageMessage', { message: lastMessage.value, regen: true })
            break
        case "translate":
            store.dispatch('translateMessage', { message: lastMessage.value, regen: true })
            break
        case "audio":
            store.dispatch('chatMessage', { message: lastMessage.value, regen: true })
            break
        default:
            alert('未知消息类型')
            break
    }
}

</script>

<template>
    <Head title="GeekChat - 支持文字、语音、翻译、画图的免费体验版ChatGPT"></Head>
    <div>
        <div class="max-w-5xl mx-auto">
            <div>
                <div class="p-3 sm:p-5 flex items-center justify-center">
                    <div>
                        <div class="flex items-center justify-center space-x-2">
                            <img src="https://image.gstatics.cn/icon/geekchat.png" alt="GeekChat"
                                class="rounded-lg w-12 h-12">
                            <div class="font-semibold text-4xl sm:text-5xl">Geek<span class="text-blue-500">Chat</span>
                            </div>
                        </div>
                        <div class="text-center my-4 font-light text-base sm:text-xl my-2 sm:my-5">
                            支持文字、语音、翻译、画图的免费体验版ChatGPT
                        </div>
                        <div class="flex space-x-2 text-sm text-center justify-center">
                            <button v-if="apiKey" @click="enterApiKey"
                                class="text-blue-500 hover:underline font-semibold inline-flex space-x-2 disabled:text-gray-500"><svg
                                    stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512"
                                    class="w-5 h-5" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M218.1 167.17c0 13 0 25.6 4.1 37.4-43.1 50.6-156.9 184.3-167.5 194.5a20.17 20.17 0 00-6.7 15c0 8.5 5.2 16.7 9.6 21.3 6.6 6.9 34.8 33 40 28 15.4-15 18.5-19 24.8-25.2 9.5-9.3-1-28.3 2.3-36s6.8-9.2 12.5-10.4 15.8 2.9 23.7 3c8.3.1 12.8-3.4 19-9.2 5-4.6 8.6-8.9 8.7-15.6.2-9-12.8-20.9-3.1-30.4s23.7 6.2 34 5 22.8-15.5 24.1-21.6-11.7-21.8-9.7-30.7c.7-3 6.8-10 11.4-11s25 6.9 29.6 5.9c5.6-1.2 12.1-7.1 17.4-10.4 15.5 6.7 29.6 9.4 47.7 9.4 68.5 0 124-53.4 124-119.2S408.5 48 340 48s-121.9 53.37-121.9 119.17zM400 144a32 32 0 11-32-32 32 32 0 0132 32z">
                                    </path>
                                </svg><span>修改 KEY ({{ apiKey }})</span></button>
                            <button v-else @click="enterApiKey"
                                class="inline-flex items-center justify-center rounded-full px-4 py-2 shadow-md bg-blue-300 text-white hover:bg-blue-500 transition-all active:bg-blue-600 group font-semibold disabled:bg-gray-400 space-x-2">
                                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512"
                                    class="w-5 h-5" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M218.1 167.17c0 13 0 25.6 4.1 37.4-43.1 50.6-156.9 184.3-167.5 194.5a20.17 20.17 0 00-6.7 15c0 8.5 5.2 16.7 9.6 21.3 6.6 6.9 34.8 33 40 28 15.4-15 18.5-19 24.8-25.2 9.5-9.3-1-28.3 2.3-36s6.8-9.2 12.5-10.4 15.8 2.9 23.7 3c8.3.1 12.8-3.4 19-9.2 5-4.6 8.6-8.9 8.7-15.6.2-9-12.8-20.9-3.1-30.4s23.7 6.2 34 5 22.8-15.5 24.1-21.6-11.7-21.8-9.7-30.7c.7-3 6.8-10 11.4-11s25 6.9 29.6 5.9c5.6-1.2 12.1-7.1 17.4-10.4 15.5 6.7 29.6 9.4 47.7 9.4 68.5 0 124-53.4 124-119.2S408.5 48 340 48s-121.9 53.37-121.9 119.17zM400 144a32 32 0 11-32-32 32 32 0 0132 32z">
                                    </path>
                                </svg><span>API KEY（必填）</span>
                            </button>
                            <a href="https://t.zsxq.com/0cC0ngiSn" target="_blank"
                                class="inline-flex items-center justify-center px-4 py-2 shadow-md bg-blue-300 hover:bg-blue-500 active:bg-blue-600 text-white rounded-full font-semibold">
                                <span>加社群不迷路</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="px-4 rounded-lg mb-4" v-for="(message, index) in messages" :key="index">
                    <div v-if="message && message.role == 'user'"
                        class="pl-12 relative response-block scroll-mt-32 min-h-[40px]">
                        <div class="absolute top-0 left-0">
                            <div
                                class="w-9 h-9 bg-gray-200 rounded-md  flex-none flex items-center justify-center text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-6 h-6">
                                    <path fill-rule="evenodd"
                                        d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="w-full">
                            <markdown :source="message.content"
                                class="text-sm space-y-2 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg max-w-full overflow-auto" />
                        </div>
                    </div>
                    <div v-else class="pl-12 relative response-block scroll-mt-32 min-h-[60px]" v-show="message">
                        <div class="absolute top-0 left-0">
                            <img src="https://image.gstatics.cn/icon/geekchat.png" class="w-9 h-9 rounded-md flex-none">
                            <div class="my-1 flex items-center justify-center"><button><svg stroke="currentColor"
                                        fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true"
                                        class="w-4 h-4 hover:text-black transition-all text-gray-400" height="1em"
                                        width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg></button></div>
                        </div>
                        <div class="w-full">
                            <markdown :source="message.content" class="prose prose-sm" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-4 text-center w-full space-x-2" v-if="messages.length > 0 && !isTyping">
                <button @click="regenerate" v-if="lastAction && lastMessage"
                    class="inline-flex items-center justify-center rounded-full px-3 py-2 shadow-md bg-blue-400 text-white hover:bg-blue-500 transition-all active:bg-blue-600 group font-semibold text-xs">
                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true"
                        class="w-4 h-4 mr-1 group-hover:rotate-180 transition-all" height="1em" width="1em"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                            clip-rule="evenodd"></path>
                    </svg><span>重新生成</span>
                </button>
                <button @click="reset"
                    class="inline-flex items-center justify-center rounded-full px-3 py-2 text-sm shadow-md bg-gray-400 hover:bg-gray-500 text-white transition-all active:bg-gray-600 group font-semibold text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        aria-hidden="true" stroke="currentColor" class="w-4 h-4 mr-1 group-hover:rotate-180 transition-all"
                        height="1em" width="1em">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg><span>清空消息</span>
                </button>
            </div>
        </div>
        <div class="sticky bottom-0 left-0 right-0">
            <div class="max-w-5xl mx-auto w-full">
                <hr>
                <div class="p-4 bg-white px-4">
                    <div class="pb-safe">
                        <form class="grid grid-cols-1 gap-2 md:flex md:items-start md:justify-center md:space-x-2 mb-2"
                            @submit.prevent="chat">
                            <textarea required id="chat-input-textbox" placeholder="输入你的问题/翻译内容/图片描述..." name="prompt"
                                autocomplete="off" v-model="form.prompt"
                                :style="{ height: (form.prompt && form.prompt.split('\n').length > 1) ? form.prompt.split('\n').length * 2 + 'rem' : '40px' }"
                                class="block w-full rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:py-1.5 sm:text-sm sm:leading-6 resize-none"></textarea>
                            <div class="flex space-x-2">
                                <button
                                    :class="{ 'flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm md:text-base': true, 'opacity-25': isTyping }"
                                    title="发送消息" type="submit" :disabled="isTyping">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                    </svg>
                                </button>
                                <audio-widget @audio-upload="audio" @audio-failed="audioFailed" :is-typing="isTyping" />
                                <button
                                    :class="{ 'flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm md:text-base': true, 'opacity-25': isTyping }"
                                    @click="translate" title="中英互译" type="button" :disabled="isTyping">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        width="24" height="24" viewBox="0 0 24 24">
                                        <g fill="none">
                                            <path d="M0 0h24v24H0z"></path>
                                            <path fill="#FFFFFF"
                                                d="M17 10.5a1.5 1.5 0 0 1 1.493 1.356L18.5 12v.5h1a2 2 0 0 1 1.995 1.85l.005.15v3a2 2 0 0 1-1.85 1.995l-.15.005h-1v.5a1.5 1.5 0 0 1-2.993.144L15.5 20v-.5h-1a2 2 0 0 1-1.995-1.85l-.005-.15v-3a2 2 0 0 1 1.85-1.995l.15-.005h1V12a1.5 1.5 0 0 1 1.5-1.5Zm-12 4A1.5 1.5 0 0 1 6.5 16v1a.5.5 0 0 0 .5.5h3a1.5 1.5 0 0 1 0 3H7A3.5 3.5 0 0 1 3.5 17v-1A1.5 1.5 0 0 1 5 14.5Zm10.5.5h-1v2h1v-2Zm4 0h-1v2h1v-2ZM9.5 2.5a1.5 1.5 0 0 1 0 3h-4v1H9a1.5 1.5 0 1 1 0 3H5.5v1H10a1.5 1.5 0 0 1 0 3H4.1a1.6 1.6 0 0 1-1.6-1.6V4.1a1.6 1.6 0 0 1 1.6-1.6h5.4Zm7.5 1A3.5 3.5 0 0 1 20.5 7v2a1.5 1.5 0 0 1-3 0V7a.5.5 0 0 0-.5-.5h-3a1.5 1.5 0 0 1 0-3h3Z">
                                            </path>
                                        </g>
                                    </svg>
                                </button>
                                <button
                                    :class="{ 'flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm md:text-base': true, 'opacity-25': isTyping }"
                                    @click="image" title="AI绘图" type="button" :disabled="isTyping">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        width="24" height="24" viewBox="0 0 24 24">
                                        <path fill="#FFFFFF"
                                            d="M4 2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4l-4 4l-4-4H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2m15 13V7l-4 4l-2-2l-6 6h12M7 5a2 2 0 0 0-2 2a2 2 0 0 0 2 2a2 2 0 0 0 2-2a2 2 0 0 0-2-2Z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>