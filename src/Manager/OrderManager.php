<?php
/**
 */

namespace Commercetools\Symfony\CartBundle\Manager;

use Commercetools\Core\Model\Cart\Cart;
use Commercetools\Core\Model\Order\Order;
use Commercetools\Core\Model\Order\OrderCollection;
use Commercetools\Core\Model\State\StateReference;
use Commercetools\Core\Request\AbstractAction;
use Commercetools\Symfony\CartBundle\Event\OrderCreateEvent;
use Commercetools\Symfony\CartBundle\Event\OrderPostCreateEvent;
use Commercetools\Symfony\CartBundle\Event\OrderPostUpdateEvent;
use Commercetools\Symfony\CartBundle\Event\OrderUpdateEvent;
use Commercetools\Symfony\CtpBundle\Security\User\CtpUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Commercetools\Symfony\CartBundle\Model\Repository\OrderRepository;
use Commercetools\Symfony\CartBundle\Model\OrderUpdateBuilder;

class OrderManager
{
    /**
     * @var OrderRepository
     */
    private $repository;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * OrderManager constructor.
     * @param OrderRepository $repository
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(OrderRepository $repository, EventDispatcherInterface $dispatcher)
    {
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param $locale
     * @param CtpUser|null $user
     * @param string|null $anonymousId
     * @return OrderCollection
     */
    public function getOrdersForUser($locale, CtpUser $user = null, $anonymousId = null)
    {
        return $this->repository->getOrders($locale, $user, $anonymousId);
    }

    /**
     * @param $locale
     * @param $orderId
     * @param CtpUser|null $user
     * @param string|null $anonymousId
     * @return Order
     */
    public function getOrderForUser($locale, $orderId, CtpUser $user = null, $anonymousId = null)
    {
        return $this->repository->getOrder($locale, $orderId, $user, $anonymousId);
    }

    /**
     * @param $locale
     * @param $paymentId
     * @param CtpUser|null $user
     * @param string|null $anonymousId
     * @return mixed
     */
    public function getOrderFromPayment($locale, $paymentId, CtpUser $user = null, $anonymousId = null)
    {
        return $this->repository->getOrderFromPayment($locale, $paymentId, $user, $anonymousId);
    }

    /**
     * @param $locale
     * @param Cart $cart
     * @param StateReference $stateReference
     * @return Order
     */
    public function createOrderFromCart($locale, Cart $cart, StateReference $stateReference)
    {
        $event = new OrderCreateEvent();
        $this->dispatcher->dispatch(OrderCreateEvent::class, $event);

        $order = $this->repository->createOrderFromCart($locale, $cart, $stateReference);

        $eventPost = new OrderPostCreateEvent();
        $this->dispatcher->dispatch(OrderPostCreateEvent::class, $eventPost);

        return $order;
    }

    /**
     * @param Order $order
     * @return OrderUpdateBuilder
     */
    public function update(Order $order)
    {
        return new OrderUpdateBuilder($order, $this);
    }

    /**
     * @param Order $order
     * @param AbstractAction $action
     * @param string|null $eventName
     * @return AbstractAction[]
     */
    public function dispatch(Order $order, AbstractAction $action, $eventName = null)
    {
        $eventName = is_null($eventName) ? get_class($action) : $eventName;

        $event = new OrderUpdateEvent($order, $action);
        $event = $this->dispatcher->dispatch($eventName, $event);

        return $event->getActions();
    }

    /**
     * @param Order $order
     * @param array $actions
     * @return Order
     */
    public function apply(Order $order, array $actions)
    {
        $order = $this->repository->update($order, $actions);

        $this->dispatchPostUpdate($order, $actions);

        return $order;
    }

    /**
     * @param Order $order
     * @param array $actions
     * @return AbstractAction[]
     */
    public function dispatchPostUpdate(Order $order, array $actions)
    {
        $event = new OrderPostUpdateEvent($order, $actions);
        $event = $this->dispatcher->dispatch(OrderPostUpdateEvent::class, $event);

        return $event->getActions();
    }
}
