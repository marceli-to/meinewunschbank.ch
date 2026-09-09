# Meine Wunschbank – Raiffeisenbank Weissenstein

Statamic 6 / Laravel 13 site. Visitors submit a *Herzenswunsch* (a wish) with a
photo; submissions land unpublished in the Control Panel for review.

## Setup

```bash
composer setup          # install, .env, key, publish CP assets, npm install + build
php artisan statamic:make:user --super
herd secure             # https://meinewunschbank.ch.test
```

This is a **flat-file install**. Users live in `/users` (gitignored: they carry
password hashes and this repo is public), roles in `resources/users/roles.yaml`,
and session/cache are file-backed. The one exception is the queue: it runs on
sqlite, holding nothing but the `jobs` and `failed_jobs` tables — so **mail needs
a worker** (`php artisan queue:work`, or `composer dev`, which runs one).

`npm run dev` for the front-end watcher, `npm run cp:dev` for Control Panel assets.

## How it fits together

- **Content model** — `pages` (structured, routable) and `wishes` (submissions,
  no route). Blueprints in `resources/blueprints`, fieldsets reduced to
  `editor` and `seo`.
- **Templates** — `resources/views/layout.antlers.html` composes the page from
  `components/layout/*`. Page content is a replicator; each set maps to a
  partial in `components/blocks/` via `components/blocks.antlers.html`.
- **The form** — a Vue island (`resources/js/components/WishForm.vue`) mounted
  on the `wish_form` block. It posts multipart to `POST /api/wishes`. The
  controller only wires things together: validation lives in
  `SubmitWishRequest`, the work in the `App\Actions\SubmitWish` action, which
  creates an **unpublished** entry in `wishes` and queues the notification to
  `MAIL_NOTIFY`.
- **Mails** are Markdown mailables themed by
  `resources/views/vendor/mail/html/themes/wunschbank.css`
  (`config('mail.markdown.theme')`). The components next to it are published, so
  header and footer are editable; the footer reads the `address` global.
- **Photos** are stored in the private `wishes` asset container
  (`storage/app/private/wishes`, outside the web root). Moderators view them
  through `GET /cp/wishes/{path}/photo`, which requires a CP session.

## Conventions

- Spacing utilities are 1:1 with pixels (`p-16` = 16px) — see
  `resources/css/partials/spacing.css`.
- Type scale tokens live in `resources/css/partials/font-sizes.css`.
- Outside production all mail is redirected to `MAIL_TO`.
- Only the queue touches the database. Password resets, sessions, cache and the
  Stache are all on disk under `storage/`.
- Controllers stay thin — put the work in an action class under `app/Actions`.
- Submissions (`content/collections/wishes/*`) are **not** versioned — they hold
  personal data and this repository is public.
