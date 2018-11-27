<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\NewsletterWidget\Subscriber;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\NewsletterSubscriberTransfer;
use Generated\Shared\Transfer\NewsletterSubscriptionRequestTransfer;
use Generated\Shared\Transfer\NewsletterSubscriptionResultTransfer;
use Generated\Shared\Transfer\NewsletterTypeTransfer;
use Spryker\Shared\Newsletter\NewsletterConstants;
use SprykerShop\Yves\NewsletterWidget\Dependency\Client\NewsletterWidgetToNewsletterClientInterface;

class Subscriber implements SubscriberInterface
{
    /**
     * @var \SprykerShop\Yves\NewsletterWidget\Dependency\Client\NewsletterWidgetToNewsletterClientInterface
     */
    protected $newsletterClient;

    /**
     * @param \SprykerShop\Yves\NewsletterWidget\Dependency\Client\NewsletterWidgetToNewsletterClientInterface $newsletterClient
     */
    public function __construct(NewsletterWidgetToNewsletterClientInterface $newsletterClient)
    {
        $this->newsletterClient = $newsletterClient;
    }

    /**
     * @param string $email
     *
     * @return \Generated\Shared\Transfer\NewsletterSubscriptionResultTransfer|null
     */
    public function subscribe(string $email): ?NewsletterSubscriptionResultTransfer
    {
        $customerTransfer = (new CustomerTransfer())
            ->setEmail($email);

        $request = $this->createNewsletterSubscriptionRequest($customerTransfer);
        $subscriptionResponse = $this->newsletterClient
            ->subscribeWithDoubleOptIn($request);

        $newsLetterSubscriptionResultTransfer = current($subscriptionResponse->getSubscriptionResults());
        if ($newsLetterSubscriptionResultTransfer === false) {
            return null;
        }

        return $newsLetterSubscriptionResultTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     * @param string|null $subscriberKey
     *
     * @return \Generated\Shared\Transfer\NewsletterSubscriptionRequestTransfer
     */
    protected function createNewsletterSubscriptionRequest(CustomerTransfer $customerTransfer, $subscriberKey = null): NewsletterSubscriptionRequestTransfer
    {
        $subscriptionRequest = new NewsletterSubscriptionRequestTransfer();

        $subscriberTransfer = new NewsletterSubscriberTransfer();
        $subscriberTransfer->setFkCustomer($customerTransfer->getIdCustomer());
        $subscriberTransfer->setEmail($customerTransfer->getEmail());
        $subscriberTransfer->setSubscriberKey($subscriberKey);

        $subscriptionRequest->setNewsletterSubscriber($subscriberTransfer);
        $subscriptionRequest->addSubscriptionType((new NewsletterTypeTransfer())
            ->setName(NewsletterConstants::DEFAULT_NEWSLETTER_TYPE));

        return $subscriptionRequest;
    }
}
