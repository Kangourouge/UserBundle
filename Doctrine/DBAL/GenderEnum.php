<?php

namespace KRG\UserBundle\Doctrine\DBAL;

use KRG\DoctrineExtensionBundle\DBAL\EnumType;

class GenderEnum extends EnumType
{
    const
        MALE = 'male',
        FEMALE = 'female';

    public static $values = [
        self::MALE,
        self::FEMALE
    ];

    public function getName()
    {
        return 'gender_enum';
    }
}
