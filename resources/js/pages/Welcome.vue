<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex items-center justify-center min-h-screen">
<div class="flex flex-col md:flex-row items-center justify-between w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="w-full">
<div class="flex items-center">
<div class="text-white p-4">
<img src="../../../public/logo.jpg" alt="logo" class="w-75">
</div>
<div class="ml-4">
<h1 class="font-bold text-gray-800" style="font-size: 25px;">Toshkent to'qimachilik va yengil sanoat instituti</h1>
<p class="text-xs text-gray-500">1932-yildan beri</p>
</div>
</div>
<h2 class="text-2xl font-bold text-blue-800 mb-8">“Omborxona hisobini yuritish” axborot tizimi</h2>
<div>
<div class="mb-4">
    <a href="/hemis/redirect" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Hemis orqali kirish</a>
</div>
<div class="mb-4">
<a href="/login" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Admin login</a>
</div>
</div>
</div>
<div class="w-full flex justify-center mt-8 md:mt-0">
<img alt="IMAGE" class="w-full h-auto" src="../../../public/welcome.png"/>
</div>
</div>
</div>
</template>
