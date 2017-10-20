# UserBundle

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

```yaml
# app/config/security.yml

security:
    providers:
        email_provider:
            entity: { class: AppBundle:User, property: emailCanonical }
        invitation_token_provider:
            entity: { class: AppBundle:User, property: invitationToken }
            
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
