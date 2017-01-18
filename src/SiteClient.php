<?php
// ex: ts=4 sts=4 sw=4 et:

namespace Lockr;

/**
 * API for site management operations.
 */
class SiteClient
{
    /**
     * @var Lockr The external interface.
     */
    protected $client;

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
     * Checks if the current site/env is registered and/or available.
     *
     * @return bool[] Returns a two-value array of booleans:
     *
     * - True if the site is registered with Lockr.
     * - True if the current env is available.
     *
     * @throws ServerException
     * if the server is unavailable or returns an error.
     * @throws ClientException if there was an unexpected client error.
     */
    public function exists()
    {
        $body = $this->client->get('/v1/site/exists');

        return $body + array(
            'cert_valid' => false,
            'exists' => false,
            'available' => false,
            'has_cc' => false,
        );
    }

    /**
     * Registers the site with Lockr.
     *
     * @param string $email The email to register with.
     * @param string $pass  (optional) The password for authentication.
     * @param string $name  (optional) The site name.
     *
     * @throws ServerException
     * if the server is unavailable or returns an error.
     * @throws ClientException if there was an unexpected client error.
     */
    public function register($email, $pass = null, $name = null) {
        $data = array(
            'email' => $email,
            'name' => $name,
        );

        if (null !== $pass) {
            $auth = "$email:$pass";
        } else {
            $auth = null;
        }

        $this->client->post('/v1/site/register', $data, $auth);
    }
}
