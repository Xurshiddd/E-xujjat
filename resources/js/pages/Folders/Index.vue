<script setup lang="ts">
import { type BreadcrumbItem, type User} from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue'
import axios from 'axios'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Papkalar', href: '/folders' },
]
function formatFileSize(bytes: number) {
  if (bytes === 0) return '0 B';
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
}
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

const user = usePage().props.auth.user as User

const showModal = ref(false)
const newFolderName = ref('')
const responseMessage = ref('')
const errorMessage = ref('')

const editModal = ref(false)
const editFolderName = ref('')
const editingFolderId = ref<number|null>(null)

function openModal() {
  showModal.value = true
}
function closeModal() {
  showModal.value = false
  newFolderName.value = ''
}
function showResponse(msg: string) {
  responseMessage.value = msg
  setTimeout(() => responseMessage.value = '', 5000)
}
function showError(msg: string) {
  errorMessage.value = msg
  setTimeout(() => errorMessage.value = '', 5000)
}

function createFolder() {
  axios.post('/folders', {
    name: newFolderName.value,
  },{
     maxContentLength: Infinity,
  maxBodyLength: Infinity,
  })
  .then(response => {
    // Folders ro‘yxatiga qo‘shish
    props.folders.data.push(response.data.data)

    // Inputni tozalash
    newFolderName.value = ''

    // Modalni yopish
    closeModal()

    // Xabar chiqarish
    showResponse(response.data.message || 'Papka muvaffaqiyatli yaratildi!')
  })
  .catch(error => {
    showError(error.response?.data?.message || 'Xatolik: papka yaratilmagan!')
    console.error('Error creating folder:', error)
  })
}

function deleteFolder(id: number){
    axios.delete(`/folders/${id}`,{
        headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
    }).then(response => {
      window.location.reload()
    // alert(response.data.message)
    })
    .catch(error => {
      console.error("Papka o'chirishda xato:", error)
      alert("Papka o'chirishda xato yuz berdi.")
    })
}
function confirmDelete(id: number) {
  if (confirm("Papkani o‘chirishga ishonchingiz komilmi?")) {
    deleteFolder(id)
  }
}

function openEditModal(folder: Folder) {
  editFolderName.value = folder.name
  editingFolderId.value = folder.id
  editModal.value = true
}
function closeEditModal() {
  editModal.value = false
  editFolderName.value = ''
  editingFolderId.value = null
}
function updateFolder() {
  if (!editingFolderId.value) return
  axios.put(`/folders/${editingFolderId.value}`, {
    name: editFolderName.value,
  })
  .then(response => {
    const idx = props.folders.data.findIndex(f => f.id === editingFolderId.value)
    if (idx !== -1) props.folders.data[idx].name = editFolderName.value

    closeEditModal()
    showResponse(response.data.message || 'Papka nomi o‘zgartirildi!')
  })
  .catch(error => {
    showError(error.response?.data?.message || 'Xatolik: papka nomi o‘zgartirilmadi!')
    console.error('Error updating folder:', error)
  })
}
</script>

<template>
  <Head title="Folders" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Success message -->
    <div v-if="responseMessage" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50 transition">
      {{ responseMessage }}
    </div>
    <!-- Error message -->
    <div v-if="errorMessage" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white px-6 py-3 rounded shadow-lg z-50 transition">
      {{ errorMessage }}
    </div>

    <nav class="mb-4 flex space-x-2 text-sm text-gray-500">
      <span v-for="(item, index) in breadcrumbs" :key="index">
        <a :href="item.href" class="hover:underline">{{ item.title }}</a>
        <span v-if="index < breadcrumbs.length - 1">/</span>
      </span>
    </nav>
