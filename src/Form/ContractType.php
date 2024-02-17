<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Contract;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;

class ContractType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('account', EntityType::class, [
                'class' => 'App\Entity\Account',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.user = :userId')
                        ->setParameter('userId', $this->security->getUser()->getId());
                },
                'choice_label' => 'name',
                'label' => 'Compte',
                'placeholder' => 'Choisissez un compte',
                'choice_attr' => function (Account $account) {
                    return ['data-price' => $account->getIndividualPrice() ?? ''];
                },
            ])
            ->add('start', DateType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
            ])
            ->add('end', DateType::class, [
                'label' => 'Fin',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('amount', NumberType::class, [
                'help' => 'Laisser vide si le montant est variable (Forfait réel)',
                'label' => 'Montant mensuel',
                'constraints' => [
                    new Positive(),
                ],
            ])
            ->add('frequency', ChoiceType::class, [
                'label' => 'Fréquence',
                'required' => true,
                'choices' => [
                    'Mensuel' => 'monthly',
                    'Journalier' => 'daily',
                ],
            ])
            ->add('reception_delayed', CheckboxType::class, [
                'help' => ' ',
                'label' => 'Réception décalée',
                'required' => false,
            ])
            ->add('billing_delayed', CheckboxType::class, [
                'help' => ' ',
                'label' => 'Facturation décalée',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contract::class,
        ]);
    }
}
