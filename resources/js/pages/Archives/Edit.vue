<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import axios from "axios"
import { set } from '@vueuse/core';
const props = defineProps<{
  archive: {
    id: number,
    name: string,
    folder_id: number,
    category_id: number,
    file?: { name: string, size: number } | null
  },
  folders: { id: number, name: string }[],
  categories: { id: number, name: string }[]
}>()

const selectedFolder = ref(props.folders.find(f => f.id === props.archive.folder_id) || null)
const selectedCategory = ref(props.archive.category_id)
const archiveName = ref(props.archive.name)
const selectedFiles = ref<File[]>([])

const responseMessage = ref('')
const isErrorMessage = ref(false)

function showResponse(msg: string, isError = false) {
  responseMessage.value = msg
  isErrorMessage.value = isError
  setTimeout(() => responseMessage.value = '', 5000)
}

function handleFiles(event: Event) {
  const files = (event.target as HTMLInputElement).files
  if (files && files[0]) {
    const file = files[0]
    if (file.size > 20 * 1024 * 1024) {
      alert('Fayl hajmi 20 MB dan oshmasligi kerak!')
      return
    }
    selectedFiles.value = [file] // faqat bitta fayl
  }
}

function removeFile() {
  selectedFiles.value = []
}

function selectFolder(folder: { id: number, name: string }) {
  selectedFolder.value = folder
}

function selectCategory(category: { id: number, name: string }) {
  selectedCategory.value = category.id
}

function formatFileSize(bytes: number) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

async function submitEdit() {
  if (!selectedFolder.value) {
    alert('Please select a folder')
    return
  }
  if (!selectedCategory.value) {
    alert('Please select a category')
    return
  }

  const formData = new FormData()
  formData.append('folder_id', selectedFolder.value.id.toString())
  formData.append('category_id', selectedCategory.value.toString())
  formData.append('name', archiveName.value)
  formData.append('_method', 'PUT')
  if (selectedFiles.value.length > 0) {
    formData.append('file', selectedFiles.value[0])
  }

try {
  const response = await axios.post(
    `/archives/${props.archive.id}`,
    formData,
    {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    }
  )

  showResponse('Archive updated successfully!', false)
    setTimeout(() => {
      window.location.reload()
    }, 3000);


} catch (error: any) {
  if (error.response && error.response.status === 422) {
    console.error('Validation errors:', error.response.data.errors)
    showResponse('Failed to update archive. Check inputs.', true)
  } else {
    console.error('Unexpected error:', error)
    showResponse('Unexpected error occurred.', true)
  }
}

}
</script>


<template>
  <Head title="Arxivni tahrirlash" />
  <AppLayout>
    <!-- Toast message -->
    <div
      v-if="responseMessage"
      :class="[
        'fixed top-4 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded shadow-lg z-50 transition',
        isErrorMessage
          ? 'bg-red-500 text-white dark:bg-red-600'
          : 'bg-green-500 text-white dark:bg-green-600'
      ]"
    >
      {{ responseMessage }}
    </div>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-2">Edit Archive</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
          Change archive info, folder, category or files
        </p>

        <!-- Archive Name -->
        <div class="mb-6">
          <label class="block text-gray-700 dark:text-gray-300 mb-2">Archive Name</label>
          <input
            v-model="archiveName"
            type="text"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500
                   bg-white dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600"
          />
        </div>

        <!-- Folder Selection -->
        <div class="mb-8">
          <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-folder-open mr-2 text-indigo-500"></i> Select Folder
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div
              v-for="folder in folders"
              :key="folder.id"
              class="folder-item cursor-pointer p-3 border rounded-lg flex items-center
                     bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
              :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/40': selectedFolder?.id === folder.id }"
              @click="selectFolder(folder)"
            >
              <i class="fas fa-folder mr-3 text-xl text-yellow-500"></i>
              <span>{{ folder.name }}</span>
            </div>
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
              class="category-pill cursor-pointer px-4 py-2 bg-gray-100 dark:bg-gray-700
                     rounded-full flex items-center text-gray-700 dark:text-gray-200"
              :class="{
                'bg-indigo-100 border border-indigo-300 dark:bg-indigo-900/40 dark:border-indigo-600':
                  selectedCategory === category.id
              }"
              @click="selectCategory(category)"
            >
              <i class="fas fa-tag mr-2 text-gray-500 dark:text-gray-300"></i>
              <span>{{ category.name }}</span>
            </div>
          </div>
        </div>

        <!-- Existing File -->
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-file mr-2 text-indigo-500"></i> Existing File
          </h2>
          <div
            v-if="props.archive.file"
            class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded"
          >
            <div class="flex items-center">
              <i class="fas fa-file text-indigo-500 mr-3"></i>
              <div>
                <div class="font-medium text-gray-800 dark:text-gray-100">{{ props.archive.name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-300">
                  {{ formatFileSize(props.archive.file.size) }}
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-500 dark:text-gray-400">No file uploaded.</div>
        </div>

        <!-- File Upload Area -->
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center">
            <i class="fas fa-cloud-upload-alt mr-2 text-indigo-500"></i> Replace/Add File
          </h2>
          <div
            class="file-drop-area p-8 text-center bg-white dark:bg-gray-700 dark:border-gray-600 rounded"
          >
            <div class="flex flex-col items-center justify-center">
              <i class="fas fa-cloud-upload-alt text-5xl text-indigo-400 mb-4"></i>
              <p class="text-gray-600 dark:text-gray-300 mb-2">Drag & drop a new file here</p>
              <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">or</p>
              <label
                for="fileInput"
                class="cursor-pointer px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition"
              >
                Browse File
              </label>
              <input type="file" id="fileInput" class="hidden" @change="handleFiles" />
            </div>
          </div>

          <div v-if="selectedFiles.length > 0" class="mt-4">
            <h3 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Selected File:</h3>
            <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
              <div class="flex items-center">
                <i class="fas fa-file text-indigo-500 mr-3"></i>
                <div>
                  <div class="font-medium text-gray-800 dark:text-gray-100">{{ selectedFiles[0].name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-300">
                    {{ formatFileSize(selectedFiles[0].size) }}
                  </div>
                </div>
              </div>
              <button
                @click="removeFile"
                type="button"
                class="text-red-500 hover:text-red-700 dark:hover:text-red-400"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="mt-4 flex justify-end">
          <button
            type="button"
            @click="submitEdit"
            class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center"
          >
            <i class="fas fa-save mr-2"></i> Save Changes
          </button>
        </div>
      </div>
    </div>
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
</style>
