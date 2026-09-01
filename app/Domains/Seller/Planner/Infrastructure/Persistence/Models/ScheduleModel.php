<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner\Infrastructure\Persistence\Models;

use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleModel extends Model
{
    use SoftDeletes;

    protected $table = 'schedules';

    protected $fillable = [
        'user_id',
        'store_id',
        'title',
        'description',
        'type',
        'priority',
        'color',
        'date',
        'start_time',
        'end_time',
        'is_all_day',
        'is_completed',
        'completed_at',
        'metadata',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_all_day' => 'boolean',
        'is_completed' => 'boolean',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(StoreModel::class);
    }
}
