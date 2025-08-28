<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  shares: {
    id: number,
    url: string,
    expires_at: string,
    shareable_name: string,
    receivers: string[]
  }[]
}>()
</script>

<template>
  <Head title="My Shares" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Mening yuborganlarim</h1>

      <table class="w-full border border-gray-300 rounded-lg">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 border">#</th>
            <th class="p-2 border">Nomi</th>
            <th class="p-2 border">Kimlarga yuborilgan</th>
            <th class="p-2 border">Muddati</th>
            <th class="p-2 border">URL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(share, index) in props.shares" :key="share.id" class="hover:bg-gray-50">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ share.shareable_name }}</td>
            <td class="p-2 border">
              <span
                v-for="(receiver, i) in share.receivers"
                :key="i"
                class="px-2 py-1 bg-blue-100 text-blue-800 rounded mr-1"
              >
                {{ receiver }}
              </span>
            </td>
            <td class="p-2 border">
              {{ share.expires_at ?? 'Cheksiz' }}
            </td>
            <td class="p-2 border text-blue-600">
              <a :href="share.url" target="_blank">{{ share.url }}</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
