<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ChatResource extends JsonResource
{
    // /**
    //  * Transform the resource into an array.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
    //  */
    // public function toArray($request)
    // {
    //     return parent::toArray($request);
    // }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'private' => $this->private,
            'direct_message' => $this->direct_message,
            'created_at' => $this->created_at,
            'participants' => $this->participants->map(function ($participant) {
                return [
                    'user_id' => $participant->id,
                    'name' => $participant->name,
                    'email' => $participant->email,
                    'avatar_url' => $participant->avatar_url,
                ];
            }),
        ];
    }
}
