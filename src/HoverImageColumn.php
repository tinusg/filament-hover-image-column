<?php

namespace TinusG\FilamentHoverImageColumn;

use Closure;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Js;

class HoverImageColumn extends ImageColumn
{
    protected int|string|Closure|null $previewWidth = 320;

    protected int|string|Closure|null $previewHeight = 320;

    protected bool|Closure $previewEnabled = true;

    protected string|Closure|null $previewImageUrl = null;

    public function previewSize(int|string|Closure $width, int|string|Closure|null $height = null): static
    {
        $this->previewWidth = $width;
        $this->previewHeight = $height ?? $width;

        return $this;
    }

    public function previewUrl(string|Closure|null $url): static
    {
        $this->previewImageUrl = $url;

        return $this;
    }

    public function preview(bool|Closure $condition = true): static
    {
        $this->previewEnabled = $condition;

        return $this;
    }

    public function getPreviewMaxWidth(): string
    {
        $width = $this->evaluate($this->previewWidth) ?? 320;

        return is_int($width) ? "{$width}px" : $width;
    }

    public function getPreviewMaxHeight(): string
    {
        $height = $this->evaluate($this->previewHeight) ?? 320;

        return is_int($height) ? "{$height}px" : $height;
    }

    public function isPreviewEnabled(): bool
    {
        return (bool) $this->evaluate($this->previewEnabled);
    }

    protected function resolvePreviewUrl(): ?string
    {
        if ($this->previewImageUrl !== null) {
            $evaluated = $this->evaluate($this->previewImageUrl);
            return is_string($evaluated) ? $evaluated : null;
        }

        $state = $this->getState();

        if ($state instanceof Collection) {
            $state = $state->first();
        }

        if (is_array($state)) {
            $state = Arr::first($state);
        }

        if (blank($state)) {
            return $this->getDefaultImageUrl();
        }

        return $this->getImageUrl($state) ?? $this->getDefaultImageUrl();
    }

    protected function getPreviewBoundarySize(): int
    {
        $width = $this->evaluate($this->previewWidth) ?? 320;

        return is_int($width) ? $width + 40 : 360;
    }

    public function toEmbeddedHtml(): string
    {
        $baseHtml = parent::toEmbeddedHtml();

        if (! $this->isPreviewEnabled()) {
            return $baseHtml;
        }

        $previewUrl = $this->resolvePreviewUrl();

        if (blank($previewUrl)) {
            return $baseHtml;
        }

        $maxWidth = $this->getPreviewMaxWidth();
        $maxHeight = $this->getPreviewMaxHeight();
        $boundarySize = $this->getPreviewBoundarySize();
        $urlJs = Js::from($previewUrl);

        ob_start(); ?>

        <div
            x-data="{
                show: false,
                x: 0,
                y: 0,
                url: <?= $urlJs ?>,
                updatePosition(event) {
                    const offset = 16;
                    const boundary = <?= $boundarySize ?>;
                    let x = event.clientX + offset;
                    let y = event.clientY + offset;
                    if (x + boundary > window.innerWidth) x = event.clientX - boundary - offset;
                    if (y + boundary > window.innerHeight) y = event.clientY - boundary - offset;
                    if (x < 0) x = offset;
                    if (y < 0) y = offset;
                    this.x = x;
                    this.y = y;
                }
            }"
            @mouseenter="updatePosition($event); show = true"
            @mousemove.throttle.50ms="updatePosition($event)"
            @mouseleave="show = false"
        >
            <?= $baseHtml ?>

            <template x-teleport="body">
                <div
                    x-show="show"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    :style="`position: fixed; left: ${x}px; top: ${y}px; z-index: 50;`"
                    class="pointer-events-none"
                    x-cloak
                >
                    <div
                        style="padding: 4px; border-radius: 8px; overflow: hidden;"
                        class="bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <img
                            :src="show ? url : ''"
                            style="max-width: <?= e($maxWidth) ?>; max-height: <?= e($maxHeight) ?>; border-radius: 6px; display: block;"
                            alt=""
                        />
                    </div>
                </div>
            </template>
        </div>

        <?php return ob_get_clean();
    }
}