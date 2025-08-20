<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  folder: {
    id: number
    name: string
    code: string
    size: number
    archives?: any[]
  }
}>()
</script>

<template>
  <Head :title="props.folder.name" />
  <AppLayout>
    <div>
      <a :href="route('folders.index')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg m-4 transition-colors">Ortga</a>
    </div>
    <div class="container mx-auto px-4 py-8 max-w-3xl">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md overflow-hidden p-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-2">
          {{ props.folder.name }}
        </h1>
        <div class="text-gray-600 dark:text-gray-400 mb-4">
          <span class="mr-4">Kod: <span class="font-mono">{{ props.folder.code }}</span></span>
          <span>Hajmi: <span class="font-semibold">{{ props.folder.size }}</span></span>
        </div>
        <div class="mt-6">
          <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">Arxivlar</h2>
          <div v-if="props.folder.archives && props.folder.archives.length > 0">
            <ul class="space-y-3">
              <li v-for="archive in props.folder.archives" :key="archive.id" class="bg-gray-50 dark:bg-gray-800 rounded p-4 flex justify-between items-center">
                <div>
                  <div class="font-medium text-gray-800 dark:text-gray-100">{{ archive.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Kod: {{ archive.code }}</div>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                  Fayl hajmi: {{ archive.file?.size || 0 }}
                </div>
              </li>
            </ul>
          </div>
          <div v-else class="text-gray-500 dark:text-gray-400 text-center py-8">
            Arxivlar topilmadi.
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Qo'shimcha dark mode uchun maxsus style kerak emas, Tailwind dark: klasslari yetarli */
</style>
