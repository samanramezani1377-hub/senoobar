# صنوبر (Senoobar)

قالب فروشگاهی وردپرس تخصصی تشک طبی، فنری و کالای خواب

## ویژگی‌ها

- 🎨 **Glassmorphism Dark Design** — طراحی شیشه‌ای مینیمال با پس‌زمینه تیره
- 📱 **Mobile First & Fully Responsive** — بهینه برای تمام دستگاه‌ها
- 🛒 **WooCommerce Ready** — پشتیبانی کامل از ووکامرس
- 🚀 **Performance Optimized** — حذف jQuery Migrate، دیفر JS، لود تنبل تصاویر
- 📳 **PWA Support** — قابلیت نصب به عنوان اپلیکیشن و Push Notification
- 🔍 **SEO Friendly** — سازگار با Yoast SEO
- 🌐 **RTL Support** — کاملاً راست‌چین و فارسی

## نصب

۱. فایل ZIP قالب را دانلود کنید
۲. در پیشخوان وردپرس به **نمایش → پوسته‌ها → افزودن پوسته → بارگذاری پوسته** بروید
۳. فایل ZIP را آپلود و فعال کنید
۴. افزونه‌های پیشنهادی: WooCommerce, Yoast SEO, Loco Translate

## ساختار فایل‌ها

```
└── senoobar/
    ├── style.css              # Header اطلاعات قالب
    ├── functions.php          # Bootstrap
    ├── header.php             # هدر + منوی موبایل
    ├── footer.php             # فوتر
    ├── index.php              # قالب اصلی
    ├── sw.js                  # Service Worker
    ├── manifest.json          # PWA manifest
    ├── inc/
    │   ├── class-senoobar-theme.php   # کلاس اصلی
    │   ├── woocommerce-setup.php      # تنظیمات ووکامرس
    │   └── push-handlers.php          # AJAX push notification
    ├── template-parts/
    │   ├── hero.php                   # بخش اصلی صفحه اول
    │   ├── categories-grid.php        # دسته‌بندی محصولات
    │   ├── featured-products.php      # محصولات ویژه
    │   ├── cta-banner.php             # بنر دعوت به خرید
    │   ├── content-single.php         # قالب نوشته
    │   ├── content-page.php           # قالب برگه
    │   ├── content-archive.php        # قالب آرشیو
    │   └── content-none.php           # قالب بدون محتوا
    ├── assets/
    │   ├── css/
    │   │   ├── critical.css           # استایل بحرانی
    │   │   ├── main.css               # استایل اصلی + ووکامرس
    │   │   └── rtl.css                # تنظیمات راست‌چین
    │   ├── js/
    │   │   ├── app.js                 # اپ فرانت‌اند
    │   │   └── push.js                # منطق Push Notification
    │   ├── icons/                     # آیکن‌های PWA
    │   └── images/                    # تصاویر
    └── data/
        ├── products-part1.csv         # ۵۰ محصول اول
        └── products-part2.csv         # ۴۹ محصول دوم
```

## توسعه‌دهنده

- **توسعه‌دهنده:** Saman Ramezani
- **وب‌سایت:** [senoobar.ir](https://senoobar.ir)
- **گیت‌هاب:** [samanramezani1377-hub/senoobar.ir](https://github.com/samanramezani1377-hub/senoobar.ir)
