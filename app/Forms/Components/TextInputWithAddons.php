<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Concerns\CanBeAutocapitalized;
use Filament\Forms\Components\Concerns\CanBeAutocompleted;
use Filament\Forms\Components\Concerns\CanBeLengthConstrained;
use Filament\Forms\Components\Concerns\HasAffixes;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained as ContractsCanBeLengthConstrained;
use Filament\Forms\Components\Contracts\CanHaveNumericState;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class TextInputWithAddons extends TextInput implements ContractsCanBeLengthConstrained, CanHaveNumericState
{
    protected string $view = 'forms.components.text-input-with-addons';

    protected $prefix = "4444444";

    protected $suffix;

    protected bool | Closure $isNumeric = false;

    public function isNumeric(): bool
    {
        return (bool) $this->evaluate($this->isNumeric);
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix(string | Closure $prefix = null): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setSuffix(string | Closure $suffix = null): static
    {
        $this->suffix = $suffix;

        return $this;
    }
}
