<template>
  <div class="sync-badge" :class="statusClass" @click="showMenu = !showMenu">
    <div class="badge-icon" :title="statusText">
      <svg v-if="isSyncing" class="animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/>
      </svg>
      <svg v-else-if="isOnline && !isLocal" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
        <path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.07 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/>
      </svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
        <path d="M23.64 7c-.45-.34-4.93-4-11.64-4C5.28 3 .81 6.66.36 7L12 21.5 23.64 7zm-6.6 8.22L3.27 1.44 2 2.72l2.05 2.06C1.91 5.76.59 6.82.36 7l11.63 14.49.01.01.01-.01 3.9-4.86 3.32 3.32 1.27-1.27-3.46-3.46z"/>
      </svg>
      <span v-if="pendingCount > 0" class="pending-dot">{{ pendingCount }}</span>
    </div>

    <div v-if="showMenu" class="badge-menu" @click.stop>
      <div class="menu-row">
        <span class="menu-label">الحالة:</span>
        <span :class="['menu-value', isOnline ? 'online' : 'offline']">
          {{ isOnline ? 'متصل' : 'غير متصل' }}
        </span>
      </div>
      <div class="menu-row">
        <span class="menu-label">الموقع:</span>
        <span :class="['menu-value', isLocal ? 'local' : 'online']">
          {{ isLocal ? 'Local' : 'Online' }}
        </span>
      </div>
      <div class="menu-actions">
        <button
          v-if="isLocal"
          @click="switchToOnline"
          class="menu-btn"
        >🌐 الانتقال لـ Online</button>
        <button
          v-else
          @click="switchToLocal"
          class="menu-btn"
        >💻 الانتقال لـ Local</button>
        <button
          v-if="isOnline && pendingCount > 0"
          @click="doSync"
          :disabled="isSyncing"
          class="menu-btn primary"
        >
          {{ isSyncing ? 'جاري...' : '🔄 مزامنة' }}
        </button>
        <Link :href="route('sync.monitor')" class="menu-link">📊 صفحة المزامنة</Link>
      </div>
    </div>
  </div>
  <div v-if="showMenu" class="badge-overlay" @click="showMenu = false"></div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();
const showMenu = ref(false);
const isOnline = ref(navigator.onLine);
const isSyncing = ref(false);
const pendingCount = ref(0);
const isLocal = ref(
  window.location.href.startsWith('http://127.0.0.1') ||
  window.location.href.startsWith('http://localhost')
);

const statusClass = computed(() => {
  if (isSyncing.value) return 'syncing';
  if (!isOnline.value) return 'offline';
  if (pendingCount.value > 0) return 'pending';
  return 'online';
});

const statusText = computed(() => {
  if (isSyncing.value) return 'جاري المزامنة';
  if (!isOnline.value) return 'غير متصل';
  if (pendingCount.value > 0) return `${pendingCount.value} معلق`;
  return isLocal.value ? 'Local' : 'Online';
});

const updateStatus = async () => {
  try {
    if (window.$db) {
      const queue = await window.$db.getAll('sync_queue', 'synced', false);
      pendingCount.value = Array.isArray(queue) ? queue.length : 0;
    }
  } catch {
    pendingCount.value = 0;
  }
};

const doSync = async () => {
  if (!isOnline.value || isSyncing.value) return;
  isSyncing.value = true;
  try {
    if (window.$db) {
      await window.$db.processSyncQueue();
      await updateStatus();
      toast.success('تمت المزامنة');
    }
  } catch (e) {
    toast.error('فشلت المزامنة');
  } finally {
    isSyncing.value = false;
  }
};

const switchToLocal = () => {
  const url = window.connectionInfo?.local_url || (window.location.origin + '/');
  window.location.href = url;
};

const switchToOnline = () => {
  const url = window.connectionInfo?.online_url || (window.location.origin + '/dashboard');
  window.location.href = url;
};

const onOnline = () => { isOnline.value = true; updateStatus(); };
const onOffline = () => { isOnline.value = false; };

onMounted(() => {
  updateStatus();
  window.addEventListener('online', onOnline);
  window.addEventListener('offline', onOffline);
});

onUnmounted(() => {
  window.removeEventListener('online', onOnline);
  window.removeEventListener('offline', onOffline);
});
</script>

<style scoped>
.sync-badge {
  position: fixed;
  bottom: 16px;
  left: 16px;
  z-index: 9999;
}

.badge-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  transition: transform 0.2s;
  position: relative;
}

.badge-icon:hover {
  transform: scale(1.08);
}

.badge-icon svg {
  width: 22px;
  height: 22px;
  color: white;
}

.sync-badge.online .badge-icon { background: linear-gradient(135deg, #00C851, #007E33); }
.sync-badge.offline .badge-icon { background: linear-gradient(135deg, #ff4444, #cc0000); }
.sync-badge.pending .badge-icon { background: linear-gradient(135deg, #ffbb33, #FF8800); }
.sync-badge.syncing .badge-icon { background: linear-gradient(135deg, #33b5e5, #0099CC); }

.pending-dot {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ff4444;
  color: white;
  font-size: 10px;
  font-weight: bold;
  min-width: 16px;
  height: 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
}

.badge-menu {
  position: absolute;
  bottom: 54px;
  left: 0;
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  min-width: 180px;
  padding: 12px;
}

.menu-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 13px;
}

.menu-label { color: #666; }
.menu-value.online { color: #00C851; font-weight: 600; }
.menu-value.offline { color: #ff4444; font-weight: 600; }
.menu-value.local { color: #f59e0b; font-weight: 600; }

.menu-actions {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.menu-btn {
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  background: #f3f4f6;
  text-align: right;
}

.menu-btn.primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.menu-link {
  display: block;
  padding: 8px;
  text-align: center;
  color: #667eea;
  font-size: 12px;
  text-decoration: none;
  border-radius: 6px;
}

.badge-overlay {
  position: fixed;
  inset: 0;
  z-index: 9998;
}

[dir="rtl"] .sync-badge { left: auto; right: 16px; }
[dir="rtl"] .badge-menu { left: auto; right: 0; }
[dir="rtl"] .pending-dot { right: auto; left: -4px; }
</style>
