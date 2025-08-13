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
    <div
      v-for="folder in props.folders.data"
      :key="folder.id"
      class="p-4 bg-white rounded shadow"
    >
      <h1 class="text-2xl font-bold mb-2">{{ folder.name }}</h1>
      <p class="text-gray-700">Code: {{ folder.code }}</p>
      <p class="text-gray-500 text-sm">User ID: {{ folder.user_id }}</p>
      <h2>User_name {{ user.name }}</h2>
    </div>
  </div>

  <p v-else class="text-gray-500">No folders found.</p>
  </AppLayout>
</template>
