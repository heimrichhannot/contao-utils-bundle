<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Command;

use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\ImageSizeItemModel;
use Contao\ImageSizeModel;
use Contao\Model;
use Contao\System;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateImageSizeCommand extends AbstractLockedCommand implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    const MODE_FIRST = 1;
    const MODE_INTERMEDIATE = 2;
    const MODE_LAST = 3;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('huh:utils:create-image-size')->setDescription('Creates image size items for a given image size entity.');
        $this->addArgument('image-size-ids', InputArgument::OPTIONAL, 'The comma separated ids of the image size. Set to "all" in order to create image size items for all image size entities. Image size entities with existing image size items will be skipped.');
        $this->addArgument('breakpoints', InputArgument::OPTIONAL, 'The comma separated breakpoints as pixel amounts (defaults to "576,768,992,1200").', '576,768,992,1200');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->rootDir = $this->getContainer()->getParameter('kernel.project_dir');
        $this->framework->initialize();

        $imageSizeIds = $input->getArgument('image-size-ids');
        $breakpoints = explode(',', $input->getArgument('breakpoints'));
        sort($breakpoints);

        // prevent sql injection
        if ('all' !== $imageSizeIds) {
            $imageSizeIds = preg_replace('@[^0-9,]@i', '', $imageSizeIds);
        }

        $creationCount = 0;
        $columns = ('all' === $imageSizeIds ? [] : ['tl_image_size.id IN ('.$imageSizeIds.')']);

        if (null === ($imageSizes = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_image_size', $columns, []))) {
            $this->io->error('No image sizes found for the given ids.');

            return 0;
        }

        /** @var ImageSizeModel $imageSize */
        foreach ($imageSizes as $imageSize) {
            $existingItems = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_image_size_item', ['tl_image_size_item.pid=?'], [$imageSize->id]);

            if (null !== $existingItems) {
                $this->io->warning('Skipping image size ID '.$imageSize->id.' because it already has existing image size items.');

                continue;
            }

            $j = 0;

            // first
            $this->createItem($imageSize, $j++, $breakpoints[0], null, static::MODE_FIRST);
            ++$creationCount;

            // intermediates
            foreach ($breakpoints as $i => $breakpoint) {
                if ($i === \count($breakpoints) - 1) {
                    continue;
                }

                $this->createItem($imageSize, $j++, $breakpoint, $breakpoints[$i + 1], static::MODE_INTERMEDIATE);

                ++$creationCount;
            }

            // last
            $this->createItem($imageSize, $j++, $breakpoints[\count($breakpoints) - 1], null, static::MODE_LAST);
            ++$creationCount;
        }

        $this->io->success($creationCount.' image size items have been created.');

        return 0;
    }

    /**
     * @param ImageSizeModel $imageSize
     */
    protected function createItem(Model $imageSize, int $index, int $breakpoint, ?int $nextBreakpoint, int $mode)
    {
        $item = new ImageSizeItemModel();

        $item->tstamp = time();
        $item->pid = $imageSize->id;
        $item->sorting = 128 * $index;
        $item->densities = $imageSize->densities;
        $item->resizeMode = $imageSize->resizeMode;

        switch ($mode) {
            case static::MODE_FIRST:
                $item->media = '(max-width: '.($breakpoint - 1).'px)';
                $item->width = $breakpoint - 1;

                break;

            case static::MODE_INTERMEDIATE:
                $item->media = '(min-width: '.$breakpoint.'px) and (max-width: '.($nextBreakpoint - 1).'px)';
                $item->width = $nextBreakpoint - 1;

                break;

            case static::MODE_LAST:
                $item->media = '(min-width: '.$breakpoint.'px)';
                $item->width = max($breakpoint, $imageSize->width);

                break;
        }

        $item->height = ($item->width * $imageSize->height) / $imageSize->width;

        $item->save();
    }
}
