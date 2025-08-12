<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Epc_Code extends BaseConfig
{
    /**
     * GS1 company prefix *digits only* (6–12 digits, no check digit).
     * Example: 120123  (→ 6-digit prefix, Partition = 6, CompanyBits = 20)
     */
    public string $companyPrefix = '800839';

    /**
     * Three-bit filter value (000 = “all others” is fine for most cases).
     * If you segregate pallets/cases/items differently, change this.
     */
    public int $filter = 0;
}
