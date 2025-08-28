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
</script>

<template>
  <Head title="Received Shares" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Menga yuborilganlar</h1>

      <table class="w-full border border-gray-300 rounded-lg">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 border">#</th>
            <th class="p-2 border">Kimdan</th>
            <th class="p-2 border">Type</th>
            <th class="p-2 border">Nomi</th>
            <th class="p-2 border">Muddati</th>
            <th class="p-2 border">Amal</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(share, index) in props.shares" :key="share.id" class="hover:bg-gray-50">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ share.sender_name }}</td>
            <td class="p-2 border">{{ share.type }}</td>
            <td class="p-2 border">{{ share.shareable_name }}</td>
            <td class="p-2 border">{{ share.expires_at ?? 'Cheksiz' }}</td>
            <td class="p-2 border">
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
      <div class="bg-white p-6 rounded shadow-lg w-96">
        <h2 class="text-lg font-bold mb-4">Parolni kiriting</h2>
        <input
          type="password"
          v-model="password"
          placeholder="Parol"
          class="border w-full p-2 rounded mb-3"
        />
        <p v-if="errorMessage" class="text-red-500 mb-2">{{ errorMessage }}</p>
        <div class="flex justify-end space-x-2">
          <button
            class="px-3 py-1 bg-gray-300 rounded"
            @click="showPasswordModal = false"
          >Bekor qilish</button>
          <button
            class="px-3 py-1 bg-green-600 text-white rounded"
            @click="verifyPassword"
          >Tasdiqlash</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
