<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';
import axios from 'axios';

const page = usePage();
const onlineUsers = ref([]);
const loading = ref(false);
const POLL_INTERVAL = 15000; // 15 ثانية
let pollTimer = null;

function getInitials(name) {
  if (!name || typeof name !== 'string') return '?';
  const n = name.trim();
  if (!n) return '?';
  const parts = n.split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] || '') + (parts[1][0] || '');
  }
  return n.substring(0, 2) || '?';
}

// عرض أولي: المستخدم الحالي من الصفحة (يظهر فوراً قبل استجابة API)
const currentUserDisplay = computed(() => {
  const u = page.props?.auth?.user;
  if (!u) return null;
  return {
    id: u.id,
    name: 'أنت (' + (u.name || '') + ')',
    initials: getInitials(u.name || ''),
    is_me: true,
  };
});

const displayUsers = computed(() => {
  if (onlineUsers.value.length > 0) return onlineUsers.value;
  if (currentUserDisplay.value) return [currentUserDisplay.value];
  return [];
});

function fetchOnlineUsers() {
  if (!page.props?.auth?.user?.id) return;
  loading.value = true;
  axios.get('/api/online-users', { withCredentials: true })
    .then(res => {
      onlineUsers.value = res.data?.online_users || [];
    })
    .catch(() => {
      onlineUsers.value = currentUserDisplay.value ? [currentUserDisplay.value] : [];
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
  <div v-if="displayUsers.length > 0" class="online-users-indicator flex items-center gap-2 print:hidden">
    <div class="flex items-center -space-x-3">
      <div
        v-for="user in displayUsers"
        :key="user.id"
        class="online-user-avatar group relative"
        :title="user.name"
      >
        <div class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-gray-400 dark:bg-gray-600 rounded-full border-2 border-white dark:border-gray-800 shadow-sm cursor-default hover:z-10 hover:scale-105 transition-transform">
          <span class="font-medium text-sm text-white">{{ user.initials }}</span>
          <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
        </div>
        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">
          {{ user.name }}
        </div>
      </div>
    </div>
  </div>
</template>
