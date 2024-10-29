<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;

class ReportKardexItemLotCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key)  {

            $status = '';

            if($row->has_sale){
                $status = 'VENDIDO';
            }else{
                $status = 'DISPONIBLE';
            }

            if($row->state == 'Inactivo'){
                $status = 'NO DISPONIBLE';
            }

            return [
                'id' => $row->id,
                'series' => $row->series,
                'name_item' => $row->item->description,
                'und_item' => $row->item->unit_type_id,
                'code_item' => $row->item->internal_id,
                'status' => $status,
                'date' => $row->date
            ];
        });
    }




}
