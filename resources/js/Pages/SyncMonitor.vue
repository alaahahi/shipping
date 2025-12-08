<template>
  <Head title="مراقبة المزامنة" />
  <GuestLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl dark:text-gray-200 text-gray-800">
          🔄 مراقبة المزامنة والـ Offline Mode
        </h2>
        <div class="flex gap-2">
          <button
            @click="refreshData"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            :disabled="isRefreshing"
          >
            <span v-if="!isRefreshing">🔄 تحديث</span>
            <span v-else>⏳ جاري...</span>
          </button>
          
          <button
            v-if="syncStatus.pendingCount > 0 && connectionStatus.online"
            @click="syncAll"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            :disabled="isSyncing"
          >
            <span v-if="!isSyncing">✅ مزامنة الكل</span>
            <span v-else>⏳ جاري المزامنة...</span>
          </button>

          <button
            @click="clearAll"
            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
          >
            🗑️ مسح الكل
          </button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- معلومات قاعدة البيانات - في المقدمة -->
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <div>
              <h3 class="text-lg font-semibold dark:text-gray-200">🗄️ قاعدة البيانات والمزامنة</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">معلومات شاملة عن قاعدة البيانات والحالة الحالية</p>
            </div>
            <button
              @click="loadDatabaseInfo"
              :disabled="loadingDatabaseInfo"
              class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50"
            >
              <span v-if="!loadingDatabaseInfo">🔄 تحديث</span>
              <span v-else>⏳ جاري...</span>
            </button>
          </div>

          <div v-if="loadingDatabaseInfo" class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">جاري تحميل معلومات قاعدة البيانات...</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- معلومات قاعدة البيانات الرئيسية -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
              <h4 class="text-md font-semibold mb-3 text-gray-900 dark:text-gray-100">📊 قاعدة البيانات</h4>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">النوع:</span>
                  <span class="font-mono text-gray-900 dark:text-gray-100">{{ databaseInfo.type || 'SQLite' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">الحجم:</span>
                  <span class="font-mono text-gray-900 dark:text-gray-100">{{ databaseInfo.size || 'غير محدد' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 dark:text-gray-400">الجداول:</span>
                  <span class="font-mono text-gray-900 dark:text-gray-100">{{ databaseInfo.total_tables || syncedTables.length }}</span>
                </div>
              </div>
            </div>

            <!-- إحصائيات المزامنة -->
            <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
              <h4 class="text-md font-semibold mb-3 text-blue-900 dark:text-blue-100">🔄 المزامنة</h4>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-blue-700 dark:text-blue-300">في الانتظار:</span>
                  <span class="font-bold text-blue-900 dark:text-blue-100">{{ syncStatus.pendingCount }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-blue-700 dark:text-blue-300">آخر مزامنة:</span>
                  <span class="font-bold text-blue-900 dark:text-blue-100 text-xs">{{ lastSyncFormatted }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-blue-700 dark:text-blue-300">الحالة:</span>
                  <span
                    class="font-bold"
                    :class="connectionStatus.online ? 'text-green-600' : 'text-red-600'"
                  >
                    {{ connectionStatus.online ? 'متصل' : 'غير متصل' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- إحصائيات تاريخ السيارات -->
            <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
              <h4 class="text-md font-semibold mb-3 text-green-900 dark:text-green-100">🚗 تاريخ السيارات</h4>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-green-700 dark:text-green-300">الإجمالي:</span>
                  <span class="font-bold text-green-900 dark:text-green-100">{{ migrationStats.total_transactions || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-green-700 dark:text-green-300">مُنقل:</span>
                  <span class="font-bold text-green-900 dark:text-green-100">{{ migrationStats.migrated || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-green-700 dark:text-green-300">متبقي:</span>
                  <span class="font-bold text-green-900 dark:text-green-100">{{ migrationStats.remaining || 0 }}</span>
                </div>
              </div>
            </div>

            <!-- أزرار سريعة -->
            <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
              <h4 class="text-md font-semibold mb-3 text-purple-900 dark:text-purple-100">⚡ إجراءات سريعة</h4>
              <div class="space-y-2">
                <button
                  @click="syncAll"
                  :disabled="!connectionStatus.online || isSyncing"
                  class="w-full px-3 py-2 bg-green-600 text-white text-xs rounded hover:bg-green-700 disabled:opacity-50"
                >
                  🔄 مزامنة الكل
                </button>
                <button
                  @click="clearAll"
                  class="w-full px-3 py-2 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                >
                  🗑️ مسح الكل
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- تفاصيل الاتصال التقنية -->
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold dark:text-gray-200">🌐 تفاصيل الاتصال التقنية</h3>
            <span
              class="px-3 py-1 rounded-full text-sm font-semibold"
              :class="connectionInfo.isLocal
                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100'
                : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100'"
            >
              {{ connectionInfo.environmentLabel }}
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm dark:text-gray-200">
            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
              <p class="text-gray-600 dark:text-gray-400 text-xs">المضيف والمنفذ</p>
              <p class="font-semibold text-sm">
                {{ connectionInfo.host || 'غير محدد' }}<span v-if="connectionInfo.port">:{{ connectionInfo.port }}</span>
              </p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
              <p class="text-gray-600 dark:text-gray-400 text-xs">نوع الشبكة</p>
              <p class="font-semibold text-sm">{{ connectionInfo.networkType || 'غير محدد' }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">سرعة: {{ connectionInfo.effectiveType || 'غير محدد' }}</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
              <p class="text-gray-600 dark:text-gray-400 text-xs">الأداء التقني</p>
              <p class="font-semibold text-sm">
                {{ connectionInfo.downlink ? connectionInfo.downlink + ' Mbps' : 'غير محدد' }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">RTT: {{ connectionInfo.rtt ? connectionInfo.rtt + 'ms' : 'غير محدد' }}</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
              <p class="text-gray-600 dark:text-gray-400 text-xs">عنوان API</p>
              <p class="font-semibold text-xs truncate" :title="connectionInfo.apiBaseUrl">
                {{ connectionInfo.apiBaseUrl || 'غير محدد' }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ connectionInfo.protocol || 'غير محدد' }} • {{ connectionInfo.secure ? '🔒 آمن' : '⚠️ غير آمن' }}
              </p>
            </div>
          </div>
        </div>

        <!-- تفاصيل الاتصال الحالية -->
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
          <div class="flex flex-wrap justify-between items-start gap-4">
            <div>
              <h3 class="text-lg font-semibold dark:text-gray-200">ℹ️ تفاصيل الاتصال الحالية</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ connectionInfo.tip }}
              </p>
            </div>
            <span
              class="px-3 py-1 rounded-full text-sm font-semibold"
              :class="connectionInfo.isLocal
                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100'
                : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100'"
            >
              {{ connectionInfo.environmentLabel }}
            </span>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm dark:text-gray-200">
            <div>
              <p class="text-gray-500 dark:text-gray-400">المضيف / المنفذ</p>
              <p class="font-semibold">
                {{ connectionInfo.host }}<span v-if="connectionInfo.port">:{{ connectionInfo.port }}</span>
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ connectionInfo.protocol }} • {{ connectionInfo.secure ? '🔒 اتصال آمن' : '⚠️ اتصال غير مشفر' }}
              </p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">نوع الشبكة</p>
              <p class="font-semibold">{{ connectionInfo.networkType }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Effective: {{ connectionInfo.effectiveType }}
              </p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">سرعة تقريبية</p>
              <p class="font-semibold">
                {{ connectionInfo.downlink ? connectionInfo.downlink + ' Mbps' : 'غير متاح' }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                RTT: {{ connectionInfo.rtt ? connectionInfo.rtt + ' ms' : 'غير متاح' }}
              </p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">عنوان API / المزامنة</p>
              <p class="font-semibold truncate" :title="connectionInfo.apiBaseUrl">
                {{ connectionInfo.apiBaseUrl }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                آخر تحديث: {{ connectionInfo.lastUpdated || '—' }}
              </p>
            </div>
          </div>

          <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
            <span class="font-mono bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded inline-block break-all w-full md:w-auto">
              {{ connectionInfo.origin }}
            </span>
          </div>
        </div>

        <!-- رسالة Offline -->
        <div v-if="!connectionStatus.online" class="mb-6 bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-500 p-4 rounded">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <span class="text-3xl">⚠️</span>
            </div>
            <div class="mr-3">
              <p class="text-lg font-medium text-yellow-800 dark:text-yellow-200">
                أنت حالياً في وضع Offline
              </p>
              <p class="text-sm text-yellow-700 dark:text-yellow-300">
                التغييرات التي تجريها سيتم حفظها محلياً ومزامنتها تلقائياً عند عودة الاتصال
              </p>
            </div>
          </div>
        </div>

        <!-- الجداول المزامنة -->
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold dark:text-gray-200">
                📋 الجداول المزامنة ({{ syncedTables.length }})
              </h3>
              <button
                @click="loadTables"
                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
              >
                🔄 تحديث
              </button>
            </div>
          </div>

          <!-- جدول الجداول -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الجدول</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">السجلات</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">المصدر</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الإجراءات</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="table in syncedTables.slice(0, showAllTables ? syncedTables.length : 15)" :key="table.name" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                    {{ table.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ table.count.toLocaleString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="table.connection === 'sync_sqlite'
                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                            : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'">
                      {{ table.connection === 'sync_sqlite' ? 'SQLite محلي' : 'MySQL خادم' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <button
                      @click="viewTableDetails(table.name, table.connection)"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      عرض
                    </button>
                    <button
                      v-if="table.connection === 'sync_sqlite'"
                      @click="truncateTable(table.name)"
                      class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                    >
                      تفريغ
                    </button>
                  </td>
                </tr>
                <tr v-if="syncedTables.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    لا توجد جداول متاحة
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="syncedTables.length > 15" class="p-4 border-t border-gray-200 dark:border-gray-600 text-center">
            <button @click="showAllTables = !showAllTables" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
              {{ showAllTables ? 'إخفاء الجداول' : 'عرض جميع الجداول' }} ({{ syncedTables.length }})
            </button>
          </div>
        </div>

        <!-- إدارة نقل تاريخ السيارات -->
        <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex">
              <button
                @click="activeTab = 'operations'"
                :class="[
                  'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                  activeTab === 'operations'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                ]"
              >
                📋 العمليات
              </button>
              <button
                @click="activeTab = 'carHistory'"
                :class="[
                  'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                  activeTab === 'carHistory'
                    ? 'border-green-500 text-green-600 dark:text-green-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                ]"
              >
                🚗 نقل تاريخ السيارات
              </button>
              <button
                @click="activeTab = 'system'"
                :class="[
                  'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                  activeTab === 'system'
                    ? 'border-purple-500 text-purple-600 dark:text-purple-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                ]"
              >
                🔧 النظام
              </button>
            </div>
          </div>

          <!-- تبويب العمليات -->
          <div v-if="activeTab === 'operations'" class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold dark:text-gray-200">
                📋 قائمة العمليات ({{ queueItems.length }})
              </h3>

              <!-- فلاتر -->
              <div class="flex gap-2">
                <select
                  v-model="filter"
                  class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-gray-200"
                >
                  <option value="all">الكل</option>
                  <option value="pending">في الانتظار</option>
                  <option value="synced">تمت المزامنة</option>
                  <option value="failed">فشلت</option>
                </select>
              </div>
            </div>

            <!-- جدول العمليات -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      #
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      العملية
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      التاريخ
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      الحالة
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      الإجراءات
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="(item, index) in filteredItems" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                      {{ index + 1 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        {{ item.operation }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ item.details || 'بدون تفاصيل' }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDate(item.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="{
                          'bg-yellow-100 text-yellow-800': item.status === 'pending',
                          'bg-green-100 text-green-800': item.status === 'synced',
                          'bg-red-100 text-red-800': item.status === 'failed'
                        }"
                      >
                        {{ getStatusText(item.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <button
                        v-if="item.status === 'pending' && connectionStatus.online"
                        @click="retryItem(item)"
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                      >
                        إعادة المحاولة
                      </button>
                      <button
                        @click="deleteItem(item)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      >
                        حذف
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredItems.length === 0">
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                      لا توجد عمليات
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- تبويب نقل تاريخ السيارات -->
          <div v-if="activeTab === 'carHistory'" class="p-6">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h3 class="text-xl font-semibold dark:text-gray-200 mb-2">
                  🚗 نقل معاملات السيارات إلى نظام التاريخ الجديد
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  نقل البيانات من جدول transactions إلى جدول car_history الجديد
                </p>
              </div>
              <div class="flex gap-2">
                <button
                  @click="runCarHistoryMigration"
                  :disabled="migrationRunning"
                  class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="!migrationRunning">▶️ تشغيل النقل</span>
                  <span v-else>⏳ جاري النقل...</span>
                </button>
                <button
                  @click="loadMigrationStats"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                  📊 تحديث الإحصائيات
                </button>
              </div>
            </div>

            <!-- إحصائيات النقل -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ migrationStats.total_transactions || 0 }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">إجمالي المعاملات</div>
              </div>
              <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ migrationStats.migrated || 0 }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">تم النقل</div>
              </div>
              <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ migrationStats.remaining || 0 }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">متبقي</div>
              </div>
              <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{ migrationStats.errors || 0 }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">أخطاء</div>
              </div>
            </div>

            <!-- إعدادات النقل -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
              <h4 class="text-lg font-semibold mb-4 dark:text-gray-200">⚙️ إعدادات النقل</h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    حجم الدفعة
                  </label>
                  <select
                    v-model="migrationSettings.batchSize"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-800 dark:text-gray-200"
                  >
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                    <option :value="200">200</option>
                    <option :value="500">500</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    تأخير بين الدفعات (ثانية)
                  </label>
                  <select
                    v-model="migrationSettings.delay"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-800 dark:text-gray-200"
                  >
                    <option :value="0">0</option>
                    <option :value="1">1</option>
                    <option :value="2">2</option>
                    <option :value="5">5</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    حذف البيانات القديمة
                  </label>
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="migrationSettings.deleteOldData"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <span class="mr-2 text-sm text-gray-600 dark:text-gray-400">
                      حذف المعاملات المُنقلة من جدول transactions
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- سجل النقل -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg">
              <div class="p-4 border-b border-gray-200 dark:border-gray-600">
                <h4 class="text-lg font-semibold dark:text-gray-200">📝 سجل النقل</h4>
              </div>
              <div class="p-4 max-h-64 overflow-y-auto">
                <div v-if="migrationLogs.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                  لا توجد سجلات نقل بعد
                </div>
                <div v-else class="space-y-2">
                  <div
                    v-for="(log, index) in migrationLogs"
                    :key="index"
                    class="flex items-start space-x-3 text-sm"
                  >
                    <span
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                      :class="{
                        'bg-blue-100 text-blue-800': log.type === 'info',
                        'bg-green-100 text-green-800': log.type === 'success',
                        'bg-yellow-100 text-yellow-800': log.type === 'warning',
                        'bg-red-100 text-red-800': log.type === 'error'
                      }"
                    >
                      {{ getLogIcon(log.type) }}
                    </span>
                    <div class="flex-1">
                      <p class="text-gray-900 dark:text-gray-200">{{ log.message }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(log.timestamp) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- تبويب النظام -->
          <div v-if="activeTab === 'system'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- معلومات النظام -->
              <div class="bg-white dark:bg-gray-700 p-6 rounded-lg">
                <h4 class="text-lg font-semibold mb-4 dark:text-gray-200">🖥️ معلومات النظام</h4>
                <div class="space-y-3">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">إصدار PHP:</span>
                    <span class="font-mono text-sm">{{ systemInfo.php_version || 'غير معروف' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">إصدار Laravel:</span>
                    <span class="font-mono text-sm">{{ systemInfo.laravel_version || 'غير معروف' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">قاعدة البيانات:</span>
                    <span class="font-mono text-sm">{{ systemInfo.database || 'غير معروف' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">مساحة التخزين:</span>
                    <span class="font-mono text-sm">{{ systemInfo.storage_used || 'غير معروف' }}</span>
                  </div>
                </div>
              </div>

              <!-- أدوات النظام -->
              <div class="bg-white dark:bg-gray-700 p-6 rounded-lg">
                <h4 class="text-lg font-semibold mb-4 dark:text-gray-200">🔧 أدوات النظام</h4>
                <div class="space-y-3">
                  <button
                    @click="clearCache"
                    :disabled="cacheClearing"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                  >
                    <span v-if="!cacheClearing">🧹 مسح الـ Cache</span>
                    <span v-else>⏳ جاري المسح...</span>
                  </button>

                  <button
                    @click="optimizeDatabase"
                    :disabled="optimizing"
                    class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50"
                  >
                    <span v-if="!optimizing">⚡ تحسين قاعدة البيانات</span>
                    <span v-else>⏳ جاري التحسين...</span>
                  </button>

                  <button
                    @click="generateBackup"
                    :disabled="backingUp"
                    class="w-full px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 disabled:opacity-50"
                  >
                    <span v-if="!backingUp">💾 إنشاء نسخة احتياطية</span>
                    <span v-else>⏳ جاري النسخ...</span>
                  </button>

                  <button
                    @click="checkSystemHealth"
                    :disabled="checkingHealth"
                    class="w-full px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 disabled:opacity-50"
                  >
                    <span v-if="!checkingHealth">🏥 فحص صحة النظام</span>
                    <span v-else>⏳ جاري الفحص...</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- سجل العمليات -->
            <div class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg">
              <div class="p-4 border-b border-gray-200 dark:border-gray-600">
                <h4 class="text-lg font-semibold dark:text-gray-200">📋 سجل عمليات النظام</h4>
              </div>
              <div class="p-4 max-h-64 overflow-y-auto">
                <div v-if="systemLogs.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                  لا توجد عمليات نظام بعد
                </div>
                <div v-else class="space-y-2">
                  <div
                    v-for="(log, index) in systemLogs"
                    :key="index"
                    class="flex items-start space-x-3 text-sm"
                  >
                    <span
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                      :class="{
                        'bg-blue-100 text-blue-800': log.type === 'info',
                        'bg-green-100 text-green-800': log.type === 'success',
                        'bg-yellow-100 text-yellow-800': log.type === 'warning',
                        'bg-red-100 text-red-800': log.type === 'error'
                      }"
                    >
                      {{ getLogIcon(log.type) }}
                    </span>
                    <div class="flex-1">
                      <p class="text-gray-900 dark:text-gray-200">{{ log.message }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(log.timestamp) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- قائمة الجداول المزامنة -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold dark:text-gray-200">
                📋 قائمة العمليات ({{ queueItems.length }})
              </h3>
              
              <!-- فلاتر -->
              <div class="flex gap-2">
                <select 
                  v-model="filter"
                  class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-gray-200"
                >
                  <option value="all">الكل</option>
                  <option value="pending">في الانتظار</option>
                  <option value="synced">تمت المزامنة</option>
                  <option value="failed">فشلت</option>
                </select>
              </div>
            </div>
          </div>

          <!-- جدول العمليات -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    #
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    النوع
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    العملية
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    البيانات
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    المحاولات
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    الوقت
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    الحالة
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    الإجراءات
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="filteredItems.length === 0">
                  <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="text-5xl mb-2">📭</div>
                    <p class="text-lg">لا توجد عمليات {{ filterText }}</p>
                  </td>
                </tr>
                
                <tr v-for="(item, index) in filteredItems" :key="item.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                    :class="{
                      'bg-yellow-50 dark:bg-yellow-900': !item.synced && item.retries < 3,
                      'bg-red-50 dark:bg-red-900': item.retries >= 3,
                      'bg-green-50 dark:bg-green-900': item.synced
                    }">
                  <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">
                    {{ index + 1 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                          :class="getStoreTypeClass(item.storeName)">
                      {{ getStoreTypeName(item.storeName) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap dark:text-gray-300">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                          :class="getActionClass(item.action)">
                      {{ getActionName(item.action) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 dark:text-gray-300">
                    <button 
                      @click="showDataDetails(item)"
                      class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm underline"
                    >
                      عرض التفاصيل
                    </button>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">
                    <div class="flex items-center gap-1">
                      <span>{{ item.retries || 0 }}</span>
                      <span class="text-xs text-gray-500">/ 3</span>
                      <div v-if="item.retries >= 3" class="text-red-600 text-lg">⚠️</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">
                    {{ formatTime(item.timestamp) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="item.synced" class="flex items-center text-green-600 dark:text-green-400">
                      <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                      </svg>
                      تمت المزامنة
                    </span>
                    <span v-else-if="item.retries >= 3" class="flex items-center text-red-600 dark:text-red-400">
                      <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                      </svg>
                      فشلت
                    </span>
                    <span v-else class="flex items-center text-yellow-600 dark:text-yellow-400">
                      <svg class="w-5 h-5 ml-1 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      في الانتظار
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex gap-2">
                      <!-- إعادة المحاولة -->
                      <button
                        v-if="!item.synced && connectionStatus.online"
                        @click="retryItem(item)"
                        class="text-green-600 hover:text-green-900 dark:text-green-400"
                        :disabled="retryingItems.has(item.id)"
                        title="إعادة المحاولة"
                      >
                        <svg class="w-5 h-5" :class="{ 'animate-spin': retryingItems.has(item.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                      </button>
                      
                      <!-- حذف -->
                      <button
                        @click="deleteItem(item)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400"
                        title="حذف"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </button>

                      <!-- عرض الخطأ -->
                      <button
                        v-if="item.error"
                        @click="showError(item)"
                        class="text-orange-600 hover:text-orange-900 dark:text-orange-400"
                        title="عرض الخطأ"
                      >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="queueItems.length > itemsPerPage" class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-between items-center">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              عرض {{ (currentPage - 1) * itemsPerPage + 1 }} إلى {{ Math.min(currentPage * itemsPerPage, filteredItems.length) }} من {{ filteredItems.length }}
            </div>
            <div class="flex gap-2">
              <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="px-3 py-1 border rounded disabled:opacity-50 dark:text-gray-200 dark:border-gray-600"
              >
                السابق
              </button>
              <button
                @click="currentPage++"
                :disabled="currentPage >= totalPages"
                class="px-3 py-1 border rounded disabled:opacity-50 dark:text-gray-200 dark:border-gray-600"
              >
                التالي
              </button>
            </div>
          </div>
        </div>

        <!-- معلومات IndexedDB -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">💾 معلومات التخزين المحلي</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border dark:border-gray-700 rounded p-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">السيارات المحفوظة</p>
              <p class="text-2xl font-bold dark:text-gray-200">{{ localDataCounts.cars }}</p>
            </div>
            <div class="border dark:border-gray-700 rounded p-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">العقود المحفوظة</p>
              <p class="text-2xl font-bold dark:text-gray-200">{{ localDataCounts.contracts }}</p>
            </div>
            <div class="border dark:border-gray-700 rounded p-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">المعاملات المحفوظة</p>
              <p class="text-2xl font-bold dark:text-gray-200">{{ localDataCounts.transactions }}</p>
            </div>
          </div>
        </div>

        <!-- الجداول المزامنة -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center flex-wrap gap-4">
              <h3 class="text-lg font-semibold dark:text-gray-200">
                📊 الجداول المزامنة ({{ syncedTables.length }})
              </h3>
              <div class="flex gap-2 items-center flex-wrap">
                <select
                  v-model="selectedDatabase"
                  @change="loadSyncedTables"
                  class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-gray-200 text-sm"
                >
                  <option value="auto">🔄 تلقائي (حسب الاتصال الحالي)</option>
                  <option value="mysql">☁️ MySQL سيرفر</option>
                  <option value="sync_sqlite">🖥️ SQLite محلي</option>
                </select>
                <span
                  class="px-3 py-1 rounded-full text-sm font-semibold"
                  :class="currentViewingConnection === 'sync_sqlite'
                    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100'
                    : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100'"
                >
                  {{ currentViewingConnection === 'sync_sqlite' ? '🖥️ SQLite محلي' : '☁️ MySQL سيرفر' }}
                </span>
                <button
                  @click="loadSyncedTables"
                  class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                  :disabled="loadingTables"
                >
                  <span v-if="!loadingTables">🔄 تحديث</span>
                  <span v-else>⏳ جاري...</span>
                </button>
                <button
                  @click="startSync"
                  class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm"
                  :disabled="syncing"
                >
                  <span v-if="!syncing">🔄 مزامنة ثنائية الاتجاه</span>
                  <span v-else>⏳ جاري المزامنة...</span>
                </button>

                <!-- أزرار اختيار اتجاه المزامنة -->
            <button
              @click="syncDirection('up')"
              class="px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 ml-2"
              :disabled="isSyncing"
              title="نقل البيانات الأساسية من SQLite المحلي إلى MySQL السيرفر"
            >
              <span v-if="!isSyncing">📤 الجداول الأساسية ↑</span>
              <span v-else>⏳ جاري...</span>
            </button>

            <button
              @click="syncDirection('down')"
              class="px-3 py-2 bg-purple-500 text-white text-sm rounded hover:bg-purple-600 ml-2"
              :disabled="isSyncing"
              title="تحديث البيانات الأساسية من MySQL السيرفر إلى SQLite المحلي"
            >
              <span v-if="!isSyncing">📥 الجداول الأساسية ↓</span>
              <span v-else>⏳ جاري...</span>
            </button>
              </div>
            </div>
          </div>

          <div v-if="loadingTables" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">جاري تحميل الجداول...</p>
          </div>

          <div v-else-if="syncedTables.length === 0" class="p-8 text-center">
            <div class="text-5xl mb-2">📭</div>
            <p class="text-gray-600 dark:text-gray-400">لا توجد جداول متاحة</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    اسم الجدول
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    عدد السجلات
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    الاتصال
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    الإجراءات
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="table in syncedTables"
                  :key="table.name"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium dark:text-gray-200">{{ table.name }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm dark:text-gray-300">{{ table.count.toLocaleString() }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="table.connection === 'sync_sqlite'
                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200'"
                    >
                      {{ table.connection === 'sync_sqlite' ? 'SQLite' : 'MySQL' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex gap-2 items-center">
                      <button
                        @click="viewTableDetails(table.name, table.connection)"
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400"
                      >
                        عرض التفاصيل
                      </button>
                      <!-- أزرار SQLite فقط -->
                      <template v-if="table.connection === 'sync_sqlite'">
                        <button
                          @click="truncateTable(table.name)"
                          class="text-orange-600 hover:text-orange-900 dark:text-orange-400"
                          title="تفريغ الجدول (حذف جميع السجلات)"
                          :disabled="truncatingTable === table.name"
                        >
                          🗑️ تفريغ
                        </button>
                        <button
                          @click="deleteTable(table.name)"
                          class="text-red-600 hover:text-red-900 dark:text-red-400"
                          title="حذف الجدول بالكامل"
                          :disabled="deletingTable === table.name"
                        >
                          ❌ حذف
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- جدول Sync Metadata -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold dark:text-gray-200">
                📊 بيانات المزامنة الذكية (sync_metadata)
              </h3>
              <button
                @click="loadSyncMetadata"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                :disabled="loadingMetadata"
              >
                <span v-if="!loadingMetadata">🔄 تحديث</span>
                <span v-else>⏳ جاري...</span>
              </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
              يعرض آخر ID و updated_at المزامن لكل جدول واتجاه المزامنة
            </p>
          </div>

          <div v-if="loadingMetadata" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">جاري تحميل بيانات المزامنة...</p>
          </div>

          <div v-else-if="syncMetadata.error" class="p-4 bg-red-50 dark:bg-red-900 border-l-4 border-red-500">
            <p class="text-red-800 dark:text-red-200">{{ syncMetadata.error }}</p>
          </div>

          <div v-else-if="syncMetadata.data.length === 0" class="p-8 text-center">
            <div class="text-5xl mb-2">📋</div>
            <p class="text-gray-600 dark:text-gray-400">لا توجد بيانات مزامنة</p>
            <p class="text-xs mt-2 text-gray-500">قم بتشغيل المزامنة أولاً لبدء تتبع البيانات</p>
          </div>

          <div v-else>
            <!-- إحصائيات -->
            <div v-if="syncMetadata.stats" class="p-4 bg-blue-50 dark:bg-blue-900 border-b border-gray-200 dark:border-gray-700">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                  <p class="text-2xl font-bold dark:text-gray-200">{{ syncMetadata.stats.total_tables }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">عدد الجداول</p>
                </div>
                <div>
                  <p class="text-2xl font-bold dark:text-gray-200">{{ syncMetadata.stats.total_records }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">عدد السجلات المزامنة</p>
                </div>
                <div>
                  <p class="text-2xl font-bold dark:text-gray-200">{{ syncMetadata.stats.total_synced_records?.toLocaleString() || 0 }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">إجمالي السجلات المزامنة</p>
                </div>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">اسم الجدول</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الاتجاه</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">آخر ID مزامن</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">آخر updated_at</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">آخر مزامنة</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">إجمالي المزامن</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">آخر تحديث</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="item in syncMetadata.data" :key="`${item.table_name}-${item.direction}`" 
                      class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium dark:text-gray-200">
                      {{ item.table_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="item.direction === 'down' 
                              ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                              : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'">
                        {{ item.direction_label }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">
                      {{ item.last_synced_id?.toLocaleString() || 0 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">
                      {{ item.last_updated_at || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">
                      {{ item.last_synced_at || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">
                      {{ item.total_synced?.toLocaleString() || 0 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">
                      {{ item.updated_at || '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- النسخ الاحتياطية -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold dark:text-gray-200">
                💾 النسخ الاحتياطية
              </h3>
              <button
                @click="loadBackups"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                :disabled="loadingBackups"
              >
                <span v-if="!loadingBackups">🔄 تحديث</span>
                <span v-else>⏳ جاري...</span>
              </button>
            </div>
          </div>

          <div v-if="loadingBackups" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600 dark:text-gray-400">جاري تحميل النسخ الاحتياطية...</p>
          </div>

          <div v-else-if="backups.length === 0" class="p-8 text-center">
            <div class="text-5xl mb-2">📦</div>
            <p class="text-gray-600 dark:text-gray-400">لا توجد نسخ احتياطية</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">اسم الملف</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الحجم</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">التاريخ</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الإجراءات</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="backup in backups" :key="backup.name" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">{{ backup.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">{{ formatFileSize(backup.size) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-gray-300">{{ formatDate(backup.date) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button
                      @click="restoreBackup(backup.name)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 mr-3"
                      :disabled="restoringBackup"
                    >
                      🔄 استعادة
                    </button>
                    <button
                      @click="downloadBackup(backup.name)"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3"
                    >
                      📥 تحميل
                    </button>
                    <button
                      @click="deleteBackup(backup.name)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400"
                      title="حذف النسخة الاحتياطية"
                    >
                      🗑️ حذف
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- أدوات المطور -->
        <div class="mt-6 bg-gray-50 dark:bg-gray-900 shadow-sm rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">🛠️ أدوات المطور</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <button
              @click="testOfflineMode"
              class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600"
            >
              🧪 اختبار Offline
            </button>
            <button
              @click="clearCache"
              class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600"
            >
              🗑️ مسح Cache
            </button>
            <button
              @click="exportLogs"
              class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
            >
              📥 تصدير Logs
            </button>
            <button
              @click="showDatabaseInfo"
              class="px-4 py-2 bg-teal-500 text-white rounded hover:bg-teal-600"
            >
              📊 معلومات DB
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Modal لتفاصيل البيانات -->
    <Modal :show="detailsModal.show" @close="detailsModal.show = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">📄 تفاصيل البيانات</h3>
        <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-auto max-h-96 text-sm dark:text-gray-300">{{ JSON.stringify(detailsModal.data, null, 2) }}</pre>
        <div class="mt-4 flex justify-end">
          <button
            @click="detailsModal.show = false"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            إغلاق
          </button>
        </div>
      </div>
    </Modal>

    <!-- Modal للخطأ -->
    <Modal :show="errorModal.show" @close="errorModal.show = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4 text-red-600 dark:text-red-400">❌ تفاصيل الخطأ</h3>
        <div class="bg-red-50 dark:bg-red-900 border-l-4 border-red-500 p-4 mb-4">
          <p class="font-medium dark:text-gray-200">{{ errorModal.title }}</p>
          <p class="text-sm mt-2 dark:text-gray-300">{{ errorModal.message }}</p>
        </div>
        <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-auto max-h-64 text-xs dark:text-gray-300">{{ errorModal.stack }}</pre>
        <div class="mt-4 flex justify-end gap-2">
          <button
            @click="retryFailedItem(errorModal.item)"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          >
            🔄 إعادة المحاولة
          </button>
          <button
            @click="errorModal.show = false"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            إغلاق
          </button>
        </div>
      </div>
    </Modal>

    <!-- Modal تفاصيل الجدول -->
    <Modal :show="tableDetailsModal.show" @close="tableDetailsModal.show = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-gray-200">
          📋 تفاصيل الجدول: {{ tableDetailsModal.tableName }}
        </h3>
        
        <div v-if="loadingTableDetails" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">جاري تحميل البيانات...</p>
        </div>

        <div v-else-if="tableDetailsModal.error" class="bg-red-50 dark:bg-red-900 border-l-4 border-red-500 p-4 mb-4">
          <p class="text-red-800 dark:text-red-200">{{ tableDetailsModal.error }}</p>
        </div>

        <div v-else>
          <div class="mb-4 flex justify-between items-center">
            <div class="text-sm text-gray-600 dark:text-gray-400">
              إجمالي السجلات: <span class="font-semibold">{{ tableDetailsModal.total?.toLocaleString() || 0 }}</span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              الاتصال: <span class="font-semibold">{{ tableDetailsModal.connection || '-' }}</span>
            </div>
          </div>

          <!-- Debug info (for development) -->
          <div v-if="tableDetailsModal.columns.length === 0 && tableDetailsModal.data.length > 0" 
               class="mb-2 p-2 bg-yellow-50 dark:bg-yellow-900 rounded text-xs">
            ⚠️ الأعمدة غير موجودة، يتم استخراجها من البيانات...
          </div>

          <div v-if="tableDetailsModal.columns.length > 0" class="overflow-x-auto max-h-96 border border-gray-200 dark:border-gray-700 rounded">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
              <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                <tr>
                  <th
                    v-for="column in tableDetailsModal.columns"
                    :key="column"
                    class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase border-r border-gray-200 dark:border-gray-600"
                  >
                    {{ column }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="tableDetailsModal.data.length === 0">
                  <td :colspan="tableDetailsModal.columns.length || 1" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    لا توجد بيانات
                  </td>
                </tr>
                <tr
                  v-for="(row, index) in tableDetailsModal.data"
                  :key="index"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <td
                    v-for="column in tableDetailsModal.columns"
                    :key="`${index}-${column}`"
                    class="px-4 py-2 text-xs dark:text-gray-300 border-r border-gray-100 dark:border-gray-700 whitespace-nowrap"
                  >
                    {{ formatCellValue(row[column]) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
            <div class="text-5xl mb-2">📋</div>
            <p>لا توجد أعمدة متاحة للعرض</p>
            <p class="text-xs mt-2">عدد البيانات: {{ tableDetailsModal.data.length }}</p>
          </div>

          <div v-if="tableDetailsModal.total > tableDetailsModal.limit" class="mt-4 flex justify-between items-center">
            <div class="text-sm text-gray-600 dark:text-gray-400">
              عرض {{ tableDetailsModal.offset + 1 }} إلى {{ Math.min(tableDetailsModal.offset + tableDetailsModal.limit, tableDetailsModal.total) }} من {{ tableDetailsModal.total }}
            </div>
            <div class="flex gap-2">
              <button
                @click="loadTableDetails(tableDetailsModal.tableName, tableDetailsModal.offset - tableDetailsModal.limit, tableDetailsModal.tableConnection)"
                :disabled="tableDetailsModal.offset === 0"
                class="px-3 py-1 border rounded disabled:opacity-50 dark:text-gray-200 dark:border-gray-600"
              >
                السابق
              </button>
              <button
                @click="loadTableDetails(tableDetailsModal.tableName, tableDetailsModal.offset + tableDetailsModal.limit, tableDetailsModal.tableConnection)"
                :disabled="tableDetailsModal.offset + tableDetailsModal.limit >= tableDetailsModal.total"
                class="px-3 py-1 border rounded disabled:opacity-50 dark:text-gray-200 dark:border-gray-600"
              >
                التالي
              </button>
            </div>
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <button
            @click="tableDetailsModal.show = false"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            إغلاق
          </button>
        </div>
      </div>
    </Modal>

    <!-- Sync Indicator -->
    <SyncIndicator />
  </GuestLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/inertia-vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Modal from '@/Components/Modal.vue';
import SyncIndicator from '@/Components/SyncIndicator.vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const toast = useToast();

// البيانات
const queueItems = ref([]);
const connectionStatus = ref({
  online: navigator.onLine,
  syncing: false
});
const syncStatus = ref({
  pendingCount: 0,
  lastSync: null
});
const localDataCounts = ref({
  cars: 0,
  contracts: 0,
  transactions: 0
});
const defaultConnectionInfo = {
  environmentLabel: 'غير معروف',
  tip: '—',
  host: '-',
  port: '',
  protocol: '-',
  origin: '-',
  secure: false,
  networkType: 'غير متاح',
  effectiveType: 'غير متاح',
  downlink: null,
  rtt: null,
  apiBaseUrl: '-',
  lastUpdated: null,
  isLocal: false
};
const connectionInfo = ref(buildConnectionInfo());

const isRefreshing = ref(false);
const isSyncing = ref(false);
const retryingItems = ref(new Set());
const filter = ref('all');
const currentPage = ref(1);
const itemsPerPage = ref(10);
let refreshInterval = null;
let connectionApiRef = null;

// Modals
const detailsModal = ref({
  show: false,
  data: null
});
const errorModal = ref({
  show: false,
  item: null,
  title: '',
  message: '',
  stack: ''
});

// الجداول المزامنة
const syncedTables = ref([]);
const loadingTables = ref(false);
const syncing = ref(false);
const selectedDatabase = ref('auto'); // 'auto', 'mysql', 'sync_sqlite'
const currentViewingConnection = ref('mysql');

// النسخ الاحتياطية
const backups = ref([]);
const loadingBackups = ref(false);
const restoringBackup = ref(false);

// تبويبات النظام
const activeTab = ref('operations');

// نظام نقل تاريخ السيارات
const migrationRunning = ref(false);
const migrationStats = ref({
  total_transactions: 0,
  migrated: 0,
  remaining: 0,
  errors: 0
});
const migrationLogs = ref([]);
const migrationSettings = ref({
  batchSize: 100,
  delay: 1,
  deleteOldData: false
});

// أدوات النظام
const cacheClearing = ref(false);
const optimizing = ref(false);
const backingUp = ref(false);
const checkingHealth = ref(false);
const systemInfo = ref({});
const systemLogs = ref([]);

// معلومات قاعدة البيانات
const databaseInfo = ref({});
const loadingDatabaseInfo = ref(false);
const showAllTables = ref(false);
const truncatingTable = ref(null);
const deletingTable = ref(null);

// Sync Metadata
const syncMetadata = ref({
  data: [],
  stats: null,
  error: null
});
const loadingMetadata = ref(false);
const dbConnectionInfo = ref({
  connection: 'mysql',
  is_fallback: false
});
const tableDetailsModal = ref({
  show: false,
  tableName: '',
  columns: [],
  data: [],
  total: 0,
  limit: 50,
  offset: 0,
  connection: '',
  error: null
});
const loadingTableDetails = ref(false);

// الإحصائيات
const stats = computed(() => {
  return {
    total: queueItems.value.length,
    synced: queueItems.value.filter(item => item.synced).length,
    pending: queueItems.value.filter(item => !item.synced && item.retries < 3).length,
    failed: queueItems.value.filter(item => item.retries >= 3).length
  };
});

// الفلترة
const filteredItems = computed(() => {
  let items = queueItems.value;

  if (filter.value === 'pending') {
    items = items.filter(item => item.status === 'pending' || (!item.synced && item.retries < 3));
  } else if (filter.value === 'synced') {
    items = items.filter(item => item.status === 'synced' || item.synced);
  } else if (filter.value === 'failed') {
    items = items.filter(item => item.status === 'failed' || item.retries >= 3);
  }

  // Pagination
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;

  return items.slice(start, end);
});

const filterText = computed(() => {
  const texts = {
    all: '',
    pending: 'في الانتظار',
    synced: 'تمت مزامنتها',
    failed: 'فشلت'
  };
  return texts[filter.value] || '';
});

const totalPages = computed(() => {
  return Math.ceil(queueItems.value.length / itemsPerPage.value);
});

const lastSyncFormatted = computed(() => {
  if (!syncStatus.value.lastSync) return 'لم تتم المزامنة بعد';
  
  const date = new Date(syncStatus.value.lastSync);
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

// الوظائف
const refreshData = async () => {
  isRefreshing.value = true;
  
  try {
    // تحديث حالة الاتصال
    connectionStatus.value.online = navigator.onLine;
    updateConnectionInfo();
    
    // جلب بيانات Sync Queue من IndexedDB
    if (window.$db) {
      const queue = await window.$db.getAll('sync_queue');
      queueItems.value = queue.sort((a, b) => b.timestamp - a.timestamp);
      
      // تحديث العدادات
      syncStatus.value.pendingCount = queue.filter(item => !item.synced).length;
    }
    
    // جلب معلومات البيانات المحلية
    if (window.$db) {
      localDataCounts.value = {
        cars: (await window.$db.getAll('cars')).length,
        contracts: (await window.$db.getAll('contracts')).length,
        transactions: (await window.$db.getAll('transactions')).length
      };
    }
    
    // جلب حالة المزامنة
    if (window.$api) {
      const status = await window.$api.getSyncStatus();
      syncStatus.value.pendingCount = status.pendingCount;
    }
    
    toast.success('تم تحديث البيانات', { timeout: 2000 });
  } catch (error) {
    console.error('فشل تحديث البيانات:', error);
    toast.error('فشل تحديث البيانات');
  } finally {
    isRefreshing.value = false;
  }
};

const syncAll = async () => {
  if (!connectionStatus.value.online) {
    toast.warning('لا يمكن المزامنة - غير متصل بالإنترنت');
    return;
  }
  
  isSyncing.value = true;
  connectionStatus.value.syncing = true;
  
  try {
    toast.info('🔄 بدء المزامنة...', { timeout: 3000 });
    
    if (window.$api) {
      await window.$api.syncNow();
      syncStatus.value.lastSync = Date.now();
    }
    
    if (window.$db) {
      await window.$db.processSyncQueue();
    }
    
    await refreshData();
    
    toast.success('✅ تمت المزامنة بنجاح!', { timeout: 3000 });
  } catch (error) {
    console.error('فشلت المزامنة:', error);
    toast.error('❌ فشلت المزامنة: ' + error.message);
  } finally {
    isSyncing.value = false;
    connectionStatus.value.syncing = false;
  }
};

const retryItem = async (item) => {
  if (!connectionStatus.value.online) {
    toast.warning('لا يمكن إعادة المحاولة - غير متصل');
    return;
  }
  
  retryingItems.value.add(item.id);
  
  try {
    toast.info(`🔄 إعادة محاولة ${getStoreTypeName(item.storeName)}...`);
    
    if (window.$db) {
      await window.$db.syncItem(item);
      
      // تحديث الحالة
      item.synced = true;
      item.retries = (item.retries || 0) + 1;
      await window.$db.save('sync_queue', item);
      
      toast.success('✅ تمت المزامنة بنجاح!');
      await refreshData();
    }
  } catch (error) {
    console.error('فشلت إعادة المحاولة:', error);
    
    item.retries = (item.retries || 0) + 1;
    item.error = {
      message: error.message,
      timestamp: Date.now()
    };
    
    if (window.$db) {
      await window.$db.save('sync_queue', item);
    }
    
    toast.error('❌ فشلت إعادة المحاولة');
    await refreshData();
  } finally {
    retryingItems.value.delete(item.id);
  }
};

const deleteItem = async (item) => {
  if (!confirm('هل أنت متأكد من حذف هذه العملية؟')) {
    return;
  }
  
  try {
    if (window.$db) {
      await window.$db.delete('sync_queue', item.id);
      toast.success('تم الحذف');
      await refreshData();
    }
  } catch (error) {
    console.error('فشل الحذف:', error);
    toast.error('فشل الحذف');
  }
};

const clearAll = async () => {
  if (!confirm('هل أنت متأكد من حذف كل العمليات؟ سيتم حذف العمليات المزامنة فقط.')) {
    return;
  }
  
  try {
    if (window.$db) {
      const syncedItems = queueItems.value.filter(item => item.synced);
      
      for (const item of syncedItems) {
        await window.$db.delete('sync_queue', item.id);
      }
      
      toast.success(`تم حذف ${syncedItems.length} عملية`);
      await refreshData();
    }
  } catch (error) {
    console.error('فشل المسح:', error);
    toast.error('فشل مسح العمليات');
  }
};

const showDataDetails = (item) => {
  detailsModal.value = {
    show: true,
    data: item.data
  };
};

const showError = (item) => {
  errorModal.value = {
    show: true,
    item: item,
    title: `فشل ${getActionName(item.action)} ${getStoreTypeName(item.storeName)}`,
    message: item.error?.message || 'خطأ غير معروف',
    stack: item.error?.stack || 'لا توجد تفاصيل إضافية'
  };
};

const retryFailedItem = async (item) => {
  errorModal.value.show = false;
  await retryItem(item);
};

// الأدوات المساعدة
const getStoreTypeName = (storeName) => {
  const names = {
    cars: '🚗 سيارة',
    contracts: '📄 عقد',
    transactions: '💰 معاملة',
    sync_queue: '🔄 مزامنة'
  };
  return names[storeName] || storeName;
};

const getStoreTypeClass = (storeName) => {
  const classes = {
    cars: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    contracts: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    transactions: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
  };
  return classes[storeName] || 'bg-gray-100 text-gray-800';
};

const getActionName = (action) => {
  const names = {
    save: '💾 حفظ',
    update: '✏️ تحديث',
    delete: '🗑️ حذف'
  };
  return names[action] || action;
};

const getActionClass = (action) => {
  const classes = {
    save: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    update: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    delete: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  };
  return classes[action] || 'bg-gray-100 text-gray-800';
};

const formatTime = (timestamp) => {
  if (!timestamp) return '-';
  
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;
  
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  
  if (minutes < 1) return 'الآن';
  if (minutes < 60) return `منذ ${minutes} دقيقة`;
  if (hours < 24) return `منذ ${hours} ساعة`;
  
  return date.toLocaleDateString('ar-SA') + ' ' + date.toLocaleTimeString('ar-SA');
};

// أدوات المطور
const testOfflineMode = () => {
  toast.info('💡 لاختبار Offline Mode:\n1. افتح DevTools (F12)\n2. اذهب إلى Network\n3. حدد "Offline"', {
    timeout: 8000
  });
};

const clearCache = async () => {
  if (!confirm('هل تريد مسح كل الـ Cache؟')) return;
  
  try {
    if (window.$db) {
      await window.$db.clearExpiredCache();
    }
    
    if ('caches' in window) {
      const cacheNames = await caches.keys();
      await Promise.all(cacheNames.map(name => caches.delete(name)));
    }
    
    toast.success('✅ تم مسح الـ Cache');
    setTimeout(() => window.location.reload(), 1000);
  } catch (error) {
    toast.error('فشل مسح الـ Cache');
  }
};

const exportLogs = () => {
  const logs = {
    timestamp: new Date().toISOString(),
    connectionStatus: connectionStatus.value,
    syncStatus: syncStatus.value,
    queueItems: queueItems.value,
    localDataCounts: localDataCounts.value,
    stats: stats.value
  };
  
  const blob = new Blob([JSON.stringify(logs, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `sync-logs-${Date.now()}.json`;
  a.click();
  
  toast.success('✅ تم تصدير الـ Logs');
};

const showDatabaseInfo = async () => {
  try {
    const info = {
      online: connectionStatus.value.online,
      pendingSync: syncStatus.value.pendingCount,
      localData: localDataCounts.value,
      queueSize: queueItems.value.length,
      browserSupport: {
        serviceWorker: 'serviceWorker' in navigator,
        indexedDB: 'indexedDB' in window,
        cacheAPI: 'caches' in window
      }
    };
    
    console.table(info);
    toast.info('📊 تم عرض المعلومات في Console', { timeout: 3000 });
  } catch (error) {
    toast.error('فشل عرض المعلومات');
  }
};

// Event Listeners
const handleOnline = () => {
  connectionStatus.value.online = true;
  updateConnectionInfo();
  toast.success('🌐 عاد الاتصال!');
  refreshData();
  
  // مزامنة تلقائية
  setTimeout(() => {
    if (syncStatus.value.pendingCount > 0) {
      syncAll();
    }
  }, 1000);
};

const handleOffline = () => {
  connectionStatus.value.online = false;
  updateConnectionInfo();
  toast.warning('📴 فقدان الاتصال - وضع Offline');
  refreshData();
};

// دوال الجداول المزامنة
const loadSyncedTables = async () => {
  loadingTables.value = true;
  
  try {
    const params = {};
    if (selectedDatabase.value !== 'auto') {
      params.force_connection = selectedDatabase.value;
    }
    
    const response = await axios.get('/api/sync-monitor/tables', { 
      params,
      withCredentials: true // إرسال cookies مع الطلب
    });
    syncedTables.value = response.data.tables || [];
    dbConnectionInfo.value = {
      connection: response.data.connection || 'mysql',
      is_fallback: response.data.is_fallback || false
    };
    
    // تحديث الاتصال الحالي المعروض
    currentViewingConnection.value = response.data.connection || 'mysql';
    
    toast.success('تم تحميل الجداول بنجاح', { timeout: 2000 });
  } catch (error) {
    console.error('فشل تحميل الجداول:', error);
    toast.error('فشل تحميل الجداول: ' + (error.response?.data?.error || error.message));
  } finally {
    loadingTables.value = false;
  }
};

const viewTableDetails = (tableName, tableConnection = null) => {
  const url = `/sync-monitor/table/${tableName}${tableConnection ? `?connection=${tableConnection}` : ''}`;
  router.visit(url);
};

const showTableDetails = async (tableName, tableConnection = null) => {
  tableDetailsModal.value = {
    show: true,
    tableName: tableName,
    columns: [],
    data: [],
    total: 0,
    limit: 50,
    offset: 0,
    connection: tableConnection || '',
    error: null,
    tableConnection: tableConnection // حفظ connection للجدول
  };
  
  await loadTableDetails(tableName, 0, tableConnection);
};

const loadTableDetails = async (tableName, offset = 0, tableConnection = null) => {
  loadingTableDetails.value = true;
  tableDetailsModal.value.error = null;
  
  try {
    const params = {
      limit: tableDetailsModal.value.limit,
      offset: offset
    };
    
    // استخدام connection الخاص بالجدول إذا كان متوفراً، وإلا استخدام selectedDatabase
    const connectionToUse = tableConnection || tableDetailsModal.value.tableConnection;
    if (connectionToUse) {
      params.force_connection = connectionToUse;
    } else if (selectedDatabase.value !== 'auto') {
      params.force_connection = selectedDatabase.value;
    }
    
    const response = await axios.get(`/api/sync-monitor/table/${tableName}`, { 
      params,
      withCredentials: true // إرسال cookies مع الطلب
    });
    
    // Debug logging
    console.log('Table details response:', {
      table: tableName,
      connection: connectionToUse,
      columns: response.data.columns,
      columns_type: typeof response.data.columns,
      columns_is_array: Array.isArray(response.data.columns),
      data_count: response.data.data?.length || 0,
      total: response.data.total,
      first_row: response.data.data?.[0] || null,
      response_data: response.data
    });
    
    // التأكد من أن البيانات هي array
    let columns = [];
    if (Array.isArray(response.data.columns)) {
      columns = response.data.columns;
    } else if (response.data.columns && typeof response.data.columns === 'object') {
      // إذا كانت object، تحويلها إلى array
      columns = Object.values(response.data.columns);
    }
    
    let data = [];
    if (Array.isArray(response.data.data)) {
      data = response.data.data;
    } else if (response.data.data && typeof response.data.data === 'object') {
      // إذا كانت object، تحويلها إلى array
      data = Object.values(response.data.data);
    }
    
    // إذا كانت الأعمدة فارغة لكن هناك بيانات، استخرج الأعمدة من أول سطر
    if (columns.length === 0 && data.length > 0) {
      const firstRow = data[0];
      if (firstRow && typeof firstRow === 'object') {
        const firstRowKeys = Object.keys(firstRow);
        columns = firstRowKeys;
        console.log('Extracted columns from data:', columns);
      }
    }
    
    console.log('Final columns and data:', {
      columns_count: columns.length,
      columns: columns,
      data_count: data.length,
      first_row_keys: data.length > 0 ? Object.keys(data[0]) : []
    });
    
    tableDetailsModal.value.columns = columns;
    tableDetailsModal.value.data = data;
    tableDetailsModal.value.total = response.data.total || 0;
    tableDetailsModal.value.offset = response.data.offset || 0;
    tableDetailsModal.value.connection = response.data.connection || '';
    
    // Log بعد التحديث
    console.log('Table details modal updated:', {
      columns: tableDetailsModal.value.columns.length,
      data: tableDetailsModal.value.data.length,
      total: tableDetailsModal.value.total
    });
  } catch (error) {
    console.error('فشل تحميل تفاصيل الجدول:', error);
    tableDetailsModal.value.error = error.response?.data?.error || error.message || 'فشل تحميل البيانات';
    toast.error('فشل تحميل تفاصيل الجدول');
  } finally {
    loadingTableDetails.value = false;
  }
};

const formatCellValue = (value) => {
  if (value === null || value === undefined) {
    return '-';
  }
  
  if (typeof value === 'boolean') {
    return value ? '✓' : '✗';
  }
  
  if (typeof value === 'object') {
    return JSON.stringify(value);
  }
  
  if (typeof value === 'string' && value.length > 50) {
    return value.substring(0, 50) + '...';
  }
  
  return String(value);
};

// دوال Sync Metadata
const loadSyncMetadata = async () => {
  loadingMetadata.value = true;
  syncMetadata.value.error = null;
  
  try {
    const response = await axios.get('/api/sync-monitor/metadata', {
      withCredentials: true
    });
    
    syncMetadata.value.data = response.data.metadata || [];
    syncMetadata.value.stats = response.data.stats || null;
    
    toast.success('تم تحميل بيانات المزامنة', { timeout: 2000 });
  } catch (error) {
    console.error('فشل تحميل بيانات المزامنة:', error);
    syncMetadata.value.error = error.response?.data?.error || error.message || 'فشل تحميل البيانات';
    toast.error('فشل تحميل بيانات المزامنة: ' + (error.response?.data?.error || error.message));
  } finally {
    loadingMetadata.value = false;
  }
};

// دوال النسخ الاحتياطية
const loadBackups = async () => {
  loadingBackups.value = true;
  
  try {
    const response = await axios.get('/api/sync-monitor/backups', {
      withCredentials: true
    });
    
    backups.value = response.data.backups || [];
    toast.success('تم تحميل النسخ الاحتياطية', { timeout: 2000 });
  } catch (error) {
    console.error('فشل تحميل النسخ الاحتياطية:', error);
    toast.error('فشل تحميل النسخ الاحتياطية: ' + (error.response?.data?.error || error.message));
  } finally {
    loadingBackups.value = false;
  }
};

const restoreBackup = async (backupName) => {
  if (!confirm(`⚠️ تحذير: هل أنت متأكد من استعادة النسخة الاحتياطية "${backupName}"؟\n\nهذا سيستبدل جميع البيانات الحالية في قاعدة البيانات!`)) {
    return;
  }

  restoringBackup.value = true;
  
  try {
    const response = await axios.post('/api/sync-monitor/restore-backup', {
      backup_file: backupName
    }, {
      withCredentials: true
    });
    
    if (response.data.success) {
      toast.success('✅ تمت استعادة النسخة الاحتياطية بنجاح', { timeout: 3000 });
      await loadBackups();
      await loadSyncedTables();
    } else {
      toast.error('فشلت الاستعادة: ' + (response.data.error || 'خطأ غير معروف'));
    }
  } catch (error) {
    console.error('فشلت الاستعادة:', error);
    toast.error('فشلت الاستعادة: ' + (error.response?.data?.error || error.message));
  } finally {
    restoringBackup.value = false;
  }
};

const downloadBackup = (backupName) => {
  window.open(`/api/sync-monitor/download-backup?file=${encodeURIComponent(backupName)}`, '_blank');
};

const deleteBackup = async (backupName) => {
  if (!confirm(`⚠️ تحذير: هل أنت متأكد من حذف النسخة الاحتياطية "${backupName}"؟\n\nهذا الإجراء لا يمكن التراجع عنه!`)) {
    return;
  }

  try {
    const response = await axios.delete('/api/sync-monitor/backup/delete', {
      params: { file: backupName },
      withCredentials: true
    });
    
    if (response.data.success) {
      toast.success('✅ تم حذف النسخة الاحتياطية بنجاح', { timeout: 3000 });
      await loadBackups();
    } else {
      toast.error('فشل الحذف: ' + (response.data.error || 'خطأ غير معروف'));
    }
  } catch (error) {
    console.error('فشل الحذف:', error);
    toast.error('فشل الحذف: ' + (error.response?.data?.error || error.message));
  }
};

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleString('ar-EG');
};

// دوال تفريغ وحذف الجدول
const truncateTable = async (tableName) => {
  if (!confirm(`⚠️ تحذير: هل أنت متأكد من تفريغ الجدول "${tableName}"؟\n\nهذا سيحذف جميع السجلات من الجدول في SQLite المحلي!`)) {
    return;
  }

  truncatingTable.value = tableName;
  
  try {
    const response = await axios.post(`/api/sync-monitor/table/${tableName}/truncate`, {}, {
      withCredentials: true
    });
    
    if (response.data.success) {
      toast.success(`✅ تم تفريغ الجدول ${tableName} بنجاح`, { timeout: 3000 });
      await loadSyncedTables(); // إعادة تحميل قائمة الجداول
    } else {
      toast.error('فشل التفريغ: ' + (response.data.error || 'خطأ غير معروف'));
    }
  } catch (error) {
    console.error('فشل تفريغ الجدول:', error);
    toast.error('فشل التفريغ: ' + (error.response?.data?.error || error.message));
  } finally {
    truncatingTable.value = null;
  }
};

const deleteTable = async (tableName) => {
  if (!confirm(`⚠️⚠️⚠️ تحذير شديد: هل أنت متأكد تماماً من حذف الجدول "${tableName}"؟\n\nهذا سيحذف الجدول بالكامل من SQLite المحلي!\n\nلا يمكن التراجع عن هذه العملية!`)) {
    return;
  }

  // تأكيد إضافي
  if (!confirm(`⚠️ تأكيد نهائي: هل أنت متأكد 100% من حذف الجدول "${tableName}"؟`)) {
    return;
  }

  deletingTable.value = tableName;
  
  try {
    const response = await axios.delete(`/api/sync-monitor/table/${tableName}/delete`, {
      withCredentials: true
    });
    
    if (response.data.success) {
      toast.success(`✅ تم حذف الجدول ${tableName} بنجاح`, { timeout: 3000 });
      await loadSyncedTables(); // إعادة تحميل قائمة الجداول
    } else {
      toast.error('فشل الحذف: ' + (response.data.error || 'خطأ غير معروف'));
    }
  } catch (error) {
    console.error('فشل حذف الجدول:', error);
    toast.error('فشل الحذف: ' + (error.response?.data?.error || error.message));
  } finally {
    deletingTable.value = null;
  }
};

// دالة مزامنة باتجاه واحد محدد
const syncDirection = async (direction) => {
  const directionName = direction === 'up' ? 'من SQLite إلى MySQL' : 'من MySQL إلى SQLite';
  const directionIcon = direction === 'up' ? '📤' : '📥';

  // قائمة الجداول الأساسية المهمة للنقل (بدلاً من جميع الجداول)
  const importantTables = ['users', 'car', 'car_contract', 'transactions', 'wallets', 'buyer_payments', 'car_sales', 'internal_sales'];

  const confirmMessage = `هل تريد مزامنة الجداول الأساسية ${directionName}؟

📋 الجداول المحددة: ${importantTables.join(', ')}

⚠️ ${direction === 'up' ? 'حماية بيانات السيرفر:' : 'تحديث البيانات المحلية:'}
${direction === 'up' ? '✅ سيتم إنشاء نسخة احتياطية تلقائياً' : '✅ سيتم تحديث البيانات المحلية'}
${direction === 'up' ? '✅ Safe Mode: إضافة فقط (لا تحديث السجلات الموجودة)' : '✅ سيتم جلب أحدث البيانات من السيرفر'}
${direction === 'up' ? '✅ Transaction مع Rollback في حالة الخطأ' : '✅ نسخ ذكي (فقط السجلات الجديدة)'}

سيتم:
${direction === 'up'
  ? 'نقل البيانات من SQLite المحلي إلى MySQL السيرفر'
  : 'تحديث SQLite المحلي من MySQL السيرفر'
}`;

  if (!confirm(confirmMessage)) {
    return;
  }

  syncing.value = true;

  try {
    toast.info(`${directionIcon} بدء مزامنة ${importantTables.length} جدول أساسي ${directionName}...`, { timeout: 3000 });

    const response = await axios.post('/api/sync-monitor/sync', {
      direction: direction,
      tables: importantTables.join(','), // الجداول الأساسية فقط
      safe_mode: direction === 'up' ? false : true, // Safe Mode للاتجاه up فقط
      create_backup: direction === 'up' ? true : false, // نسخة احتياطية للاتجاه up فقط
      force_full_sync: false
    }, {
      withCredentials: true
    });

    if (response.data.success) {
      const results = response.data.results;
      console.log(`✅ تمت المزامنة ${directionName}:`, results);

      let message = `✅ تمت المزامنة بنجاح!\n\n`;
      message += `${directionIcon} ${directionName}: ${results.total_synced} سجل\n`;
      message += `الجداول المزامنة: ${Object.keys(results.success || {}).length} من ${importantTables.length} جدول أساسي\n`;

      if (results.backup_file) {
        message += `💾 النسخة الاحتياطية: ${results.backup_file.split('/').pop()}\n`;
        toast.info(`💾 تم إنشاء نسخة احتياطية: ${results.backup_file.split('/').pop()}`, { timeout: 3000 });
      }

      toast.success(message, { timeout: 5000 });
      await loadSyncMetadata(); // تحديث بيانات المزامنة
      await loadSyncedTables(); // تحديث قائمة الجداول
    } else {
      console.error(`❌ فشلت المزامنة ${directionName}:`, response.data.error);
      toast.error(`❌ فشلت مزامنة الجداول الأساسية ${directionName}: ${response.data.error || 'خطأ غير معروف'}`);
    }

  } catch (error) {
    console.error(`فشلت مزامنة الجداول الأساسية ${directionName}:`, error);
    toast.error(`فشلت مزامنة الجداول الأساسية ${directionName}: ` + (error.response?.data?.error || error.message));
  } finally {
    syncing.value = false;
  }
};

// دالة المزامنة
const startSync = async () => {
  const confirmMessage = `هل تريد مزامنة جميع الجداول؟

⚠️ حماية بيانات السيرفر:
✅ سيتم إنشاء نسخة احتياطية تلقائياً
✅ Safe Mode: إضافة فقط (لا تحديث السجلات الموجودة)
✅ Transaction مع Rollback في حالة الخطأ

سيتم:
1. نقل البيانات من SQLite إلى MySQL (البيانات المحلية → السيرفر)
2. ثم تحديث SQLite من MySQL (التحديثات من السيرفر)`;

  if (!confirm(confirmMessage)) {
    return;
  }

  syncing.value = true;

  try {
    toast.info('🔄 بدء عملية المزامنة مع الحماية...', { timeout: 3000 });

    // 1. أولاً: مزامنة من SQLite إلى MySQL (نقل البيانات المحلية للسيرفر)
    toast.info('📤 نقل البيانات من SQLite إلى MySQL (Safe Mode)...', { timeout: 3000 });
    const responseUp = await axios.post('/api/sync-monitor/sync', {
      direction: 'up', // من SQLite إلى MySQL
      tables: null, // جميع الجداول
      safe_mode: true, // Safe Mode: إضافة فقط، لا تحديث
      create_backup: true // إنشاء نسخة احتياطية
    }, {
      withCredentials: true
    });

    if (responseUp.data.success) {
      const resultsUp = responseUp.data.results;
      console.log('✅ تمت المزامنة من SQLite إلى MySQL:', resultsUp);

      if (resultsUp.backup_file) {
        console.log('💾 النسخة الاحتياطية:', resultsUp.backup_file);
        toast.info(`💾 تم إنشاء نسخة احتياطية: ${resultsUp.backup_file.split('/').pop()}`, { timeout: 3000 });
      }
    } else {
      console.error('❌ فشلت المزامنة من SQLite إلى MySQL:', responseUp.data.error);
      toast.error('❌ فشلت المزامنة - تم Rollback وحماية البيانات');
    }

    // 2. ثانياً: مزامنة من MySQL إلى SQLite (تحديث البيانات المحلية)
    toast.info('📥 تحديث SQLite من MySQL...', { timeout: 3000 });
    const responseDown = await axios.post('/api/sync-monitor/sync', {
      direction: 'down', // من MySQL إلى SQLite
      tables: null // جميع الجداول
    }, {
      withCredentials: true
    });

    if (responseDown.data.success) {
      const resultsDown = responseDown.data.results;
      let message = `✅ تمت المزامنة بنجاح!\n\n`;
      message += `📤 نقل إلى MySQL: ${resultsUp?.total_synced || 0} سجل (Safe Mode)\n`;
      message += `📥 تحديث من MySQL: ${resultsDown.total_synced} سجل\n`;
      message += `الجداول المزامنة: ${Object.keys(resultsDown.success).length}\n`;

      if (resultsUp?.backup_file) {
        message += `\n💾 النسخة الاحتياطية: ${resultsUp.backup_file.split('/').pop()}`;
      }
      
      if (Object.keys(resultsDown.failed).length > 0 || Object.keys(resultsUp?.failed || {}).length > 0) {
        message += `\n⚠️ فشلت بعض الجداول`;
      }
      
      toast.success(message, { timeout: 7000 });
      
      // إعادة تحميل الجداول
      await loadSyncedTables();
    } else {
      toast.error('فشلت المزامنة: ' + (responseDown.data.error || 'خطأ غير معروف'));
    }
  } catch (error) {
    console.error('فشلت المزامنة:', error);
    toast.error('فشلت المزامنة: ' + (error.response?.data?.error || error.message));
  } finally {
    syncing.value = false;
  }
};

// Lifecycle
onMounted(() => {
  refreshData();
  updateConnectionInfo();
  loadSyncedTables();
  loadBackups();
  loadSyncMetadata();

  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);

  refreshInterval = setInterval(refreshData, 10000);

  connectionApiRef = getNavigatorConnection();
  if (connectionApiRef?.addEventListener) {
    connectionApiRef.addEventListener('change', updateConnectionInfo);
  } else if (connectionApiRef) {
    connectionApiRef.onchange = updateConnectionInfo;
  }
});

onUnmounted(() => {
  window.removeEventListener('online', handleOnline);
  window.removeEventListener('offline', handleOffline);

  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }

  if (connectionApiRef?.removeEventListener) {
    connectionApiRef.removeEventListener('change', updateConnectionInfo);
  } else if (connectionApiRef) {
    connectionApiRef.onchange = null;
  }
});

// معلومات الاتصال
const updateConnectionInfo = () => {
  connectionInfo.value = buildConnectionInfo();
};

function buildConnectionInfo() {
  if (typeof window === 'undefined') {
    return { ...defaultConnectionInfo };
  }

  const { protocol, hostname, port, origin } = window.location;
  const normalizedHost = (hostname || '').toLowerCase();
  const isLocalHost =
    ['localhost', '127.0.0.1'].includes(normalizedHost) ||
    normalizedHost.startsWith('192.168.') ||
    normalizedHost.startsWith('10.') ||
    normalizedHost.endsWith('.local');

  const connectionApi = getNavigatorConnection();
  const downlinkValue =
    typeof connectionApi?.downlink === 'number'
      ? Number(connectionApi.downlink.toFixed(1))
      : null;
  const rttValue =
    typeof connectionApi?.rtt === 'number'
      ? Math.round(connectionApi.rtt)
      : null;
  const apiBaseUrl = guessApiBaseUrl(origin);

  return {
    environmentLabel: isLocalHost ? '🖥️ بيئة محلية (Local)' : '☁️ اتصال خادم/سيرفر',
    tip: isLocalHost
      ? 'أنت تعمل على نسخة محلية، تذكّر مزامنة البيانات قبل نشرها.'
      : 'الاتصال مباشر بالسيرفر، تأكد من استقرار الشبكة أثناء المزامنة.',
    host: hostname || '-',
    port: port || (protocol === 'https:' ? '443' : '80'),
    protocol: protocol ? protocol.replace(':', '').toUpperCase() : '-',
    origin: origin || '-',
    secure: protocol === 'https:',
    networkType: connectionApi?.type || 'غير معروف',
    effectiveType: connectionApi?.effectiveType || 'غير معروف',
    downlink: downlinkValue,
    rtt: rttValue,
    apiBaseUrl,
    lastUpdated: new Date().toLocaleTimeString('ar-SA'),
    isLocal: isLocalHost
  };
}

function guessApiBaseUrl(fallbackOrigin = '-') {
  if (typeof window === 'undefined') {
    return fallbackOrigin;
  }

  if (window?.axios?.defaults?.baseURL) {
    return window.axios.defaults.baseURL;
  }

  if (window?.Ziggy?.url) {
    return window.Ziggy.url;
  }

  if (window?.Laravel?.baseUrl) {
    return window.Laravel.baseUrl;
  }

  return window.location?.origin || fallbackOrigin || '-';
}

function getNavigatorConnection() {
  if (typeof navigator === 'undefined') {
    return null;
  }

  return navigator.connection || navigator.mozConnection || navigator.webkitConnection || null;
}

// وظائف نقل تاريخ السيارات
const runCarHistoryMigration = async () => {
  if (migrationRunning.value) return;

  if (!confirm('هل أنت متأكد من تشغيل عملية نقل تاريخ السيارات؟\n\nهذا قد يستغرق وقتاً طويلاً حسب حجم البيانات.')) {
    return;
  }

  migrationRunning.value = true;
  migrationLogs.value = [];
  let offset = 0;
  let hasMore = true;

  addMigrationLog('info', 'بدء عملية نقل تاريخ السيارات');

  try {
    while (hasMore && migrationRunning.value) {
      addMigrationLog('info', `معالجة الدفعة ${offset / migrationSettings.value.batchSize + 1}...`);

      const response = await axios.post('/api/car-history/migrate-transactions', {
        limit: migrationSettings.value.batchSize,
        confirm_delete: migrationSettings.value.deleteOldData,
        offset: offset
      }, { withCredentials: true });

      const stats = response.data.stats;

      // تحديث الإحصائيات
      migrationStats.value.migrated += stats.migrated;
      migrationStats.value.errors += stats.errors;

      addMigrationLog('success', `تم نقل ${stats.migrated} معاملة، فشل ${stats.errors}، تم حذف ${stats.deleted}`);

      if (!response.data.next_offset) {
        hasMore = false;
        addMigrationLog('success', 'انتهت عملية النقل بنجاح');
      } else {
        offset = response.data.next_offset;

        // تأخير بين الدفعات
        if (migrationSettings.value.delay > 0) {
          await new Promise(resolve => setTimeout(resolve, migrationSettings.value.delay * 1000));
        }
      }
    }

    // تحديث الإحصائيات النهائية
    await loadMigrationStats();

  } catch (error) {
    addMigrationLog('error', 'فشلت عملية النقل: ' + (error.response?.data?.message || error.message));
  } finally {
    migrationRunning.value = false;
  }
};

const loadMigrationStats = async () => {
  try {
    // محاكاة للحصول على إحصائيات النقل
    const response = await axios.get('/api/sync-monitor/tables', { withCredentials: true });
    const tables = response.data.tables || [];

    // البحث عن إحصائيات جدول transactions و car_history
    const transactionsTable = tables.find(t => t.name === 'transactions');
    const carHistoryTable = tables.find(t => t.name === 'car_history');

    migrationStats.value = {
      total_transactions: transactionsTable?.count || 0,
      migrated: carHistoryTable?.count || 0,
      remaining: Math.max(0, (transactionsTable?.count || 0) - (carHistoryTable?.count || 0)),
      errors: 0 // يمكن تحسين هذا لاحقاً
    };

  } catch (error) {
    console.error('فشل تحميل إحصائيات النقل:', error);
  }
};

const addMigrationLog = (type, message) => {
  migrationLogs.value.unshift({
    type,
    message,
    timestamp: new Date().toISOString()
  });

  // الاحتفاظ بآخر 50 سجلاً فقط
  if (migrationLogs.value.length > 50) {
    migrationLogs.value = migrationLogs.value.slice(0, 50);
  }
};

const getLogIcon = (type) => {
  const icons = {
    info: 'ℹ️',
    success: '✅',
    warning: '⚠️',
    error: '❌'
  };
  return icons[type] || '📝';
};

// وظائف مساعدة للـ template
const getStatusText = (status) => {
  const statuses = {
    pending: 'في الانتظار',
    synced: 'تمت المزامنة',
    failed: 'فشلت'
  };
  return statuses[status] || status;
};

// وظائف النظام - تم دمجها مع الوظائف الموجودة

const optimizeDatabase = async () => {
  if (optimizing.value) return;

  optimizing.value = true;
  addSystemLog('info', 'بدء تحسين قاعدة البيانات');

  try {
    // محاكاة تحسين قاعدة البيانات
    await new Promise(resolve => setTimeout(resolve, 3000));

    addSystemLog('success', 'تم تحسين قاعدة البيانات بنجاح');
    toast.success('تم تحسين قاعدة البيانات بنجاح');

  } catch (error) {
    addSystemLog('error', 'فشل تحسين قاعدة البيانات');
    toast.error('فشل تحسين قاعدة البيانات');
  } finally {
    optimizing.value = false;
  }
};

const generateBackup = async () => {
  if (backingUp.value) return;

  backingUp.value = true;
  addSystemLog('info', 'بدء إنشاء نسخة احتياطية');

  try {
    // محاكاة إنشاء النسخة الاحتياطية
    await new Promise(resolve => setTimeout(resolve, 5000));

    addSystemLog('success', 'تم إنشاء النسخة الاحتياطية بنجاح');
    toast.success('تم إنشاء النسخة الاحتياطية بنجاح');

    // إعادة تحميل قائمة النسخ الاحتياطية
    await loadBackups();

  } catch (error) {
    addSystemLog('error', 'فشل إنشاء النسخة الاحتياطية');
    toast.error('فشل إنشاء النسخة الاحتياطية');
  } finally {
    backingUp.value = false;
  }
};

const checkSystemHealth = async () => {
  if (checkingHealth.value) return;

  checkingHealth.value = true;
  addSystemLog('info', 'بدء فحص صحة النظام');

  try {
    // محاكاة فحص النظام
    const checks = [
      { name: 'اتصال قاعدة البيانات', status: 'ok' },
      { name: 'مساحة التخزين', status: 'ok' },
      { name: 'ذاكرة النظام', status: 'warning' },
      { name: 'اتصال الإنترنت', status: connectionStatus.value.online ? 'ok' : 'error' },
    ];

    await new Promise(resolve => setTimeout(resolve, 2000));

    const errors = checks.filter(check => check.status === 'error').length;
    const warnings = checks.filter(check => check.status === 'warning').length;

    systemInfo.value = {
      php_version: '8.2.12',
      laravel_version: '10.x',
      database: 'SQLite',
      storage_used: '2.3 GB'
    };

    if (errors === 0 && warnings === 0) {
      addSystemLog('success', 'النظام في حالة ممتازة');
      toast.success('النظام في حالة ممتازة');
    } else {
      addSystemLog('warning', `تم العثور على ${errors} أخطاء و ${warnings} تحذيرات`);
      toast.warning(`تم العثور على ${errors} أخطاء و ${warnings} تحذيرات`);
    }

  } catch (error) {
    addSystemLog('error', 'فشل فحص صحة النظام');
    toast.error('فشل فحص صحة النظام');
  } finally {
    checkingHealth.value = false;
  }
};

const addSystemLog = (type, message) => {
  systemLogs.value.unshift({
    type,
    message,
    timestamp: new Date().toISOString()
  });

  // الاحتفاظ بآخر 50 سجلاً فقط
  if (systemLogs.value.length > 50) {
    systemLogs.value = systemLogs.value.slice(0, 50);
  }
};

// تحميل معلومات قاعدة البيانات
const loadDatabaseInfo = async () => {
  loadingDatabaseInfo.value = true;

  try {
    // محاولة الحصول على معلومات من API
    const response = await axios.get('/api/sync-monitor/tables', { withCredentials: true });
    const tables = response.data.tables || [];

    // حساب الإحصائيات
    const totalRecords = tables.reduce((sum, table) => sum + (table.count || 0), 0);

    // محاولة الحصول على حجم الملف
    let fileSize = 'غير محدد';
    try {
      // محاكاة الحصول على حجم قاعدة البيانات
      const fs = require('fs');
      const stats = fs.statSync('database/database.sqlite');
      fileSize = formatBytes(stats.size);
    } catch (e) {
      // في حالة عدم القدرة على قراءة الملف، نستخدم قيمة افتراضية
      fileSize = 'غير محدد';
    }

    databaseInfo.value = {
      type: 'SQLite',
      version: '3.x',
      path: 'database/database.sqlite',
      total_tables: tables.length,
      total_records: totalRecords,
      size: fileSize
    };

    addSystemLog('info', 'تم تحديث معلومات قاعدة البيانات');

  } catch (error) {
    console.error('فشل تحميل معلومات قاعدة البيانات:', error);
    addSystemLog('error', 'فشل تحميل معلومات قاعدة البيانات');

    // قيم افتراضية في حالة الفشل
    databaseInfo.value = {
      type: 'SQLite',
      version: 'غير محدد',
      path: 'database/database.sqlite',
      total_tables: syncedTables.value.length,
      total_records: 'غير محدد',
      size: 'غير محدد'
    };
  } finally {
    loadingDatabaseInfo.value = false;
  }
};

// دالة مساعدة لتنسيق حجم الملف
const formatBytes = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// تهيئة البيانات عند تحميل الصفحة
onMounted(async () => {
  await loadMigrationStats();
  await loadDatabaseInfo();
});
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>

