<?php
namespace CG\Product\Link\Storage;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

trait LogMatchingLinkIdsOnNotFoundTrait
{
    /** from LogTrait */
    abstract public function logDebugDump($debug, $message, $args = [], $logConstant = '', $additionalParams = [], $truncate = true);
    /** from LogTrait */
    abstract public function logWarning($message, $args = [], $logConstant = '', $additionalParams = []);
    abstract protected function getLogCode(): string;

    public function logMatchingLinkIdsOnNotFound(Sql $readSql, Select $linkIdSelect, Select $originalQuery): void
    {
        $linkIdResults = $readSql->prepareStatementForSqlObject($linkIdSelect)->execute();
        if ($linkIdResults->count() == 0) {
            return;
        }
        $linkIds = array_column(iterator_to_array($linkIdResults), 'linkId');
        $this->logWarning('Link IDs found yet corresponding main query had no results', [], $this->getLogCode());
        $this->logDebugDump($originalQuery->getSqlString($readSql->getAdapter()->getPlatform()), 'Link IDs (%s) exist for this query, yet it returned no results', [implode(',', $linkIds)], $this->getLogCode());
    }
}