<?php

namespace Zobova\PhoneInput;

use Illuminate\Support\Arr;
use Laravel\Nova\Fields\Field;

class PhoneInput extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'phone-input';

    public function withCustomFormats(string|array ...$customFormats): self
    {
        return $this->withMeta([
            'customFormats' => Arr::flatten($customFormats),
        ]);
    }

    public function onlyCountries(string|array ...$countries): self
    {
        return $this->withMeta([
            'onlyCountries' => Arr::flatten($countries),
        ]);
    }

    public function onlyCustomFormats(): self
    {
        return $this->withMeta([
            'onlyCustomFormats' => true,
        ]);
    }
}
