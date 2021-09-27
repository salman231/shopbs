<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Encryption;

class Encryptor implements EncryptorInterface
{
    /**
     * @param string $data
     * @return string
     */
    public function getMd5Hash(string $data)
    {
        return hash(self::HASH_VERSION_MD5, $data);
    }
}
