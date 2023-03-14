<?php

namespace Zobova\GoogleMaps;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class GoogleMaps extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'google-maps';

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        $this->attribute = $attribute ?? str_replace(' ', '_', Str::lower($name));

        parent::__construct($name, $this->attribute);

        $this->apiKey(config('nova-google-maps.google_maps_api_key'))
            ->zoom(config('nova-google-maps.default_zoom'));
    }

    public function apiKey($apiKey)
    {
        return $this->withMeta(['api_key' => $apiKey]);
    }

    public function latitude($latitude)
    {
        return $this->withMeta(['latitude' => $latitude]);
    }

    public function longitude($longitude)
    {
        return $this->withMeta(['longitude' => $longitude]);
    }

    public function zoom($zoom)
    {
        return $this->withMeta(['zoom' => $zoom]);
    }

    public function hideLatitude()
    {
        return $this->withMeta(['hide_latitude' => true]);
    }

    public function hideLongitude()
    {
        return $this->withMeta(['hide_longitude' => true]);
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if($request->input($attribute)) {
            foreach ($request->input($attribute) as $attr => $data) {
                if ($data != 'null') {
                    $model->setAttribute($attr, $data);
                }
            }
        }
    }

    public function resolve($resource, $attribute = null)
    {
        if ($resource->getAttribute('latitude')) {
            $this->latitude(floatval($resource->getAttribute('latitude')));
        }
        if ($resource->getAttribute('longitude')) {
            $this->longitude(floatval($resource->getAttribute('longitude')));
        }
    }
}
