<script setup>
import AudioWidget from '@/Components/AudioWidget.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    messages: Array
})

const form = useForm({
    prompt: ''
});

const data = reactive({
    error: '',
    toast: ''
})

const chat = () => {
    form.post(route('chat'), {
        onStart: () => {
            data.error = ''
            form.reset()
            data.toast = 'GeekChat正在思考如何回答您的问题，请稍候...'
        },
        onFinish: response => {
            if (response.status >= 400) {
                if (response.status == 429) {
                    data.error = '请求过于频繁，请稍后再试'
                } else {
                    data.error = '请求处理失败，请重试'
                }

            }
            data.toast = ''
            scrollToButtom()
        }
    });
}

const reset = () => {
    axios.get(route('reset'))
        .then(response => {
            router.reload()
        })
}

const scrollToButtom = () => {
    const msgAnchor = document.querySelector('#msg-anchor')
    msgAnchor.scrollIntoView({ behavior: 'smooth' })
}

const audio = (blob) => {
    const formData = new FormData();
    formData.append('audio', blob);
    data.error = ''
    data.toast = 'GeekChat正在识别语音并思考如何回答您的问题，请稍候...'
    axios.post(route('audio'), formData)
        .then(response => {
            data.toast = ''
            location.reload();
            scrollToButtom()
        }).catch(error => {
            data.toast = ''
            if (error.includes('429')) {
                data.error = '请求过于频繁，请稍后再试'
            } else {
                data.error = '处理语音失败，可能没录音成功（按下话筒图标->开始讲话->讲完按下终止图标，操作不要太快），再来一次试试吧'
            }
            scrollToButtom()
        })
}

const audioFailed = (error) => {
    data.error = error
    data.toast = ''
}
</script>

<template>
    <Head title="GeekChat - ChatGPT免费体验版"></Head>

    <div class="flex flex-col space-y-4 p-4">
        <div v-for="(message, index) in messages" :key="index"
            :class="[message.role == 'assistant' ? 'flex rounded-lg p-4 bg-green-200 flex-reverse' : 'flex rounded-lg p-4 bg-blue-200']">
            <div class="ml-4">
                <div class="text-lg">
                    <a v-if="message.role == 'assistant'" href="#" class="font-medium text-gray-900">GeekChat</a>
                    <a v-else href="#" class="font-medium text-gray-900">你</a>
                </div>
                <div class="mt-1">
                    <p class="text-gray-600">
                        <markdown :source="message.content" />
                    </p>
                </div>
            </div>
        </div>
        <div id="msg-anchor"></div>
        <!-- 处理中提示 -->
        <div v-if="data.toast" class="flex rounded-lg p-4 bg-green-200 flex-reverse'">
            <div class="ml-4">
                <div class="mt-1">
                    <p class="text-gray-500">{{ data.toast }}</p>
                </div>
            </div>
        </div>
        <!-- 响应错误提示 -->
        <div v-if="data.error" class="flex rounded-lg p-4 bg-red-400 flex-reverse'">
            <div class="ml-4">
                <div class="mt-1">
                    <p class="text-gray-100">{{ data.error }}</p>
                </div>
            </div>
        </div>
    </div>

    <form class="p-4 flex space-x-4 justify-center items-center" @submit.prevent="chat">
        <div class="relative w-full">
            <input id="message" placeholder="输入你的问题..." type="text" name="prompt" autocomplete="off" v-model="form.prompt"
                class="w-full first-letter:border rounded-md p-2 flex-1" required />
            <audio-widget @audio-upload="audio" @audio-failed="audioFailed" />
        </div>
        <button
            :class="{ 'flex items-center justify-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md text-sm md:text-base': true, 'opacity-25': form.processing }"
            :disabled="form.processing" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
            </svg>
        </button>
        <button
            class="flex items-center justify-center px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-md text-sm md:text-base"
            @click="reset">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
        </button>
    </form>

    <footer class="text-center sm:text-left">
        <div class="p-4 text-center text-neutral-700">
            GeekChat 是一个小而美的免费体验版 ChatGPT，由
            <a href="https://geekr.dev" target="_blank" class="text-neutral-800 dark:text-neutral-400">极客书房</a>
            开发，支持文字语音聊天咨询。
        </div>
    </footer>
</template>