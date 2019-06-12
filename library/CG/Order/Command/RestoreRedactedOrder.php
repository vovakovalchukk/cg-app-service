<?php
namespace CG\Order\Command;

use CG\Db\Mysqli;
use CG\Db\Query\Where;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Service\RedactLocker;
use CG\Order\Shared\Address\Mapper as AddressMapper;
use CG\Order\Shared\Address\Redacted as RedactedAddress;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\Order\Shared\Item\GiftWrap\Entity as GiftWrap;
use CG\Order\Shared\Item\GiftWrap\StorageInterface as GiftWrapStorage;
use CG\Order\Shared\StorageInterface as OrderStorage;
use CG\Stdlib\Cryptor;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreRedactedOrder
{
    const DEFAULT_RESTORE_UNTIL = '1 day';

    /** @var Mysqli */
    protected $mysqli;
    /** @var OrderStorage */
    protected $orderStorage;
    /** @var GiftWrapStorage */
    protected $giftWrapStorage;
    /** @var Cryptor */
    protected $cryptor;
    /** @var RedactLocker */
    protected $redactLocker;
    /** @var AddressMapper */
    protected $addressMapper;

    public function __construct(
        Mysqli $mysqli,
        OrderStorage $orderStorage,
        GiftWrapStorage $giftWrapStorage,
        Cryptor $cryptor,
        RedactLocker $redactLocker,
        AddressMapper $addressMapper
    ) {
        $this->mysqli = $mysqli;
        $this->orderStorage = $orderStorage;
        $this->giftWrapStorage = $giftWrapStorage;
        $this->cryptor = $cryptor;
        $this->redactLocker = $redactLocker;
        $this->addressMapper = $addressMapper;
    }

    public function __invoke(OutputInterface $output, string $orderId, string $restoreUntil = null)
    {
        $restoreUntil = $restoreUntil ?? static::DEFAULT_RESTORE_UNTIL;
        $dateTime = new DateTime($restoreUntil);
        $output->writeln(sprintf('Restoring redacted pii on order %s for <comment>%s (%s)</comment>', $orderId, $restoreUntil, $dateTime->stdFormat()));

        $order = $this->fetchOrder($orderId);
        if ($order === null) {
            $output->writeln(sprintf('<error>Unable to find order %s</error>', $orderId));
            return;
        }

        $this->redactLocker->preventRedaction($order->getId(), $dateTime);
        $this->restoreOrder($output, $order);

        /** @var OrderItem $orderItem */
        foreach ($order->getItems() as $orderItem) {
            /** @var GiftWrap $giftWrap */
            foreach ($orderItem->getGiftWraps() as $giftWrap) {
                $this->restoreGiftWrap($output, $order, $giftWrap);
            }
        }
    }

    protected function fetchOrder(string $orderId): ?Order
    {
        try {
            return $this->orderStorage->fetch($orderId);
        } catch (NotFound $exception) {
            return null;
        }
    }

    protected function decrypt(Order $order, string $encryptedData)
    {
        return $this->cryptor->decrypt(
            $this->cryptor->getKey($order->getChannel()),
            $encryptedData
        );
    }

    protected function restoreOrder(OutputInterface $output, Order $order): void
    {
        $encryptedData = $this->fetchOrderEncryptedData($order->getId());
        if ($encryptedData === null) {
            $output->writeln(sprintf('    <error>Unable to find redacted pii for order %s</error>', $order->getId()));
            return;
        }

        try {
            $this->restoreRedactedData($order, $encryptedData);
            $this->orderStorage->save($order);
            $output->writeln(sprintf('    <info>Order %s has had pii information restored</info>', $order->getId()));
        } catch (NotModified $exception) {
            $output->writeln(sprintf('    <error>Order %s was not modified</error>', $order->getId()));
        }
    }

    protected function fetchOrderEncryptedData(string $orderId): ?array
    {
        $sql = 'SELECT * FROM `orderEncrypted`';
        $where = (new Where())->equals('`orderId`', 's', $orderId);

        return $this->mysqli->query(
            $sql . $where,
            $where->getWhereParameters(),
            function(\mysqli_result $result) {
                return $result->fetch_assoc();
            }
        );
    }

    protected function restoreRedactedData(Order $order, array $encryptedData): void
    {
        foreach (['billingAddress', 'shippingAddress', 'fulfilmentAddress'] as $address) {
            $this->restoreRedactedAddress($order, $address, $encryptedData[$address] ?? '');
        }

        $this->restoreBuyerMessage($order, $encryptedData['buyerMessage'] ?? '');
    }

    protected function restoreRedactedAddress(Order $order, string $type, string $encryptedData): void
    {
        $address = $order->{'get' . ucfirst($type)}();
        if (!($address instanceof RedactedAddress)) {
            return;
        }

        $order->{'set' . ucfirst($type)}($this->addressMapper->fromArray(
            $this->decrypt($order, $encryptedData)
        ));
    }

    protected function restoreBuyerMessage(Order $order, string $encryptedData): void
    {
        if (!$order->getBuyerMessageRedacted()) {
            return;
        }

        $order
            ->setBuyerMessageRedacted(false)
            ->setBuyerMessage(
                $this->decrypt($order, $encryptedData)
            );
    }

    protected function restoreGiftWrap(OutputInterface $output, Order $order, GiftWrap $giftWrap): void
    {
        if (!$giftWrap->isGiftWrapRedacted()) {
            return;
        }

        $output->writeln(sprintf('Restoring redacted pii on gift wrap %d', $giftWrap->getId()));

        $encryptedData = $this->fetchGiftWrapEncryptedData($giftWrap->getId());
        if ($encryptedData === null) {
            $output->writeln(sprintf('    <error>Unable to find redacted pii for gift wrap %d</error>', $giftWrap->getId()));
            return;
        }

        try {
            $this->giftWrapStorage->save(
                $giftWrap
                    ->setGiftWrapRedacted(false)
                    ->setGiftWrapMessage($this->decrypt($order, $encryptedData))
            );
            $output->writeln(sprintf('    <info>Gift wrap %d has had pii information restored</info>', $giftWrap->getId()));
        } catch (NotModified $exception) {
            $output->writeln(sprintf('    <error>Gift wrap %d was not modified</error>', $giftWrap->getId()));
        }
    }

    protected function fetchGiftWrapEncryptedData(int $giftWrapId): ?string
    {
        $sql = 'SELECT `giftWrapMessage` FROM `giftWrap`';
        $where = (new Where())->equals('`id`', 's', $giftWrapId);

        return $this->mysqli->query(
            $sql . $where,
            $where->getWhereParameters(),
            function(\mysqli_result $result) {
                return $result->fetch_assoc()['giftWrapMessage'] ?? null;
            }
        );
    }
}