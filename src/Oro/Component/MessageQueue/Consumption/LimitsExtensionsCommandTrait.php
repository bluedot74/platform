<?php
namespace Oro\Component\MessageQueue\Consumption;

use Oro\Component\MessageQueue\Consumption\Extension\LimitConsumedMessagesExtension;
use Oro\Component\MessageQueue\Consumption\Extension\LimitConsumerMemoryExtension;
use Oro\Component\MessageQueue\Consumption\Extension\LimitConsumptionTimeExtension;
use Oro\Component\MessageQueue\Consumption\Extension\LoggerExtension;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

trait LimitsExtensionsCommandTrait
{
    /**
     * {@inheritdoc}
     */
    protected function configureLimitsExtensions()
    {
        $this
            ->addOption('message-limit', null, InputOption::VALUE_REQUIRED, 'Consume n messages and exit')
            ->addOption('time-limit', null, InputOption::VALUE_REQUIRED, 'Consume messages during this time')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Consume messages until process reaches'.
                ' this memory limit in MB');
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return ExtensionInterface[]
     *
     * @throws \Exception
     */
    protected function getLimitsExtensions(InputInterface $input, OutputInterface $output)
    {
        $extensions = [];

        $messageLimit = (int) $input->getOption('message-limit');
        if ($messageLimit) {
            $extensions[] = new LimitConsumedMessagesExtension($messageLimit);
        }

        $timeLimit = $input->getOption('time-limit');
        if ($timeLimit) {
            try {
                $timeLimit = new \DateTime($timeLimit);
            } catch (\Exception $e) {
                $output->writeln('<error>Invalid time limit</error>');

                throw $e;
            }

            $extensions[] = new LimitConsumptionTimeExtension($timeLimit);
        }

        $memoryLimit = (int) $input->getOption('memory-limit');
        if ($memoryLimit) {
            $extensions[] = new LimitConsumerMemoryExtension($memoryLimit);
        }

        return $extensions;
    }
}
