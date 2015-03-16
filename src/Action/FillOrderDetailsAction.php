<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Security\SensitiveValue;
use Payum\Offline\Constants;

class FillOrderDetailsAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getOrder();
        $divisor = pow(10, $order->getCurrencyDigitsAfterDecimalPoint());

        $details = $order->getDetails();
        $details['amount'] = (float) $order->getTotalAmount() / $divisor;
        $details['currency'] = $order->getCurrencyCode();
        $details['description'] = $order->getDescription();

        if ($order->getCreditCard()) {
            $card = $order->getCreditCard();

            $details['card'] = new SensitiveValue(array(
                'number' => $card->getNumber(),
                'cvv' => $card->getSecurityCode(),
                'expiryMonth' => $card->getExpireAt()->format('m'),
                'expiryYear' => $card->getExpireAt()->format('y'),
                'firstName' => $card->getHolder(),
                'lastName' => '',
            ));
        }

        $order->setDetails($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FillOrderDetails;
    }
}
