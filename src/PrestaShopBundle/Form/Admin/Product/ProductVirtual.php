<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
namespace PrestaShopBundle\Form\Admin\Product;

use PrestaShopBundle\Form\Admin\Type\CommonAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use PrestaShopBundle\Form\Admin\Type as PsFormType;

/**
 * This form class is responsible to generate the virtual product
 */
class ProductVirtual extends CommonAbstractType
{
    private $translator;
    private $legacyContext;
    private $configuration;

    /**
     * Constructor
     *
     * @param object $translator
     * @param object $legacyContext
     */
    public function __construct($translator, $legacyContext)
    {
        $this->translator = $translator;
        $this->legacyContext = $legacyContext;
        $this->configuration = $this->getConfiguration();
    }

    /**
     * {@inheritdoc}
     *
     * Builds form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('is_virtual_file', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
            'choices'  => array(
                $this->translator->trans('Yes', [], 'Admin.Global') => 1,
                $this->translator->trans('No', [], 'Admin.Global') => 0,
            ),
            'choices_as_values' => true,
            'expanded' => true,
            'required' => true,
            'multiple' => false,
        ))
        ->add('file', 'Symfony\Component\Form\Extension\Core\Type\FileType', array(
            'required' => false,
            'label' => $this->translator->trans('File', [], 'Admin.Global'),
            'constraints' => array(
                new Assert\File(array('maxSize' => $this->configuration->get('PS_ATTACHMENT_MAXIMUM_SIZE').'M')),
            )
        ))
        ->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
            'label'    => $this->translator->trans('Filename', [], 'Admin.Global'),
            'constraints' => array(
                new Assert\NotBlank(),
            ),
        ))
        ->add('nb_downloadable', 'Symfony\Component\Form\Extension\Core\Type\NumberType', array(
            'label'    => $this->translator->trans('Number of allowed downloads', [], 'Admin.Catalog.Feature'),
            'required' => false,
            'constraints' => array(
                new Assert\Type(array('type' => 'numeric')),
            ),
        ))
        ->add('expiration_date', 'PrestaShopBundle\Form\Admin\Type\DatePickerType', array(
            'label'    => $this->translator->trans('Expiration date', [], 'Admin.Catalog.Feature'),
            'required' => false,
            'attr' => ['placeholder' => 'YYYY-MM-DD']
        ))
        ->add('nb_days', 'Symfony\Component\Form\Extension\Core\Type\NumberType', array(
            'label'    => $this->translator->trans('Number of days', [], 'Admin.Catalog.Feature'),
            'required' => false,
            'constraints' => array(
                new Assert\Type(array('type' => 'numeric')),
            )
        ))
        ->add('save', 'Symfony\Component\Form\Extension\Core\Type\ButtonType', array(
            'label' => $this->translator->trans('Save', [], 'Admin.Actions'),
            'attr' => ['class' => 'btn-primary pull-right']
        ));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            //if this partial form is submit from a parent form, disable it
            if ($form->getParent()) {
                $event->setData([]);
                $form->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('mapped' => false));
            } elseif ($data['is_virtual_file'] == 0) {
                //disable name mapping when is virtual not defined to yes
                $form->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('mapped' => false));
            }
        });
    }

    /**
     * Returns the block prefix of this type.
     *
     * @return string The prefix name
     */
    public function getBlockPrefix()
    {
        return 'product_virtual';
    }
}
