<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps<{
  shares: {
    id: number,
    url: string,
    expires_at: string,
    shareable_name: string,
    sender_name: string,
    type: string,
    has_password: boolean
  }[]
}>()

const showPasswordModal = ref(false)
const selectedShare: any = ref(null)
const password = ref('')
const errorMessage = ref('')

function downloadShare(share: any) {
  if (share.has_password) {
    selectedShare.value = share
    password.value = ''
    errorMessage.value = ''
    showPasswordModal.value = true
  } else {
    window.location.href = share.url
  }
}

async function verifyPassword() {
  try {
    const res = await axios.get(`/s/document/${selectedShare.value.token}`, {
      params: { password: password.value },
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', res.headers['content-disposition']
  ?.split('filename=')[1] || 'file'
)
    document.body.appendChild(link)
    link.click()

    showPasswordModal.value = false
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Xatolik yuz berdi'
  }
}
function formatDate(dateStr: string | null | undefined): string {
  if (!dateStr) return "Cheksiz";

  const date = new Date(dateStr);

  return date.toLocaleString("uz-UZ", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}
</script>

<template>
  <Head title="Received Shares" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">
        Menga yuborilganlar
      </h1>

      <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
        <thead class="bg-gray-100 dark:bg-gray-800">
          <tr class="text-gray-700 dark:text-gray-200">
            <th class="p-2 border dark:border-gray-700">#</th>
            <th class="p-2 border dark:border-gray-700">Kimdan</th>
            <th class="p-2 border dark:border-gray-700">Type</th>
            <th class="p-2 border dark:border-gray-700">Nomi</th>
            <th class="p-2 border dark:border-gray-700">Muddati</th>
            <th class="p-2 border dark:border-gray-700">Amal</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(share, index) in props.shares"
            :key="share.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-100 text-center"
          >
            <td class="p-2 border dark:border-gray-700">{{ index + 1 }}</td>
            <td class="p-2 border dark:border-gray-700">{{ share.sender_name }}</td>
            <td class="p-2 border dark:border-gray-700">{{ share.type }}</td>
            <td class="p-2 border dark:border-gray-700">{{ share.shareable_name }}</td>
            <td
              class="p-2 border dark:border-gray-700"
              :class="{ 'bg-red-500 text-white': share.expires_at && new Date(share.expires_at) < new Date() }"
            >
              {{ formatDate(share.expires_at) }}
            </td>
            <td class="p-2 border dark:border-gray-700">
              <button
                class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
                @click="downloadShare(share)"
              >
                Yuklab olish
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Password modal -->
    <div
      v-if="showPasswordModal"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg w-96">
        <h2 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">
          Parolni kiriting
        </h2>
        <input
          type="password"
          v-model="password"
          placeholder="Parol"
          class="border w-full p-2 rounded mb-3 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
        />
        <p v-if="errorMessage" class="text-red-500 mb-2">{{ errorMessage }}</p>
        <div class="flex justify-end space-x-2">
          <button
            class="px-3 py-1 bg-gray-300 dark:bg-gray-600 dark:text-gray-200 rounded"
            @click="showPasswordModal = false"
          >
            Bekor qilish
          </button>
          <button
            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
            @click="verifyPassword"
          >
            Tasdiqlash
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

