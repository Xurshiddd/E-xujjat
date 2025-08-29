<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  shares: {
    id: number,
    url: string,
    expires_at: string,
    shareable_name: string,
    shareable_type: string,
    receivers: string[]
  }[]
}>()
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
  <Head title="My Shares" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">
        Mening yuborganlarim
      </h1>

      <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
        <thead class="bg-gray-100 dark:bg-gray-800">
          <tr class="text-gray-700 dark:text-gray-200">
            <th class="p-2 border dark:border-gray-700">#</th>
            <th class="p-2 border dark:border-gray-700">Type</th>
            <th class="p-2 border dark:border-gray-700">Nomi</th>
            <th class="p-2 border dark:border-gray-700">Kimlarga yuborilgan</th>
            <th class="p-2 border dark:border-gray-700">Muddati</th>
            <th class="p-2 border dark:border-gray-700">URL</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(share, index) in props.shares"
            :key="share.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-100"
          >
            <td class="p-2 border dark:border-gray-700">{{ index + 1 }}</td>
            <td class="p-2 border dark:border-gray-700">
              {{ share.shareable_type.split("\\").pop() }}
            </td>
            <td class="p-2 border dark:border-gray-700">{{ share.shareable_name }}</td>
            <td class="p-2 border dark:border-gray-700">
              <span
                v-for="(receiver, i) in share.receivers"
                :key="i"
                class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded mr-1"
              >
                {{ receiver }}
              </span>
            </td>
            <td
              class="p-2 border dark:border-gray-700"
              :class="{ 'bg-red-500 text-white': share.expires_at && new Date(share.expires_at) < new Date() }"
            >
              {{ formatDate(share.expires_at) }}
            </td>
            <td class="p-2 border dark:border-gray-700">
              <a
                :href="share.url"
                target="_blank"
                class="text-blue-600 dark:text-blue-400 hover:underline"
              >
                {{ share.url }}
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

