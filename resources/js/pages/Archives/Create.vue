<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import axios from 'axios'

let folders = []
let categories = []
const props = defineProps<{
  folders: { id: number, name: string, icon?: string, color?: string }[],
  categories: {id: number, name: string, icon?: string, color?: string }[]
}>()
folders = props.folders || null
categories = props.categories || null

const selectedFolder = ref<{ id: number, name: string, icon?: string, color?: string } | null>(null)
const selectedCategory = ref<number | null>(null)
const selectedFiles = ref<File[]>([])
const showNewFolderModal = ref(false)
const newFolderName = ref('')
const responseMessage = ref('')
const isErrorMessage = ref(false)

function showResponse(msg: string, isError = false) {
  responseMessage.value = msg
  isErrorMessage.value = isError
  setTimeout(() => responseMessage.value = '', 5000)
}

function selectFolder(folder: { id: number, name: string }) {
  selectedFolder.value = folder
}
function selectCategory(category: { id: number, name: string }) {
  selectedCategory.value = category.id
}
function handleFiles(event: Event) {
  const files = (event.target as HTMLInputElement).files
  if (files) {
    const arr = Array.from(files)
    const filtered = arr.filter(f => f.size <= 50971520)
    if (filtered.length < arr.length) {
      alert('Fayl hajmi 20 MB dan oshmasligi kerak!')
    }
    selectedFiles.value = filtered
  }
}
function removeFile(index: number) {
  selectedFiles.value.splice(index, 1)
}
function openNewFolderModal() {
  showNewFolderModal.value = true
  newFolderName.value = ''
}
function closeNewFolderModal() {
  showNewFolderModal.value = false
  newFolderName.value = ''
}
function confirmNewFolder() {
  if (newFolderName.value.trim()) {
    axios.post('/folders', { name: newFolderName.value.trim() })
      .then(response => {
        folders.push(response.data.data)
        selectFolder(response.data.data)
        closeNewFolderModal()
        showResponse(response.data.data.message || 'Papka muvaffaqiyatli yaratildi!', false)
      })
      .catch(error => {
        console.error('Error creating folder:', error)
        showResponse(error.response?.data?.data?.message || 'Failed to create folder. Please try again.', true)
      })
    closeNewFolderModal()
  }
}
function formatFileSize(bytes: number) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
function submitFiles() {
  if (!selectedFolder.value) {
    alert('Please select a folder')
    return
  }
  if (!selectedCategory.value) {
    alert('Please select a category')
    return
  }
  if (selectedFiles.value.length === 0) {
    alert('Please select at least one file to upload')
    return
  }
  const formData = new FormData()
  formData.append('folder_id', selectedFolder.value.id.toString())
  formData.append('category_id', selectedCategory.value.toString())
  selectedFiles.value.forEach(file => {
    formData.append('files[]', file)
  })
  axios.post('/archives', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }).then(response => {
    showResponse(response.data.data?.message || 'Files uploaded successfully!', false)
    selectedFolder.value = null
    selectedCategory.value = null
    selectedFiles.value = []
  }).catch(error => {
    console.error('Error uploading files:', error)
    showResponse(error.response?.data?.data?.message || 'Failed to upload files. Please try again.', true)
  })
  selectedFolder.value = null
  selectedCategory.value = null
  selectedFiles.value = []
}
</script>

