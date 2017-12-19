<?php

namespace RIPS\Connector;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use RIPS\Connector\Exceptions\ClientException;
use RIPS\Connector\Requests\ApplicationRequests;
use RIPS\Connector\Requests\LicenseRequests;
use RIPS\Connector\Requests\LogRequests;
use RIPS\Connector\Requests\OAuth2\AccessTokenRequest;
use RIPS\Connector\Requests\OAuth2Requests;
use RIPS\Connector\Requests\OrgRequests;
use RIPS\Connector\Requests\QuotaRequests;
use RIPS\Connector\Requests\SettingsRequests;
use RIPS\Connector\Requests\SourceRequests;
use RIPS\Connector\Requests\StatusRequests;
use RIPS\Connector\Requests\TeamRequests;
use RIPS\Connector\Requests\UserRequests;

class API
{
    /**
     * @var string
     */
    protected $version = '2.9.0';

    /**
     * @var ApplicationRequests
     */
    public $applications;

    /**
     * @var LicenseRequests
     */
    public $licenses;

    /**
     * @var LogRequests
     */
    public $logs;

    /**
     * @var OrgRequests
     */
    public $orgs;

    /**
     * @var QuotaRequests
     */
    public $quotas;

    /**
     * @var SettingsRequests
     */
    public $settings;

    /**
     * @var SourceRequests
     */
    public $sources;

    /**
     * @var StatusRequests
     */
    public $status;

    /**
     * @var TeamRequests
     */
    public $teams;

    /**
     * @var UserRequests
     */
    public $users;

    /**
     * @var OAuth2Requests
     */
    public $oauth2;

    /**
     * @var array - Config values for GuzzleClient
     */
    protected $clientConfig = [
        'base_uri' => 'http://localhost:8080',
        'timeout' => 100,
        'connect_timeout' => 10,
        'http_errors' => false,
    ];

    /**
     * Construct and optionally initialize new API
     * Initialization is only done if both username and password are specified.
     *
     * @param string $username
     * @param string $password
     * @param array $clientConfig
     */
    public function __construct($username = null, $password = null, array $clientConfig = [])
    {
        if ($username && $password) {
            $this->initialize($username, $password, $clientConfig);
        }
    }

    /**
     * Initialize new API
     * Separation from the constructor is required because in some cases the information are not yet known when
     * the object is constructed.
     *
     * @param string $username
     * @param string $password
     * @param array $clientConfig
     */
    public function initialize($username, $password, array $clientConfig = [])
    {
        $mergedConfig = array_merge(
            $this->clientConfig,
            $clientConfig,
            [
                'headers' => [
                    'User-Agent' => "RIPS-API-Connector/{$this->version}",
                ],
            ],
            [
                'headers' => $this->getAuthHeaders($username, $password, $clientConfig)
            ]
        );

        $client = new Client($mergedConfig);
        $this->applications = new ApplicationRequests($client);
        $this->licenses = new LicenseRequests($client);
        $this->logs = new LogRequests($client);
        $this->orgs = new OrgRequests($client);
        $this->quotas = new QuotaRequests($client);
        $this->settings = new SettingsRequests($client);
        $this->sources = new SourceRequests($client);
        $this->status = new StatusRequests($client);
        $this->teams = new TeamRequests($client);
        $this->users = new UserRequests($client);
        $this->oauth2 = new OAuth2Requests($client);
    }

    /**
     * Get the current version number
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the authentication headers
     *
     * @param string $username
     * @param string $password
     * @param array $clientConfig
     * @return array
     * @throws ClientException
     */
    private function getAuthHeaders($username, $password, $clientConfig)
    {
        if (!array_key_exists('oauth2', $clientConfig)) {
            return [
                'X-API-Username' => $username,
                'X-API-Password' => $password
            ];
        }

        $oauth2Config = $clientConfig['oauth2'];
        $accessToken = array_key_exists('access_token', $oauth2Config) ? $oauth2Config["access_token"] : "";
        if (empty($accessToken)) {
            $accessToken = $this->getAccessToken($username, $password, $clientConfig);
        }

        if (!$this->isAccessTokenValid($clientConfig, $accessToken)) {
            throw new ClientException('Cannot find/create valid token');
        }

        return [
            'Authorization' => "Bearer {$accessToken}"
        ];
    }

    /**
     * Gets an access token either from config, from disk or by requesting a new one
     *
     * @param $username
     * @param $password
     * @param $clientConfig
     * @return string|null
     */
    private function getAccessToken($username, $password, $clientConfig)
    {
        $oauth2Config = $clientConfig['oauth2'];
        $accessToken = null;

        // If there is a token file, try to read it and test the found token
        try {
            $filePath = array_key_exists('token_file_path', $oauth2Config) && !empty($oauth2Config['token_file_path'])
                ? $oauth2Config['token_file_path'] : '';

            if (file_exists($filePath)) {
                $data = file_get_contents($filePath);

                if (!empty($data)) {
                    $accessToken = (json_decode($data))->access_token;

                    return $accessToken;
                }
            }
        } catch (\Exception $e) {
        }

        return $this->createAccessToken($username, $password, $clientConfig);
    }

    /**
     * Checks if the given access token is valid
     *
     * @param $clientConfig
     * @param $accessToken
     * @return bool
     */
    private function isAccessTokenValid($clientConfig, $accessToken)
    {
        if (is_null($accessToken)) {
            return false;
        }

        $mergedConfig = array_merge($this->clientConfig, $clientConfig, [
            'headers' => [
                'User-Agent' => "RIPS-API-Connector/{$this->version}",
                'Authorization' => "Bearer {$accessToken}",
            ],
        ]);
        $request = new StatusRequests(new Client($mergedConfig));

        return $request->isLoggedIn();
    }

    /**
     * Creates an access token by requesting it from the server
     *
     * @param $username
     * @param $password
     * @param $clientConfig
     * @return string
     * @throws ClientException
     * @throws Exception
     */
    private function createAccessToken($username, $password, $clientConfig)
    {
        $oauth2Config = $clientConfig['oauth2'];
        if (!array_key_exists('client_id', $oauth2Config)) {
            throw new ClientException('Cannot create new oauth token without client id');
        }

        $mergedConfig = array_merge($this->clientConfig, $clientConfig, [
            'headers' => [
                'User-Agent' => "RIPS-API-Connector/{$this->version}",
            ],
            RequestOptions::JSON => [
                'grant_type' => 'password',
                'client_id' => $oauth2Config['client_id'],
                'username' => $username,
                'password' => $password
            ]
        ]);

        $request = new AccessTokenRequest(new Client($mergedConfig));
        $tokens = $request->getTokens();

        if (isset($oauth2Config['store_token']) && $oauth2Config['store_token'] === true) {
            if (!array_key_exists('token_file_path', $oauth2Config) || empty($oauth2Config['token_file_path'])) {
                throw new Exception('Token path is needed to store token.');
            }
            $filePath = $oauth2Config['token_file_path'];
            file_put_contents($filePath, json_encode($tokens));
        }

        return $tokens->access_token;
    }
}
