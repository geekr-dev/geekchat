<template>
    <div>
        <icon-button :class="buttonClass" v-if="recording" name="stop" @click="toggleRecording"
            title="Finish & Send Voice" />
        <icon-button :class="buttonClass" v-else name="mic" @click="toggleRecording" title="Record Voice" />
    </div>
</template>
  
<script>
import Recorder from "../lib/Recorder";
import IconButton from "./IconButton.vue";

const ERROR_MESSAGE = "Unable to use microphone, please ensure that you have the hardware requirements and have authorized the application to use your microphone.";
const ERROR_TIMEOUT_MESSAGE = "The trial version only supports voice recordings under 30 seconds, please try again.";
const ERROR_BLOB_MESSAGE = "The recording data is empty. Click the microphone icon -> Start speaking -> Press the stop button when finished. Try again.";

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
                alert("Only one message can be processed at a time.");
                return;
            }
            // Triggered when the user clicks the button
            this.recording = !this.recording;
            if (this.recording) {
                // Start recording
                this.initRecorder();
            } else {
                // Finish recording.
                this.stopRecording();
            }
        },
        initRecorder() {
            // Initialize the recorder
            this.recorder = new Recorder({
                micFailed: this.micFailed,
                bitRate: this.bitRate,
                sampleRate: this.sampleRate,
            });
            // Start recording
            this.recorder.start();
            this.errorMessage = null;
        },
        stopRecording() {
            // Stop recording
            this.recorder.stop();
            const recordList = this.recorder.recordList();
            this.recordedAudio = recordList[0].url;
            this.recordedBlob = recordList[0].blob;
            // Trigger upload when recording data is not empty
            if (this.recordedAudio && this.recordedBlob) {
                // Recording successful, first determine the duration
                if (this.recorder.duration > this.time) {
                    this.errorMessage = ERROR_TIMEOUT_MESSAGE;
                    this.$emit('audio-failed', this.errorMessage);
                    return;
                }
                // Recording data is empty, do not process
                if (!this.recordedBlob) {
                    this.errorMessage = ERROR_BLOB_MESSAGE;
                    this.$emit('audio-failed', this.errorMessage);
                    return;
                }
                // Submit recording data to the backend
                this.$emit('audio-upload', this.recordedBlob);
            }
        },
        micFailed() {
            // Recording failed
            this.recording = false;
            this.errorMessage = ERROR_MESSAGE;
            this.$emit('audio-failed', this.errorMessage);
        },
    },
};
</script>
  