<template>
  <Head title="Arxivlar yaratish" />
  <AppLayout>
    <!-- Success/Error message -->
    <div
      v-if="responseMessage"
      :class="[
        'fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded shadow-lg z-50 transition',
        isErrorMessage
          ? 'bg-red-500 text-white'
          : 'bg-green-500 text-white'
      ]"
    >
      {{ responseMessage }}
    </div>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-2">Upload Your Files</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Select folder and category for better organization</p>

        <!-- Folder Selection -->
        <div class="mb-8">
          <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-folder-open mr-2 text-indigo-500"></i> Select Folder
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div
              v-for="folder in folders"
              :key="folder.id"
              class="folder-item cursor-pointer p-3 border rounded-lg flex items-center dark:border-gray-600"
              :class="{
                'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/40': selectedFolder?.id === folder.id
              }"
              @click="selectFolder(folder)"
            >
              <i :class="`fas ${folder.icon} mr-3 text-xl ${folder.color}`"></i>
              <span class="text-gray-800 dark:text-gray-100">{{ folder.name }}</span>
            </div>
          </div>
          <div class="mt-4">
            <button @click="openNewFolderModal" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center">
              <i class="fas fa-plus-circle mr-2"></i> Create New Folder
            </button>
          </div>
        </div>

        <!-- Category Selection -->
        <div class="mb-8">
          <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-tags mr-2 text-indigo-500"></i> Select Category
          </h2>
          <div class="flex flex-wrap gap-3">
            <div
              v-for="category in categories"
              :key="category.id"
              class="category-pill cursor-pointer px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center"
              :class="{
                'bg-indigo-100 dark:bg-indigo-900/40 border border-indigo-300 dark:border-indigo-600': selectedCategory === category.id
              }"
              @click="selectCategory(category)"
            >
              <i :class="`fas ${category.icon} mr-2 ${category.color}`"></i>
              <span class="text-gray-800 dark:text-gray-100">{{ category.name }}</span>
            </div>
          </div>
        </div>

        <!-- File Upload Area -->
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-cloud-upload-alt mr-2 text-indigo-500"></i> Upload Files
          </h2>
          <div class="file-drop-area p-8 text-center dark:border-gray-600 dark:bg-gray-700/40">
            <div class="flex flex-col items-center justify-center">
              <i class="fas fa-cloud-upload-alt text-5xl text-indigo-400 mb-4"></i>
              <p class="text-gray-600 dark:text-gray-300 mb-2">Drag & drop your files here</p>
              <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">or</p>
              <label for="fileInput" class="cursor-pointer px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                Browse Files
              </label>
              <input type="file" id="fileInput" class="hidden" multiple @change="handleFiles">
            </div>
          </div>
          <div v-if="selectedFiles.length > 0" class="mt-4">
            <h3 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Selected Files:</h3>
            <div class="space-y-2">
              <div v-for="(file, idx) in selectedFiles" :key="file.name + idx" class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                <div class="flex items-center">
                  <i class="fas fa-file text-indigo-500 mr-3"></i>
                  <div>
                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ file.name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatFileSize(file.size) }}</div>
                  </div>
                </div>
                <button @click="removeFile(idx)" class="text-red-500 hover:text-red-700">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary and Submit -->
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h3 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Upload Summary:</h3>
          <div class="flex flex-wrap gap-4">
            <div class="flex items-center">
              <span class="text-gray-600 dark:text-gray-400 mr-2">Folder:</span>
              <span class="font-medium text-gray-800 dark:text-gray-100">{{ selectedFolder?.name || 'Not selected' }}</span>
            </div>
            <div class="flex items-center">
              <span class="text-gray-600 dark:text-gray-400 mr-2">Category:</span>
              <span class="font-medium text-gray-800 dark:text-gray-100">
                {{ categories.find(c => c.id === selectedCategory)?.name || 'Not selected' }}
              </span>
            </div>
            <div class="flex items-center">
              <span class="text-gray-600 dark:text-gray-400 mr-2">Files:</span>
              <span class="font-medium text-gray-800 dark:text-gray-100">{{ selectedFiles.length }}</span>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button @click="submitFiles" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center">
              <i class="fas fa-upload mr-2"></i> Upload Files
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- New Folder Modal -->
    <transition name="modal">
      <div v-if="showNewFolderModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
          <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Create New Folder</h3>
          <div class="mb-4">
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Folder Name</label>
            <input v-model="newFolderName" type="text" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600">
          </div>
          <div class="flex justify-end space-x-3">
            <button @click="closeNewFolderModal" class="px-4 py-2 border rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 dark:border-gray-500">Cancel</button>
            <button @click="confirmNewFolder" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Create</button>
          </div>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>


<style scoped>
.file-drop-area {
  border: 2px dashed #cbd5e0;
  border-radius: 0.5rem;
  transition: all 0.3s ease;
}
.folder-item:hover {
  background-color: #f3f4f6;
}
.category-pill {
  transition: all 0.2s ease;
}
.category-pill:hover {
  transform: translateY(-2px);
}
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}
.modal-enter, .modal-leave-to {
  opacity: 0;
}
</style>
