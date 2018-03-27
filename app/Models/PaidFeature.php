<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

class PaidFeature extends Model
{
    protected $table = 'paid_feature';
    
    protected $fillable =
        [
            'user_id',
            'feature_id',
            'payment_type',
            'scratch_card',
            'txn_id',
            'total_resource',
            'remain_resoure',
            'active_date',
            'purchase_date',
            'expire_date',
            'payment_status',
            'payment_status_text',
        ];
}
