<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import axios from 'axios';

const page = usePage();
const onlineUsers = ref([]);
const loading = ref(false);
const POLL_INTERVAL = 15000; // 15 ثانية
let pollTimer = null;

function fetchOnlineUsers() {
  if (!page.props?.auth?.user?.id) return;
  loading.value = true;
  axios.get('/api/online-users', { withCredentials: true })
    .then(res => {
      onlineUsers.value = res.data?.online_users || [];
    })
    .catch(() => {
      onlineUsers.value = [];
    })
    .finally(() => {
      loading.value = false;
    });
}

onMounted(() => {
  fetchOnlineUsers();
  pollTimer = setInterval(fetchOnlineUsers, POLL_INTERVAL);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
});
</script>

<template>
  <div v-if="onlineUsers.length > 0" class="online-users-indicator flex items-center gap-1.5">
    <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">متصلون:</span>
    <div class="flex items-center -space-x-2">
      <div
        v-for="user in onlineUsers"
        :key="user.id"
        class="online-user-avatar group relative"
        :title="user.name"
      >
        <div
          class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white border-2 border-white dark:border-gray-800 shadow-sm cursor-default bg-emerald-500 hover:z-10 hover:scale-110 transition-transform"
          :style="{ backgroundColor: 'var(--user-color, #10b981)' }"
        >
          {{ user.initials }}
        </div>
        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">
          {{ user.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.online-user-avatar {
  position: relative;
}
.online-user-avatar:hover .absolute {
  opacity: 1;
}
</style>
