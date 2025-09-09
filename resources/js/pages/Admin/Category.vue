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
      onSuccess: () => (showModal.value = false, document.location.reload()),
    })
  } else {
    router.post(route('categories.store'), form.value, {
      onSuccess: () => (showModal.value = false, document.location.reload()),
    })
  }
}
function deleteCategory(id: number) {
  if (confirm('Are you sure you want to delete this category?')) {
    router.delete(route('categories.destroy', id), {
      onSuccess: () => document.location.reload(),
    })
  }
}
</script>

<template>
  <Head title="Category" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto p-4">
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
          Category List
        </h1>
        <button
          @click="openModal()"
          class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700
                 dark:bg-blue-500 dark:hover:bg-blue-600 transition"
        >
          + Create Category
        </button>
      </div>

      <!-- Table -->
      <table class="min-w-full border border-gray-300 dark:border-gray-700 rounded-lg">
        <thead>
          <tr class="bg-gray-100 dark:bg-gray-800">
            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">ID</th>
            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Name</th>
            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Created At</th>
            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="category in categories.data"
            :key="category.id"
            class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
              {{ category.id }}
            </td>
            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
              {{ category.name }}
            </td>
            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
              {{ new Date(category.created_at).toLocaleDateString() }}
            </td>
            <td class="px-4 py-2 flex space-x-2">
              <!-- Edit -->
              <button
                @click="openModal(category)"
                class="px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200
                       dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 transition"
              >
                Edit
              </button>
              <!-- Delete -->
              <button @click="deleteCategory(category.id)"
                class="px-3 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200
                       dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800 transition"
              >
                Delete
              </button>
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
          class="px-3 py-1 border rounded text-gray-800 dark:text-gray-200 dark:border-gray-700"
          :class="{
            'bg-gray-200 font-bold dark:bg-gray-700': link.active,
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
      <div
        class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96 text-gray-900 dark:text-gray-100"
      >
        <h2 class="text-lg font-bold mb-4">
          {{ isEdit ? 'Edit Category' : 'Create Category' }}
        </h2>

        <form @submit.prevent="submitForm">
          <div class="mb-4">
            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
              Name
            </label>
            <input
              v-model="form.name"
              type="text"
              class="w-full border px-3 py-2 rounded bg-white text-gray-900
                     dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700"
              required
            />
          </div>

          <div class="flex justify-end space-x-2">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-100
                     dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700
                     dark:bg-blue-500 dark:hover:bg-blue-600 transition"
            >
              {{ isEdit ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>


