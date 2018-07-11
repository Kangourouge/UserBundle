# UserBundle

Configuration
-------------

```yaml
# app/config/config.yml

krg_user:
    registration:
        confirmed_target_route: homepage 
    login:
        admin_target_route: easyadmin
        user_target_route: homepage

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

```yaml
# app/config/security.yml

security:
    providers:
        email_provider:
            entity: { class: KRG\UserBundle\Entity\UserInterface, property: emailCanonical }
        invitation_token_provider:
            entity: { class: KRG\UserBundle\Entity\UserInterface, property: invitationToken }
            
    firewalls:
        ...
        guess:
            pattern:    ^/guess
            anonymous:  ~
            logout:     ~
            context:    main
            guard:
                provider: invitation_token_provider
                authenticators: [KRG\UserBundle\Security\Authenticator\TokenAuthenticator]
                    
        main:
            pattern:     ^/
            logout:      true
            anonymous:   true
            switch_user: true
            access_denied_handler: KRG\UserBundle\Security\Firewall\AccessDeniedHandler
            form_login:
                provider: email_provider
                default_target_path: /
                login_path: krg_user_login
                check_path: krg_user_login_check
                csrf_token_generator: security.csrf.token_manager
                success_handler: KRG\UserBundle\Security\Firewall\AuthenticationSuccessHandler

```

## User entity

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

## Admin


EasyAdmin configuration:

```yaml
# app/config/admin.yml

parameters:
    krg_cms.user.class: AppBundle\Entity\User

imports:
    - { resource: '@KRGUserBundle/Resources/config/easyadmin/*.yml' }
```

## Override form type

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

## PrivateData annotation

PrivateData annotation reset property value on softDelete.

Usage:

```php
<?php

use KRG\UserBundle\Annotation\PrivateData;

class User 
{
    /**
     * @PrivateData(replaceWith="John")
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstname;

    /**
     * @PrivateData(domain="kangourouge.com")
     * @ORM\Column(type="string", unique=true)
     */
    protected $email;
}
```
