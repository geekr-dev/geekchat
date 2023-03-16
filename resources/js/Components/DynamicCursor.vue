<template>
    <span class="cursor" v-show="showCursor">|</span>
</template>
  
<script>
export default {
    data() {
        return {
            showCursor: true,
        };
    },
    mounted() {
        setInterval(() => {
            this.showCursor = !this.showCursor;
        }, 500);
    },
};
</script>
  
<style scoped>
.cursor {
    animation: blink 1s infinite;
}

@keyframes blink {
    50% {
        opacity: 0;
    }
}
</style>
  