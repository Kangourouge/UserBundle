# UserBundle

composer require stof/doctrine-extensions-bundle

Configuration
-------------

```yaml
# app/config/config.yml
krg_user:
    registration:
        confirmed_target_route: homepage 

doctrine:
    dbal:
        types:
            gender_enum: KRG\UserBundle\Doctrine\DBAL\GenderEnum
    resolve_target_entities:
        KRG\UserBundle\Entity\UserInterface: AppBundle\Entity\User
```

```yaml
# app/config/routing.yml
krg_user:
    resource: "@KRGUserBundle/Controller/"
    type:     annotation
    prefix:   /
```

Entity
------

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class User extends \KRG\UserBundle\Entity\User
{
}
```

Override
--------

```php
<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class RegistrationType extends \KRG\UserBundle\Form\Type\RegistrationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ...
    }
}
```

```yaml
services:
    AppBundle\Form\Type\RegistrationType:
        decorates: KRG\UserBundle\Form\Type\RegistrationType
```
