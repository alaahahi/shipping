<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: { type: Object, default: null }
});

const displayUsers = ref([]);
const POLL_INTERVAL = 5 * 60 * 1000; // فحص الكاش كل 5 دقائق
const CACHE_TTL = 60 * 60 * 1000; // كاش الهيدر: ساعة واحدة
const CACHE_KEY_PREFIX = 'header_online_users_';
let pollTimer = null;

function getCacheKey(userId) {
  return `${CACHE_KEY_PREFIX}${userId}`;
}

function readCachedUsers(userId) {
  try {
    const raw = localStorage.getItem(getCacheKey(userId));
    if (!raw) return null;

    const parsed = JSON.parse(raw);
    if (!parsed?.expiresAt || Date.now() > parsed.expiresAt) {
      localStorage.removeItem(getCacheKey(userId));
      return null;
    }

    return Array.isArray(parsed.users) ? parsed.users : null;
  } catch {
    return null;
  }
}

function writeCachedUsers(userId, users) {
  try {
    localStorage.setItem(getCacheKey(userId), JSON.stringify({
      users,
      expiresAt: Date.now() + CACHE_TTL,
    }));
  } catch {
    // تجاهل إذا امتلأ التخزين
  }
}

function applyUsers(list) {
  displayUsers.value = list.length > 0 ? list : [buildUserFromAuth(props.user)];
}

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

function fetchOnlineUsers(force = false) {
  if (!props.user?.id) return;

  if (!force) {
    const cached = readCachedUsers(props.user.id);
    if (cached) {
      applyUsers(cached);
      return;
    }
  }

  axios.get('/online-users', { withCredentials: true })
    .then(res => {
      const list = res.data?.online_users || [];
      writeCachedUsers(props.user.id, list);
      applyUsers(list);
    })
    .catch(() => {
      const cached = readCachedUsers(props.user.id);
      if (cached) {
        applyUsers(cached);
        return;
      }
      displayUsers.value = [buildUserFromAuth(props.user)];
    });
}

onMounted(() => {
  updateFromUser();
  const cached = props.user?.id ? readCachedUsers(props.user.id) : null;
  if (cached) {
    applyUsers(cached);
  } else {
    fetchOnlineUsers(true);
  }
  pollTimer = setInterval(() => fetchOnlineUsers(false), POLL_INTERVAL);
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
