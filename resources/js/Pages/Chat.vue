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

const form = useForm({
    prompt: null,
})

const chat = () => {
    store.dispatch('chatMessage', form.prompt)
    form.reset()
}

const reset = () => {
    store.dispatch('clearMessages')
}

const audio = (blob) => {
    store.dispatch('audioMessage', blob)
}

const audioFailed = (error) => {
    store.commit('addMessage', {
        role: 'assistant',
        content: error
    })
}

</script>

<template>
    <Head title="GeekChat - 支持语音的免费体验版ChatGPT"></Head>
    <div>
        <div class="max-w-2xl mx-auto">
            <div class="py-8">
                <div class="p-6 sm:p-10 flex items-center justify-center">
                    <div>
                        <div class="flex items-center justify-center space-x-2"><img
                                src="https://image.gstatics.cn/icon/geekchat.png" alt="GeekChat"
                                class="rounded-lg w-12 h-12">
                            <div class="font-semibold text-4xl sm:text-5xl">Geek<span class="text-blue-500">Chat</span>
                            </div><span
                                class="bg-gradient-to-r from-purple-400 to-pink-500 px-3 py-1 text-xs font-semibold text-white text-center rounded-full inline-block ">Beta</span>
                        </div>
                        <div class="text-center my-4 font-light text-base sm:text-xl my-4 sm:my-10">支持语音的免费体验版ChatGPT
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
                            <markdown :source="message.content" class="prose prose-sm " />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sticky bottom-0 left-0 right-0">
            <div class="max-w-2xl mx-auto w-full">
                <hr>
                <div class="p-4 bg-white px-4">
                    <div class="pb-safe">
                        <form class="flex items-start justify-center space-x-2 mb-2" @submit.prevent="chat">
                            <textarea required id="chat-input-textbox" placeholder="输入你的问题..." name="prompt"
                                autocomplete="off" v-model="form.prompt" style="height: 40px !important;"
                                class="block w-full rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:py-1.5 sm:text-sm sm:leading-6"></textarea>
                            <button
                                :class="{ 'flex items-center justify-center px-4 py-2 border border-green-600 bg-green-500 hover:bg-green-600 text-white rounded-md text-sm md:text-base': true }"
                                title="发送消息" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                            </button>
                            <audio-widget @audio-upload="audio" @audio-failed="audioFailed" />
                            <button
                                class="flex items-center justify-center px-4 py-2 border border-gray-500 bg-gray-400 hover:bg-gray-500 text-white rounded-md text-sm md:text-base"
                                @click="reset" title="清空消息" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>