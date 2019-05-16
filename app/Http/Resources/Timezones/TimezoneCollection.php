<?php

namespace App\Http\Resources\Timezones;

use DateTime;
use DateTimeZone;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TimezoneCollection extends ResourceCollection
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

        return $collection;
    }

    public static function generate($regions = null)
    {
        $regions = $regions ? $regions : array(
            DateTimeZone::ASIA,
            DateTimeZone::EUROPE,
        );

        $timezones = array();
        foreach( $regions as $region )
        {
            $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
        }

        $timezone_offsets = array();
        foreach( $timezones as $timezone )
        {
            $tz = new DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
        }

        // sort timezone by offset
        asort($timezone_offsets);

        $timezone_list = array();
        foreach( $timezone_offsets as $timezone => $offset )
        {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate( 'H:i', abs($offset) );

            $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

            $timezone_list[] = ['id'=> $timezone, 'name' => "(${pretty_offset}) $timezone"];
        }

        return collect( $timezone_list );
    }
}
