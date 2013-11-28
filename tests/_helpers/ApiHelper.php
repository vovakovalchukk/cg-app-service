<?php
namespace Codeception\Module;

use CG\Codeception\ApiHelper as CGApiHelper;
// here you can define custom functions for ApiGuy 

class ApiHelper extends CGApiHelper
{
    public function appendFilters($url, $filters = array())
    {
        if (empty($filters)) {
            return $url;
        }
        http_build_url(
            $url,
            array('query' => urldecode(http_build_query($filters))),
            HTTP_URL_JOIN_QUERY,
            $parts
        );
        return preg_replace('/\[\d+\]/','[]', urldecode($parts['path'] . "?" . $parts['query']));
    }
}
