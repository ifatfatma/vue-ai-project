
<template>
    <div class="container mt-5">
        <h1>{{ message }}</h1>

        <p>Counter: {{ count }}</p>

        <button class="btn btn-primary" @click="count++">
            Click Me
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const message = ref('Hello Vue 3 with Laravel 12!');
const count = ref(0);
</script>
