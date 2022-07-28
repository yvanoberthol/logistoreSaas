<?php

namespace App\Form;

use App\Entity\Bank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,
                $this->options("form.bank.name.title",
                    'form.bank.name.placeholder'))
            ->add('accountName',
                TextType::class,
                $this->options("form.bank.accountName.title",
                    'form.bank.accountName.placeholder'))
            ->add('accountNumber',
                TextType::class,
                $this->options("form.bank.accountNumber.title",
                    'form.bank.accountNumber.placeholder'),[
                    'required' => false
                ])
            ->add('initialBalance',
                NumberType::class,
                $this->options("form.bank.initialBalance.title",
                    'form.bank.initialBalance.placeholder'))
            ->add('address',
                TextType::class,
                $this->options("form.bank.address.title",
                    'form.bank.address.placeholder'),[
                    'required' => false
                ])
            ->add('phoneNumber',
                TextType::class,
                $this->options("form.bank.phoneNumber.title",
                    'form.bank.phoneNumber.placeholder'),[
                    'required' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bank::class,
        ]);
    }
}
