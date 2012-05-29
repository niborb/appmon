<?php

namespace Rts\Bundle\AppMonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class AppType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('api_url')

            ->add('api_regex')
            ->add('name')
            ->add('category')
//            ->add('server')l
//            ->add('meta_data_json')
            ->add('version')
//            ->add('http_status')
            ->add('home_url');
    }

    public function getName()
    {
        return 'rts_bundle_appmonbundle_apptype';
    }
}
