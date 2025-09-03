<script setup lang="ts">
import { type BreadcrumbItem } from '@/types'
import { Head, usePage, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'

const { props } = usePage<{ categories: any }>()
const categories = props.categories

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Kategoriya', href: '/categories' },
]

// Modal state
const showModal = ref(false)
const isEdit = ref(false)
const form = ref<{ id?: number; name: string }>({
  id: undefined,
  name: '',
})

// Modal ochish (create yoki edit uchun)
const openModal = (category: any = null) => {
  if (category) {
    isEdit.value = true
    form.value = { id: category.id, name: category.name }
  } else {
    isEdit.value = false
    form.value = { name: '' }
  }
  showModal.value = true
}

// Yuborish
const submitForm = () => {
  if (isEdit.value && form.value.id) {
    router.put(route('categories.update', form.value.id), form.value, {
      onSuccess: () => (showModal.value = false),
    })
  } else {
    router.post(route('categories.store'), form.value, {
      onSuccess: () => (showModal.value = false),
    })
  }
}
</script>

<template>
  <Head title="Category" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto p-4">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Category List</h1>
        <button
          @click="openModal()"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          + Create Category
        </button>
      </div>

      <table class="min-w-full border border-gray-300 rounded-lg">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">ID</th>
            <th class="px-4 py-2 text-left">Name</th>
            <th class="px-4 py-2 text-left">Created At</th>
            <th class="px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="category in categories.data"
            :key="category.id"
            class="border-b hover:bg-gray-50"
          >
            <td class="px-4 py-2">{{ category.id }}</td>
            <td class="px-4 py-2">{{ category.name }}</td>
            <td class="px-4 py-2">
              {{ new Date(category.created_at).toLocaleDateString() }}
            </td>
            <td class="px-4 py-2 space-x-2">
              <button
                @click="openModal(category)"
                class="text-blue-600 hover:underline"
              >
                Edit
              </button>
              <Link
                as="button"
                method="delete"
                :href="route('categories.destroy', category.id)"
                class="text-red-600 hover:underline"
              >
                Delete
              </Link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="mt-4 flex justify-center space-x-2">
        <Link
          v-for="link in categories.links"
          :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3 py-1 border rounded"
          :class="{
            'bg-gray-200 font-bold': link.active,
            'text-gray-400 pointer-events-none': !link.url,
          }"
        />
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-bold mb-4">
          {{ isEdit ? 'Edit Category' : 'Create Category' }}
        </h2>

        <form @submit.prevent="submitForm">
          <div class="mb-4">
            <label class="block mb-1 text-sm font-medium">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full border px-3 py-2 rounded"
              required
            />
          </div>

          <div class="flex justify-end space-x-2">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 border rounded"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              {{ isEdit ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
