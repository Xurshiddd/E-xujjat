<script setup lang="ts">
import { type BreadcrumbItem, type User} from '@/types'
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Folders', href: '/folders' },
]

type Folder = {
  id: number
  name: string
  code: string
  user_id: number
  created_at: Date
}

interface FoldersResponse {
  data: Folder[]
  links: any[]
  meta: any
}


interface Props {
  folders: FoldersResponse
}

const props = defineProps<Props>()
const user = usePage().props.auth.user as User
console.log(props.folders);
</script>

<template>
  <Head title="Folders" />
<AppLayout :breadcrumbs="breadcrumbs">
  <nav class="mb-4 flex space-x-2 text-sm text-gray-500">
    <span v-for="(item, index) in breadcrumbs" :key="index">
      <a :href="item.href" class="hover:underline">{{ item.title }}</a>
      <span v-if="index < breadcrumbs.length - 1">/</span>
    </span>
  </nav>

  <div v-if="props.folders.data.length > 0" class="space-y-4">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Folder Manager</h1>
                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-plus mr-2"></i> New Folder
                    </button>
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
                                <td class="px-6 py-4">N/A</td>
                                <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button class="text-blue-500 hover:text-blue-700" title="Open">
                                <i class="fas fa-folder-open"></i>
                            </button>
                            <button class="text-green-500 hover:text-green-700" title="Share">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <button class="text-red-500 hover:text-red-700" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
                    <div>Showing <span id="showingStart">1</span> to <span id="showingEnd">5</span> of <span id="totalItems">12</span> folders</div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 border rounded-md hover:bg-gray-100" onclick="prevPage()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="px-3 py-1 border rounded-md bg-blue-500 text-white">1</button>
                        <button class="px-3 py-1 border rounded-md hover:bg-gray-100" onclick="nextPage()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <p v-else class="text-gray-500">No folders found.</p>
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
</style>
