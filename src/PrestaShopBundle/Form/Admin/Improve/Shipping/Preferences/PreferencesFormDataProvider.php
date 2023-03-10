<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShopBundle\Form\Admin\Improve\Shipping\Preferences;

use PrestaShop\PrestaShop\Adapter\Carrier\CarrierOptionsConfiguration;
use PrestaShop\PrestaShop\Adapter\Carrier\HandlingConfiguration;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class is responsible of managing the data manipulated using forms
 * in "Improve > Shipping > Preferences" page.
 *
 * @deprecated since 1.7.8, will be removed in the next major version
 */
class PreferencesFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var HandlingConfiguration
     */
    private $handlingConfiguration;

    /**
     * @var CarrierOptionsConfiguration
     */
    private $carrierOptionsConfiguration;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        HandlingConfiguration $handlingConfiguration,
        CarrierOptionsConfiguration $carrierOptionsConfiguration,
        TranslatorInterface $translator
    ) {
        $this->handlingConfiguration = $handlingConfiguration;
        $this->carrierOptionsConfiguration = $carrierOptionsConfiguration;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return [
            'handling' => $this->handlingConfiguration->getConfiguration(),
            'carrier_options' => $this->carrierOptionsConfiguration->getConfiguration(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        if ($errors = $this->validate($data)) {
            return $errors;
        }

        return array_merge(
            $this->handlingConfiguration->updateConfiguration($data['handling']),
            $this->carrierOptionsConfiguration->updateConfiguration($data['carrier_options'])
        );
    }

    /**
     * Perform validation on form data before saving it.
     *
     * @param array $data
     *
     * @return array Returns array of errors
     */
    protected function validate(array $data)
    {
        $errors = [];
        $numericFields = [
            [
                'value' => $data['handling']['shipping_handling_charges'],
                'name' => $this->translator->trans('Handling charges', [], 'Admin.Shipping.Feature'),
            ],
            [
                'value' => $data['handling']['free_shipping_price'],
                'name' => $this->translator->trans('Free shipping starts at', [], 'Admin.Shipping.Feature'),
            ],
            [
                'value' => $data['handling']['free_shipping_weight'],
                'name' => $this->translator->trans('Free shipping starts at', [], 'Admin.Shipping.Feature'),
            ],
        ];

        // Check if all numeric fields are positive numbers
        foreach ($numericFields as $field) {
            if (!is_numeric($field['value']) || $field['value'] < 0) {
                $errors[] = [
                    'key' => 'The %s field is invalid.',
                    'domain' => 'Admin.Notifications.Error',
                    'parameters' => [$field['name']],
                ];
            }
        }

        return $errors;
    }
}
