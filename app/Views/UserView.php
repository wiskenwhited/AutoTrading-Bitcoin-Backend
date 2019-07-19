<?php

namespace App\Views;

class UserView extends AbstractView
{
    protected $fields = [
        'name',
        'email',
        'country',
        'city',
        'phone',
        'currency'
    ];
}