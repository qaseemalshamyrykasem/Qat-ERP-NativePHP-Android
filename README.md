# Qat ERP - Android Native App (NativePHP)

> نظام ERP متكامل لتجار القات اليمني - تطبيق Android أصلي مبني بـ NativePHP Mobile v3

## التشغيل السريع على الويب

```bash
composer install --optimize-autoloader
cp .env.example .env
php artisan key:generate
# عدّل .env: ضع بيانات قاعدة البيانات
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

زور http://localhost:8000 — تسجيل الدخول: `admin` / `password`

## بناء تطبيق Android

### المتطلبات
- PHP 8.3+
- Node.js 18+
- Android Studio 2024.2.1+
- Android SDK API 35+
- JDK 17

### البناء محلياً

```bash
# تثبيت التطبيق كملف Android أصلي
php artisan native:install android

# تشغيل مباشر على جهاز/محاكي
php artisan native:run android

# بناء APK موقّع
php artisan native:package android --build-type=release

# بناء AAB (لـ Google Play)
php artisan native:package android --build-type=bundle
```

### البناء عبر GitHub Actions

1. ارفع المشروع إلى GitHub
2. أضف الأسرار التالية في Settings → Secrets and variables → Actions:
   - `ANDROID_KEYSTORE_BASE64`: ملف keystore بصيغة Base64 (`base64 -i keystore.jks`)
   - `ANDROID_KEYSTORE_PASSWORD`: كلمة مرور Keystore
   - `ANDROID_KEY_ALIAS`: اسم المفتاح
   - `ANDROID_KEY_PASSWORD`: كلمة مرور المفتاح
3. أنشئ Tag للإصدار: `git tag v1.0.0 && git push origin v1.0.0`
4. ستبدأ GitHub Actions تلقائياً ببناء APK و AAB وإنشاء GitHub Release

### إعدادات إضافية
```
NATIVEPHP_APP_ID=com.qaterp.qat       # معرّف التطبيق
NATIVEPHP_APP_VERSION=1.0.0            # رقم الإصدار
NATIVEPHP_ANDROID_MIN_SDK=26            # أقل إصدار Android مدعوم (Android 8.0)
NATIVEPHP_ANDROID_COMPILE_SDK=35        # إصدار SDK للبناء
NATIVEPHP_ANDROID_TARGET_SDK=35         # إصدار SDK المستهدف
```

## GitHub Actions Workflows

| Workflow | الزناد | الوصف |
|----------|--------|-------|
| `build-android.yml` | Tag `v*` أو يدوي | بناء APK/AAB + إنشاء Release |
| `ci.yml` | Push/PR على main | التحقق من صحة المشروع |

## التوثيق

- 📐 [ARCHITECTURE.md](docs/ARCHITECTURE.md) — هيكل النظام
- 📦 [INSTALLATION.md](docs/INSTALLATION.md) — دليل التثبيت
- 🔌 [API_DOCS.md](docs/API_DOCS.md) — مرجع REST API v1
- 🗄️ [DATABASE.md](docs/DATABASE.md) — مخطط قاعدة البيانات
- 🔄 [MIGRATION_REPORT.md](docs/MIGRATION_REPORT.md) — سجل التحويل من النظام القديم

## التقنيات المستخدمة

- **الإطار**: Laravel 11 (PHP 8.3+)
- **القاعدة**: MySQL 8 / MariaDB 10.6+ / SQLite (للموبايل)
- **المصادقة**: Sanctum + Spatie Permission (RBAC)
- **الواجهة**: Blade + Bootstrap 5 RTL (خط Cairo)
- **الموبايل**: NativePHP Mobile v3 (Android أصلي)
- **البناء**: Vite + GitHub Actions

## الرخصة

MIT
