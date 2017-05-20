<?php

namespace Driade\Fixtures\Test\Models;

class Person extends \Illuminate\Database\Eloquent\Model
{
    public function photos()
    {
        return $this->morphMany('Driade\Fixtures\Test\Models\Photo', 'imageable');
    }
}
