<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusShipmentExportPlugin\Model;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Currency\Converter\CurrencyConverter;
use Sylius\Component\Payment\Model\PaymentInterface;

class GeisShipmentExporter implements ShipmentExporterInterface
{
    /** @var CurrencyConverter */
    private $currencyConverter;

    public function __construct(
        CurrencyConverter $currencyConverter
    ) {
        $this->currencyConverter = $currencyConverter;
    }

    private function convert(int $amount, string $sourceCurrencyCode, string $targetCurrencyCode): int
    {
        return $this->currencyConverter->convert($amount, $sourceCurrencyCode, $targetCurrencyCode);
    }

    public function getShippingMethodsCodes(): array
    {
        return ['geis-cz', 'geis-sk', 'geis-eu', 'geis-other'];
    }

    public function getRow(ShipmentInterface $shipment, array $questionsArray): array
    {
        $order = $shipment->getOrder();
        assert($order !== null);
        $address = $order->getShippingAddress();
        assert($address !== null);
        $customer = $order->getCustomer();
        assert($customer !== null);

        $days_offset = (int) $questionsArray['days_offset'];

        $payment = $order->getPayments()->first();
        assert($payment instanceof PaymentInterface);
        $paymentMethod = $payment->getMethod();
        assert($paymentMethod instanceof PaymentMethodInterface);
        assert($paymentMethod->getGatewayConfig() !== null);
        $isCashOnDelivery = $paymentMethod->getGatewayConfig()->getFactoryName() === 'offline';

        $currencyCode = $order->getCurrencyCode();
        assert($currencyCode !== null);
        if ($address->getCountryCode() === 'CZ') {
            $totalAmount = $this->convert($order->getTotal(), $currencyCode, 'CZK');
        } elseif ($address->getCountryCode() === 'SK') {
            $totalAmount = $this->convert($order->getTotal(), $currencyCode, 'EUR');
        } elseif ($address->getCountryCode() === 'PL') {
            $totalAmount = $this->convert($order->getTotal(), $currencyCode, 'PLN');
        } elseif ($address->getCountryCode() === 'HU') {
            $totalAmount = $this->convert($order->getTotal(), $currencyCode, 'HUF');
        } elseif ($address->getCountryCode() === 'RO') {
            $totalAmount = $this->convert($order->getTotal(), $currencyCode, 'RON');
        } else {
            $totalAmount = null;
        }

        if ($totalAmount !== null) {
            $totalAmount = number_format(
                $totalAmount / 100,
                0,
                '.',
                ''
            );
        }

        $weight = 0;
        foreach ($order->getItems() as $item) {
            /** @var OrderItemInterface $item */
            $variant = $item->getVariant();
            if ($variant !== null) {
                $weight += $variant->getWeight();
            }
        }

        $array = [
            /* 1    ????slo dokladu */
            $order->getNumber(),

            /* 2    P????jemce - N??zev */
            $address->getCompany() ?? $address->getFullName(),

            /* 3    P????jemce - St??t */
            $address->getCountryCode(),

            /* 4    P????jemce - M??sto */
            $address->getCity(),

            /* 5    P????jemce - Ulice */
            $address->getStreet(),

            /* 6    P????jemce - PS?? */
            $address->getPostcode(),

            /* 7    P??ijemce - kontaktn?? osoba */
            $address->getFullName(),

            /* 8    P????jemce - kontaktn?? email */
            $customer->getEmail(),

            /* 9    P??ijemce - kontaktn?? telefon */
            $address->getPhoneNumber(),

            /* 10    Datum svozu */
            date('d.m.Y', time() + ($days_offset * 24 * 60 * 60)),

            /* 11    Reference */
            $order->getNumber(),

            /* 12    EXW */
            '',

            /* 13    Dob??rka (COD) */
            $isCashOnDelivery ? '1' : '0',

            /* 14    Hodnota dob??rky */
            $isCashOnDelivery
                ? $totalAmount ?? 'unsuported'
                : '',

            /* 15    Variabiln?? symbol */
            $order->getNumber(),

            /* 16    Hmotnost */
            $weight,

            /* 17    Objem */
            '',

            /* 18    Po??et */
            1,

            /* 19    Popis zbo???? */
            '',

            /* 20    Druh obalu (zkratka) */
            '',

            /**
             * 21    Typ z??silky
             *
             * 0 - cargo, 1 - parcel (bal??k)
             */
            1,

            /**
             * 22. Pozna??mka pro pr??i??jemce (volitelne??, nemusi?? by??t vyplne??no, pr??i pouz??iti?? VM viz bod 48)
             */
            '',

            /**
             * 23. Pozna??mka pro r??idic??e (volitelne??, nemusi?? by??t vyplne??no)
             */
            '',

            /**
             * 24. Pr??ipojis??te??ni?? (ano - 1, ne - 0)
             */
            0,

            /**
             * 25. Hodnota pr??ipojis??te??ni??
             * (o pr??ipojis??te??ni?? za??silky nad 500.000 CZK je tr??eba poz??a??dat na stra??nka??ch www.geis-group . cz o nadlimitni?? pojis??te??ni??)
             */
            0,

            /**
             * 26. Avi??zo doruc??ene?? za??silky (ano - 1, ne - 0)
             */
            0,

            /**
             * 27. Avi??zo doruc??ene?? za??silky (telefonni?? c??i??slo nebo E-mail)
             */
            $customer->getEmail(),

            /**
             * 28. Avi??zo pos??kozene?? za??silky (ano - 1, ne - 0)
             */
            0,

            /**
             * 29. Avi??zo pos??kozene?? za??silky (telefonni?? c??i??slo nebo E-mail)
             */
            $customer->getEmail(),

            /**
             * 30. Avi??zo proble??move?? za??silky (ano - 1, ne - 0)
             */
            0,

            /**
             * 31. Avi??zo proble??move?? za??silky (telefonni?? c??i??slo nebo E-mail)
             */
            $customer->getEmail(),

            /**
             * 32    Slu??ba - B2C (soukrom?? adresa) (B2C) ( 0 nebo pr??zdn?? - ne, 1 - ano)
             */
            1,

            /**
             * 33    Slu??ba - Doru??en?? do 12:00 hodin (D12) ( 0 nebo pr??zdn?? - ne, 1 - ano)
             */
            null,

            /**
             * 34    Slu??ba - Garantovan?? doru??en?? (GDO)( 0 nebo pr??zdn?? - ne, 1 - ano)
             */
            null,

            /**
             * 35    Slu??ba - POD   (0 nebo pr??zdn?? - ne, 1 - ano)
             */
            null,

            /**
             * 36    Slu??ba - email pro POD
             */
            null,

            /**
             * 37    Slu??ba - SMS av??zo   ( 0 nebo pr??zdn?? - ne, 1 - ano)
             */
            null,

            /**
             * 38    Slu??ba - Telefonick?? av??zo  ( 0 nebo pr??zdn?? - ne, 1 - ano)
             */
            null,

            /**
             * 39    P????jemce - ????slo popisn??
             * ????slo ulice je mo??n?? zadat do pole P????jemce - Ulice
             */
            null,

            /**
             * 40    P????jemce - ????slo orienta??n??
             * ????slo ulice je mo??n?? zadat do pole P????jemce - Ulice
             */
            null,

            /**
             * 41    CrossDock - Jm??no (pouze pro CD)
             */
            null,

            /**
             * 42    CrossDock - Typ CD svozu (1 - vlastn??, 2 - Geis)
             */
            null,

            /**
             * 43    CrossDock - ????slo auta - vl.dod??n??
             */
            null,

            /**
             * 44    CrossDock - Datum dod??n?? (pouze pro CD)
             */
            null,

            /**
             * 45    Slu??ba - Email p????jemci (0 nebo pr??zdn?? - ne, 1 - ano)
             */
            1,
        ];

        for ($i = 0; $i < count($array); ++$i) {
            if ($array[$i] !== null) {
                $array[$i] = iconv('UTF-8', 'WINDOWS-1250', (string) $array[$i]);
            }
        }

        return $array;
    }

    public function getDelimiter(): string
    {
        return ';';
    }

    public function getQuestionsArray(): ?array
    {
        $question[] = new Question('days_offset', 'Zadejte po??et dn?? do svozu (0-9).', '0', '/^[0-9]?$/');

        return $question;
    }

    public function getHeaders(): ?array
    {
        return null;
    }
}
