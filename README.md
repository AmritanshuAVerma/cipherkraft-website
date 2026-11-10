# Suverma website

Company website for Suverma, an IT services firm based in Mumbai. It is a single-page static site (HTML, CSS, vanilla JavaScript) with a small PHP handler for the contact form. There is no build step and no dependencies.

Hosted on Hostinger, intended for `https://suverma.com/`.

## Files

| File | Purpose |
| --- | --- |
| `index.html` | The page: hero, services, approach, about, FAQ, contact |
| `styles.css` | All styling (colors and fonts are variables at the top) |
| `script.js` | Mobile menu, scroll animations, terminal animation, form validation |
| `head.js` | One-liner loaded before first paint to enable scroll animations |
| `contact.php` | Contact form handler, sends enquiries by email |
| `404.html` | Not-found page |
| `.htaccess` | Security headers, blocked paths, caching, compression |
| `logo.png`, `favicon.png`, `icon-192.png` | Logo and icons |
| `robots.txt`, `sitemap.xml`, `.well-known/security.txt` | Search engine and security contact files |

## Run locally

Any static file server works:

```bash
python -m http.server 8000
```

Then open `http://localhost:8000/`. The contact form needs PHP, so it only works on the host (or under `php -S localhost:8000`).

## Deploy

The repository is connected to Hostinger through its Git import. Every push to `main` redeploys the site automatically.

## Contact form setup

`contact.php` uses PHP `mail()`. For it to deliver on Hostinger:

1. Create a mailbox on the site's domain in hPanel, for example `noreply@suverma.com`.
2. Set `FROM_ADDRESS` in `contact.php` to that mailbox.
3. Set `TO_ADDRESS` to the inbox that should receive enquiries.

Hostinger rejects mail whose sender is not on a domain hosted in the account.

## Security

- **Content Security Policy:** scripts, styles and images load only from this site, plus Google Fonts. Inline scripts are not allowed, so keep JavaScript in `.js` files.
- **Headers:** HSTS, no framing, no MIME sniffing, restricted browser permissions, cross-origin isolation.
- **Blocked paths:** dotfiles (except `.well-known`) and file types such as `.md`, `.log`, `.sql`, `.zip` return 403, so this README is not served on the live site.
- **Contact form:** same-origin check, honeypot field, minimum fill time, rate limit of 5 messages per IP per hour, control characters stripped from fields.

## Editing notes

- **Caching:** CSS and JS are cached by browsers for 7 days. After changing `styles.css`, `script.js`, `head.js` or an image, bump the `?v=` number on its link in `index.html` and `404.html`.
- **Domain:** `sitemap.xml`, `robots.txt`, `security.txt` and the canonical link in `index.html` all point at `https://suverma.com/`. Update them if the address changes.
- **Colors:** the palette lives in the `:root` block at the top of `styles.css`.
- **New external resources:** anything loaded from another domain must be added to the Content Security Policy in `.htaccess` or the browser will block it.
