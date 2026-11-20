# Progress

Running notes on where the site stands. Newest first; how things work lives in
`README.md`.

## Done

- **2026-11-20 — Timeline from the final doc.** The client had sent the wrong
  doc. The 17 milestones now follow `RBW_Geschichte_Website_19112026_final.docx`;
  the photos it adds were taken from the doc itself (some are small, e.g. the
  1907 note at 191px, turned upright). The doc's captions, credits included,
  are the alt texts, not shown on the page. An «uncropped» toggle fits graphics
  and the 1994 photo whole into the square; photos zoom on hover. The milestones heading is back to
  «Meilensteine aus 125 Jahren»; the merger tree carries the «Seit 1902…» title
  and lead, both editable on the block.
- **2026-11-19 — History timeline.** The homepage `milestones` block carries
  the 13 entries from the client's `RBW_Geschichte_Website.docx`, with the
  doc's heading and intro. Eight of Sara's photos are matched by year and sit in
  `public/assets/geschichte/` (alt texts in `.meta/`). Slides without a photo
  show an empty linen square, text below as on every other slide.
- **2026-11-12/13 — Homepage animation.** Transparent 125 animation on a
  crimson band drawn by CSS, bled to the window edges.
- **2026-10-20 — Client text changes.** Jubiläumsgala dropped (won't be
  communicated); photo note moved from the form into step 1.
- **2026-10-19 — Indexing.** `robots.txt` per environment, `noindex` outside
  production.
- **2026-10-09 — Wish form and mail.** Confirmation mail to visitors,
  Turnstile, queue drained from cron, `mail:test`, mail styling.
- **Earlier** — site build, fonts, logo, scroll reveals, menu, wish form.

## Open

### Waiting on the client

- **Timeline images**: 2023 drone shot of Langendorf (from José), a static
  «meine Wunschbank» logo for 2027, and larger originals for the small doc
  images (1900, 1907, 1986, 2000 Museumspass, 2008).
- **Global notification address** for `MAIL_NOTIFY`.

### Dummy content still on the homepage

- Testimonials (Marie Muster, Thomas Beispiel, Anna Vorlage) and their images.
- The call-to-action image (`dummy/dummy-testimonial-2.jpg`).
- The timeline's button «Mehr erfahren» still links to raiffeisen.ch.

### Staging

- Upload `public/assets/geschichte/` (with `.meta/`, which holds the alt texts; new and renamed files and new
  alt texts on 2026-11-20), e.g.
  `rsync -avz public/assets/geschichte/ <host>:www/meinewunschbank.ch/public/assets/geschichte/`,
  then
  `php please stache:refresh`. If an earlier copy of the Solothurn photo went
  up (it was a PNG named `.jpg`), also `php please glide:clear` and
  `php artisan cache:clear`.
- Confirm where staging lives: on 2026-11-20 its domain resolved to Hostpoint
  (`sl40.web.hostpoint.ch`), not the cyon server in the notes.
- Restore `MAIL_TO` once the agency's mail test is over, and set
  `MAIL_NOTIFY`.

### Go-live

- Verify `meinewunschbank.ch` in Resend.
- Turnstile keys and `TURNSTILE_HOSTNAMES=meinewunschbank.ch`.
- Cron with PHP ≥ 8.4.1, and `php artisan migrate --force`.
- See `README.md` → Going live.

### Local

- Herd served the site with PHP 8.3 and returned a 500 error; the project needs
  PHP 8.4 or newer. It worked again later in the session.
