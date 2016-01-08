<?php

namespace Eole\Games\Awale;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Core\Event\PartyEvent;
use Eole\Games\Awale\Model\AwaleParty;

class EventListener implements EventSubscriberInterface
{
    /**
     * @var ObjectRepository
     */
    private $awalePartyRepository;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectRepository $awalePartyRepository
     * @param ObjectManager $om
     */
    public function __construct(ObjectRepository $awalePartyRepository, ObjectManager $om)
    {
        $this->awalePartyRepository = $awalePartyRepository;
        $this->om = $om;
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyCreate(PartyEvent $event)
    {
        if ('awale' !== $event->getParty()->getGame()->getName()) {
            return;
        }

        $awaleParty = AwaleParty::createWithSeedsPerContainer(3);

        $awaleParty->setParty($event->getParty());

        $this->om->persist($awaleParty);
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PartyEvent::CREATE_BEFORE => array(
                array('onPartyCreate'),
            ),
        );
    }
}
