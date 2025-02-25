<?php

namespace App\Services;

use App\Models\SystemConfig;

class CompanyConfigService
{
    // private SystemConfig $systemConfig;

    // public function __construct(SystemConfig $systemConfig)
    // {
    //     $this->systemConfig = $systemConfig;
    // }

    public function getCompanyDetails(): array
    {
        return [
            'name' => null, //$this->systemConfig->receiptStr00()->firstOrFail()->value,
            'company_name' => null, //$this->systemConfig->receiptStr11()->firstOrFail()->value,
            'address' => [
                'street' => null, //$this->systemConfig->receiptStr22()->firstOrFail()->value,
                'city' => null,
                'postal_code' => null, //$this->systemConfig->receiptStr33()->firstOrFail()->value,
                'state' => null,
                'country' => null,
            ],
            'email' => null, //$this->systemConfig->email()->firstOrFail()->value,
            'phone_number' => null, //$this->systemConfig->receiptStr44()->firstOrFail()->value,
            'tax_number' => null, //$this->systemConfig->receiptStr55()->firstOrFail()->value,
            'company_number' => null,
        ];
    }
}