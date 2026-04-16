# Filament Hover Image Column

A Filament table column that displays a larger image preview card on hover. Drop-in replacement for `ImageColumn` — all existing methods work unchanged.

![Preview](https://raw.githubusercontent.com/tinusg/filament-hover-image-column/main/art/preview.gif)

## Installation

```bash
composer require tinusg/filament-hover-image-column
```

No service provider or configuration needed.

## Usage

Replace `ImageColumn` with `HoverImageColumn`:

```php
use TinusG\FilamentHoverImageColumn\HoverImageColumn;

HoverImageColumn::make('avatar')
    ->circular()
```

Hovering over the thumbnail shows a larger preview in a floating card that follows the cursor.

### Custom preview size

```php
HoverImageColumn::make('photo')
    ->previewSize(400) // both width and height

HoverImageColumn::make('photo')
    ->previewSize(width: 500, height: 300) // separate dimensions
```

### Custom preview URL

Show a higher-resolution image in the preview while keeping a small thumbnail:

```php
HoverImageColumn::make('thumbnail_url')
    ->previewUrl(fn ($record) => $record->full_resolution_url)
```

### Disable preview

```php
HoverImageColumn::make('photo')
    ->preview(false)
```

### Use with computed state

```php
HoverImageColumn::make('media.source_url')
    ->label('Image')
    ->circular()
    ->state(fn ($record) => $record->media->where('is_primary', true)->first()?->display_url)
```

## API

| Method | Description | Default |
|---|---|---|
| `previewSize(width, height?)` | Max dimensions of the preview image | `320px` |
| `previewUrl(string\|Closure)` | Custom URL for the preview (e.g. high-res version) | Same as thumbnail |
| `preview(bool\|Closure)` | Enable or disable the hover preview | `true` |

All `ImageColumn` methods (`circular()`, `square()`, `stacked()`, `disk()`, `visibility()`, etc.) are inherited and work as expected.

## How it works

- Extends Filament's `ImageColumn` and overrides `toEmbeddedHtml()`
- Uses Alpine.js (already loaded by Filament) for hover state and cursor tracking
- Preview is rendered via `x-teleport="body"` to escape table overflow clipping
- Image is lazy-loaded — only fetched when you hover
- Viewport boundary detection prevents the preview from going off-screen
- No additional CSS or JavaScript assets required

## Requirements

- PHP 8.2+
- Filament 5.x

## License

MIT License. See [LICENSE](LICENSE) for details.
