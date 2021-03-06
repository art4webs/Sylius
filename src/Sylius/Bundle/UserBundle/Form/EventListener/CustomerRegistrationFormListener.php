<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\UserBundle\Form\EventListener;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\User\Model\CustomerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Michał Marcinkowski <michal.marcinkowski@lakion.com>
 */
class CustomerRegistrationFormListener implements EventSubscriberInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param RepositoryInterface $customerRepository
     */
    public function __construct(RepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit',
        );
    }

    /**
     * {@inheritDoc}
     */
    public function preSubmit(FormEvent $event)
    {
        $rawData = $event->getData();
        $form = $event->getForm();
        $data = $form->getData();

        if (!$data instanceof CustomerInterface) {
            throw new UnexpectedTypeException($data, 'Sylius\Component\User\Model\CustomerInterface');
        }

        // if email is not filled in, go on
        if (!isset($rawData['email']) || empty($rawData['email'])) {
            return;
        }
        $existingCustomer = $this->customerRepository->findOneBy(array('email' => $rawData['email']));
        if (null === $existingCustomer || null !== $existingCustomer->getUser()) {
            return;
        }

        $existingCustomer->setUser($data->getUser());
        $form->setData($existingCustomer);
    }
}
