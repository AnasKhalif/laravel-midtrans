<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $guarded = [];

    public function setStatusPending()
    {
        $this->attributes['status'] = 'pending';
        self::save();
    }

    public function setStatusSuccess()
    {
        $this->attributes['status'] = 'success';
        self::save();
    }

    public function setStatusFailed()
    {
        $this->attributes['status'] = 'failed';
        self::save();
    }

    public function setStatusExpired()
    {
        $this->attributes['status'] = 'expired';
        self::save();
    }
}
