# سجل التغييرات - تحويل Qat ERP إلى تطبيق Android أصلي (NativePHP)

## التاريخ: 2026-06-19

---

## الملخص

تم تحويل مشروع Qat ERP (Laravel 11) من نظام ويب إلى تطبيق Android أصلي باستخدام NativePHP Mobile v3.3.6 مع الحفاظ على جميع الوظائف الحالية وبدون كسر أي Route أو Controller أو منطق نظام.

---

## ما تم تنفيذه

### 1. تثبيت NativePHP Mobile
- تثبيت `nativephp/mobile` v3.3.6 عبر Composer
- تشغيل `php artisan native:install android` بنجاح
- تنزيل PHP binaries للـ Android (PHP 8.3.31)
- إنشاء هيكل مشروع Android داخل `nativephp/android/`

### 2. إعداد التكوين
- إنشاء `config/nativephp.php` مع إعدادات مخصصة:
  - App ID: `com.qaterp.qat`
  - Start URL: `/login` (تطبيق المصادقة أولاً)
  - Runtime: Persistent mode (~5-30ms لكل طلب)
  - Android SDK: API 26-35 (Android 8.0+)
  - Theme: الأخضر الداكن (#1B5E20) متناسق مع هوية Qat ERP
  - Portrait فقط (مناسب لتطبيق ERP)
- إضافة متغيرات NativePHP إلى `.env` و `.env.example`

### 3. إعداد Vite
- إنشاء `vite.config.js` مع NativePHP Vite Plugin
- نقل الأصول من `public/assets/` إلى `resources/` (CSS و JS)
- تحديث `layouts/app.blade.php` لاستخدام `@vite()` بدل `asset()`
- إضافة meta tags للموبايل (viewport-fit=cover, theme-color, mobile-web-app-capable)

### 4. GitHub Actions Workflows
- إنشاء `.github/workflows/build-android.yml`:
  - بناء APK و AAB تلقائياً عند إنشاء Tag (v*)
  - دعم البناء اليدوي عبر workflow_dispatch
  - توقيع APK/AAB باستخدام GitHub Secrets
  - إنشاء GitHub Release تلقائي مع رفع الملفات
  - JDK 17 + Android SDK
  - Cache للـ Composer و Android SDK
- إنشاء `.github/workflows/ci.yml`:
  - التحقق من المشروع عند Push/PR
  - تأكيد صحة NativePHP config و routes

### 5. تنظيف المشروع
- حذف `FilamentServiceProvider.php` (كود ميت - لا يوجد Filament)
- إنشاء `NativeServiceProvider.php` فارغ (جاهز لتسجيل Plugins مستقبلاً)
- تحديث `.gitignore` لإضافة `nativephp/` وملفات hot
- تحديث `README.md` بتعليمات التشغيل والبناء

### 6. سكريبت الإعداد
- إنشاء `setup.sh` - سكريبت تثبيت تلقائي للابتوب المحلي
- يتضمن فحص PHP 8.3+, Node.js 18+, Composer
- تثبيت تبعيات Composer و npm تلقائياً
- بناء الأصول الأمامية وتجهيز قاعدة البيانات

---

## الملفات المُعدّلة

| الملف | التغيير |
|-------|---------|
| `composer.json` | إضافة `nativephp/mobile` v3.3.6 |
| `composer.lock` | تحديث التبعيات |
| `.env` | إضافة متغيرات NativePHP |
| `.env.example` | إضافة متغيرات NativePHP + Android Signing |
| `.gitignore` | إضافة `nativephp/` وملفات hot |
| `config/nativephp.php` | ملف جديد - تكوين NativePHP |
| `vite.config.js` | ملف جديد - إعداد Vite مع NativePHP Plugin |
| `resources/css/app.css` | ملف جديد - نسخة من public/assets/css/app.css |
| `resources/js/app.js` | ملف جديد - نسخة من public/assets/js/app.js |
| `resources/views/layouts/app.blade.php` | تحديث لاستخدام @vite + meta tags موبايل |
| `app/Providers/NativeServiceProvider.php` | ملف جديد - Service Provider لـ NativePHP |
| `.github/workflows/build-android.yml` | ملف جديد - بناء APK/AAB |
| `.github/workflows/ci.yml` | ملف جديد - CI للتحقق |
| `README.md` | تحديث بالتعليمات الجديدة |
| `setup.sh` | ملف جديد - سكريبت الإعداد |

## الملفات المحذوفة

| الملف | السبب |
|-------|-------|
| `app/Providers/FilamentServiceProvider.php` | كود ميت - لا يوجد Filament في المشروع |

## الملفات التي لم تُلمس (محفوظة بالكامل)

- جميع `app/Http/Controllers/` (26 controller)
- جميع `app/Models/` (40 model)
- جميع `app/Services/` (21 service)
- جميع `routes/` (web.php, api.php, console.php, channels.php)
- جميع `resources/views/` (باستثناء layouts/app.blade.php)
- جميع `database/migrations/`
- جميع `database/seeders/`
- جميع `app/Policies/`
- جميع `app/Http/Middleware/`
- جميع `app/Http/Requests/`
- جميع `app/Http/Resources/`
- جميع `config/` (باستثناء إضافة nativephp.php)
- `public/assets/` (محفوظة للتوافق العكسي)
- `bootstrap/app.php`
- `artisan`

---

## إعداد GitHub Secrets المطلوب

قبل رفع المشروع إلى GitHub، أضف هذه الأسرار في:

**Settings → Secrets and variables → Actions**

| السر | الوصف |
|------|-------|
| `ANDROID_KEYSTORE_BASE64` | ملف keystore بصيغة Base64: `base64 -i keystore.jks \| pbcopy` |
| `ANDROID_KEYSTORE_PASSWORD` | كلمة مرور Keystore |
| `ANDROID_KEY_ALIAS` | اسم المفتاح داخل Keystore |
| `ANDROID_KEY_PASSWORD` | كلمة مرور المفتاح |

### إنشاء Keystore (مرة واحدة)
```bash
keytool -genkeypair -v \
  -keystore qat-erp-release.jks \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000 \
  -alias qat-erp
```

---

## كيفية التشغيل

### على الويب (كما كان سابقاً)
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

### كتطبيق Android
```bash
# محلياً
php artisan native:install android
php artisan native:run android

# عبر GitHub Actions
git tag v1.0.0
git push origin v1.0.0
# → سيتم بناء APK و AAB تلقائياً وإنشاء Release
```

---

## ما لم يُنفذ (قابل للتطوير المستقبلي)

1. **تثبيت Plugins** - لم يتم تثبيت أي Plugins إضافية (Camera, Push Notifications, etc.)
   - يمكن تثبيتها حسب الحاجة: `composer require nativephp/camera-plugin`
2. **أيقونات التطبيق** - يمكن إضافة أيقونات مخصصة لـ Qat ERP
   - عبر config/nativephp.php أو مجلد resources المخصص
3. **Splash Screen مخصص** - الشاشة الافتراضية كافية حالياً
4. **Push Notifications عبر Firebase** - يحتاج إعداد Firebase project
5. **Deep Links متقدمة** - التطبيق يدعم deep links أساسية
6. **تحسين الأداء** - R8/ProGuard معطل حالياً (مفعل عند الإنتاج)
7. **اختبارات تلقائية** - لا توجد اختبارات PHPUnit حالياً
