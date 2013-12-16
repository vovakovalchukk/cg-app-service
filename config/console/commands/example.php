<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return array (
    'example:command' => array (
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            if ($input->getOption('customVerbose')) {
                $outputText = "Example command with required argument: " . $input->getArgument('argument1') . "\n";
                if ($input->getArgument('argument2')) {
                    $outputText .=  "Optional argument is set";
                }
            } else {
                $outputText = "Command complete, rerun with --customVerbose for more output";
            }
            $output->writeln($outputText);
        },
        'description' => 'This is an example command',
        'arguments' => array (
            'argument1' => array (
                'required' => true
            ),
            'argument2' => array (
                'required' => false
            )
        ),
        'options' => array (
            'customVerbose' => array (
            )
        )
    ) 
);