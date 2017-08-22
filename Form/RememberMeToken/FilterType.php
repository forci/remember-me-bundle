<?php

namespace Forci\Bundle\RememberMeBundle\Form\RememberMeToken;

use Forci\Bundle\RememberMeBundle\Filter\RememberMeTokenFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wucdbm\Bundle\QuickUIBundle\Form\Filter\BaseFilterType;
use Wucdbm\Bundle\QuickUIBundle\Form\Filter\DateRangeFilterType;
use Wucdbm\Bundle\QuickUIBundle\Form\Filter\TextFilterType;

class FilterType extends BaseFilterType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('userId', TextFilterType::class, [
                'placeholder' => 'User ID'
            ])
            ->add('area', TextFilterType::class, [
                'placeholder' => 'Firewall Area'
            ])
            ->add('dateCreated', DateRangeFilterType::class, [
                'min_field_name' => 'dateMin',
                'max_field_name' => 'dateMax',
                'placeholder'    => 'Date Created'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => RememberMeTokenFilter::class
        ]);
    }
}