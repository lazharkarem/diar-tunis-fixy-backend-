<?php
namespace App\Enums;

class UserType
{
    const GUEST = 'guest';
    const HOST = 'host';
    const SERVICE_CUSTOMER = 'service_customer';
    const SERVICE_PROVIDER = 'service_provider';
    const ADMIN = 'admin';

    public static function values(): array
    {
        return [
            self::GUEST,
            self::HOST,
            self::SERVICE_CUSTOMER,
            self::SERVICE_PROVIDER,
            self::ADMIN
        ];
    }

    public static function names(): array
    {
        return [
            'GUEST',
            'HOST',
            'SERVICE_CUSTOMER',
            'SERVICE_PROVIDER',
            'ADMIN'
        ];
    }
}
