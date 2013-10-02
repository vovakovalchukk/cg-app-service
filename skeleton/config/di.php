<?php
return array(
    'definition' => array(
        'class' => array(
            'CG\Skeleton\Application' => array(
                'addStartupCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\StartupCommandInterface', 'required' => true)
                ),
                'addCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\CommandInterface', 'required' => true)
                ),
                'addShutdownCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\ShutdownCommandInterface', 'required' => true)
                )
            )
        )
    ),
    'instance' => array(
        'CG\Skeleton\Application' => array(
            'injections' => array(
                'CG\Skeleton\Environment\StartupCommand',
                'CG\Skeleton\Vagrant\StartupCommand',
                'CG\Skeleton\Chef\StartupCommand',
                'CG\Skeleton\Vagrant\Command\Up',
                'CG\Skeleton\Vagrant\Command\Provision',
                'CG\Skeleton\Vagrant\Command\Reload',
                'CG\Skeleton\Vagrant\Command\Halt',
                'CG\Skeleton\Environment\ShutdownCommand'
            ),
            'shared' => true
        ),
        'CG\Skeleton\Arguments' => array(
            'shared' => true
        )
    )
);