-- ============================================
-- نظام دفعات النقل الداخلي
-- تاريخ: 2026-01-18
-- ============================================

-- الخطوة 1: إنشاء جدول دفعات النقل الداخلي
-- ============================================

CREATE TABLE IF NOT EXISTS `internal_transport_payments` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `trip_company_id` bigint unsigned NOT NULL,
    `amount` decimal(10, 2) NOT NULL COMMENT 'مبلغ الدفعة بالدولار',
    `driver_name` varchar(255) NOT NULL COMMENT 'اسم السائق',
    `cmr_number` varchar(255) NULL COMMENT 'رقم CMR',
    `payment_date` date NOT NULL COMMENT 'تاريخ الدفعة',
    `notes` text NULL COMMENT 'ملاحظات',
    `owner_id` bigint unsigned NOT NULL,
    `created_at` timestamp NULL,
    `updated_at` timestamp NULL,
    PRIMARY KEY (`id`),
    KEY `internal_transport_payments_trip_company_id_index` (`trip_company_id`),
    KEY `internal_transport_payments_owner_id_index` (`owner_id`),
    KEY `internal_transport_payments_payment_date_index` (`payment_date`),
    CONSTRAINT `internal_transport_payments_trip_company_id_foreign` 
        FOREIGN KEY (`trip_company_id`) REFERENCES `trip_companies` (`id`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- الخطوة 2: تسجيل الـ Migration
-- ============================================

INSERT INTO `migrations` (`migration`, `batch`) 
VALUES ('2026_01_18_130135_create_internal_transport_payments_table', 
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) AS temp));

-- ============================================
-- التحقق من النجاح
-- ============================================

-- عرض بنية الجدول
DESCRIBE internal_transport_payments;

-- عرض الفهارس
SHOW INDEX FROM internal_transport_payments;

-- ============================================
-- مثال على الاستخدام
-- ============================================

-- إضافة دفعة نقل
-- INSERT INTO internal_transport_payments 
-- (trip_company_id, amount, driver_name, cmr_number, payment_date, owner_id, created_at, updated_at)
-- VALUES 
-- (1, 500.00, 'أحمد محمد', 'CMR-001', '2026-01-18', 1, NOW(), NOW());

-- حساب مجموع دفعات النقل لشحنة معينة
-- SELECT 
--     tc.id AS shipment_id,
--     COUNT(itp.id) AS payments_count,
--     SUM(itp.amount) AS total_transport,
--     COUNT(tcr.id) AS cars_count,
--     ROUND(SUM(itp.amount) / COUNT(DISTINCT tcr.id), 2) AS transport_per_car
-- FROM trip_companies tc
-- LEFT JOIN internal_transport_payments itp ON tc.id = itp.trip_company_id
-- LEFT JOIN trip_cars tcr ON tc.id = tcr.trip_company_id
-- WHERE tc.id = 1
-- GROUP BY tc.id;

-- عرض جميع دفعات النقل لشحنة معينة
-- SELECT 
--     id,
--     amount,
--     driver_name,
--     cmr_number,
--     payment_date,
--     notes,
--     created_at
-- FROM internal_transport_payments
-- WHERE trip_company_id = 1
-- ORDER BY payment_date DESC;

-- حساب الإجمالي الكلي (التخليص + مجموع النقل)
-- SELECT 
--     tc.id,
--     c.name AS company_name,
--     t.ship_name,
--     COUNT(DISTINCT tcr.id) AS cars_count,
--     tc.clearance_per_car,
--     (tc.clearance_per_car * COUNT(DISTINCT tcr.id)) AS total_clearance,
--     SUM(itp.amount) AS total_transport,
--     ((tc.clearance_per_car * COUNT(DISTINCT tcr.id)) + COALESCE(SUM(itp.amount), 0)) AS grand_total
-- FROM trip_companies tc
-- LEFT JOIN users c ON tc.company_id = c.id
-- LEFT JOIN trips t ON tc.trip_id = t.id
-- LEFT JOIN trip_cars tcr ON tc.id = tcr.trip_company_id
-- LEFT JOIN internal_transport_payments itp ON tc.id = itp.trip_company_id
-- WHERE tc.id = 1
-- GROUP BY tc.id, c.name, t.ship_name, tc.clearance_per_car;

-- ============================================
-- ملاحظات مهمة
-- ============================================

-- 1. الدفعات:
--    - كل دفعة منفصلة (المبلغ، السائق، CMR، التاريخ)
--    - يمكن إضافة عدد غير محدود من الدفعات
--    - المجموع يُحسب تلقائياً

-- 2. CMR Number:
--    - رقم الوثيقة الدولية للنقل (Convention Merchandise Road)
--    - اختياري (يمكن تركه فارغاً)

-- 3. الحسابات:
--    - المجموع الكلي = SUM(amount) من جميع الدفعات
--    - لكل سيارة = المجموع الكلي ÷ عدد السيارات
--    - الإجمالي = التخليص + مجموع النقل

-- 4. الحذف:
--    - عند حذف trip_company، تُحذف جميع دفعاتها تلقائياً (CASCADE)

-- ============================================
-- مثال عملي كامل
-- ============================================

-- السيناريو: شحنة بها 50 سيارة، 3 دفعات نقل

-- الدفعات:
-- 1. 500 دولار - أحمد محمد - CMR-001 - 2026-01-10
-- 2. 300 دولار - خالد علي - CMR-002 - 2026-01-12
-- 3. 700 دولار - محمد حسن - CMR-003 - 2026-01-15

-- الحسابات:
-- مجموع النقل: 500 + 300 + 700 = 1,500 دولار
-- لكل سيارة: 1,500 ÷ 50 = 30 دولار
-- التخليص: 40 × 50 = 2,000 دولار
-- الإجمالي: 2,000 + 1,500 = 3,500 دولار

-- ============================================
-- تم بنجاح! ✅
-- ============================================
