<script setup lang="ts">
import { type BreadcrumbItem, type User } from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import axios from 'axios'
import Multiselect from "@vueform/multiselect"
import "@vueform/multiselect/themes/default.css"

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Papkalar', href: '/folders' },
]

// Helper: file size format
function formatFileSize(bytes: number) {
  if (bytes === 0) return '0 B'
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i]
}

// Types
type Folder = {
  id: number
  name: string
  code: string
  size: number
  user_id: number
  created_at: Date
}
interface FoldersResponse {
  data: Folder[]
  links: any[]
  current_page: number
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}
interface Props {
  folders: FoldersResponse
}
const props = defineProps<Props>()

// Auth user
const user = usePage().props.auth.user as User

// Flash messages
const responseMessage = ref('')
const errorMessage = ref('')
function showResponse(msg: string) {
  responseMessage.value = msg
  setTimeout(() => responseMessage.value = '', 5000)
}
function showError(msg: string) {
  errorMessage.value = msg
  setTimeout(() => errorMessage.value = '', 5000)
}

// 📂 Create folder
const showModal = ref(false)
const newFolderName = ref('')
function openModal() { showModal.value = true }
function closeModal() { showModal.value = false; newFolderName.value = '' }

function createFolder() {
  axios.post('/folders', { name: newFolderName.value })
    .then(res => {
      props.folders.data.push(res.data.data)
      closeModal()
      showResponse(res.data.message || 'Papka yaratildi!')
    })
    .catch(err => showError(err.response?.data?.message || 'Xatolik: papka yaratilmagan!'))
}

// 📂 Delete folder
function deleteFolder(id: number) {
  axios.delete(`/folders/${id}`, {
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') }
  }).then(() => window.location.reload())
    .catch(() => alert("Papka o'chirishda xato yuz berdi."))
}
function confirmDelete(id: number) {
  if (confirm("Papkani o‘chirishga ishonchingiz komilmi?")) deleteFolder(id)
}

// 📂 Edit folder
const editModal = ref(false)
const editFolderName = ref('')
const editingFolderId = ref<number | null>(null)
function openEditModal(folder: Folder) {
  editFolderName.value = folder.name
  editingFolderId.value = folder.id
  editModal.value = true
}
function closeEditModal() { editModal.value = false; editFolderName.value = ''; editingFolderId.value = null }
function updateFolder() {
  if (!editingFolderId.value) return
  axios.put(`/folders/${editingFolderId.value}`, { name: editFolderName.value })
    .then(res => {
      const idx = props.folders.data.findIndex(f => f.id === editingFolderId.value)
      if (idx !== -1) props.folders.data[idx].name = editFolderName.value
      closeEditModal()
      showResponse(res.data.message || 'Papka nomi o‘zgartirildi!')
    })
    .catch(err => showError(err.response?.data?.message || 'Xatolik: papka nomi o‘zgartirilmadi!'))
}

// 📂 Share folder
const shareModal = ref(false)
const sharingFolderId = ref<number | null>(null)
const sharingFolderName = ref('')
const expiresAt = ref('')
const password = ref('')
const generatedUrl = ref('')
const users = ref<{ id: number, name: string }[]>([])
const selectedUsers = ref<number[]>([])

function openShareModal(folder: Folder) {
  sharingFolderId.value = folder.id
  sharingFolderName.value = folder.name
  shareModal.value = true
  generatedUrl.value = ''
  expiresAt.value = ''
  password.value = ''
  selectedUsers.value = []

  axios.get('/users')
    .then(res => {
      users.value = res.data.data // <-- API {id, name} qaytarishi kerak
    })
    .catch(() => showError("Userlarni olishda xato!"))

}
function closeShareModal() { shareModal.value = false }

function generateUrl() {
  if (!sharingFolderId.value) return
  axios.post(`/shareble/${sharingFolderId.value}/share`, {
    type: 'folder', expires_at: expiresAt.value || null, password: password.value || null,
  }).then(res => {
    generatedUrl.value = res.data.url
    showResponse("Share link yaratildi!")
  }).catch(() => showError("Share link yaratishda xato!"))
}

function sendToUsers() {
  if (!sharingFolderId.value) return
  axios.post(`/shareble/${sharingFolderId.value}/share/send`, {
    password: password.value,
    expires_at: expiresAt.value,
    type: 'folder',
    url: generatedUrl.value,
    users: selectedUsers.value,
  }).then(res => {
    showResponse(res.data.message || "Link yuborildi!")
    closeShareModal()
  }).catch(() => showError("Userlarga yuborishda xato!"))
}

</script>

