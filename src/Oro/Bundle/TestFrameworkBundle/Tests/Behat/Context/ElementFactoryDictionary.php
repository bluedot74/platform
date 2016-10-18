<?php

namespace Oro\Bundle\TestFrameworkBundle\Tests\Behat\Context;

use Oro\Bundle\TestFrameworkBundle\Behat\Element\Element as OroElement;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\OroElementFactory;

trait ElementFactoryDictionary
{
    /**
     * @var OroElementFactory
     */
    protected $elementFactory;

    /**
     * {@inheritdoc}
     */
    public function setElementFactory(OroElementFactory $elementFactory)
    {
        $this->elementFactory = $elementFactory;
    }

    /**
     * @param string $name
     * @return OroElement
     */
    public function createElement($name)
    {
        return $this->elementFactory->createElement($name);
    }

    /**
     * @param string $name Element name
     * @param string $text Text that contains in element node
     * @param OroElement $context
     *
     * @return OroElement
     */
    public function findElementContains($name, $text, OroElement $context = null)
    {
        return $this->elementFactory->findElementContains($name, $text, $context);
    }

    /**
     * @return OroElement
     */
    public function getPage()
    {
        return $this->elementFactory->getPage();
    }
}
