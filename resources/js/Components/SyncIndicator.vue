<template>
    <div class="sync-indicator-container" :style="containerStyle">
        <!-- مؤشر الاتصال -->
        <div 
            class="sync-indicator"
            :class="statusClass"
            @click="handleClick"
            :title="statusText"
        >
            <!-- أيقونة الحالة -->
            <div class="status-icon">
                <svg v-if="isOnline && !isSyncing" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/>
                </svg>
                
                <svg v-else-if="!isOnline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M23.64 7c-.45-.34-4.93-4-11.64-4-1.5 0-2.89.19-4.15.48L18.18 13.8 23.64 7zm-6.6 8.22L3.27 1.44 2 2.72l2.05 2.06C1.91 5.76.59 6.82.36 7l11.63 14.49.01.01.01-.01 3.9-4.86 3.32 3.32 1.27-1.27-3.46-3.46z"/>
                </svg>
                
                <svg v-else-if="isSyncing" class="animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/>
                </svg>
            </div>

            <!-- عدد العناصر في الانتظار -->
            <div v-if="pendingCount > 0" class="pending-badge">
                {{ pendingCount }}
            </div>
        </div>

        <!-- تفاصيل الحالة (Popup) -->
        <transition name="fade">
            <div v-if="showDetails" class="sync-details" @click.stop>
                <div class="sync-details-header">
                    <h3>حالة المزامنة</h3>
                    <button @click="showDetails = false" class="close-btn">&times;</button>
                </div>

                <div class="sync-details-body">
                    <!-- حالة الاتصال -->
                    <div class="detail-item">
                        <span class="detail-label">الاتصال:</span>
                        <span class="detail-value" :class="{ 'text-green': isOnline, 'text-red': !isOnline }">
                            {{ isOnline ? '🌐 متصل' : '📴 غير متصل' }}
                        </span>
                    </div>

                    <!-- حالة المزامنة -->
                    <div class="detail-item">
                        <span class="detail-label">المزامنة:</span>
                        <span class="detail-value">
                            {{ syncStatusText }}
                        </span>
                    </div>

                    <!-- آخر مزامنة -->
                    <div v-if="lastSync" class="detail-item">
                        <span class="detail-label">آخر مزامنة:</span>
                        <span class="detail-value">{{ lastSyncFormatted }}</span>
                    </div>

                    <!-- عناصر في الانتظار -->
                    <div v-if="pendingCount > 0" class="detail-item highlight">
                        <span class="detail-label">في الانتظار:</span>
                        <span class="detail-value">{{ pendingCount }} عملية</span>
                    </div>

                    <!-- زر المزامنة اليدوية -->
                    <button 
                        v-if="isOnline && pendingCount > 0"
                        @click="syncNow"
                        :disabled="isSyncing"
                        class="sync-btn"
                    >
                        <span v-if="!isSyncing">🔄 مزامنة الآن</span>
                        <span v-else>⏳ جاري المزامنة...</span>
                    </button>

                    <!-- رابط لصفحة المراقبة -->
                    <Link
                        :href="route('sync.monitor')"
                        class="monitor-link"
                    >
                        📊 صفحة المراقبة الكاملة
                    </Link>

                    <!-- رسالة Offline -->
                    <div v-if="!isOnline" class="offline-message">
                        <p>⚠️ أنت تعمل في وضع Offline</p>
                        <p class="text-sm">سيتم حفظ تغييراتك محلياً ومزامنتها عند عودة الاتصال</p>
                    </div>

                    <!-- أزرار التبديل بين Local/Online -->
                    <div class="switch-section">
                        <div class="switch-header">
                            <span>🔄 التبديل بين السيرفرات</span>
                        </div>
                        <div class="switch-buttons">
                            <button
                                @click="switchToLocal"
                                class="switch-btn"
                                :class="{ 'active': isLocal, 'disabled': isLocal }"
                                :disabled="isLocal"
                            >
                                💻 Local
                            </button>
                            <button
                                @click="switchToOnline"
                                class="switch-btn"
                                :class="{ 'active': !isLocal, 'disabled': !isLocal }"
                                :disabled="!isLocal"
                            >
                                🌐 Online
                            </button>
                        </div>
                        <div class="current-location">
                            <span class="location-label">الموقع الحالي:</span>
                            <span :class="['location-value', isLocal ? 'local' : 'online']">
                                {{ isLocal ? '💻 Local' : '🌐 Online' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Overlay -->
        <div 
            v-if="showDetails" 
            class="overlay"
            @click="showDetails = false"
        ></div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/inertia-vue3';

// Props للتحكم في المكون
const props = defineProps({
    position: {
        type: String,
        default: 'fixed', // fixed | relative | absolute
        validator: (value) => ['fixed', 'relative', 'absolute'].includes(value)
    },
    bottom: {
        type: String,
        default: '20px'
    },
    left: {
        type: String,
        default: '20px'
    },
    right: {
        type: String,
        default: 'auto'
    },
    top: {
        type: String,
        default: 'auto'
    },
    showSwitchButtons: {
        type: Boolean,
        default: true
    }
});

// البيانات التفاعلية
const isOnline = ref(navigator.onLine);
const isSyncing = ref(false);
const pendingCount = ref(0);
const lastSync = ref(null);
const showDetails = ref(false);
const isLocal = ref(
    window.location.href.startsWith("http://127.0.0.1") || 
    window.location.href.startsWith("http://localhost")
);

// الحالة المحسوبة
const statusClass = computed(() => {
    if (isSyncing.value) return 'syncing';
    if (!isOnline.value) return 'offline';
    if (pendingCount.value > 0) return 'pending';
    return 'online';
});

const statusText = computed(() => {
    if (isSyncing.value) return 'جاري المزامنة...';
    if (!isOnline.value) return 'غير متصل - وضع Offline';
    if (pendingCount.value > 0) return `${pendingCount.value} عملية في الانتظار`;
    return 'متصل - كل شيء محدث';
});

const syncStatusText = computed(() => {
    if (isSyncing.value) return '🔄 جاري المزامنة...';
    if (pendingCount.value > 0) return `⏳ ${pendingCount.value} عملية في الانتظار`;
    return '✅ كل شيء محدّث';
});

const lastSyncFormatted = computed(() => {
    if (!lastSync.value) return 'لم تتم المزامنة بعد';
    
    const date = new Date(lastSync.value);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'منذ لحظات';
    if (minutes < 60) return `منذ ${minutes} دقيقة`;
    if (hours < 24) return `منذ ${hours} ساعة`;
    return `منذ ${days} يوم`;
});

// نمط الحاوية
const containerStyle = computed(() => {
    return {
        position: props.position,
        bottom: props.position === 'fixed' ? props.bottom : 'auto',
        left: props.position === 'fixed' ? props.left : 'auto',
        right: props.position === 'fixed' ? props.right : 'auto',
        top: props.position === 'fixed' ? props.top : 'auto',
    };
});

// الوظائف
const handleClick = () => {
    showDetails.value = !showDetails.value;
};

const syncNow = async () => {
    if (!isOnline.value || isSyncing.value) return;
    
    isSyncing.value = true;
    
    try {
        // استخدام API الخاص بنا
        if (window.$api) {
            await window.$api.syncNow();
            lastSync.value = Date.now();
            await updateSyncStatus();
            
            if (window.$toast) {
                window.$toast.success('تمت المزامنة بنجاح!');
            }
        }
    } catch (error) {
        console.error('فشلت المزامنة:', error);
        
        if (window.$toast) {
            window.$toast.error('فشلت المزامنة: ' + error.message);
        }
    } finally {
        isSyncing.value = false;
    }
};

const updateSyncStatus = async () => {
    try {
        if (window.$api) {
            const status = await window.$api.getSyncStatus();
            pendingCount.value = status.pendingCount;
        }
    } catch (error) {
        console.error('فشل تحديث حالة المزامنة:', error);
    }
};

const handleOnline = async () => {
    isOnline.value = true;
    console.log('🌐 الاتصال عاد');
    
    // مزامنة تلقائية
    await syncNow();
};

const handleOffline = () => {
    isOnline.value = false;
    console.log('📴 فقدان الاتصال');
};

// وظائف التبديل بين Local/Online
const switchToLocal = () => {
    if (window.switchToLocal) {
        window.switchToLocal();
    } else {
        const localUrl = window.connectionInfo?.local_url || (window.location.origin + '/');
        window.location.href = localUrl;
    }
};

const switchToOnline = () => {
    if (window.switchToOnline) {
        window.switchToOnline();
    } else {
        const onlineUrl = window.connectionInfo?.online_url || "https://system.intellijapp.com/dashboard";
        window.location.href = onlineUrl;
    }
};

// دورة حياة المكون
onMounted(() => {
    // إضافة مستمعين
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    
    // تحديث حالة Local عند تغيير الصفحة
    const updateLocation = () => {
        isLocal.value = window.location.href.startsWith("http://127.0.0.1") || 
                        window.location.href.startsWith("http://localhost");
    };
    
    window.addEventListener('focus', updateLocation);
    
    // تحديث الحالة الأولية
    updateSyncStatus();
    updateLocation();
    
    // تحديث دوري
    const interval = setInterval(updateSyncStatus, 30000); // كل 30 ثانية
    
    // حفظ الـ interval للتنظيف لاحقاً
    onUnmounted(() => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
        window.removeEventListener('focus', updateLocation);
        clearInterval(interval);
    });
});
</script>

<style scoped>
.sync-indicator-container {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 9999;
    font-family: Arial, sans-serif;
}

.sync-indicator {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    position: relative;
}

.sync-indicator:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.sync-indicator.online {
    background: linear-gradient(135deg, #00C851 0%, #007E33 100%);
}

.sync-indicator.offline {
    background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
    animation: pulse 2s ease-in-out infinite;
}

.sync-indicator.syncing {
    background: linear-gradient(135deg, #33b5e5 0%, #0099CC 100%);
}

.sync-indicator.pending {
    background: linear-gradient(135deg, #ffbb33 0%, #FF8800 100%);
}

.status-icon {
    width: 24px;
    height: 24px;
    color: white;
}

.status-icon svg {
    width: 100%;
    height: 100%;
}

.pending-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    border: 2px solid white;
}

.sync-details {
    position: fixed;
    bottom: 80px;
    left: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    width: 320px;
    z-index: 10000;
    overflow: hidden;
}

.sync-details-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sync-details-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: bold;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
}

.sync-details-body {
    padding: 15px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item.highlight {
    background: #fff3cd;
    margin: 0 -15px;
    padding: 10px 15px;
}

.detail-label {
    font-weight: 600;
    color: #666;
}

.detail-value {
    color: #333;
}

.text-green {
    color: #00C851;
}

.text-red {
    color: #ff4444;
}

.sync-btn {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s;
}

.sync-btn:hover:not(:disabled) {
    transform: translateY(-2px);
}

.sync-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.monitor-link {
    display: block;
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    background: linear-gradient(135deg, #33b5e5 0%, #0099CC 100%);
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    transition: transform 0.2s;
}

.monitor-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.offline-message {
    margin-top: 15px;
    padding: 12px;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid #ffbb33;
}

.offline-message p {
    margin: 5px 0;
    color: #856404;
}

.offline-message .text-sm {
    font-size: 12px;
}

/* قسم التبديل */
.switch-section {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid #e5e7eb;
}

.switch-header {
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
    font-size: 14px;
}

.switch-buttons {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
}

.switch-btn {
    flex: 1;
    padding: 10px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
    background: white;
    color: #374151;
}

.switch-btn:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #d1d5db;
    transform: translateY(-1px);
}

.switch-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.switch-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #f3f4f6;
}

.current-location {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #f9fafb;
    border-radius: 6px;
    margin-top: 8px;
}

.location-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.location-value {
    font-size: 12px;
    font-weight: 600;
}

.location-value.online {
    color: #10b981;
}

.location-value.local {
    color: #f59e0b;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 9998;
}

/* Animations */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* RTL Support */
[dir="rtl"] .sync-indicator-container {
    left: auto;
    right: 20px;
}

[dir="rtl"] .sync-details {
    left: auto;
    right: 20px;
}

[dir="rtl"] .pending-badge {
    right: auto;
    left: -5px;
}
</style>

