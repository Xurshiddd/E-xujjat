<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'
import { ref } from 'vue'

type File = {
  id: number
  title: string
  path: string
  size: number
}
interface Archive {
  id: number
  name: string
  code: string
  folder_id: number
  user_id: number
  category_id: number
  created_at: string
  updated_at: string
  folder?: any
  user?: any
  category?: any
  file: File | null // Agar backendda faqat bitta fayl bo'lsa, shunday qilib yozamiz
}

interface Props {
  archives: Archive[]
}
const props = defineProps<Props>()
const archives = ref(props.archives)

function deleteArchive(id: number) {
  axios.delete(`/archives/${id}`, {
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
  })
    .then(() => {
      // page reload
      window.location.reload()
    })
    .catch(error => {
      console.error("Arxiv o'chirishda xato:", error)
      alert("Arxiv o'chirishda xato yuz berdi.")
    })
}
function confirmDelete(id: number) {
  if (confirm("Arxivni o‘chirishga ishonchingiz komilmi?")) {
    deleteArchive(id)
  }
}
</script>

<template>
  <Head title="Arxivlar" />
  <AppLayout>
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Arxivlar ro‘yxati</h1>
        <a href="/archives/create" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
          <i class="fas fa-plus mr-2"></i> Yangi arxiv
        </a>
      </div>
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-6">
          <div class="relative overflow-x-auto responsive-table">
            <table class="w-full text-sm text-left text-gray-500">
              <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                  <th class="px-6 py-3">Nomi</th>
                  <th class="px-6 py-3">Kod</th>
                  <th class="px-6 py-3">Kategoriya</th>
                  <th class="px-6 py-3">Papka</th>
                  <th class="px-6 py-3">Foydalanuvchi</th>
                  <th class="px-6 py-3">Yaratilgan sana</th>
                  <th class="px-6 py-3">Amallar</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="archive in props.archives" :key="archive.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4">{{ archive.name }}</td>
                  <td class="px-6 py-4">{{ archive.code }}</td>
                  <td class="px-6 py-4">{{ archive.category?.name || '-' }}</td>
                  <td class="px-6 py-4">{{ archive.folder?.name || '-' }}</td>
                  <td class="px-6 py-4">{{ archive.user?.name || '-' }}</td>
                  <td class="px-6 py-4">{{ new Date(archive.created_at).toLocaleDateString() }}</td>
                  <td class="px-6 py-4">
                    <a :href="`/storage/${archive.file?.path}`" v-if="archive.file" class="text-blue-500 hover:text-blue-700 mr-2" title="Ko‘rish" target="_blank">
                      <i class="fas fa-eye"></i>
                    </a>
                    <button
                      class="text-red-500 hover:text-red-700"
                      @click="confirmDelete(archive.id)"
                      title="O‘chirish"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="props.archives.length === 0" class="text-gray-500 text-center py-8">
              Arxivlar topilmadi.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
</style>
