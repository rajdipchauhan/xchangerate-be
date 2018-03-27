<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

class FeatureList extends Model
{
    protected $table = 'feature_list';
    
    protected $fillable =
        [
            'name',
            'price',
            'feature_type',
            'sort_name',
            'duration',
            'total_resource',
            'description',
        ];
}
