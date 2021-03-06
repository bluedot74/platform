<?php

namespace Oro\Bundle\TestFrameworkBundle\Behat\Listener;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroElementFactory;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoader;
use Oro\Bundle\TestFrameworkBundle\Behat\Dumper\DumperInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\ReferenceRepositoryInitializer;
use Oro\Bundle\TestFrameworkBundle\Behat\ServiceContainer\KernelServiceFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FeatureIsolationSubscriber implements EventSubscriberInterface
{
    /** @var DumperInterface[] */
    protected $dumpers;

    /** @var FixtureLoader */
    protected $fixtureLoader;

    /** @var  OroElementFactory */
    protected $elementFactory;

    /** @var KernelServiceFactory */
    protected $kernelServiceFactory;

    /** @var ReferenceRepositoryInitializer  */
    protected $referenceRepositoryInitializer;

    /**
     * @param DumperInterface[] $dumpers
     * @param FixtureLoader $fixtureLoader
     * @param OroElementFactory $elementFactory
     * @param KernelServiceFactory $kernelServiceFactory
     * @param ReferenceRepositoryInitializer $referenceRepositoryInitializer
     */
    public function __construct(
        array $dumpers,
        FixtureLoader $fixtureLoader,
        OroElementFactory $elementFactory,
        KernelServiceFactory $kernelServiceFactory,
        ReferenceRepositoryInitializer $referenceRepositoryInitializer
    ) {
        $this->dumpers = $dumpers;
        $this->fixtureLoader = $fixtureLoader;
        $this->elementFactory = $elementFactory;
        $this->kernelServiceFactory = $kernelServiceFactory;
        $this->referenceRepositoryInitializer = $referenceRepositoryInitializer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeSuiteTested::BEFORE => ['injectSuite', 5],
            BeforeFeatureTested::BEFORE  => ['beforeFeature', 100],
            AfterFeatureTested::AFTER  => ['afterFeature', -100],
        ];
    }

    /**
     * @param BeforeSuiteTested $event
     */
    public function injectSuite(BeforeSuiteTested $event)
    {
        $this->fixtureLoader->setSuite($event->getSuite());
        $this->elementFactory->setSuite($event->getSuite());
    }

    /**
     * @param BeforeFeatureTested $event
     */
    public function beforeFeature(BeforeFeatureTested $event)
    {
        $this->bootKernel();
        $this->initDependencies();
        $this->loadFixtures($event->getFeature()->getTags());
    }

    /**
     * @param AfterFeatureTested $event
     */
    public function afterFeature(AfterFeatureTested $event)
    {
        $this->clearDependencies();
        $this->restore();
        $this->shutdownKernel();
    }

    public function bootKernel()
    {
        $this->kernelServiceFactory->boot();
    }

    public function initDependencies()
    {
        $this->referenceRepositoryInitializer->init();
    }

    /**
     * @param array $tags
     */
    public function loadFixtures(array $tags)
    {
        $fixturesTags = array_filter($tags, function ($tag) {
            return strpos($tag, 'fixture-') === 0;
        });

        if (0 === count($fixturesTags)) {
            return;
        }

        foreach ($fixturesTags as $tag) {
            $filename = substr($tag, 8);
            $this->fixtureLoader->loadFixtureFile($filename);
        }
    }

    public function clearDependencies()
    {
        $this->referenceRepositoryInitializer->clear();
    }

    public function restore()
    {
        foreach ($this->dumpers as $dumper) {
            $dumper->restore();
        }
    }

    public function shutdownKernel()
    {
        $this->kernelServiceFactory->shutdown();
    }
}
