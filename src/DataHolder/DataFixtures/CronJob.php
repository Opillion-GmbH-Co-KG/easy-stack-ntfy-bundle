<?php

declare(strict_types=1);

namespace Opillion\EasyStack\NtfyBundle\DataHolder\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ObjectManager;
use Override;

final class CronJob extends Fixture implements OrderedFixtureInterface
{
    public function getOrder(): int
    {
        return 3;
    }

    /**
     * @throws Exception
     * @throws ORMException
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        if (!method_exists($manager, 'getConnection')) {
            return;
        }

        $connection = $manager->getConnection();
        foreach ($this->getData() as $data) {
            try {
                $connection->executeStatement(
                    "
                        INSERT INTO cron_job (
                            id,
                            name,
                            is_active,
                            cron_job_status,
                            schedule_frequency,
                            description,
                            async,
                            command
                        )
                        VALUES (:id, :name, :is_active, :status, :frequency, :description, :async, :command)
                        ON DUPLICATE KEY UPDATE
                            is_active = VALUES(is_active),
                            name = VALUES(name),
                            cron_job_status = VALUES(cron_job_status),
                            schedule_frequency = VALUES(schedule_frequency),
                            description = VALUES(description),
                            async = VALUES(async),
                            command = VALUES(command)
                    ",
                    [
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'is_active' => $data['isActive'] ? 1 : 0,
                        'status' => 'PENDING',
                        'frequency' => $data['scheduleFrequency'],
                        'description' => $data['description'],
                        'async' => $data['async'] ? 1 : 0,
                        'command' => $data['command'],
                    ]
                );
            } catch (Exception) {
            }
        }
    }

    /**
     * @return list<array{
     *     id: int,
     *     isActive: bool,
     *     name: string,
     *     scheduleFrequency: string,
     *     description: string,
     *     command: string,
     *     async: bool
     * }>
     */
    private function getData(): array
    {
        return [
            [
                'id' => 2,
                'isActive' => true,
                'name' => 'ntfy:sync',
                'scheduleFrequency' => 'PT30S',
                'description' => 'Runs every 30 seconds',
                'command' => 'ntfy:sync',
                'async' => false,
            ],
            [
                'id' => 7,
                'isActive' => true,
                'name' => 'ntfy:zip-and-send',
                'scheduleFrequency' => 'PT1H',
                'description' => 'Runs every hour',
                'command' => 'ntfy:zip-and-send',
                'async' => false,
            ],
        ];
    }
}
