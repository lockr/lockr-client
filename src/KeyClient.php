<?php
// ex: ts=4 sts=4 sw=4 et:

namespace Lockr;

class KeyClient
{
    /**
     * @var Lockr The external interface.
     */
    protected $client;

    /**
     * @var string|bool Data to decrypt keys.
     */
    protected $encoded = null;

    /**
     * Constructs a SiteClient.
     *
     * @param Lockr $client The external interface.
     */
    public function __construct(Lockr $client)
    {
        $this->client = $client;
    }

    /**
     * Gets and sets encrypted keys.
     *
     * @param string $encoded (optional) Data to decrypt keys.
     *
     * @return self The client for method chaining.
     */
    public function encrypted($encoded = true)
    {
        $this->encoded = $encoded;
        return $this;
    }

    /**
     * Gets a key from Lockr.
     *
     * @param string $name The key name.
     *
     * @return string The key.
     */
    public function get($name)
    {
        $body = $this->client->get($this->uri($name));

        if (null !== $this->encoded) {
            return $this->decrypt($body['key_value'], $this->encoded);
        }
        return $body['key_value'];
    }

    /**
     * Creates a key in Lockr.
     *
     * @param int $key_size The size of key to create.
     *
     * @return string Returns the created key value.
     */
    public function create($key_size)
    {
        $body = $this->client->get(
            "/v1/generate-key?key_size={$key_size}",
            $data
        );

        return $body['key_value'];
    }

    /**
     * Sets a key in Lockr.
     *
     * @param string $name The key name.
     * @param string $value The key value.
     * @param string $label The key label.
     *
     * @return string Returns the decrypt data or true.
     */
    public function set($name, $value, $label, $encoded = null)
    {
        if ($this->encoded) {
            if ($encoded === NULL) {
                list($value, $encoded) = $this->encrypt($value);
            } else {
                list($value, $encoded) = $this->reencrypt($value, $encoded);
            }
        }
        $data = array(
            'key_value' => $value,
            'key_label' => $label,
        );
        $this->client->patch($this->uri($name), $data);

        if ($this->encoded) {
            return $encoded;
        }
        return true;
    }

    /**
     * Deletes a key from Lockr.
     *
     * @param string $name The key name.
     */
    public function delete($name)
    {
        $this->client->delete($this->uri($name));
    }

    protected function uri($name)
    {
        return '/v1/key/'.urlencode($name);
    }

    protected function reencrypt($plaintext, $encoded)
    {
        list($cipher, $mode, $iv, $key) = $this->decode($encoded);
        $ciphertext = mcrypt_encrypt($cipher, $key, $plaintext, $mode, $iv);
				$ciphertext = base64_encode($ciphertext);
        $encoded = $this->encode($cipher, $mode, $iv, $key);
        return array($ciphertext, $encoded);
    }

    protected function encrypt($plaintext)
    {
        $cipher = MCRYPT_RIJNDAEL_256;
        $mode = MCRYPT_MODE_CBC;
        
        $key = openssl_random_pseudo_bytes(32);
        $iv_len = mcrypt_get_iv_size($cipher, $mode);
        $iv = mcrypt_create_iv($iv_len);

        $ciphertext = mcrypt_encrypt($cipher, $key, $plaintext, $mode, $iv);
        $ciphertext = base64_encode($ciphertext);
        $encoded = $this->encode($cipher, $mode, $iv, $key);

        return array($ciphertext, $encoded);
    }

    protected function decrypt($ciphertext, $encoded)
    {
        list($cipher, $mode, $iv, $key) = $this->decode($encoded);
        $ciphertext = base64_decode($ciphertext);

        $key = mcrypt_decrypt($cipher, $key, $ciphertext, $mode, $iv);

        return trim($key);
    }

    protected function encode($cipher, $mode, $iv, $key)
    {
        $parts = array(
            $cipher,
            $mode,
            base64_encode($iv),
            base64_encode($key),
        );

        return implode('$', $parts);
    }

    protected function decode($encoded)
    {
        list($cipher, $mode, $iv, $key) = explode('$', $encoded, 4);
        $iv = base64_decode($iv);
        $key = base64_decode($key);
        return array($cipher, $mode, $iv, $key);
    }
}
