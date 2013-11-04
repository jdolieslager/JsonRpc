<?php
namespace Jdolieslager\JsonRpc\ProtocolLayer;

use Jdolieslager\JsonRpc\Common\ArrayUtils;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  ProtocolLayer
 */
abstract class AbstractEncrpytion implements ProtocolLayerInterface
{
    /**
     * @var ProtocolLayerInterface | NULL
     */
    protected $upperLayer;

    /**
     * @var ProtocolLayerInterface | NULL
     */
    protected $lowerLayer;

    /**
     * The cipher used for encryption
     *
     * @var const (MCRYPT_ciphername)
     */
    protected $cipher;

    /**
     * Private key for encrpytion
     *
     * @var string
     */
    protected $key;


    /**
     * MCRYPT_MODE_modename
     *
     * @var const
     */
    protected $mode;

    /**
     * IV Size
     *
     * @var integer
     */
    protected $ivSize;

    /**
     * Constructor set settings for encryption
     *
     * @param array $config array (
     *      'key' => string,
     *      'cipher' => MCRYPT_ciphername,
     *      'mode' => MCRYPT_MODE_modename
     * )
     * @return Encryption
     */
    public function __construct(array $config = array())
    {
        // Set general settings
        $this->key    = ArrayUtils::arrayTarget('key', $config, 'mykey');
        $this->cipher = ArrayUtils::arrayTarget('cipher', $config, MCRYPT_RIJNDAEL_256);
        $this->mode   = ArrayUtils::arrayTarget('mode', $config, MCRYPT_MODE_CFB);

        // Calculate IV Size
        $this->ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
    }

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        // base64 decode
        $data = base64_decode($data);

        // get iv
        $iv      = substr($data, 0, $this->ivSize);

        // get encoded data
        $data = substr($data, $this->ivSize);

        // Decrypt the data
        $data  = mcrypt_decrypt(
            $this->cipher,
            $this->key,
            $data,
            $this->mode,
            $iv
        );

        // return decoded data
        return $data;
    }

    /**
     * Encrypt data
     *
     * @param  string $data
     * @return string
     */
    public function encrypt($data)
    {
        // Create IV
        $iv        = mcrypt_create_iv($this->ivSize, MCRYPT_DEV_URANDOM);

        // Encode DATA
        $data  = mcrypt_encrypt($this->cipher, $this->key, $data, $this->mode, $iv);

        // Base64 + append IV for decoding
        $data  = base64_encode($iv . $data);

        // return encoded response
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setLowerLayer(ProtocolLayerInterface $layer = null)
    {
        $this->lowerLayer = $layer;
    }

    /**
     * {@inheritdoc}
     */
    public function getLowerLayer()
    {
        return $this->lowerLayer;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpperLayer(ProtocolLayerInterface $layer = null)
    {
        $this->upperLayer = $layer;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpperLayer()
    {
        return $this->upperLayer;
    }
}