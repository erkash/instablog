<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RegistrationType extends AbstractType
{
    const LABELS = ['POST' => 'Register', 'PUT' => 'Update', 'DELETE' => 'Delete'];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('imageFile', VichImageType::class, [
            'label'         => 'Avatar',
            'download_link' => false,
            'allow_delete'  => true,
            'required'      => false,
        ])
            ->add($options['method'], SubmitType::class, [
                'label' => self::LABELS[$options['method']],
                'attr' => [
                    'class' => 'btn btn-primary btn-md'
                ]]);
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }
}
