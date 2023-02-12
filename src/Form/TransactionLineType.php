<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\TransactionLine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('account', EntityType::class, [
                'label' => false,
                'class' => 'App\Entity\Account',
                'choice_label' => function (Account $account) {
                    $label = $account->getName().' ('.$account->getBalance();
                    if ($account->getIndividualPrice()) {
                        $label .= ' x '.$account->getIndividualPrice();
                    }

                    return $label.' €)';
                },
                'placeholder' => 'Choisir un compte',
                'row_attr' => [
                    'class' => 'col-3',
                ],
            ])
            ->add('amount', NumberType::class, [
                'label' => false,
                'row_attr' => [
                    'class' => 'col-7',
                ],
            ])
            ->add('remove', ButtonType::class, [
                'label' => '<i class="fas fa-trash"></i>',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-danger remove-line',
                ],
                'row_attr' => [
                    'class' => 'd-grid col-2',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransactionLine::class,
        ]);
    }
}
