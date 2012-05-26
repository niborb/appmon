<?php

namespace Rts\Bundle\AppMonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServerType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('hostname')
            ->add('ip_address')
            ->add('description')
//            ->add('tags')
        ;
    }

    public function getName()
    {
        return 'rts_bundle_appmonbundle_servertype';
    }
}
