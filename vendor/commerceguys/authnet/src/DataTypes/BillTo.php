<?php

namespace CommerceGuys\AuthNet\DataTypes;

class BillTo extends BaseDataType
{

    protected $propertyMap = [
        'firstName',
        'lastName',
        'company',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'phoneNumber',
        'faxNumber',
    ];
}
