<?php
namespace CG\InputValidation\ProductLink;

use CG\Validation\ArrayOfValidatorAbstract;

class StockArray extends ArrayOfValidatorAbstract
{
    const ERROR_NOT_ARRAY = 'not-array';
    const ERROR_MISSING_KEYS = 'missing-keys';

    protected $name;

    public function __construct($name = 'Stock Array')
    {
        parent::__construct();
        $this->name = $name;
        $this->abstractOptions['messageVariables']['name'] = 'name';
        $this->abstractOptions['messageTemplates'][static::ERROR_NOT_ARRAY] = '%name% entry is not an array';
        $this->abstractOptions['messageTemplates'][static::ERROR_MISSING_KEYS] = '%name% entry is missing the following keys: %value%';
    }

    public function isValidElement($element)
    {
        if (!is_array($element)) {
            $this->error(static::ERROR_NOT_ARRAY);
            return false;
        }

        $missingKeys = [];
        foreach (['sku', 'quantity'] as $key) {
            if (!isset($element[$key])) {
                $missingKeys[] = $key;
            }
        }

        if (!empty($missingKeys)) {
            $this->error(static::ERROR_MISSING_KEYS, implode(', ', $missingKeys));
            return false;
        }

        return true;
    }
}