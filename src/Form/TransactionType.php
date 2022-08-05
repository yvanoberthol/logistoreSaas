<?php

namespace App\Form;

use App\Entity\Bank;
use App\Entity\Transaction;
use App\Form\DataTransformer\LocaleToDateTimeTransformer;
use App\Repository\BankRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends ApplicationType
{
    /**
     * @var Session
     */
    private $session;

    /**
     * TransactionType constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('bank', EntityType::class,[
                'class' => Bank::class,
                'query_builder' => function(BankRepository $bankRepository){
                    return $bankRepository->qbFindActive(true);
                },
                'label' => 'form.transaction.bank.title',
                'choice_label' => 'title',
                'required' => true
            ])
            ->add('type',
                ChoiceType::class,
                $this->options('form.transaction.type.title', 'form.transaction.type.placeholder',
                    ['choices' => [
                        'debit' => '0',
                        'credit' => '1'
                    ]]))
            ->add('date',TextType::class,
                $this->options("form.transaction.date.title",
                    $this->session->get('setting')->getDateMediumPicker(),[
                    'attr' => ['class' => 'datepicker']
                ]))
            ->add('transactionCode',
                TextType::class,
                $this->options("form.transaction.transactionCode.title",
                    'form.transaction.transactionCode.placeholder',[
                        'required' => false
                    ]))
            ->add('amount',
                NumberType::class,
                $this->options("form.transaction.amount.title",
                    'form.transaction.amount.placeholder'))
            ->add('fee',
                NumberType::class,
                $this->options("form.transaction.fee.title",
                    'form.transaction.fee.placeholder',[
                        'required' => false
                    ]))
            ->add('description',
                TextareaType::class,
                $this->options("form.transaction.description.title",
                    'form.transaction.description.placeholder',[
                        'required' => false
                    ]))
        ;

        $builder->get('date')
            ->addModelTransformer(new LocaleToDateTimeTransformer($this->session));
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
