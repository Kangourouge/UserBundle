<?php

namespace KRG\UserBundle\Validator\Constraint;

use Doctrine\ORM\EntityManagerInterface;
use KRG\UserBundle\Entity\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class EmailUniqueValidator extends ConstraintValidator
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (false === $this->checkUnique($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }

    public function checkUnique(string $email)
    {
        $user = $this->entityManager->getRepository(UserInterface::class)->findOneBy([
            'email' => $email
        ]);

        return null === $user;
    }
}
