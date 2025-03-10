<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'cnpj'       => $this->cnpj,
            'address'    => $this->address,
            'phone'      => $this->phone,
            'status'     => $this->status, // Verifique se precisa do enum aqui
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null
        ];
    }
}
