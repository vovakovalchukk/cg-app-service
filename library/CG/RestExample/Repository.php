<?php
namespace CG\RestExample;

use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;

class Repository
{
    /**
     * This should now employ the repository pattern to fetch, save or delete the data.
     * The storages will throw exceptions where applicable, which are caught in the controller,
     * and converted to Http Exceptions.
     */
    public function fetch($status = 200)
    {
        if ($status == 404) {
            throw new NotFound('couldn\'t find entity');
        }
        if ($status == 409) {
            throw new Conflict('a conflict occurred');
        }
        return new Entity($status, true);
    }
}