# Ronaldo Aires — personal site

Personal site of civil engineer Ronaldo Aires (CREA-MT 62629): ebooks with an online
reader, engineering spreadsheets, a buyer library and the engineering services showcase.
Built on the UnSet BIM codebase, without Revit families, textures, plans or the API.

**Stack:** PHP 8.3 · Laravel 13 · Filament 5 · Livewire 4 · Tailwind CSS 4 · Pest 4 · MySQL (SQLite in tests)

## What is in it

| Area | Routes | Notes |
| --- | --- | --- |
| Store | `/ebooks`, `/ebooks/{slug}`, `/planilhas`, `/planilhas/{slug}` | One `products` table; `App\Enums\ProductType` sets the URL prefix. A product opened under the wrong prefix gets a 301. |
| Free sample | `/ebooks/{slug}/amostra/{chapter}` | Chapters flagged `is_preview`. Indexed in the sitemap. |
| Checkout | `/comprar/{slug}` → Mercado Pago Checkout Pro → `/compra/{id}/retorno` | Price 0 = free product, released without payment. Webhook at `/webhooks/mercadopago` (x-signature checked). |
| Library | `/conta/biblioteca`, `/conta/biblioteca/{slug}` | Paid purchases only. Soft-deleted products stay in the buyer's library. |
| Online reader | `/conta/biblioteca/{slug}/ler/{chapter?}` | Remembers the last chapter ("continue reading"); buyer name printed under each chapter. |
| Downloads | `/conta/arquivos/{id}/baixar` | Files live on the private `local` disk, checked against the purchase and logged in `product_downloads`. |
| Account | `/conta`, `/conta/compras`, profile, password, e-mail, avatar, account closing | Same flows as UnSet (verification, Google login, e-mail change, 30-day closing). |
| Legal | `/termos-de-uso`, `/licenca-de-uso`, `/politica-de-privacidade` | Accepted at sign-up and again at checkout when `config('legal.version')` changes. |
| Admin `/admin` | Ebooks e planilhas (files + chapters), Vendas (with "Liberar acesso"), users, leads, services, portfolio | Sales and manual grants are superadmin only. |

## Setup

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan storage:link
npm install && npm run build
```

Fill `MERCADOPAGO_ACCESS_TOKEN`, `MERCADOPAGO_WEBHOOK_SECRET`, `GOOGLE_CLIENT_ID/SECRET` and the
`LEGAL_*` values. Create the first superadmin with tinker (`role = superadmin`).

Upload limits: product files accept up to 50 MB (Livewire temporary upload limit). Check
`upload_max_filesize` and `post_max_size` on the hosting.

## Tests

```bash
php artisan test
```

Tests run on in-memory SQLite. Sign-up and lead tests validate the e-mail domain MX record, so
they need DNS access.

## Next steps

See [docs/roadmap.md](docs/roadmap.md).
