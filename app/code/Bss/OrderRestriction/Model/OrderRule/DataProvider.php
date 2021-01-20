<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\OrderRule;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * DataProvider constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    public function getData()
    {
        dd($this->request->getParams());
        return parent::getData(); // TODO: Change the autogenerated stub
    }
}
