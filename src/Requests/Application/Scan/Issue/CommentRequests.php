<?php

namespace RIPS\Connector\Requests\Application\Scan\Issue;

use RIPS\Connector\Requests\BaseRequest;

class CommentRequests extends BaseRequest
{
    /**
     * Build a uri for the request
     *
     * @param int $appId
     * @param int $scanId
     * @param int $issueId
     * @param int $commentId
     * @return string
     */
    protected function uri($appId, $scanId, $issueId, $commentId = null)
    {
        return is_null($commentId)
            ? "/applications/{$appId}/scans/{$scanId}/issues/{$issueId}/comments"
            : "/applications/{$appId}/scans/{$scanId}/issues/{$issueId}/comments/{$commentId}";
    }

    /**
     * Get all comments for an issue
     *
     * @param int $appId
     * @param int $scanId
     * @param int $issueId
     * @param array $queryParams
     * @return \stdClass
     */
    public function getAll($appId, $scanId, $issueId, array $queryParams = [])
    {
        $response = $this->client->get($this->uri($appId, $scanId, $issueId), [
            'query' => $queryParams,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Get comment for issue by id
     *
     * @param int $appId
     * @param int $scanId
     * @param int $issueId
     * @param int $commentId
     * @return \stdClass
     */
    public function getById($appId, $scanId, $issueId, $commentId)
    {
        $response = $this->client->get($this->uri($appId, $scanId, $issueId, $commentId));

        return $this->handleResponse($response);
    }

    /**
     * Create a new comment for an issue
     *
     * @param int $appId
     * @param int $scanId
     * @param int $issueId
     * @param array $input
     * @return \stdClass
     */
    public function create($appId, $scanId, $issueId, array $input = [])
    {
        $response = $this->client->post($this->uri($appId, $scanId, $issueId), [
            'form_params' => ['comment' => $input],
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Delete all comments for an issue
     *
     * @param int $appId
     * @param int $scanId
     * @param int $issueId
     * @return void
     */
    public function deleteAll($appId, $scanId, $issueId)
    {
        $this->client->delete($this->uri($appId, $scanId, $issueId));
    }

    /**
     * Delete comment for an issue by id
     *
     * @param int $appId
     * @param int $scanId
     * @param int $issueId
     * @param int $commentId
     * @return void
     */
    public function deleteById($appId, $scanId, $issueId, $commentId)
    {
        $this->client->delete($this->uri($appId, $scanId, $issueId, $commentId));
    }
}
