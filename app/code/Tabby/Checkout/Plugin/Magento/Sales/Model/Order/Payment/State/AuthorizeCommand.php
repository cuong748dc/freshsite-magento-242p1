<?php

namespace Tabby\Checkout\Plugin\Magento\Sales\Model\Order\Payment\State;

use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class AuthorizeCommand
{
    /**
     * @param \Magento\Sales\Model\Order\Payment\State\AuthorizeCommand $command
     * @param $result
     * @param OrderPaymentInterface $payment
     * @return mixed|string
     */
    public function afterExecute(
        \Magento\Sales\Model\Order\Payment\State\AuthorizeCommand $command,
        $result,
        OrderPaymentInterface $payment
    ) {

        if (preg_match('#^tabby_#', $payment->getMethod()) && $payment->getExtensionAttributes()) {
            $result = $payment->getExtensionAttributes()->getNotificationMessage() ?: $result;
        }

        return ($result instanceof Phrase) ? $result->render() : $result;
    }
}
