<?php

namespace Wotz\FilamentAdminBar\Tabs;

use Illuminate\View\View;

abstract class Tab
{
    public string $name;

    abstract public function render(): View;

    public function name(): string
    {
        return $this->name;
    }

    public function key(): string
    {
        return md5(get_class($this));
    }

    public function canSee(): bool
    {
        return true;
    }
}
