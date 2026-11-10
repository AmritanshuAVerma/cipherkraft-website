# SUVERMA GROUP website

Website for SUVERMA GROUP, a Mumbai-based group of companies:

- **Altevance Global** — software (launching soon)
- **Dost Initiative** — counselling and guidance
- **Lux Social** — community events and meetups

Static site (HTML, CSS, vanilla JavaScript) with a small PHP handler for the contact form. There is no build step and no dependencies.

Hosted on Hostinger, intended for `https://suverma.com/`.

## Files

| File | Purpose |
| --- | --- |
| `index.html` | Group home: intro, the three companies, about, contact form |
| `altevance.html` | Altevance Global: services, approach, FAQ |
| `dost.html` | Dost Initiative: what it helps with, how it works |
| `lux.html` | Lux Social: what it hosts, how to join |
| `styles.css` | All styling (colors and fonts are variables at the top) |
| `script.js` | Mobile menu, scroll animations, terminal animation, form validation |
| `head.js` | One-liner loaded before first paint to enable scroll animations |
| `contact.php` | Contact form handler, sends enquiries by email |
| `404.html` | Not-found page |
| `.htaccess` | Security headers, blocked paths, caching, compression |
| `logo.png`, `favicon.png`, `icon-192.png` | SUVERMA GROUP logo and icons |
| `logo-altevance.png`, `logo-dost.svg`, `logo-lux.svg` | Company logos. The two SVG logos are also pasted inline into the pages so their text uses the site font; edit both places |
| `robots.txt`, `sitemap.xml`, `.well-known/security.txt` | Search engine and security contact files |

The header and footer are repeated in each page. A change to navigation must be made in all four HTML files.

Company pages link to the form as `./?company=altevance#contact` (or `dost`, `lux`), which preselects the company in the dropdown.

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

- **Caching:** CSS and JS are cached by browsers for 7 days. After changing `styles.css`, `script.js`, `head.js` or an image, bump the `?v=` number on its link in every HTML page.
- **Domain:** `sitemap.xml`, `robots.txt`, `security.txt` and the canonical link in each HTML page all point at `https://suverma.com/`. Update them if the address changes.
- **Colors:** the palette lives in the `:root` block at the top of `styles.css`.
- **New external resources:** anything loaded from another domain must be added to the Content Security Policy in `.htaccess` or the browser will block it.
