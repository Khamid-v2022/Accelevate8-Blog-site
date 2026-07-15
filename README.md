# Accelevate — Local WordPress Blog

Local WordPress blog site for **Accelevate**, built with the Kadence theme and 24 imported posts from `.docx` sources.

## Quick start (Laragon)

### 1) Link Laragon (first time only)

Open PowerShell **as Administrator** and run:

```powershell
cd C:\WorkSpace\WP-blogs
.\scripts\link-laragon.ps1
```

Start **Apache + MySQL** in Laragon, then open:

- Site: http://wp-blogs.test
- Admin: http://wp-blogs.test/wp-admin

### 2) Login

| Field | Value |
|------|--------|
| Username | `admin` |
| Password | `admin123` |
| Email | `admin@example.com` |

> Change the password before deploying to production.

---

## Site setup

| Item | Details |
|------|---------|
| Theme | **Kadence** (header / footer / logo via Customizer) |
| Plugins | Kadence Blocks, Accelevate Site Styles |
| Categories | Goals, Habits, Mindset, Reflection |
| Posts | 24 (imported from `blogs/` docx files) |
| Homepage | Gutenberg block page (editable in admin) |
| Blog archive | `/blog/` |

Top navigation: **Home**, **Blog**. Category filters (All, Goals, Habits, Mindset, Reflection) appear on the Blog archive and category pages.

---

## Editing in WP Admin

### Add / edit posts
**Posts → All Posts → Add New**

### Change logos
Copy files into both:
- `assets/branding/`
- `wordpress/wp-content/plugins/mindful-living/assets/logos/`

Recommended filenames: `logo-white.png`, `logo-color.png`, `logo-dark.png`.

### Menu / Header / Footer
**Appearance → Customize**
- Header (logo, menu, layout)
- Footer (copyright, links, layout)

### Edit homepage
**Pages → Home → Edit**

### Colors & fonts
**Appearance → Customize → General → Colors & Fonts**

Readable defaults also live in the `Accelevate Site Styles` plugin CSS.

---

## Scripts

### Full site setup (theme, menu, homepage, import)

```powershell
cd C:\WorkSpace\WP-blogs
.\scripts\setup_site.ps1
```

### Re-import posts from docx only

```powershell
cd C:\WorkSpace\WP-blogs
python scripts\import_posts.py
```

---

## Hosting deployment

1. Install **All-in-One WP Migration** (local and host).
2. Export locally → download the `.wpress` file.
3. Import into the hosted WordPress site.
4. Update **Settings → General** Site URL / WordPress URL to the live domain.
5. Change the admin password.
6. Confirm logo, menu, and footer.

Or migrate manually:
- Upload `wordpress/` files
- Export / import the `wp_blogs` database
- Update DB credentials in `wp-config.php`

---

## Design notes

- Body: **Lora** serif (larger type, comfortable line height)
- UI / titles: **Source Sans 3** sans-serif
- Warm background (`#faf8f5`), high-contrast body text
- Mobile: body 18px+, touch targets 48px+
- Responsive card-style archive grid (4 columns on desktop)

---

## Project structure

```
WP-blogs/
├── blogs/              # Source docx files by category
├── assets/branding/    # Logo source copies
├── scripts/            # Import / setup scripts
├── wordpress/          # WordPress install
└── wp-cli.phar         # WP-CLI
```

---

## Troubleshooting

**wp-blogs.test will not open**
- Confirm Laragon is running
- Re-run `link-laragon.ps1` as Administrator
- Laragon → Menu → Apache → Reload

**Post URLs return 404**
- Run `.\scripts\fix-permalinks.ps1`
- Laragon → Menu → Apache → **Reload**
- Confirm `wordpress/.htaccess` exists

**Database connection errors**
- Confirm MySQL is running in Laragon
- DB name: `wp_blogs`, user: `root`, password: (empty)

**Broken post content**
- Re-import with `python scripts\import_posts.py`
