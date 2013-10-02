<?php
return array(
    'definition' => array(
        'class' => array(
            'CG\Skeleton\Setup' => array(
                'addStartupCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\StartupCommand', 'required' => true)
                ),
                'addCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\Command', 'required' => true)
                ),
                'addShutdownCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\ShutdownCommand', 'required' => true)
                )
            )
        )
    ),
    'instance' => array(
        'CG\Skeleton\Setup' => array(
            'injections' => array(
                'CG\Skeleton\Chef\StartupCommand\SaveNode',
                'CG\Skeleton\Vagrant\Command\Up'
            ),
            'shared' => true
        ),
        'CG\Skeleton\Arguments' => array(
            'shared' => true
        )
    )
);