<?php

namespace App\Http\Resources\Receivers;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReceiverCollection extends ResourceCollection
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
            unset($value['project']);
            return $value;
        });
        return $result;
    }
}
