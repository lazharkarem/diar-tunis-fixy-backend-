<?php
namespace App\Enums;

enum UserType: string
{
    case GUEST = 'guest';
    case HOST = 'host';
    case SERVICE_CUSTOMER = 'service_customer';
    case SERVICE_PROVIDER = 'service_provider';
    case ADMIN = 'admin';
    
    // You can keep your helper methods if needed
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}