<?php

namespace App\Domains\Catalog\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class StoreModel extends Model
{
    protected $table = 'stores';

    public function detail()
    {
        return $this->hasOne(StoreDetailModel::class,'store_id');
    }
}
