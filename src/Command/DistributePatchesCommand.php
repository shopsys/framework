<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Yaml\ParametersMerger;
use function getcwd;

class DistributePatchesCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:patch:distribute';

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $projectBaseComposerPath = getcwd() . '/project-base/composer.json';
        $projectBaseComposerContent = FileSystem::read($projectBaseComposerPath);
        $projectBaseComposer = Json::decode($projectBaseComposerContent, Json::FORCE_ARRAY);

        $monorepoComposerPath = getcwd() . '/composer.json';
        $monorepoComposerContent = FileSystem::read($monorepoComposerPath);
        $monorepoComposer = Json::decode($monorepoComposerContent, Json::FORCE_ARRAY);

        if ($monorepoComposer['extra']['patches'] ?? false) {
            $patchesToMerge = [
                'extra' => [
                    'patches' => $monorepoComposer['extra']['patches'],
                ],
            ];
            $parametersMerger = new ParametersMerger();
            $projectBaseComposer = $parametersMerger->merge($projectBaseComposer, $patchesToMerge);

            $updatedProjectBaseComposerContent = Json::encode($projectBaseComposer, Json::PRETTY);
            file_put_contents($projectBaseComposerPath, $updatedProjectBaseComposerContent);
            $io->info('DistributePatchesCommand: patches were merged from mono-repo to project-base.');
        }

        return static::SUCCESS;
    }
}
