<template>
    <div>
        <icon-button :class="buttonClass" v-if="recording" name="stop" @click="toggleRecording" title="结束&发送语音" />
        <icon-button :class="buttonClass" v-else name="mic" @click="toggleRecording" title="录制语音" />
    </div>
</template>
  
<script>
import Recorder from "../lib/Recorder";
import IconButton from "./IconButton.vue";

const ERROR_MESSAGE = "无法使用麦克风，请确保具备硬件条件以及授权应用使用你的麦克风";
const ERROR_TIMEOUT_MESSAGE = "体验版目前仅支持30秒以内语音, 请重试";
const ERROR_BLOB_MESSAGE = "录音数据为空, 点击小话筒->开始讲话->讲完点终止键，再来一次吧";

export default {
    name: "AudioWidget",
    props: {
        // in seconds
        time: { type: Number, default: 30 },
        bitRate: { type: Number, default: 128 },
        sampleRate: { type: Number, default: 44100 },
        isTyping: { type: Boolean, default: false },
    },
    components: {
        IconButton,
    },
    data() {
        return {
            recording: false,
            recordedAudio: null,
            recordedBlob: null,
            recorder: null,
            errorMessage: null,
        };
    },
    computed: {
        buttonClass() {
            if (this.isTyping) {
                return "flex items-center justify-center px-4 py-2 border border-blue-600 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm md:text-base cursor-pointer opacity-25";
            } else {
                return "flex items-center justify-center px-4 py-2 border border-blue-600 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm md:text-base cursor-pointer";
            }
        }
    },
    beforeUnmount() {
        if (this.recording) {
            this.stopRecorder();
        }
    },
    methods: {
        toggleRecording() {
            if (this.isTyping) {
                alert("同时只能处理一个消息");
                return;
            }
            // 用户点击按钮触发
            this.recording = !this.recording;
            if (this.recording) {
                // 开始录音
                this.initRecorder();
            } else {
                // 结束录音
                this.stopRecording();
            }
        },
        initRecorder() {
            // 初始化Recoder
            this.recorder = new Recorder({
                micFailed: this.micFailed,
                bitRate: this.bitRate,
                sampleRate: this.sampleRate,
            });
            // 开始录音
            this.recorder.start();
            this.errorMessage = null;
        },
        stopRecording() {
            // 停止录音
            this.recorder.stop();
            const recordList = this.recorder.recordList();
            this.recordedAudio = recordList[0].url;
            this.recordedBlob = recordList[0].blob;
            // 录音数据不为空触发上传
            if (this.recordedAudio && this.recordedBlob) {
                // 录音成功，先判断时长
                if (this.recorder.duration > this.time) {
                    this.errorMessage = ERROR_TIMEOUT_MESSAGE;
                    this.$emit('audio-failed', this.errorMessage);
                    return;
                }
                // 录音数据为空，不处理
                if (!this.recordedBlob) {
                    this.errorMessage = ERROR_BLOB_MESSAGE;
                    this.$emit('audio-failed', this.errorMessage);
                    return;
                }
                // 提交录音数据给后端
                this.$emit('audio-upload', this.recordedBlob);
            }
        },
        micFailed() {
            // 录音失败
            this.recording = false;
            this.errorMessage = ERROR_MESSAGE;
            this.$emit('audio-failed', this.errorMessage);
        },
    },
};
</script>
  