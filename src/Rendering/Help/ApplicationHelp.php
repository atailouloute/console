<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering\Help;

use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Rendering\Element\EmptyLine;
use Webmozart\Console\Rendering\Element\LabeledParagraph;
use Webmozart\Console\Rendering\Element\Paragraph;
use Webmozart\Console\Rendering\Element\NameVersion;
use Webmozart\Console\Rendering\Layout\BlockLayout;

/**
 * Renders the help of a console application.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ApplicationHelp extends AbstractHelp
{
    /**
     * @var Application
     */
    private $application;

    /**
     * Creates the help.
     *
     * @param Application $application The application to render.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderHelp(BlockLayout $layout)
    {
        $description = $this->application->getConfig()->getDescription();
        $commands = $this->application->getNamedCommands();
        $globalArgsFormat = $this->application->getGlobalArgsFormat();

        $argsFormat = ArgsFormat::build()
            ->addArgument(new Argument('command', Argument::REQUIRED, 'The command to execute'))
            ->addArgument(new Argument('arg', Argument::MULTI_VALUED, 'The arguments of the command'))
            // Global arguments are rendered in the command usage only
            ->addOptions($globalArgsFormat->getOptions())
            ->getFormat();

        $this->renderName($layout, $this->application);
        $this->renderUsage($layout, $this->application, $argsFormat);
        $this->renderArguments($layout, $argsFormat->getArguments());

        if ($argsFormat->hasOptions()) {
            $this->renderGlobalOptions($layout, $argsFormat->getOptions());
        }

        if (!$commands->isEmpty()) {
            $this->renderCommands($layout, $commands);
        }

        if ($description) {
            $this->renderDescription($layout, $description);
        }
    }

    /**
     * Renders the application name.
     *
     * @param BlockLayout $layout      The layout.
     * @param Application $application The application.
     */
    protected function renderName(BlockLayout $layout, Application $application)
    {
        $layout->add(new NameVersion($application->getConfig()));
        $layout->add(new EmptyLine());
    }

    /**
     * Renders the "Usage" section.
     *
     * @param BlockLayout $layout      The layout.
     * @param Application $application The application to describe.
     * @param ArgsFormat  $argsFormat  The format of the console arguments.
     */
    protected function renderUsage(BlockLayout $layout, Application $application, ArgsFormat $argsFormat)
    {
        $appName = $application->getConfig()->getName();

        $layout->add(new Paragraph("<h>USAGE</h>"));
        $layout->beginBlock();

        $this->renderSynopsis($layout, $argsFormat, $appName);

        $layout->endBlock();
        $layout->add(new EmptyLine());
    }

    /**
     * Renders the "Commands" section.
     *
     * @param BlockLayout       $layout   The layout.
     * @param CommandCollection $commands The commands to describe.
     */
    protected function renderCommands(BlockLayout $layout, CommandCollection $commands)
    {
        $layout->add(new Paragraph('<h>AVAILABLE COMMANDS</h>'));
        $layout->beginBlock();

        $commands = $commands->toArray();
        ksort($commands);

        foreach ($commands as $command) {
            $this->renderCommand($layout, $command);
        }

        $layout->endBlock();
        $layout->add(new EmptyLine());
    }

    /**
     * Renders a command in the "Commands" section.
     *
     * @param BlockLayout $layout  The layout.
     * @param Command     $command The command to describe.
     */
    protected function renderCommand(BlockLayout $layout, Command $command)
    {
        $summary = $command->getConfig()->getSummary();
        $name = '<em>'.$command->getName().'</em>';

        $layout->add(new LabeledParagraph($name, $summary));
    }

    /**
     * Renders the "Description" section.
     *
     * @param BlockLayout $layout      The layout.
     * @param string      $description The description.
     */
    protected function renderDescription(BlockLayout $layout, $description)
    {
        $layout
            ->add(new Paragraph('<h>DESCRIPTION</h>'))
            ->beginBlock()
                ->add(new Paragraph($description))
            ->endBlock()
            ->add(new EmptyLine())
        ;
    }
}
