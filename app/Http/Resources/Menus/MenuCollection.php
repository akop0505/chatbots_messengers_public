<?php

namespace App\Http\Resources\Menus;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MenuCollection extends ResourceCollection
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
            $value['Project'] = $value['project'];
            $value['MenuItems'] = $value['menu_items'];
            unset($value['project']);
            unset($value['menu_items']);
            return $value;
        });
        return $result;
    }
}