<template>
  <Head title="Folders" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Flash messages -->
    <div
      v-if="responseMessage"
      class="fixed top-4 left-1/2 transform -translate-x-1/2
             bg-green-500 text-white px-6 py-3 rounded shadow-lg
             dark:bg-green-600"
    >
      {{ responseMessage }}
    </div>
    <div
      v-if="errorMessage"
      class="fixed top-4 left-1/2 transform -translate-x-1/2
             bg-red-500 text-white px-6 py-3 rounded shadow-lg
             dark:bg-red-600"
    >
      {{ errorMessage }}
    </div>

    <!-- Create new folder button -->
    <div class="mb-4">
      <button
        @click="openModal"
        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <i class="fas fa-plus mr-2"></i> New Folder
      </button>
    </div>

    <!-- Folder Table -->
    <div
      v-if="props.folders.data.length"
      class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6"
    >
      <table class="w-full text-sm text-left text-gray-500 dark:text-gray-300">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
          <tr>
            <th class="px-6 py-3">Name</th>
            <th class="px-6 py-3">Date Modified</th>
            <th class="px-6 py-3">Type</th>
            <th class="px-6 py-3">Size</th>
            <th class="px-6 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="folder in props.folders.data"
            :key="folder.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <td class="px-6 py-4 flex items-center">
              <i class="fas fa-folder text-yellow-500 mr-2"></i>{{ folder.name }}
            </td>
            <td class="px-6 py-4">{{ new Date(folder.created_at).toLocaleDateString() }}</td>
            <td class="px-6 py-4">Folder</td>
            <td class="px-6 py-4">{{ formatFileSize(folder.size) }}</td>
            <td class="px-6 py-4 flex space-x-2">
              <a
                :href="route('folders.show', folder.id)"
                class="text-blue-500 hover:text-blue-700"
              >
                <i class="fas fa-folder-open"></i>
              </a>
              <button
                @click="openShareModal(folder)"
                class="text-green-500 hover:text-green-700"
              >
                <i class="fas fa-share-alt"></i>
              </button>
              <button
                @click="confirmDelete(folder.id)"
                class="text-red-500 hover:text-red-700"
              >
                <i class="fas fa-trash-alt"></i>
              </button>
              <button
                @click="openEditModal(folder)"
                class="text-yellow-500 hover:text-yellow-700"
              >
                <i class="fas fa-edit"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-else class="text-gray-500 dark:text-gray-400">No folders found.</p>

    <!-- Create Modal -->
    <transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full">
          <h2 class="text-lg font-semibold mb-4 dark:text-gray-100">Yangi papka yaratish</h2>
          <input
            v-model="newFolderName"
            type="text"
            placeholder="Enter folder name"
            class="border rounded px-3 py-2 w-full mb-4
                   bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
          />
          <div class="flex justify-end space-x-2">
            <button
              @click="closeModal"
              class="px-4 py-2 bg-gray-200 rounded dark:bg-gray-600 dark:text-gray-200"
            >
              Bekor qilish
            </button>
            <button @click="createFolder" class="px-4 py-2 bg-blue-500 text-white rounded">
              Saqlash
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Edit Modal -->
    <transition name="modal">
      <div v-if="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full">
          <h2 class="text-lg font-semibold mb-4 dark:text-gray-100">Papka nomini o‘zgartirish</h2>
          <input
            v-model="editFolderName"
            type="text"
            placeholder="Enter new folder name"
            class="border rounded px-3 py-2 w-full mb-4
                   bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
          />
          <div class="flex justify-end space-x-2">
            <button
              @click="closeEditModal"
              class="px-4 py-2 bg-gray-200 rounded dark:bg-gray-600 dark:text-gray-200"
            >
              Bekor qilish
            </button>
            <button @click="updateFolder" class="px-4 py-2 bg-blue-500 text-white rounded">
              Yangilash
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Share Modal -->
    <transition name="modal">
      <div v-if="shareModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full">
          <h2 class="text-lg font-semibold mb-4 dark:text-gray-100">Papkani ulash</h2>
          <p class="mb-4 dark:text-gray-200">
            <strong>Papka nomi:</strong> {{ sharingFolderName }}
          </p>

          <input
            type="datetime-local"
            v-model="expiresAt"
            class="border rounded px-3 py-2 w-full mb-3
                   bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
          />
          <input
            type="text"
            v-model="password"
            placeholder="Parol (ixtiyoriy)"
            class="border rounded px-3 py-2 w-full mb-3
                   bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
          />

          <div class="flex space-x-2 mb-3">
            <button @click="generateUrl" class="px-4 py-2 bg-blue-500 text-white rounded">
              Generate URL
            </button>
            <input
              v-if="generatedUrl"
              v-model="generatedUrl"
              readonly
              class="border px-3 py-2 rounded w-full
                     bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
            />
          </div>

          <div v-if="generatedUrl" class="mb-3">
            <label class="block text-sm mb-1 dark:text-gray-200">Select Users</label>
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
            <button
              @click="closeShareModal"
              class="px-4 py-2 bg-gray-200 rounded dark:bg-gray-600 dark:text-gray-200"
            >
              Bekor qilish
            </button>
            <button
              v-if="generatedUrl"
              @click="sendToUsers"
              class="px-4 py-2 bg-green-500 text-white rounded dark:bg-green-600"
            >
              Jo‘natish
            </button>
          </div>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>

