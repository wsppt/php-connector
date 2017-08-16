<?php

namespace RIPS\Connector\Requests\Application\Scan;

use RIPS\Connector\Requests\BaseRequest;

class IssueRequests extends BaseRequest
{
    // @var string
    protected $uri = '/applications';

    /**
     * Get all issues within a scan
     *
     * @param int $applicationId
     * @param int $scanId
     * @param array $queryParams
     * @return array
     */
    public function getAll(int $applicationId, int $scanId, array $queryParams = [])
    {
        $response = $this->client->get("{$this->uri}/{$applicationId}/scans/{$scanId}/issues", [
            'query' => $queryParams,
        ]);

        return $this->handleResponse($response);
    }
}
