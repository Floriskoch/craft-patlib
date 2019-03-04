<?php

namespace floriskoch\craftpatlib\models;

use craft\base\Model;

class Settings extends Model
{
    public $assets = [];

    public $componentRoot = '_partials';

    public function init()
    {
        parent::init();
    }
}
