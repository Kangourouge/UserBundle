<?php

namespace KRG\UserBundle\Annotation\Listener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\Column;
use KRG\UserBundle\Annotation\PrivateData;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PrivateDataListener
{
    /** @var AnnotationReader */
    private $annotationReader;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    public function __construct()
    {
        try {
            $this->annotationReader = new AnnotationReader();
        } catch (\Exception $exception) {
        }

        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()->getPropertyAccessor();
    }

    public function preSoftDelete(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $entityManager = $args->getEntityManager();

        try {
            $metadata = $entityManager->getMetadataFactory()->getMetadataFor(get_class($object));
            foreach ($metadata->getReflectionClass()->getProperties() as $property) {
                foreach ($this->annotationReader->getPropertyAnnotations($property) as $annotation) {
                    if ($annotation instanceof PrivateData) {
                        $this->propertyAccessor->setValue($object, $property->getName(), $this->getValue($property, $annotation));
                    }
                }
            }

            $entityManager->getUnitOfWork()->computeChangeSet($metadata, $object);
        } catch (\Exception $exception) {
        }
    }

    protected function getValue(\ReflectionProperty $property, $annotation)
    {
        if ($annotation->replaceWith) {
            return $annotation->replaceWith;
        }

        if ($annotation->domain && strstr(strtolower($property->getName()), 'email')) {
            return sprintf('%s@%s', uniqid(), $annotation->domain);
        }

        foreach ($this->annotationReader->getPropertyAnnotations($property) as $annotation) {
            if ($annotation instanceof Column) {
                switch ($annotation->type) {
                    case 'smallint':
                    case 'integer':
                    case 'bigint':
                    case 'decimal':
                    case 'float':
                        return 0;
                    case 'string':
                    case 'text':
                        return '';
                    case 'boolean':
                        return false;
                    case 'date':
                    case 'datetime':
                    case 'time':
                        return new \DateTime();
                    case 'array':
                    case 'json_array':
                        return [];
                }
            }
        }

        return null;
    }
}
