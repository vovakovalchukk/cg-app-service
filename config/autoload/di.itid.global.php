<?php

use CG\Log\ITIDService;

return array(
    'di' => array(
        'instance' => array(
            ITIDService::class => array(
                'shared' => true
            )
        )
    )
);
