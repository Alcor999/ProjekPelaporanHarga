---
module: pelaporanharga-and-flutter-app
date: 2026-11-15
problem_type: integration_issue
component: api_layer
severity: high
symptoms:
  - "Route::resource('komoditas', ...) generates {komodita} route parameter, leaving Komoditas $komoditas unbound (empty model with null id) in update and destroy"
  - "Test suite mixing Sanctum Bearer requests before web actingAs($admin) retains sanctum as active default guard unless reset"
  - "HargaPublikScreen overflows RenderFlex horizontally at 320px viewport width"
root_cause: wrong_api
resolution_type: code_fix
related_components:
  - frontend
  - database
  - infrastructure
tags:
  - laravel-routing
  - route-model-binding
  - flutter-responsive-layout
  - shared-hosting
---

# Laravel Resource Binding for Indonesian Nouns and Flutter 320px Overflow Guard

## Problem

1. Laravel's `Route::resource('komoditas', KomoditasController::class)` inflects `komoditas` using English pluralization rules into `{komodita}`. Because `KomoditasController::update()` and `KomoditasController::destroy()` type-hint `Komoditas $komoditas`, Laravel fails to match parameter `{komodita}` to `$komoditas` and passes an unpersisted model (`id === null`). Updates and reference checks during deletion silently operate on nothing.
2. On Flutter mobile viewports at `320px` width (or with wide glyphs), `Row` headers and summary cards in `HargaPublikScreen` overflow horizontally when text or badge rows lack `Expanded`, `Wrap`, or `FittedBox(fit: BoxFit.scaleDown)`.

## Solution

1. Explicitly pin the route parameter name for non-English resource nouns in `pelaporanharga/routes/web.php`:

```php
Route::resource('komoditas', KomoditasController::class)
    ->parameters(['komoditas' => 'komoditas'])
    ->except(['show', 'create', 'edit']);
```

2. Verify route bindings after changes via `php artisan route:list --path=admin` and cover both `update` and `destroy` with Feature tests.
3. In Laravel Feature tests that mix API Bearer calls and web session requests inside one test method, pass request headers per call (for example `getJson($uri, ['Authorization' => "Bearer $token"])`) instead of sticky `$this->withHeader()`, and call `app('auth')->forgetGuards()` before `$this->actingAs($admin, 'web')`.
4. In Flutter `Row` layouts targeting `320px` devices (`lib/screens/harga_publik_screen.dart`), wrap label `Text` widgets inside `Expanded`, replace inline badge `Row`s with `Wrap`, and wrap formatted currency figures inside `FittedBox(fit: BoxFit.scaleDown)`.

## Prevention Tip

Always inspect `php artisan route:list` when registering `Route::resource()` on Indonesian or irregular nouns (`komoditas`, `aktivitas`, `fasilitas`), and run Flutter widget tests inside a `320x640` constrained `MediaQuery` so layout overflows fail CI immediately.
