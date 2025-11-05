<script setup>
import { Head } from '@inertiajs/vue3'
</script>

<template>
    <div class="min-h-screen flex">  
        <div class="min-h-screen flex flex-col justify-center items-center w-full md:w-1/3 p-8 bg-white">
          <Head title="Login" />
          <slot />
        </div>
        <div class="hidden md:flex w-2/3 bg-grey items-center justify-center">
            <img
            src="/images/login.png"
            alt="Login Illustration"
            class="w-3/4 max-w-md"
            />
        </div>
    </div>
</template>
