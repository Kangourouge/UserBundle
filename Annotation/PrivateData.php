<?php

namespace KRG\UserBundle\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class PrivateData
{
    /** @var string */
    public $replaceWith;

    /*** @var string */
    public $domain;
}
