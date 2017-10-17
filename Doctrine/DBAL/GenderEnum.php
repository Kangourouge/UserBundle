<?php

namespace KRG\UserBundle\Doctrine\DBAL;

class GenderEnum extends Enum
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
