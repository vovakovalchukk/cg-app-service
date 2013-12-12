<?php
return array(
    'console example :from :to' => array (
        'controllers' => function($from, $to) use ($serviceManager) {
            echo "Console Example from: " . $from . " to: " . $to . " requested";
        },
        'name' => 'ConsoleExample'
    )
);