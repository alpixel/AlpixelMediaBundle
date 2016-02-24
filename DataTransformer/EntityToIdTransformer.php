<?php

namespace Alpixel\Bundle\MediaBundle\DataTransformer;

use Alpixel\Bundle\MediaBundle\Entity\Media;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Data transformation class.
 *
 * @author Gregwar <g.passault@gmail.com>
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    protected $em;
    private $class;
    private $multiple;

    public function __construct(EntityManager $em, $class, $property, $queryBuilder, $multiple)
    {
        if (!(null === $queryBuilder || $queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder or \Closure');
        }

        if (null === $class) {
            throw new UnexpectedTypeException($class, 'string');
        }

        $this->em = $em;
        $this->class = $class;
        $this->multiple = $multiple;

    }

    public function transform($data)
    {
        if ($data instanceof Collection) {
            return $this->reverseTransform($data);
        }

        if (null === $data) {
            return;
        }

        if (!$this->multiple) {
            return $this->transformSingleEntity($data);
        }

        $return = [];
        $data = explode('#&#', $data);

        foreach ($data as $element) {
            $return[] = $this->transformSingleEntity($element);
        }

        return $return;
    }

    protected function transformSingleEntity($data)
    {
        if ($data instanceof Media) {
            return $data->getSecretKey();
        } else {
            return $this->em->getRepository($this->class)->findOneBySecretKey($data);
        }
    }

    public function reverseTransform($data)
    {
        if (!$data) {
            return;
        }

        if (!($data instanceof Collection)) {
            return $this->transform($data);
        }

        $return = [];

        foreach ($data as $element) {
            $return[] = $this->reverseTransformSingleEntity($element);
        }

        return implode('#&#', $return);
    }

    protected function reverseTransformSingleEntity($data)
    {
        if (!($data instanceof Media)) {
            throw new TransformationFailedException('Can not find entity');
        }

        return $data->getSecretKey();
    }
}
