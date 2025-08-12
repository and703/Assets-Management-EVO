<?php

/**
 * Encode a numeric GIAI-96 EPC per TDS v1.13.
 *
 * @param string $assetNumber  ERP/Excel asset number (digits only).
 * @param int    $pieceIndex   1-based index within Quantity.
 *
 * @return string 24-hex-char EPC (e.g. 33000F123456789ABCDEF012)
 */
function encode_giai96(string $assetNumber, int $pieceIndex): string
{
    /**
     * GS1 company prefix *digits only* (6–12 digits, no check digit).
     * Example: 120123  (→ 6-digit prefix, Partition = 6, CompanyBits = 20)
     */
    $companyPrefix = '800839';

    /**
     * Three-bit filter value (000 = “all others” is fine for most cases).
     * If you segregate pallets/cases/items differently, change this.
     */
    $filter = 0;

    // ---------- constants ----------
    $HEADER     = 0x33;              // 8 bits  (0011 0011)  GIAI-96 numeric
    $PARTITION  = 6;                 // 3 bits  → 6-digit company prefix
    $COMP_BITS  = 20;                // table value for Partition 6
    $ASSET_BITS = 62;                // 96-14-COMP_BITS
    // --------------------------------

    // Validate
    if (!ctype_digit($companyPrefix) || strlen($companyPrefix) !== 6) {
        throw new InvalidArgumentException(
            'companyPrefix must be exactly 6 digits for Partition 6'
        );
    }
    if (!ctype_digit($assetNumber)) {
        throw new InvalidArgumentException('assetNumber must be numeric');
    }

    // Build numeric “individual asset reference”.
    // Here we concatenate assetNumber + pieceIndex (zero-padded to 3).
    // Feel free to invent your own scheme as long as it stays numeric ≤ 62 bits.
    $assetRefNumeric = $assetNumber . str_pad((string)$pieceIndex, 3, '0', STR_PAD_LEFT);
    if (strlen($assetRefNumeric) > 18) {         // 18 digits ≈ 60 bits
        throw new LengthException('Asset reference too long for GIAI-96');
    }

    // ---------- pack into binary string ----------
    $bin =
        str_pad(decbin($HEADER),          8, '0', STR_PAD_LEFT) .
        str_pad(decbin($filter),     3, '0', STR_PAD_LEFT) .
        str_pad(decbin($PARTITION),       3, '0', STR_PAD_LEFT) .
        str_pad(decbin((int)$companyPrefix), $COMP_BITS,  '0', STR_PAD_LEFT) .
        str_pad(decbin((int)$assetRefNumeric),    $ASSET_BITS, '0', STR_PAD_LEFT);

    // ---------- binary ➜ hex (24 chars) ----------
    $hex = '';
    for ($i = 0; $i < 96; $i += 4) {
        $hex .= dechex(bindec(substr($bin, $i, 4)));
    }
    return strtoupper($hex);
}
