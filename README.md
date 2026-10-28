# ระบบ POS ด้วย Laravel

ระบบ Point of Sale สำหรับร้านค้าปลีก พัฒนาด้วย Laravel 13 / PHP 8.4 / SQLite / Bootstrap 5

## โมดูล
Login · สมาชิก (แต้มสะสม/ระดับ) · พนักงาน · สินค้าและสต็อก · การขาย (POS) · การชำระเงิน (เงินสด/โอน/บัตร + ใบเสร็จ) · รายงานการขาย (+CSV) · Dashboard · หมวดหมู่ · ความเคลื่อนไหวสต็อก

## ติดตั้ง
ต้องมี PHP 8.3+ (pdo_sqlite, mbstring, openssl, fileinfo) และ Composer

```
composer install
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

เปิด http://127.0.0.1:8000 (บน Windows ดับเบิลคลิก `run-pos.bat` ได้)

## บัญชีทดลอง (รหัสผ่าน `password`)
| บทบาท | อีเมล |
|---|---|
| ผู้ดูแลระบบ | admin@pos.test |
| พนักงานขาย | cashier@pos.test |

## ทดสอบ
```
php artisan test
```
