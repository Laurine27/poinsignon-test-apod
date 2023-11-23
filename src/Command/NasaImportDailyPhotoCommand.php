<?php

namespace App\Command;

use App\Exception\PhotoAlreadyExistException;
use App\Service\NasaApiService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NasaImportDailyPhotoCommand extends Command
{
    protected static $defaultName = 'nasa:import-daily-photo';
    protected static $defaultDescription = 'Command that retrieves NASA\'s daily photo and saves it to the database.';

    public function __construct(
        private readonly NasaApiService $nasaApiService,
        string $name = null
    )
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime();

        try {
            $this->nasaApiService->fetchAndStoreNasaPhoto($date);
        }
        catch(PhotoAlreadyExistException $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }
        catch (\Exception $e) {
            $output->writeln('An error occurred while retrieving the photo of the day.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
