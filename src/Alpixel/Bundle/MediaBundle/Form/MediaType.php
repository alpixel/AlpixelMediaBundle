<?php
namespace Alpixel\Bundle\MediaBundle\Form;

use Alpixel\Bundle\MediaBundle\DataTransformer\EntityToIdTransformer;
use Alpixel\Bundle\MediaBundle\Entity\Media;
use Alpixel\Bundle\MediaBundle\EventListener\MediaEvent;
use Alpixel\Bundle\MediaBundle\EventListener\MediaListener;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    protected $registry;
    protected $dispatcher;

    public function __construct(RegistryInterface $registry, TraceableEventDispatcher $dispatcher)
    {
        $this->registry   = $registry;
        $this->dispatcher = $dispatcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ('2' == Kernel::MAJOR_VERSION && Kernel::MINOR_VERSION < '1') {
            $em = $this->registry->getEntityManager($options['em']);
        } else {
            $em = $this->registry->getManager($options['em']);
        }

        $builder->addModelTransformer(new EntityToIdTransformer(
            $em,
            'Alpixel\Bundle\MediaBundle\Entity\Media',
            'secretKey',
            $options['query_builder'],
            $options['multiple']
        ));

        $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
    }

    public function onPostSubmit(FormEvent $event)
    {
        $mediaEvent = new MediaEvent($event);
        $this->dispatcher->dispatch(MediaEvent::POST_SUBMIT, $mediaEvent);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'em'            => null,
            'property'      => null,
            'query_builder' => null,
            'hidden'        => true,
            'multiple'      => false,
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (true === $options['hidden']) {
            $view->vars['type'] = 'hidden';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view
            ->vars['multipart'] = true
        ;
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'media';
    }
}
