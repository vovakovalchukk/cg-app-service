<?php
namespace CG\InputValidation\ProductLink;

use CG\Validation\Rules\IntegerValidator;
use Zend\Validator\AbstractValidator;

class StockQty extends AbstractValidator
{
    const NOT_AN_ARRAY = 'notAnArray';
    const NOT_GREATER_THAN_ZERO = 'notGreaterThanZero';
    const MESSAGE = '%name% values must an array of sku => qty, where all qty are greater than 0';

    /** @var IntegerValidator $integerValidator */
    protected $integerValidator;

    protected $messageTemplates = [
        self::NOT_AN_ARRAY => self::MESSAGE . ', %value% passed',
        IntegerValidator::NOT_INTEGER => self::MESSAGE . ', %sku% => %value%',
        self::NOT_GREATER_THAN_ZERO => self::MESSAGE . ', %sku% => %value%',
    ];

    protected $options = [
        'name' => 'stock',
        'sku' => null,
    ];

    protected $messageVariables = [
        'name' => ['options' => 'name'],
        'sku' => ['options' => 'sku'],
    ];

    public function __construct(IntegerValidator $integerValidator, $options = null)
    {
        parent::__construct($options);
        $this->integerValidator = $integerValidator;
    }

    public function isValid($value)
    {
        if (!is_array($value)) {
            $this->error(static::NOT_AN_ARRAY, gettype($value));
            return false;
        }

        foreach ($value as $sku => $qty) {
            if (!$this->isStockQtyValid($sku, $qty)) {
                return false;
            }
        }

        return true;
    }

    public function isStockQtyValid($sku, $qty)
    {
        $this->options['sku'] = $sku;

        if (!$this->integerValidator->isValid($qty)) {
            $this->error(IntegerValidator::NOT_INTEGER, gettype($qty));
            return false;
        }

        if ($qty <= 0) {
            $this->error(static::NOT_GREATER_THAN_ZERO, $qty);
            return false;
        }

        return true;
    }
}