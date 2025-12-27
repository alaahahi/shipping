/**
 * نظام قفل للطلبات الحساسة
 * يمنع تكرار أي طلب حساس (خط أحمر)
 */

// Map لتتبع الطلبات الجارية
const pendingRequests = new Map();

// Map لتتبع آخر مرة تم فيها إرسال طلب معين
const requestHistory = new Map();

// الحد الأدنى بين الطلبات المتتالية (500ms)
const MIN_REQUEST_INTERVAL = 500;

/**
 * قفل طلب حساس - يمنع التكرار تماماً
 * @param {string} key - مفتاح فريد للطلب
 * @param {Function} requestFn - دالة الطلب
 * @param {Object} options - خيارات إضافية
 * @returns {Promise}
 */
export async function lockSensitiveRequest(key, requestFn, options = {}) {
    const {
        timeout = 30000, // 30 ثانية timeout للطلبات الحساسة
        allowDuplicate = false // السماح بالتكرار (افتراضي: لا)
    } = options;

    // التحقق من آخر مرة تم فيها إرسال هذا الطلب
    const lastRequestTime = requestHistory.get(key);
    const now = Date.now();
    
    if (lastRequestTime && (now - lastRequestTime) < MIN_REQUEST_INTERVAL) {
        console.warn('⛔ طلب متكرر تم منعه (أقل من 500ms):', key);
        throw new Error('طلب متكرر - يرجى الانتظار');
    }

    // إذا كان الطلب قيد التنفيذ بالفعل
    if (pendingRequests.has(key) && !allowDuplicate) {
        console.warn('⏳ طلب قيد التنفيذ - انتظار النتيجة:', key);
        return pendingRequests.get(key);
    }

    // تسجيل وقت الطلب
    requestHistory.set(key, now);

    // إنشاء promise مع timeout
    const requestPromise = Promise.race([
        requestFn(),
        new Promise((_, reject) => 
            setTimeout(() => reject(new Error('انتهت مهلة الطلب')), timeout)
        )
    ]);

    // حفظ الطلب في القائمة
    pendingRequests.set(key, requestPromise);

    try {
        const result = await requestPromise;
        return result;
    } finally {
        // إزالة من القائمة بعد الانتهاء
        setTimeout(() => {
            pendingRequests.delete(key);
        }, 100);
    }
}

/**
 * إنشاء مفتاح فريد للطلب
 * @param {string} method - HTTP method
 * @param {string} url - URL
 * @param {Object} data - بيانات الطلب (اختياري)
 * @returns {string}
 */
export function createRequestKey(method, url, data = null) {
    const dataHash = data ? JSON.stringify(data).substring(0, 50) : '';
    return `${method.toUpperCase()}-${url}-${dataHash}`;
}

/**
 * تنظيف السجل القديم
 */
function cleanupHistory() {
    const now = Date.now();
    const maxAge = 60000; // دقيقة واحدة
    
    for (const [key, time] of requestHistory.entries()) {
        if (now - time > maxAge) {
            requestHistory.delete(key);
        }
    }
}

// تنظيف السجل كل 30 ثانية
setInterval(cleanupHistory, 30000);

/**
 * التحقق من كون الطلب حساس
 * @param {string} url - URL
 * @returns {boolean}
 */
export function isSensitiveRequest(url) {
    const sensitivePaths = [
        'payment',
        'transaction',
        'accounting',
        'wallet',
        'balance',
        'debt',
        'addPayment',
        'DelPayment',
        'updateCars',
        'DelCar',
        'addCarContracts',
        'editCarContracts',
        'makeCarExit',
        'makeDrivingDocument',
        'checkClientBalance'
    ];
    
    const urlLower = url.toLowerCase();
    return sensitivePaths.some(path => urlLower.includes(path.toLowerCase()));
}

/**
 * إلغاء جميع الطلبات الجارية (للطوارئ)
 */
export function cancelAllPendingRequests() {
    console.warn('🚨 إلغاء جميع الطلبات الجارية');
    pendingRequests.clear();
    requestHistory.clear();
}

/**
 * الحصول على عدد الطلبات الجارية
 * @returns {number}
 */
export function getPendingRequestsCount() {
    return pendingRequests.size;
}

// تصدير للوصول العام
if (typeof window !== 'undefined') {
    window.requestLock = {
        lock: lockSensitiveRequest,
        createKey: createRequestKey,
        isSensitive: isSensitiveRequest,
        cancelAll: cancelAllPendingRequests,
        getPendingCount: getPendingRequestsCount
    };
}

