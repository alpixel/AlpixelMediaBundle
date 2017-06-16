<?php

namespace Alpixel\Bundle\MediaBundle\Form\Type;

use Alpixel\Bundle\MediaBundle\DataTransformer\EntityToIdTransformer;
use Alpixel\Bundle\MediaBundle\EventListener\MediaEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Benjamin HUBERT <benjamin@alpixel.fr>
 */
class AlpixelDropzoneType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    protected $uploadConfigurations;

    /**
     * AlpixelDropzoneType constructor.
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher,
        $uploadConfigurations
    ) {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->uploadConfigurations = $uploadConfigurations;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new EntityToIdTransformer(
                $this->entityManager,
                'Alpixel\Bundle\MediaBundle\Entity\Media',
                'secretKey',
                null,
                $options['multiple']
            )
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $formConfig = $form->getConfig();
        $uploadConfiguration = $formConfig->getOption("upload_configuration");

        if (!empty($uploadConfiguration)) {
            if (empty($this->uploadConfigurations) ||
                !array_key_exists($uploadConfiguration, $this->uploadConfigurations)
            ) {
                throw new \InvalidArgumentException(
                    sprintf("The %s configuration doesn't exist in MediaBundle", $uploadConfiguration)
                );
            }
        }
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $results = $event->getForm()->getData();

        if ($results === null) {
            return;
        }

        if (!is_array($results)) {
            $results = [$results];
        }

        foreach ($results as $media) {
            $mediaEvent = new MediaEvent($media);
            $this->dispatcher->dispatch(MediaEvent::POST_SUBMIT, $mediaEvent);
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'type'                 => TextType::class,
                'hidden'               => true,
                'upload_configuration' => "",
                'multiple'             => false,
                'label'                => false,
                'helper'               => 'Ajouter une photo / un fichier',
                'max_nb_file'          => 10,
            ]
        );
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (true === $options['hidden']) {
            $view->vars['type'] = 'hidden';
        }

        $view->vars['helper'] = $options['helper'];
        $view->vars['multiple'] = $options['multiple'];
        if (!empty($options['upload_configuration'])) {
            $view->vars['mimetypes'] = $this->uploadConfigurations[$options['upload_configuration']]['allowed_mimetypes'];
            $view->vars['upload_configuration'] = $options['upload_configuration'];
        }

        $view->vars['max_nb_file'] = $options['max_nb_file'];
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['multipart'] = true;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'alpixel_dropzone';
    }
}
