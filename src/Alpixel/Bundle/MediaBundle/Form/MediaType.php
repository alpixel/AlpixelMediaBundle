<?php
namespace Alpixel\Bundle\MediaBundle\Form;

use Alpixel\Bundle\MediaBundle\DataTransformer\EntityToIdTransformer;
use Alpixel\Bundle\MediaBundle\Entity\Media;
use Alpixel\Bundle\MediaBundle\EventListener\MediaEvent;
use Alpixel\Bundle\MediaBundle\EventListener\MediaListener;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    public function __construct(RegistryInterface $registry, EventDispatcherInterface $dispatcher)
    {
        $this->registry   = $registry;
        $this->dispatcher = $dispatcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new EntityToIdTransformer(
            $em = $this->registry->getManager(),
            'Alpixel\Bundle\MediaBundle\Entity\Media',
            'secretKey',
            null,
            $options['multiple']
        ));

        $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
    }


    public function onPostSubmit(FormEvent $event)
    {
        $results = $event->getForm()->getData();

        if($results === null)
            return;

        if(!is_array($results))
            $results = array($results);

        foreach($results as $media) {
            $mediaEvent = new MediaEvent($media);
            $this->dispatcher->dispatch(MediaEvent::POST_SUBMIT, $mediaEvent);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'type'          => 'text',
            'hidden'        => true,
            'multiple'      => false,
            'label'         => false,
            'helper'        => 'Ajouter une photo / un fichier',
            'max_nb_file'   => 10,
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (true === $options['hidden']) {
            $view->vars['type'] = 'hidden';
        }

        $view->vars['helper'] = $options['helper'];
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['max_nb_file'] = $options['max_nb_file'];
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
