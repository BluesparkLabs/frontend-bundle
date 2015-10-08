<?php

namespace Rj\FrontendBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class InjectLiveReloadListener implements EventSubscriberInterface
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->shouldInject($event)) {
            return;
        }

        $response = $event->getResponse();
        $content = $response->getContent();

        $pos = strripos($content, '</body>');
        if (false === $pos) {
            return;
        }

        $script = '<script src="'.$this->url.'"></script>';
        $response->setContent(substr($content, 0, $pos).$script.substr($content, $pos));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse', -128),
        );
    }

    private function shouldInject($event)
    {
        if (!$event->isMasterRequest()) {
            return false;
        }

        if ($event->getRequest()->isXmlHttpRequest()) {
            return false;
        }

        return $event->getResponse()->headers->has('X-Debug-Token');
    }
}
