<?php

use Filament\Tables\Columns\ImageColumn;
use TinusG\FilamentHoverImageColumn\HoverImageColumn;

it('extends ImageColumn', function () {
    $column = HoverImageColumn::make('image');

    expect($column)->toBeInstanceOf(ImageColumn::class);
});

it('has preview enabled by default', function () {
    $column = HoverImageColumn::make('image');

    expect($column->isPreviewEnabled())->toBeTrue();
});

it('can disable preview', function () {
    $column = HoverImageColumn::make('image');
    $column->preview(false);

    expect($column->isPreviewEnabled())->toBeFalse();
});

it('has default preview size of 320px', function () {
    $column = HoverImageColumn::make('image');

    expect($column->getPreviewMaxWidth())->toBe('320px');
    expect($column->getPreviewMaxHeight())->toBe('320px');
});

it('can set uniform preview size', function () {
    $column = HoverImageColumn::make('image')->previewSize(400);

    expect($column->getPreviewMaxWidth())->toBe('400px');
    expect($column->getPreviewMaxHeight())->toBe('400px');
});

it('can set separate width and height', function () {
    $column = HoverImageColumn::make('image')->previewSize(500, 300);

    expect($column->getPreviewMaxWidth())->toBe('500px');
    expect($column->getPreviewMaxHeight())->toBe('300px');
});

it('accepts string preview size', function () {
    $column = HoverImageColumn::make('image')->previewSize('20rem');

    expect($column->getPreviewMaxWidth())->toBe('20rem');
    expect($column->getPreviewMaxHeight())->toBe('20rem');
});

it('inherits ImageColumn circular method', function () {
    $column = HoverImageColumn::make('image')->circular();

    expect($column->isCircular())->toBeTrue();
});

it('inherits ImageColumn square method', function () {
    $column = HoverImageColumn::make('image')->square();

    expect($column->isSquare())->toBeTrue();
});

it('returns base html when preview disabled', function () {
    $enabled = HoverImageColumn::make('image');
    $disabled = HoverImageColumn::make('image')->preview(false);

    // Both should produce HTML without errors when state is blank
    $enabledHtml = $enabled->toEmbeddedHtml();
    $disabledHtml = $disabled->toEmbeddedHtml();

    // Disabled should not contain hover markup
    expect($disabledHtml)->not->toContain('x-teleport');
    expect($disabledHtml)->toContain('fi-ta-image');
});

it('includes hover markup when default image url is set', function () {
    $column = HoverImageColumn::make('image')
        ->defaultImageUrl('https://example.com/photo.jpg');

    $html = $column->toEmbeddedHtml();

    expect($html)
        ->toContain('x-teleport="body"')
        ->toContain('x-show="show"')
        ->toContain('@mouseenter')
        ->toContain('@mouseleave')
        ->toContain('https://example.com/photo.jpg')
        ->toContain('max-width: 320px')
        ->toContain('max-height: 320px');
});

it('uses custom preview size in html', function () {
    $column = HoverImageColumn::make('image')
        ->defaultImageUrl('https://example.com/photo.jpg')
        ->previewSize(500, 400);

    $html = $column->toEmbeddedHtml();

    expect($html)
        ->toContain('max-width: 500px')
        ->toContain('max-height: 400px');
});

it('skips hover markup when state is blank', function () {
    $column = HoverImageColumn::make('image');

    $html = $column->toEmbeddedHtml();

    expect($html)->not->toContain('x-teleport');
});

it('supports method chaining', function () {
    $column = HoverImageColumn::make('image')
        ->circular()
        ->previewSize(400)
        ->preview(true)
        ->previewUrl('https://example.com/large.jpg');

    expect($column)->toBeInstanceOf(HoverImageColumn::class);
    expect($column->isCircular())->toBeTrue();
    expect($column->getPreviewMaxWidth())->toBe('400px');
    expect($column->isPreviewEnabled())->toBeTrue();
});
