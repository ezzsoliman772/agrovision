<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = [
        'user_id', 'due_date', 'client_name', 'client_contact', 'amount', 'status'
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }

}