<div>
  <button @click="openModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                          <i class="fas fa-plus mr-2"></i> New Folder
                      </button>
                    </div>
    <div v-if="props.folders.data.length > 0" class="space-y-4">
      <div class="container mx-auto px-4 py-8 max-w-6xl">
          <div class="bg-white rounded-xl shadow-md overflow-hidden">
              <div class="p-6">
                  <div class="flex justify-between items-center mb-6">
                      <h1 class="text-2xl font-bold text-gray-800">Folder Manager</h1>
                  </div>

                  <div class="relative overflow-x-auto responsive-table">
                      <table class="w-full text-sm text-left text-gray-500">
                          <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                              <tr>
                                  <th scope="col" class="px-6 py-3 sortable" onclick="sortTable(0)">
                                      <div class="flex items-center">
                                          Name
                                          <i class="fas fa-sort ml-2 text-gray-400"></i>
                                      </div>
                                  </th>
                                  <th scope="col" class="px-6 py-3 sortable" onclick="sortTable(1)">
                                      <div class="flex items-center">
                                          Date Modified
                                          <i class="fas fa-sort ml-2 text-gray-400"></i>
                                      </div>
                                  </th>
                                  <th scope="col" class="px-6 py-3 sortable" onclick="sortTable(2)">
                                      <div class="flex items-center">
                                          Type
                                          <i class="fas fa-sort ml-2 text-gray-400"></i>
                                      </div>
                                  </th>
                                  <th scope="col" class="px-6 py-3 sortable" onclick="sortTable(3)">
                                      <div class="flex items-center">
                                          Size
                                          <i class="fas fa-sort ml-2 text-gray-400"></i>
                                      </div>
                                  </th>
                                  <th scope="col" class="px-6 py-3">
                                      Actions
                                  </th>
                              </tr>
                          </thead>
                          <tbody id="folderTable">
                              <tr v-for="folder in props.folders.data" :key="folder.id" class="folder-row hover:bg-gray-50 transition-colors">
                                  <td class="px-6 py-4 flex items-center">
                                      <i class="fas fa-folder folder-icon mr-2 text-yellow-500"></i>
                                      {{ folder.name }}
                                  </td>
                                  <td class="px-6 py-4">{{ new Date(folder.created_at).toLocaleDateString() }}</td>
                                  <td class="px-6 py-4">Folder</td>
                                  <td class="px-6 py-4">{{ formatFileSize(folder.size) }}</td>
                                  <td class="px-6 py-4">
                          <div class="flex space-x-2">
                              <button class="text-blue-500 hover:text-blue-700" title="Open">
                                  <a :href="route('folders.show', folder.id)"><i class="fas fa-folder-open"></i></a>
                              </button>
                              <button class="text-green-500 hover:text-green-700" title="Share">
                                  <i class="fas fa-share-alt"></i>
                              </button>
                              <button class="text-red-500 hover:text-red-700" @click="confirmDelete(folder.id)" title="Delete">
                                  <i class="fas fa-trash-alt"></i>
                              </button>
                              <button class="text-yellow-500 hover:text-yellow-700" @click="openEditModal(folder)" title="Edit">
                                  <i class="fas fa-edit"></i>
                              </button>
                          </div>
                      </td>
                              </tr>
                          </tbody>
                      </table>
                  </div>

                  <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
                    <div>Showing <span id="showingStart">{{ props.folders.from }}</span> to <span id="showingEnd">{{ props.folders.to }}</span> of <span id="totalItems">{{ props.folders.total }}</span> folders</div>
                    <div class="flex justify-end space-x-2">
                    <div class="flex space-x-2" v-for="value in props.folders.links" :key="value.label">
                      <a
                        :href="value.url || '#'"
                        :class="[
                          'px-3 py-1 border rounded-md',
                          value.active ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 hover:bg-gray-100',
                          !value.url ? 'pointer-events-none opacity-50' : ''
                        ]"
                        v-html="value.label"
                      ></a>
                    </div>
                    </div>
                  </div>
              </div>
          </div>
      </div>
    </div>

    <p v-else class="text-gray-500">No folders found.</p>

    <!-- New Folder Modal -->
    <transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
          <h2 class="text-lg font-semibold mb-4">Yangi papka yaratish</h2>
          <div class="mb-4">
            <label for="folderName" class="block text-sm font-medium text-gray-700 mb-2">Papka nomi</label>
            <input
              v-model="newFolderName"
              type="text"
              id="folderName"
              class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter folder name"
            />
          </div>
          <div class="flex justify-end space-x-2">
            <button @click="closeModal" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
              Bekor qilish
            </button>
            <button @click="createFolder" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
              Saqlash
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Edit Folder Modal -->
    <transition name="modal">
      <div v-if="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
          <h2 class="text-lg font-semibold mb-4">Papka nomini o‘zgartirish</h2>
          <div class="mb-4">
            <label for="editFolderName" class="block text-sm font-medium text-gray-700 mb-2">Yangi papka nomi</label>
            <input
              v-model="editFolderName"
              type="text"
              id="editFolderName"
              class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter new folder name"
            />
          </div>
          <div class="flex justify-end space-x-2">
            <button @click="closeEditModal" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
              Bekor qilish
            </button>
            <button @click="updateFolder" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
              Yangilash
            </button>
          </div>
        </div>
      </div>
    </transition>
    </AppLayout>
</template>

<style scoped>

 .folder-row:hover {
            background-color: #f3f4f6;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .sortable:hover {
            background-color: #e5e7eb;
            cursor: pointer;
        }

        @media (max-width: 640px) {
            .responsive-table {
                font-size: 0.8rem;
            }
            .folder-icon {
                font-size: 1rem;
            }
        }

.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}
.modal-enter, .modal-leave-to /* .modal-leave-active in <2.1.8 */ {
  opacity: 0;
}
</style>
