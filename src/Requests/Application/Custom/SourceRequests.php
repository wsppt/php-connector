<?php

namespace RIPS\Connector\Requests\Application\Custom;

use GuzzleHttp\RequestOptions;
use RIPS\Connector\Requests\BaseRequest;

class SourceRequests extends BaseRequest
{
    /**
     * Build the URI for the requests
     *
     * @param int $appId
     * @param int $customId
     * @param int $sourceId
     * @return string
     */
    protected function uri($appId, $customId, $sourceId = null)
    {
        return is_null($sourceId)
            ? "/applications/{$appId}/customs/{$customId}/sources"
            : "/applications/{$appId}/customs/{$customId}/sources/{$sourceId}";
    }

    /**
     * Get all sources for custom profile
     *
     * @param int $appId
     * @param int $customId
     * @param array $queryParams
     * @return \stdClass[]
     */
    public function getAll($appId, $customId, array $queryParams = [])
    {
        $response = $this->client->get($this->uri($appId, $customId), [
            'query' => $queryParams,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Get specific source for custom profile by id
     *
     * @param int $appId
     * @param int $customId
     * @param int $sourceId
     * @param array $queryParams
     * @return \stdClass
     */
    public function getById($appId, $customId, $sourceId, array $queryParams = [])
    {
        $response = $this->client->get($this->uri($appId, $customId, $sourceId), [
            'query' => $queryParams,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Create a new source for a custom profile
     *
     * @param int $appId
     * @param int $customId
     * @param array $input
     * @param array $queryParams
     * @return \stdClass
     */
    public function create($appId, $customId, array $input, array $queryParams = [])
    {
        $response = $this->client->post($this->uri($appId, $customId), [
            RequestOptions::JSON => ['source' => $input],
            'query' => $queryParams,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Update an source rule for a custom profile by id
     *
     * @param int $appId
     * @param int $customId
     * @param int $sourceId
     * @param array $input
     * @param array $queryParams
     * @return \stdClass
     */
    public function update($appId, $customId, $sourceId, array $input, array $queryParams = [])
    {
        $response = $this->client->patch($this->uri($appId, $customId, $sourceId), [
            RequestOptions::JSON => ['source' => $input],
            'query' => $queryParams,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Delete all sources for a custom profile
     *
     * @param int $appId
     * @param int $customId
     * @param array $queryParams
     * @return void
     */
    public function deleteAll($appId, $customId, array $queryParams = [])
    {
        $response = $this->client->delete($this->uri($appId, $customId), [
            'query' => $queryParams,
        ]);

        $this->handleResponse($response, true);
    }

    /**
     * Delete an source for a custom profile by id
     *
     * @param int $appId
     * @param int $customId
     * @param int $sourceId
     * @param array $queryParams
     * @return void
     */
    public function deleteById($appId, $customId, $sourceId, array $queryParams = [])
    {
        $response = $this->client->delete($this->uri($appId, $customId, $sourceId), [
            'query' => $queryParams,
        ]);

        $this->handleResponse($response, true);
    }
}
