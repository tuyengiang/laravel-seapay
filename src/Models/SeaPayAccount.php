<?php

namespace SeaPay\LaravelSeaPay\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $merchant_id
 * @property string      $api_key
 * @property string      $secret_key
 * @property string|null $description
 * @property bool        $is_active
 */
class SeaPayAccount extends Model
{
    protected $table = 'seapay_accounts';

    protected $fillable = [
        'name',
        'merchant_id',
        'api_key',
        'secret_key',
        'description',
        'is_active',
    ];

    protected $hidden = [
        'api_key',
        'secret_key',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
