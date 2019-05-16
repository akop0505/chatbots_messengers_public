<?php

namespace App\Http\Resources\Templates;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TemplateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $collection  = collect(parent::toArray($this));

        $result = $collection->map(function($value,$key){
            $value['Language'] = $value['language'];
            $value['Event'] = $value['event'];
            $value['Project'] = $value['project'];
            $value['Receiver'] = $value['receiver'];
            $value['Menu'] = $value['menu'];
            $value['Menu']['MenuItems'] = $value['menu']['menu_items'];
            unset($value['language']);
            unset($value['event']);
            unset($value['project']);
            unset($value['receiver']);
            unset($value['menu']);
            unset($value['Menu']['menu_items']);
            return $value;
        });
        return $result;
    }
}
