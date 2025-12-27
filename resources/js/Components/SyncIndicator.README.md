# مكون SyncIndicator

مكون Vue للتحكم في حالة المزامنة والتبديل بين السيرفر المحلي والسيرفر على الإنترنت.

## المميزات

- ✅ عرض حالة الاتصال (Online/Offline)
- ✅ عرض حالة المزامنة
- ✅ التبديل بين Local و Online
- ✅ مزامنة يدوية
- ✅ قابل للتخصيص (الموضع، الحجم، إلخ)
- ✅ دعم RTL

## الاستخدام الأساسي

```vue
<template>
  <div>
    <!-- استخدام بسيط -->
    <SyncIndicator />
    
    <!-- أو مع خيارات مخصصة -->
    <SyncIndicator 
      position="fixed"
      bottom="20px"
      left="20px"
      :show-switch-buttons="true"
    />
  </div>
</template>

<script setup>
import SyncIndicator from '@/Components/SyncIndicator.vue';
</script>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `position` | String | `'fixed'` | موضع المكون: `'fixed'`, `'relative'`, `'absolute'` |
| `bottom` | String | `'20px'` | المسافة من الأسفل |
| `left` | String | `'20px'` | المسافة من اليسار |
| `right` | String | `'auto'` | المسافة من اليمين |
| `top` | String | `'auto'` | المسافة من الأعلى |
| `showSwitchButtons` | Boolean | `true` | إظهار أزرار التبديل بين Local/Online |

## أمثلة الاستخدام

### 1. في صفحة معينة (موضع ثابت)

```vue
<template>
  <AuthenticatedLayout>
    <div>
      <!-- محتوى الصفحة -->
      
      <!-- مؤشر المزامنة في الزاوية -->
      <SyncIndicator />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SyncIndicator from '@/Components/SyncIndicator.vue';
</script>
```

### 2. في شريط جانبي (موضع نسبي)

```vue
<template>
  <div class="sidebar">
    <SyncIndicator 
      position="relative"
      bottom="auto"
      left="auto"
    />
  </div>
</template>
```

### 3. بدون أزرار التبديل

```vue
<template>
  <SyncIndicator :show-switch-buttons="false" />
</template>
```

### 4. موضع مخصص

```vue
<template>
  <!-- في الزاوية اليمنى السفلى -->
  <SyncIndicator 
    position="fixed"
    bottom="20px"
    right="20px"
    left="auto"
  />
  
  <!-- في الزاوية اليسرى العلوية -->
  <SyncIndicator 
    position="fixed"
    top="20px"
    left="20px"
    bottom="auto"
  />
</template>
```

## الوظائف المتاحة

المكون يستخدم الوظائف التالية من `window`:

- `window.switchToLocal()` - التبديل إلى السيرفر المحلي
- `window.switchToOnline()` - التبديل إلى السيرفر على الإنترنت
- `window.connectionInfo` - معلومات الاتصال (URLs، الحالة، إلخ)

## الألوان والحالات

- 🟢 **Online**: أخضر - الاتصال متاح
- 🔴 **Offline**: أحمر - الاتصال غير متاح
- 🔵 **Syncing**: أزرق - جاري المزامنة
- 🟡 **Pending**: أصفر - عمليات في الانتظار

## ملاحظات

- المكون يعمل تلقائياً عند تحميل الصفحة
- يتم تحديث الحالة كل 30 ثانية
- عند عودة الاتصال، يتم المزامنة تلقائياً
- المكون يستخدم `window.connectionInfo` للحصول على URLs من Laravel

