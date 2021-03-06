<?php

namespace App\Http\Resources;

use App\Config;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MessageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
    public function with($request){
        return [
            'status'=>'success',
            'status_code'=>'200',
            'slogan'=>Config::find('2'),
            'miaoguoco'=>Config::find('6'),
            'miaoguoslide'=>json_decode(Config::find('6')->slide),
        ];
    }
}
