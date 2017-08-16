<?php

namespace RIPS\Connector\Requests\Application;

use RIPS\Connector\Requests\BaseRequest;

class ScanRequests extends BaseRequest
{
    /** @var string */
    protected $uri = '/applications';

    /**
     * Get all scans (independent of the application)
     *
     * @param array $queryParams
     * @return \stdClass[]
     */
    public function getAll(array $queryParams = [])
    {
        $response = $this->client->get("{$this->uri}/scans/all", [
            'query' => $queryParams,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Get a scan by application ID and scan ID
     *
     * @param int $applicationId
     * @param int $scanId
     * @return \stdClass
     */
    public function getById(int $applicationId, int $scanId)
    {
        $response = $this->client->get("{$this->uri}/{$applicationId}/scans/{$scanId}");

        return $this->handleResponse($response);
    }

    /**
     * Get scan statistics for a single scan
     *
     * @param int $applicationId
     * @param int $scanId
     * @return \stdClass
     */
    public function getStatsById(int $applicationId, int $scanId)
    {
        $response = $this->client->get("{$this->uri}/{$applicationId}/scans/stats", [
            'query' => ['equal[id]' => $scanId],
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Start a new scan
     *
     * @param int $applicationId
     * @param array $input
     * @return \stdClass
     */
    public function scan(int $applicationId, array $input)
    {
        $response = $this->client->post("{$this->uri}/{$applicationId}/scans", [
            'form_params' => ['scan' => $input],
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Block until scan is finished
     *
     * @param int $applicationId
     * @param int $scanId
     * @param int $waitTime - Optional time to wait, will wait indefinitely if 0 (default: 0)
     * @param int $sleepTime - Time to wait between scan completion checks (default: 5)
     * @return void
     */
    public function blockUntilDone(
        int $applicationId,
        int $scanId,
        int $waitTime = 0,
        int $sleepTime = 5
    ) {
        for ($iteration = 0;; $iteration++) {
            $scan = $this->getById($applicationId, $scanId);

            if ((int) $scan->phase === 0 && (int) $scan->percent === 100) {
                break;
            } else if ($waitTime > 0 && $iteration > ($waitTime / $sleepTime)) {
                throw new \Exception('Scan did not finish before the defined wait time.');
            }

            sleep($sleepTime);
        }
    }
}
