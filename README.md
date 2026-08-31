# 3D Modeling E-Commerce Platform

A full-featured e-commerce platform for home fixtures & fittings (bathroom faucets, vanities, sinks, hardware, etc.), inspired by [nameeks.com](https://www.nameeks.com), built with **Laravel** (backend/API/admin) and **Blade** (storefront templating).

The platform goes beyond a standard catalog by letting every product be viewed as an **interactive 3D model** (with mobile AR support), by letting merchandisers build **cross-category collections** sold as a single purchasable product, and by giving every category its **own dynamic spec/description schema** — exactly like the Remer faucet example on nameeks.com (Overview, bullet features, "More Features" key/value grid, Certifications, Info & Guides PDFs, Specifications table, breadcrumbs, SKU/MSRP).

> **Status:** this repository is a working implementation of the design below — migrations, models, services, storefront, admin dashboard, Paymob integration, and post-payment order tracking are all built and seeded with a working demo catalog (the Remer faucet example, a variant-driven Vanity, and a cross-category Collection). Run the [Quick Start](#quick-start) to see it live.

---

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate

# SQLite is the zero-config default (database/database.sqlite is created automatically
# by the migrate command below). To use MySQL instead, set DB_CONNECTION=mysql and the
# related DB_* vars in .env first.
touch database/database.sqlite
php artisan migrate --seed   # seeds the Remer faucet, a variant-driven Vanity, a Collection, coupons, offers, shipping & stores

npm run build                # or `npm run dev` while developing
php artisan serve
```

Then visit:
- **Storefront:** `http://localhost:8000` — browse `Bathroom Fixtures → Bathroom Faucets` for the Remer faucet PDP (dynamic spec sections, variant picker), or `→ Bathroom Vanities` for the Handle Finish × Vanity Finish × Size variant matrix, or `/collections/modena-matte-black-bath-suite` for the cross-category bundle.
- **Admin dashboard:** `http://localhost:8000/admin` — log in with `admin@example.com` / `password` (seeded by `DemoSeeder`) to manage categories/schema, products, 3D models, variants, stock, coupons, offers, and orders.
- **Order tracking:** `/track-order` — look up any order by number + email; the timeline updates live (via Reverb) or by polling if Reverb isn't running.

Real-time features (`php artisan reverb:start`) and the queue worker (`php artisan queue:work`) are optional for browsing the catalog, but required for live stock badges and live order-status updates — see [Installation & Setup](#installation--setup) for the full local dev stack. Paymob checkout requires real `PAYMOB_*` credentials in `.env`; without them, `/checkout` will fail at the payment step (everything up to and including order/stock creation still works).

---

## Table of Contents

1. [Feature Overview](#feature-overview)
2. [Tech Stack](#tech-stack)
3. [Architecture](#architecture)
4. [Domain Model & Database Schema](#domain-model--database-schema)
5. [Key Modules In Depth](#key-modules-in-depth)
   - [Dynamic Category Attributes & Product Detail Page](#1-dynamic-category-attributes--product-detail-page)
   - [Product Variants (SKU/Price per Option)](#2-product-variants-skuprice-per-option)
   - [Cross-Category Collections (Bundles)](#3-cross-category-collections-bundles)
   - [3D Model Viewer & AR](#4-3d-model-viewer--ar)
   - [Stock / Inventory Channels](#5-stock--inventory-channels)
   - [Offers & Coupons](#6-offers--coupons)
   - [Paymob Payment Gateway](#7-paymob-payment-gateway)
   - [Shipping & Store Locator](#8-shipping--store-locator)
   - [Admin Dashboard](#9-admin-dashboard)
6. [Folder Structure](#folder-structure)
7. [Package Dependencies](#package-dependencies)
8. [Installation & Setup](#installation--setup)
9. [Environment Variables](#environment-variables)
10. [Route Map](#route-map)
11. [Queues, Jobs & Events](#queues-jobs--events)
12. [Testing](#testing)
13. [Roadmap](#roadmap)
14. [License](#license)

---

## Feature Overview

| Area | Description |
|---|---|
| **Catalog** | Categories → Subcategories → Products → Variants. Unlimited nesting, category-level attribute schema. |
| **3D Models** | Upload `.glb`/`.gltf`/`.usdz` per product/variant from the admin dashboard; rendered on the storefront with `<model-viewer>` (rotate/zoom/AR "View in your room"). |
| **Dynamic Specs** | Every category defines its own attribute groups (Overview bullets, "More Features", Certifications, Specifications, Info & Guides PDFs) — no code changes needed to add a new category type. |
| **Variants** | A product (e.g. a Vanity) can vary by *Handle Finish*, *Vanity Finish*, *Size*, etc. Each combination is its own SKU with its own price, stock, and images. |
| **Collections** | Merchandisers assemble a "Collection" (e.g. a full bathroom suite) composed of products pulled from *different* categories (vanity + faucet + mirror) and sell it as one purchasable listing, with optional per-slot variant selection. |
| **Stock Channels** | Multi-channel inventory (Online Store / Wholesale / POS / Marketplace) per SKU, plus a real-time broadcast channel so storefront stock badges update live when admin adjusts stock. |
| **Offers & Coupons** | Scheduled flash sales/offers (auto-applied) and coupon codes (percentage/fixed, min order, usage limits, category/product/collection scoping, per-customer limits). |
| **Payments** | [Paymob](https://paymob.com) integration (Card, wallet, Kiosk) with HMAC-verified webhooks. |
| **Shipping** | Shipping zones & rates, multiple carriers, order tracking, "Find a Store" locator page (like nameeks' *Where to Buy*). |
| **Admin Dashboard** | Full back office: catalog, variants, 3D model uploads, stock, orders, coupons/offers, payments, shipping, CMS pages, media library. |
| **Storefront (Blade)** | Category landing pages, faceted search/filtering, PDP with 3D viewer + spec tabs, cart, checkout, order tracking, wishlists, reviews. |

---

## Tech Stack

**Backend**
- PHP 8.3 + Laravel 11
- MySQL 8 (primary datastore)
- Redis (cache, queues, session)
- Laravel Reverb (WebSockets — self-hosted, Pusher-protocol compatible) for real-time stock/order broadcasting
- Laravel Sanctum (SPA/API auth for the admin dashboard's AJAX calls and any future mobile app)
- Spatie packages: `laravel-permission` (roles/abilities), `laravel-medialibrary` (images, PDFs, 3D model files), `laravel-sluggable`, `laravel-activitylog`
- Laravel Scout + Meilisearch (faceted product search)
- `barryvdh/laravel-dompdf` (invoices)

**Frontend (Storefront)**
- Blade templates + Laravel Vite
- Tailwind CSS
- Alpine.js (variant selector, PDP tabs, cart drawer, filters)
- Google `<model-viewer>` web component (Three.js under the hood) for 3D/AR rendering
- Laravel Echo + Reverb client for live stock badges

**Admin Dashboard**
- Blade + Alpine.js/Tailwind custom-built (or Filament 3 as an accelerator — see [Roadmap](#roadmap)) with a dedicated **3D Model Manager** screen (drag-drop `.glb` upload, live preview via `<model-viewer>`, auto-generated poster/thumbnail).

**Payments / Infra**
- Paymob (Card / Wallet / Kiosk) via REST API
- Horizon (queue monitoring), Telescope (dev debugging)
- Docker / Laravel Sail for local dev

---

## Architecture

```
┌─────────────────────────────┐        ┌──────────────────────────────┐
│         Storefront           │        │         Admin Dashboard        │
│   Blade + Alpine + Vite       │        │   Blade + Alpine (SPA-ish)     │
│   /  /category/*  /product/*  │        │   /admin/*                     │
│   /cart /checkout /account     │        │   catalog, variants, 3D models,│
└───────────────┬───────────────┘        │   stock, orders, coupons, CMS  │
                │                        └───────────────┬────────────────┘
                │        HTTP / Livewire-style forms       │
                ▼                                          ▼
┌───────────────────────────────────────────────────────────────────────┐
│                          Laravel Application Core                      │
│  Http/Controllers  ·  Actions  ·  Services  ·  Policies  ·  Events      │
│  Modules: Catalog, Variants, Collections, Inventory, Pricing/Coupons,   │
│           Orders, Payments (Paymob), Shipping, Media/3DModels, CMS      │
└───────────────┬───────────────────────────────┬────────────────────────┘
                │                               │
        ┌───────▼────────┐              ┌───────▼────────┐
        │   MySQL (DB)    │              │  Redis (cache,  │
        │                 │              │  queue, session)│
        └────────────────┘              └───────┬────────┘
                                                 │
                                    ┌────────────▼─────────────┐
                                    │  Laravel Reverb (WS)      │
                                    │  channels: stock.{sku}    │
                                    │  private-order.{id}       │
                                    └───────────────────────────┘

External services: Paymob API (payments) · Meilisearch (search) · S3/Spaces (media & 3D model storage)
```

---

## Domain Model & Database Schema

> Simplified — types abbreviated, FKs implied by `_id` suffix.

### Catalog

**`categories`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| parent_id | bigint, nullable | self-referencing, unlimited depth |
| name, slug | string | |
| description | text, nullable | shown on category landing page |
| image, banner | string, nullable | media library instead in practice |
| meta_title, meta_description | string | SEO |
| is_active, sort_order | | |

**`category_attribute_groups`** — defines the *sections* a category's PDP shows (mirrors the nameeks layout: Overview, More Features, Certifications, Specifications, Info & Guides).
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| category_id | bigint | |
| key | string | `overview`, `more_features`, `certifications`, `specifications`, `guides` |
| label | string | display label |
| type | enum | `richtext`, `bullet_list`, `key_value`, `file_list`, `badge_list` |
| sort_order | int | |

**`category_attributes`** — the individual fields inside a group, defined per category, so adding "Faucet Flow Rate" to *Bathroom Faucets* never touches code.
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| category_attribute_group_id | bigint | |
| key, label | string | e.g. `finish`, `handle_style`, `flow_rate` |
| input_type | enum | `text`, `select`, `number`, `boolean`, `file` |
| options | json, nullable | for `select` |
| is_variant_option | boolean | **true** if this attribute also drives product **variants** (see below) — e.g. `finish`, `size` |
| is_filterable | boolean | surfaces in category facet filters |
| sort_order | int | |

### Products & Variants

**`products`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| category_id | bigint | primary/owning category |
| brand_id | bigint, nullable | e.g. "Remer" |
| name, slug | string | |
| base_sku | string | family SKU, e.g. `L11USNL` |
| msrp | decimal(10,2) | list/MSRP price shown struck-through |
| collection_line | string, nullable | e.g. "Class Line" |
| short_description | text | |
| is_active, is_featured | boolean | |
| meta_title, meta_description | string | SEO |

**`product_attribute_values`** — EAV table filling in the category's dynamic schema per product (Overview bullets, More Features grid, Certifications badges, Specifications key/value, Info & Guides files).
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| product_id | bigint | |
| category_attribute_id | bigint | |
| value | text / json | text, number, boolean, or media-library file id |

**`product_variants`** — every sellable combination (this is what actually has a price, SKU, stock, and images).
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| product_id | bigint | |
| sku | string, unique | e.g. `L11USNL-NO-BLK-24IN` |
| price | decimal(10,2) | selling price |
| compare_at_price | decimal(10,2), nullable | e.g. MSRP for strike-through |
| weight, dimensions | | shipping calc |
| is_default | boolean | which variant loads first on PDP |
| barcode | string, nullable | |

**`variant_option_values`** — pivot linking a variant to the specific option choice per variant-driving attribute.
| Column | Type | Notes |
|---|---|---|
| variant_id | bigint | |
| category_attribute_id | bigint | must have `is_variant_option = true`, e.g. "Handle Finish" |
| value | string | e.g. `Matte Black`, `Brushed Nickel`, `24"` |

> Example: a **Vanity** product has variant options `Handle Finish` × `Vanity Finish` × `Size`. Each combination row in `product_variants` gets its own SKU/price exactly like the nameeks pattern where different finish/size = different product listing.

**`product_media`** (via Spatie Media Library `media` table, custom collections)
- `gallery` — product/variant images
- `models_3d` — `.glb` / `.gltf` / `.usdz` files + auto poster image
- `documents` — Warranty PDF, Spec Sheet PDF, Installation Guide PDF (maps to "Info & Guides")

### Collections (Cross-Category Bundles)

**`collections`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| name, slug | string | e.g. "Modern Matte Black Bath Suite" |
| description | text | |
| pricing_mode | enum | `fixed` (one bundle price) or `sum_of_selections` (+ optional discount) |
| fixed_price | decimal, nullable | used when `pricing_mode = fixed` |
| discount_percent | decimal, nullable | applied to sum-of-selections mode |

**`collection_slots`** — each "slot" pulls from a category, letting the shopper pick *within* that category (e.g. Slot 1: choose any Bathroom Faucet; Slot 2: choose any Vanity).
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| collection_id | bigint | |
| category_id | bigint | which category this slot offers |
| label | string | e.g. "Choose your Faucet" |
| is_required | boolean | |
| allowed_product_ids | json, nullable | optionally restrict to a curated subset instead of the whole category |

**`collection_slot_defaults`** — the preselected product/variant per slot (what's shown before the customer changes anything, and what "Add Collection to Cart" uses if untouched).

### Inventory / Stock Channels

**`sales_channels`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| name | string | `Online Store`, `Wholesale B2B`, `POS`, `Marketplace (Amazon/Wayfair)` |
| code | string | |

**`stock_items`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| product_variant_id | bigint | |
| sales_channel_id | bigint | per-channel stock ledger |
| quantity_on_hand | int | |
| quantity_reserved | int | held by open, unpaid orders |
| low_stock_threshold | int | |
| backorder_allowed | boolean | |

**`stock_movements`** — audit trail (restock, sale, return, adjustment, channel transfer) — every write to `stock_items.quantity_on_hand` is journaled here.

> **Real-time "stock channel"**: whenever a `StockAdjusted` event fires (admin edits stock, an order is paid, a return is processed), Laravel broadcasts it on a public channel `stock.{sku}`. The storefront PDP subscribes via Laravel Echo and updates the "In Stock / Low Stock / Out of Stock" badge and Add-to-Cart button live, without a page refresh — useful when two customers are viewing the last unit.

### Offers & Coupons

**`coupons`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| code | string, unique | |
| type | enum | `percentage`, `fixed_amount`, `free_shipping` |
| value | decimal | |
| min_order_amount | decimal, nullable | |
| max_discount_amount | decimal, nullable | cap for percentage coupons |
| usage_limit | int, nullable | total redemptions |
| usage_limit_per_customer | int, nullable | |
| starts_at, expires_at | datetime | |
| scope | enum | `all`, `category`, `product`, `collection` |
| is_active | boolean | |

**`coupon_scopes`** — pivot table (coupon_id, scopable_type, scopable_id) when scope ≠ `all`.

**`offers`** — automatic, no-code-required promotions (flash sales, category-wide markdowns, "Buy the Collection & Save 10%").
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| name | string | |
| type | enum | `percentage_off`, `fixed_off`, `bundle_discount` |
| value | decimal | |
| target_type | enum | `product`, `category`, `collection` |
| target_id | bigint | |
| starts_at, ends_at | datetime | |
| badge_label | string, nullable | e.g. "SALE", "20% OFF" ribbon on PDP/PLP |
| priority | int | stacking order when multiple offers could apply |

### Orders / Payments / Shipping

**`carts`**, **`cart_items`** (`cart_items.itemable_type/itemable_id` — polymorphic so a line can be a `ProductVariant` **or** a `Collection` selection set)

**`orders`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| user_id | bigint, nullable | guest checkout supported |
| order_number | string, unique | |
| status | enum | `pending`, `paid`, `processing`, `shipped`, `delivered`, `cancelled`, `refunded` |
| subtotal, discount_total, shipping_total, tax_total, grand_total | decimal | |
| coupon_id | bigint, nullable | |
| shipping_address_id, billing_address_id | bigint | |

**`order_items`** — polymorphic `itemable` same as cart, snapshots price/SKU/attributes at time of purchase, plus `collection_selection` json capturing which product/variant was chosen per collection slot.

**`payments`**
| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| order_id | bigint | |
| gateway | string | `paymob` |
| paymob_order_id | string | Paymob's order id |
| transaction_id | string, nullable | |
| method | enum | `card`, `wallet`, `kiosk` |
| status | enum | `pending`, `success`, `failed`, `refunded` |
| amount | decimal | |
| raw_response | json | full callback payload for auditing |

**`shipping_zones`**, **`shipping_rates`** (per zone: flat rate / weight-based / free-over-threshold), **`store_locations`** (for the "Find a Store" locator: name, address, lat/lng, phone, hours).

---

## Key Modules In Depth

### 1. Dynamic Category Attributes & Product Detail Page

Every category owns its own **attribute schema** (`category_attribute_groups` + `category_attributes`) instead of hard-coded product fields. This is what lets *Bathroom Faucets* show `Flow Rate` / `Number of Handles` / `Faucet Holes`, while *Vanities* show `Vanity Finish` / `Countertop Material` / `Number of Sinks` — with zero code changes, purely admin configuration.

The PDP (`resources/views/storefront/products/show.blade.php`) renders generically:

```blade
<x-storefront.breadcrumbs :trail="$product->breadcrumbTrail()" />
<x-storefront.product.gallery-and-viewer :product="$product" :variant="$selectedVariant" />
<x-storefront.product.buybox :product="$product" :variant="$selectedVariant" />

@foreach ($product->category->attributeGroups as $group)
    <x-storefront.product.spec-section :group="$group" :product="$product" />
@endforeach
```

`spec-section` switches on `$group->type` (`richtext`, `bullet_list`, `key_value`, `file_list`, `badge_list`) to render the Overview paragraph + bullets, the "More Features" 2-column key/value grid, the Certifications badge row, the Specifications key/value table, and the "Info & Guides" PDF link list — matching the nameeks layout section-for-section.

### 2. Product Variants (SKU/Price per Option)

`ProductVariantService::generateFromAttributes($product)` takes the attributes on the product's category flagged `is_variant_option` (e.g. Handle Finish × Vanity Finish × Size) and produces the Cartesian product of `product_variants` rows, each requiring its own SKU + price to be filled in by the merchandiser in the admin — never auto-priced blindly.

The storefront buybox renders one `<select>`/swatch-picker per variant option; Alpine.js resolves the chosen combination to a `product_variants.id` client-side (a JSON map is inlined per PDP), then swaps price, SKU, stock badge, and gallery images without a full page reload. If a specific combination doesn't exist, that option value is disabled in the picker.

### 3. Cross-Category Collections (Bundles)

A `Collection` is itself a purchasable, page-having entity (`/collections/{slug}`) composed of `collection_slots`, each slot scoped to a *different category* (Faucet slot, Vanity slot, Mirror slot, Towel Bar slot…). The shopper picks a specific product/variant per slot via the same variant-picker UI used on a normal PDP, embedded per slot. `CollectionPriceCalculator` either:

- returns the collection's `fixed_price`, or
- sums the chosen variants' prices and applies `discount_percent` ("Buy as a Suite and Save 10%").

Adding it to cart creates **one** `cart_items` row of `itemable_type = Collection`, with a `collection_selection` JSON snapshotting which variant was picked per slot — so it behaves as a single line item in cart/checkout/order history, fulfilling "buy a collection from different categories as 1 product."

### 4. 3D Model Viewer & AR

- Admin dashboard → Product → **3D Model** tab: drag-and-drop `.glb`/`.gltf` (desktop/web) and optional `.usdz` (iOS Quick Look AR), stored via Spatie Media Library in the `models_3d` collection, one per product **or** per variant (a Matte Black finish can have its own textured model vs. Brushed Nickel).
- A `GenerateModelPoster` queued job renders a static thumbnail frame from the `.glb` (headless Puppeteer/`model-viewer` screenshot) for use as a card fallback and Open Graph image.
- Storefront renders:

```blade
<model-viewer
    src="{{ $variant->model3d('glb') }}"
    ios-src="{{ $variant->model3d('usdz') }}"
    poster="{{ $variant->model3dPoster() }}"
    camera-controls
    auto-rotate
    ar
    shadow-intensity="1"
    exposure="1"
    alt="{{ $product->name }}">
</model-viewer>
```

- The gallery/viewer tab toggles between product photos and the interactive model; switching a variant swaps the `src` reactively via Alpine.
- Files are size/type validated server-side and served from S3/Spaces behind a CDN.

### 5. Stock / Inventory Channels

Two related but distinct concepts, both requested:

1. **Multi-channel inventory** — `sales_channels` + `stock_items` let the same SKU carry a different on-hand quantity per channel (Online Store vs. Wholesale vs. POS), so selling out on the website doesn't touch wholesale allocation.
2. **Real-time stock broadcast channel** — every stock write goes through `InventoryService::adjust()`, which updates `stock_items` inside a DB transaction (row-locked to prevent oversell), writes a `stock_movements` audit row, then fires `StockAdjusted` which broadcasts on Reverb:

```php
// app/Events/StockAdjusted.php
public function broadcastOn(): array
{
    return [new Channel('stock.'.$this->variant->sku)];
}
```

```js
// resources/js/stock-badge.js
Echo.channel(`stock.${sku}`).listen('StockAdjusted', (e) => {
    updateStockBadge(e.quantity_available, e.status); // in_stock | low_stock | out_of_stock
});
```

Checkout reserves stock (`quantity_reserved`) the moment an order is placed and releases it on cancellation/expiry via a scheduled job, preventing overselling during Paymob's redirect round-trip.

### 6. Offers & Coupons

- **Coupons** are customer-entered codes validated by `CouponValidator` (active window, usage limits, min order, scope) and applied in `CartTotalsCalculator` before tax/shipping.
- **Offers** are automatic — a scheduled job (`ActivateScheduledOffers`) flips `is_active` in the eligible window; `PriceResolver::priceFor($variant)` always checks for a live offer on the variant/its category/any collection it belongs to, applies the highest-priority discount, and returns both `price` and `compare_at_price` so the storefront can show a struck-through MSRP plus a "20% OFF" ribbon.
- Both coupons and offers are stackable-or-not per configuration (`config('shop.discounts.stackable')`), decided once at checkout time so totals are deterministic.

### 7. Paymob Payment Gateway

`App\Services\Payments\PaymobGateway` implements a common `PaymentGatewayContract` so other gateways can be added later without touching checkout code.

**Flow:**
1. `POST /api/v1/paymob/auth` — exchange `PAYMOB_API_KEY` for a short-lived auth token.
2. `POST /ecommerce/orders` — register the Laravel `order` as a Paymob order (amount in cents, currency, merchant order id = our `order_number`).
3. `POST /acceptance/payment_keys` — request a payment key, passing billing data + the desired `integration_id` (Card / Wallet / Kiosk each have their own Paymob integration id in config).
4. Redirect the customer to Paymob's hosted iframe/unified checkout using the returned payment token.
5. **Webhook** `POST /webhooks/paymob/transaction` — Paymob posts the transaction result; `PaymobWebhookController` recomputes the **HMAC** over the documented field order and compares against the `hmac` query param to reject spoofed callbacks, then marks `payments.status` and `orders.status` accordingly, releases/consumes reserved stock, and fires `OrderPaid` (queues the confirmation email, decrements stock, clears the cart).
6. Customer is redirected back to `/checkout/thank-you/{order}`, which itself re-verifies status server-side rather than trusting the URL query string alone.

```env
PAYMOB_API_KEY=
PAYMOB_HMAC_SECRET=
PAYMOB_INTEGRATION_ID_CARD=
PAYMOB_INTEGRATION_ID_WALLET=
PAYMOB_INTEGRATION_ID_KIOSK=
PAYMOB_IFRAME_ID=
```

### 8. Shipping & Store Locator

- `shipping_zones` map countries/regions to a set of `shipping_rates` (flat, weight-tiered, or free-over-threshold); checkout resolves the applicable zone from the shipping address and lists available rates.
- Order status progression (`processing → shipped → delivered`) optionally carries a `tracking_number` + `carrier`, surfaced on `/account/orders/{order}`.
- `/where-to-buy` reproduces nameeks' store locator: a searchable/filterable map (Google Maps or Leaflet) over `store_locations`, with distance sorting from a customer-entered ZIP.

### 9. Admin Dashboard

`/admin` (role-gated via `spatie/laravel-permission`, roles: Super Admin, Catalog Manager, Order Manager, Support):

- **Catalog**: categories (+ drag-sort, + attribute-group/attribute builder), products, variant matrix editor, media/gallery, **3D Model Manager**.
- **Inventory**: per-channel stock grid, bulk adjust, low-stock report, movement history.
- **Marketing**: coupons, offers/flash sales, collection builder (slot designer with live price preview).
- **Sales**: orders, payments (Paymob transaction detail + refund action), invoices (PDF).
- **CMS**: static pages, homepage banners, navigation menu builder.
- **Settings**: shipping zones/rates, store locations, tax rules, general store settings.

---

## Folder Structure

```
app/
  Actions/
    Catalog/
    Variants/
    Collections/
    Inventory/
    Pricing/
  Broadcasting/
    Channels/StockChannel.php
  Events/
    StockAdjusted.php
    OrderPaid.php
  Http/
    Controllers/
      Storefront/ (Home, Category, Product, Collection, Cart, Checkout, Account, StoreLocator)
      Admin/      (Category, Product, Variant, Model3D, Inventory, Coupon, Offer, Order, Payment, Shipping, Cms)
      Webhooks/PaymobWebhookController.php
    Requests/
  Models/
    Category.php, CategoryAttributeGroup.php, CategoryAttribute.php
    Product.php, ProductAttributeValue.php, ProductVariant.php, VariantOptionValue.php
    Collection.php, CollectionSlot.php
    SalesChannel.php, StockItem.php, StockMovement.php
    Coupon.php, Offer.php
    Cart.php, CartItem.php, Order.php, OrderItem.php, Payment.php
    ShippingZone.php, ShippingRate.php, StoreLocation.php
  Services/
    Payments/PaymobGateway.php, PaymentGatewayContract.php
    Inventory/InventoryService.php
    Pricing/PriceResolver.php, CartTotalsCalculator.php, CollectionPriceCalculator.php
    Media/Model3DManager.php
resources/
  views/
    storefront/ (layouts, home, categories, products, collections, cart, checkout, account, store-locator)
    admin/      (layouts, catalog, variants, models3d, inventory, marketing, sales, cms, settings)
    components/ (product/spec-section, product/gallery-and-viewer, product/buybox, breadcrumbs, ...)
  js/ (echo.js, stock-badge.js, variant-picker.js, model-viewer-loader.js)
  css/
routes/
  web.php        # storefront
  admin.php      # /admin/*
  api.php        # AJAX endpoints (variant resolve, cart, coupon apply)
  webhooks.php   # paymob
  channels.php   # broadcasting auth
database/
  migrations/
  seeders/ (DemoCategorySeeder, DemoProductSeeder mirroring the Remer faucet example)
config/
  paymob.php
  shop.php       # discount stacking rules, stock thresholds, channel list
```

---

## Package Dependencies

**Composer**
```
laravel/framework ^11.0
laravel/sanctum
laravel/reverb
laravel/scout
laravel/horizon
laravel/telescope        # dev only
spatie/laravel-permission
spatie/laravel-medialibrary
spatie/laravel-sluggable
spatie/laravel-activitylog
barryvdh/laravel-dompdf
meilisearch/meilisearch-php
guzzlehttp/guzzle        # Paymob API calls
```

**NPM**
```
vite
tailwindcss
alpinejs
laravel-echo
pusher-js                 # protocol client used by Reverb
@google/model-viewer
```

---

## Installation & Setup

```bash
git clone <repo-url> 3d-modeling-ecommerce
cd 3d-modeling-ecommerce

composer install
cp .env.example .env
php artisan key:generate

npm install

# configure DB, Redis, Reverb, Paymob, Meilisearch in .env (see below)

php artisan migrate --seed     # seeds demo categories/products/variants/collections
php artisan storage:link

php artisan reverb:start &     # websocket server for stock broadcasting
php artisan queue:work &       # posters, emails, offer activation, stock reservation cleanup
npm run dev                    # or `npm run build` for production

php artisan serve
```

---

## Environment Variables

```env
APP_NAME="3D Modeling E-Commerce"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_DATABASE=ecommerce_3d
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=

MEDIA_DISK=s3
AWS_BUCKET=
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=

# Paymob
PAYMOB_API_KEY=
PAYMOB_HMAC_SECRET=
PAYMOB_INTEGRATION_ID_CARD=
PAYMOB_INTEGRATION_ID_WALLET=
PAYMOB_INTEGRATION_ID_KIOSK=
PAYMOB_IFRAME_ID=
PAYMOB_CURRENCY=EGP
```

---

## Route Map

| Method | URI | Purpose |
|---|---|---|
| GET | `/` | Homepage (banners, featured categories/offers) |
| GET | `/category/{slug}` | Category landing page + facet filters |
| GET | `/product/{slug}` | PDP: gallery/3D viewer, buybox, dynamic spec sections |
| GET | `/collections/{slug}` | Collection builder page (per-slot pickers) |
| POST | `/cart/items` | Add product variant or collection selection to cart |
| GET/POST | `/checkout` | Address, shipping method, coupon, Paymob redirect |
| GET | `/checkout/thank-you/{order}` | Order confirmation |
| POST | `/webhooks/paymob/transaction` | Paymob server-to-server callback |
| GET | `/where-to-buy` | Store locator |
| GET | `/account/orders` | Order history & tracking |
| GET/POST | `/admin/...` | Full back office (see [Admin Dashboard](#9-admin-dashboard)) |

---

## Queues, Jobs & Events

| Name | Trigger | Action |
|---|---|---|
| `GenerateModelPoster` (job) | 3D model uploaded | Renders + stores a thumbnail frame |
| `StockAdjusted` (event) | any stock write | Broadcasts on `stock.{sku}`; updates search index facet |
| `ActivateScheduledOffers` (scheduled job, every minute) | offer `starts_at`/`ends_at` window | Flips `offers.is_active` |
| `ReleaseExpiredReservations` (scheduled job) | unpaid order older than X minutes | Releases `quantity_reserved` |
| `OrderPaid` (event) | Paymob webhook confirms success | Decrements stock, sends confirmation email/invoice PDF, clears cart |
| `SyncProductSearchIndex` (job) | product/variant saved | Updates Meilisearch document |

---

## Testing

```bash
php artisan test                 # Pest/PHPUnit feature + unit tests
php artisan test --group=paymob  # webhook signature verification, gateway mocks
php artisan test --group=variants
php artisan test --group=collections
php artisan test --group=inventory
```

Recommended coverage priorities: variant generation/pricing, collection price calculation, coupon/offer stacking rules, stock reservation race conditions (use `DB::transaction` + row locks, test with concurrent requests), and Paymob HMAC verification.

---

## Roadmap

- [ ] Swap/augment the hand-built admin with **Filament 3** for faster CRUD scaffolding, keeping the 3D Model Manager and variant matrix as custom Filament pages.
- [ ] Wishlist sharing + "back in stock" email subscriptions (hooks into `StockAdjusted`).
- [ ] Product reviews & Q&A with photo uploads.
- [ ] Multi-currency / multi-language storefront.
- [ ] Native mobile app via Sanctum-backed API (schema already API-ready).
- [ ] AR "measure in your space" enhancement on top of `<model-viewer>`'s existing Quick Look/Scene Viewer AR.

---

## License

Proprietary — all rights reserved unless a license file is added.
