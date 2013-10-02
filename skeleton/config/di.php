<?php
return array(
    'definition' => array(
        'class' => array(
            'CG\Skeleton\Setup' => array(
                'addCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\Command', 'required' => true)
                )
            )
        )
    ),
    'instance' => array(
        'CG\Skeleton\Setup' => array(
            'injections' => array(
                'CG\Skeleton\Command\Vagrant\SaveNode'
            ),
            'shared' => true
        ),
        'CG\Skeleton\Arguments' => array(
            'shared' => true
        ),
        'CG\Skeleton\Command\Vagrant\SaveNode' => array(
            'shared' => true
        )
    )
);