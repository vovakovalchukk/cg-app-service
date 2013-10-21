<?php
namespace CG\Skeleton;

define('TICKET_REGEX','/([A-Z]+-[0-9]+)/');

trait GitTicketIdTrait
{
    public function getGitTicketId()
    {
        $branch = shell_exec('git rev-parse --abbrev-ref HEAD');
        preg_match(TICKET_REGEX, $branch, $matches);
        return $matches[0];
    }
}