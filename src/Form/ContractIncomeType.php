<?php

namespace App\Form;

use App\Entity\ContractIncome;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ContractIncomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('description', TextType::class, [
            'label' => 'Description',
        ]);

        /**
         * @var ContractIncome $data
         */
        $data = $options['data'];
        if ($data->getIsPlanned()) {
            $builder->add('planned', NumberType::class, [
                'label' => 'Montant prévu',
                'constraints' => [
                    new PositiveOrZero(),
                ],
            ]);
        }
        if ($data->getIsBilled()) {
            $builder->add('billed', NumberType::class, [
                'label' => 'Montant facturé',
                'constraints' => [
                    new PositiveOrZero(),
                ],
            ]);
        }
        if ($data->getIsReceived()) {
            $builder->add('received', NumberType::class, [
                'label' => 'Montant reçu',
                'constraints' => [
                    new PositiveOrZero(),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContractIncome::class,
        ]);
    }
}
