<?php
namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use App\Support\AccessorialCategories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accessorial extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Accessorial $accessorial) {
            if (!$accessorial->category) {
                $accessorial->category = AccessorialCategories::forName($accessorial->name);
            }
        });
    }

    public function orderStops()
    {
        return $this->belongsToMany(OrderStop::class, 'order_stop_accessorials');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_accessorial');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function categoryLabel(): string
    {
        return AccessorialCategories::label($this->category);
    }
}
