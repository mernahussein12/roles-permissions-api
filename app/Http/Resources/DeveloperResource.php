<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeveloperResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'project_name' => $this->project_name,
            'project_type' => $this->project_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'project_leader' => $this->project_leader,
            'support' => $this->support,
            'summary' => $this->summary ? url('storage/' . $this->summary) : null,
            'cost' => $this->cost,
            'profit_margin' => $this->profit_margin,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),


        ];
        }
    }

