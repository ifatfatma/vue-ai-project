<script setup>
import { ref } from 'vue';

const count = ref(0);
const message = ref('Vue 3 is working with Laravel 12!');

</script>

<template>
    <div class="vue-test-page">
        <div class="test-card">
            <div class="vue-logo">V</div>

            <h1>Laravel + Vue 3</h1>

            <p class="success-message">{{ message }}</p>

            <div class="counter">
                <p>Button clicks: <strong>{{ count }}</strong></p>

                <button @click="count++">
                    Click Me
                </button>

                <button class="reset-button" @click="count = 0">
                    Reset
                </button>
            </div>

            <p class="footer-text">
                This is a separate Vue test page.
            </p>
        </div>
    </div>
</template>

<style scoped>
.vue-test-page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 24px;
    background: #f1f5f9;
    font-family: Arial, sans-serif;
}

.test-card {
    width: 100%;
    max-width: 520px;
    padding: 40px;
    text-align: center;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(15, 23, 42, 0.10);
}

.vue-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    margin-bottom: 16px;
    border-radius: 16px;
    background: #42b883;
    color: white;
    font-size: 36px;
    font-weight: bold;
}

h1 {
    margin-bottom: 12px;
    color: #1e293b;
}

.success-message {
    color: #15803d;
    font-size: 17px;
}

.counter {
    margin-top: 28px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
}

button {
    margin: 6px;
    padding: 11px 20px;
    border: none;
    border-radius: 8px;
    background: #42b883;
    color: white;
    cursor: pointer;
    font-size: 15px;
}

button:hover {
    opacity: 0.88;
}

.reset-button {
    background: #475569;
}

.footer-text {
    margin-top: 24px;
    margin-bottom: 0;
    color: #64748b;
    font-size: 13px;
}
</style>
