<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'
import { ref } from 'vue'
import Multiselect from "@vueform/multiselect"
import "@vueform/multiselect/themes/default.css"

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
  file: File | null
}

interface Props {
  archives: Archive[]
}
const props = defineProps<Props>()
const archives = ref(props.archives)

// 📂 Delete
function deleteArchive(id: number) {
  axios.delete(`/archives/${id}`, {
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
  })
    .then(() => window.location.reload())
    .catch(error => {
      console.error("Arxiv o'chirishda xato:", error)
      alert("Arxiv o'chirishda xato yuz berdi.")
    })
}
function confirmDelete(id: number) {
  if (confirm("Arxivni o‘chirishga ishonchingiz komilmi?")) deleteArchive(id)
}

// 📂 Share
const shareModal = ref(false)
const sharingArchiveId = ref<number | null>(null)
const sharingArchiveName = ref('')
const expiresAt = ref('')
const password = ref('')
const generatedUrl = ref('')
const users = ref<{ id: number, name: string }[]>([])
const selectedUsers = ref<number[]>([])

function openShareModal(archive: Archive) {
  sharingArchiveId.value = archive.id
  sharingArchiveName.value = archive.name
  shareModal.value = true
  generatedUrl.value = ''
  expiresAt.value = ''
  password.value = ''
  selectedUsers.value = []

  axios.get('/users')
    .then(res => {
      users.value = res.data.data
    })
    .catch(() => alert("Userlarni olishda xato!"))
}
function closeShareModal() {
  shareModal.value = false
}

function generateUrl() {
  if (!sharingArchiveId.value) return
  axios.post(`/shareble/${sharingArchiveId.value}/share`, {
    type: 'archive',
    expires_at: expiresAt.value || null,
    password: password.value || null,
  }).then(res => {
    generatedUrl.value = res.data.url
    alert("Share link yaratildi!")
  }).catch(() => alert("Share link yaratishda xato!"))
}

function sendToUsers() {
  if (!sharingArchiveId.value) return
  axios.post(`/shareble/${sharingArchiveId.value}/share/send`, {
    url: generatedUrl.value,
    users: selectedUsers.value,
  }).then(res => {
    alert(res.data.message || "Link yuborildi!")
    closeShareModal()
  }).catch(() => alert("Userlarga yuborishda xato!"))
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
                  <td class="px-6 py-4">{{ archive.name.substring(0, 50) }}</td>
                  <td class="px-6 py-4">{{ archive.code }}</td>
                  <td class="px-6 py-4">{{ archive.category?.name || '-' }}</td>
                  <td class="px-6 py-4">{{ archive.folder?.name || '-' }}</td>
                  <td class="px-6 py-4">{{ archive.user?.name || '-' }}</td>
                  <td class="px-6 py-4">{{ new Date(archive.created_at).toLocaleDateString() }}</td>
                  <td class="px-6 py-4 flex space-x-2">
                    <a :href="`/storage/${archive.file?.path}`" v-if="archive.file" class="text-blue-500 hover:text-blue-700" title="Ko‘rish" target="_blank">
                      <i class="fas fa-eye"></i>
                    </a>
                    <button class="text-green-500 hover:text-green-700" @click="openShareModal(archive)" title="Ulashish">
                      <i class="fas fa-share-alt"></i>
                    </button>
                    <a :href="route('archives.edit', archive.id)" class="text-yellow-500 hover:text-yellow-700" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <button class="text-red-500 hover:text-red-700" @click="confirmDelete(archive.id)" title="O‘chirish">
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

    <!-- Share Modal -->
    <transition name="modal">
      <div v-if="shareModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
          <h2 class="text-lg font-semibold mb-4">Arxivni ulash</h2>
          <p class="mb-4"><strong>Arxiv nomi:</strong> {{ sharingArchiveName }}</p>

          <input type="datetime-local" v-model="expiresAt" class="border rounded px-3 py-2 w-full mb-3" />
          <input type="text" v-model="password" placeholder="Parol (ixtiyoriy)" class="border rounded px-3 py-2 w-full mb-3" />

          <div class="flex space-x-2 mb-3">
            <button @click="generateUrl" class="px-4 py-2 bg-blue-500 text-white rounded">Generate URL</button>
            <input v-if="generatedUrl" v-model="generatedUrl" readonly class="border px-3 py-2 rounded w-full" />
          </div>

          <div v-if="generatedUrl" class="mb-3">
            <label class="block text-sm mb-1">Select Users</label>
            <Multiselect
              v-model="selectedUsers"
              :options="users"
              mode="tags"
              label="name"
              track-by="id"
              value-prop="id"
              placeholder="Foydalanuvchilarni tanlang"
            />
          </div>

          <div class="flex justify-end space-x-2">
            <button @click="closeShareModal" class="px-4 py-2 bg-gray-200 rounded">Bekor qilish</button>
            <button v-if="generatedUrl" @click="sendToUsers" class="px-4 py-2 bg-green-500 text-white rounded">Jo‘natish</button>
          </div>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>
