<?php
return array(
    'ebay order :from :to' => array (
        'controllers' => function($from, $to) use ($serviceManager) {
            die("Download eBay orders from: " . $from . " to: " . $to);
        },
        'via' => 'GET',
        'name' => 'EbayOrderDownload'
    )
);