<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Stock;

use Shopsys\FrameworkBundle\Form\MessageType;
use Shopsys\FrameworkBundle\Model\Stock\StockSettingsData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Twig\Environment;

class StockSettingsFormType extends AbstractType
{
    /**
     * @param \Twig\Environment $environment
     */
    public function __construct(
        protected readonly Environment $environment,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('transfer', TextType::class, [
                'label' => t('Days for transfer between warehouses'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Regex(['pattern' => '/^\d+$/']),
                    new Constraints\GreaterThanOrEqual(['value' => 0]),
                ],
            ])
            ->add('luigisBoxRank', TextType::class, [
                'label' => t('Luigi\'s Box rank'),
                'required' => false,
                'constraints' => [
                    new Constraints\Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Enter an integer, please',
                    ]),
                    new Constraints\Range(['min' => 1, 'max' => 15]),
                ],
            ])
            ->add('luigisBoxRankInfo', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_INFO,
                'data' => t('The value is used for availability_rank setting in Luigi\'s Box feed. See <a href="https://docs.luigisbox.com/indexing/feeds.html">the docs</a> for more information.'),
            ])
            ->add('feedDeliveryDaysForOutOfStockProducts', IntegerType::class, [
                'label' => t('Number of delivery days for out of stock products in XML feeds'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotNull([
                        'message' => 'Please enter the number of delivery days.',
                    ]),
                ],
            ])
            ->add('feedDeliveryDaysForOutOfStockProductsInfo', MessageType::class, [
                'message_level' => MessageType::MESSAGE_LEVEL_INFO,
                'data' => $this->environment->render('@ShopsysFramework/Admin/Content/Feed/feedDeliveryDaysForOutOfStockProductsInfo.html.twig'),
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => StockSettingsData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
