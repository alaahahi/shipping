<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: { type: Object, default: null }
});

const displayUsers = ref([]);
const POLL_INTERVAL = 15000;
let pollTimer = null;

function getInitials(name) {
  if (!name || typeof name !== 'string') return '?';
  const n = String(name).trim();
  if (!n) return '?';
  const parts = n.split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] || '') + (parts[1][0] || '');
  }
  return n.substring(0, 2) || '?';
}

function buildUserFromAuth(u) {
  if (!u) return null;
  return {
    id: u.id,
    name: 'أنت (' + (u.name || '') + ')',
    initials: getInitials(u.name || ''),
  };
}

function updateFromUser() {
  const me = buildUserFromAuth(props.user);
  if (me && displayUsers.value.length === 0) {
    displayUsers.value = [me];
  }
}

function fetchOnlineUsers() {
  if (!props.user?.id) return;
  axios.get('/online-users', { withCredentials: true })
    .then(res => {
      const list = res.data?.online_users || [];
      displayUsers.value = list.length > 0 ? list : [buildUserFromAuth(props.user)];
    })
    .catch(() => {
      displayUsers.value = [buildUserFromAuth(props.user)];
    });
}

onMounted(() => {
  updateFromUser();
  fetchOnlineUsers();
  pollTimer = setInterval(fetchOnlineUsers, POLL_INTERVAL);
});

watch(() => props.user, (user) => {
  if (user && displayUsers.value.length === 0) {
    displayUsers.value = [buildUserFromAuth(user)];
  }
}, { immediate: true });

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
});

// دائماً نعرض قائمة: إما من API أو المستخدم الحالي على الأقل
const listToShow = computed(() => {
  if (displayUsers.value.length > 0) return displayUsers.value;
  return props.user ? [buildUserFromAuth(props.user)] : [];
});
</script>

<template>
  <div v-if="user" class="online-users-indicator flex items-center gap-2 print:hidden shrink-0">
    <div class="flex items-center -space-x-3">
      <div
        v-for="user in listToShow"
        :key="user.id"
        class="online-user-avatar group relative"
        :title="user.name"
      >
        <div class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-gray-400 dark:bg-gray-600 rounded-full border-2 border-white dark:border-gray-800 shadow-sm cursor-default hover:z-10 hover:scale-105 transition-transform">
          <span class="font-medium text-sm text-white">{{ user.initials }}</span>
          <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
        </div>
        <div class="absolute top-full left-1/2 -translate-x-1/2 mt-1 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">
          {{ user.name }}
        </div>
      </div>
    </div>
  </div>
</template>
