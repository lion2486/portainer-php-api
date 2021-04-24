<?php

namespace Mangati\Portainer;

use Mangati\Api\Client as BaseClient;
use Mangati\Api\Path;

/**
 * Portainer Client
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Client
{
    /**
     * @var string
     */
    public $authToken;

    private $client;

    public function __construct(string $endpoint)
    {
        $this->client = new BaseClient($endpoint);
    }

    /**
     * Auth alias
     * @param string $user
     * @param string $pass
     * @throws Exception
     */
    public function login(string $user, string $pass)
    {
        return $this->auth($user, $pass);
    }

    /**
     * Authenticate against Portainer HTTP API
     * @param string $user
     * @param string $pass
     * @throws Exception
     */
    public function auth(string $user, string $pass)
    {
        $data = [
            'Username' => $user,
            'Password' => $pass,
        ];

        $json = $this->client->request('POST', 'auth', $data);
        $this->authToken = $json['jwt'];
        $this->client->session()->headers[] = 'Authorization: Bearer ' . $this->authToken;
    }

    /**
     * Set authentication token for Portainer HTTP API
     * @param string $authToken
     * @throws Exception
     */
    public function setAuthToken(string $authToken)
    {
        $this->authToken = $authToken;
        foreach ($this->client->session()->headers as $key => $header)
        {
            if (strpos($header, "Authorization: Bearer") === 0)
            {
                unset($this->client->session()->headers[$key]);
            }
        }

        $this->client->session()->headers[] = 'Authorization: Bearer ' . $this->authToken;
    }

    /**
     * Docker registries API
     * @return Path
     */
    public function registries(): Path
    {
        $path = $this->client->createPath('registries');

        return $path;
    }

    /**
     * Docker environments API
     * @return Path
     */
    public function endpoints(): Path
    {
        $path = $this->client->createPath('endpoints');

        return $path;
    }

    /**
     * Docker API
     * @param int $endpointId
     * @return Path
     */
    public function dockerInfo(int $endpointId): array
    {
        $info = $this->client->request('GET', "endpoints/{$endpointId}/docker/info", [], $this->client->session()->headers);

        return $info;
    }

    public function dockerContainers(int $endpointId): array
    {
        $info = $this->client->request('GET', "endpoints/{$endpointId}/docker/containers/json?all=1", [], $this->client->session()->headers);

        return $info;
    }

    public function dockerContainersRunning(int $endpointId): array
    {
        $info = $this->client->request('GET', "endpoints/{$endpointId}/docker/containers/json?all=0", [], $this->client->session()->headers);

        return $info;
    }

    public function dockerContainerInfo(int $endpointId, string $containerId): array
    {
        $info = $this->client->request('GET',"endpoints/{$endpointId}/docker/containers/{$containerId}/json", [], $this->client->session()->headers);

        return $info;
    }

    public function dockerContainerStats(int $endpointId, string $containerId): array
    {
        $info = $this->client->request('GET',"endpoints/{$endpointId}/docker/containers/{$containerId}/stats?stream=false", [], $this->client->session()->headers);

        return $info;
    }

    public function dockerContainerCommand(int $endpointId, string $containerId, string $command, array $options = []): array
    {
        //api/endpoints/1/docker/containers/e2b25ff4953f7ac322478fcc6dc46fbbf242a3f2ad85ed1c08f3154e5e9944cb/logs?since=0&stderr=1&stdout=1&tail=100&timestamps=0
        $info = $this->client->request('POST',"endpoints/{$endpointId}/docker/containers/{$containerId}/{$command}", $options, $this->client->session()->headers, true);

        return $info;
    }

    /**
     * Docker stack Containers
     */
    public function stackContainers(int $endpointId, string $stackName): array
    {
        // endpoints/1/docker/containers/json?all=1&filters=%7B%22label%22:%5B%22com.docker.compose.project%3Dwordpress%22%5D%7D
        // {"label":["com.docker.compose.project=wordpress"]}
        $filters = [
            "all" => 1,
            "stream" => false,
            "filters" => "{\"label\":[\"com.docker.compose.project={$stackName}\"]}"
        ];

        $info = $this->client->request('GET',"endpoints/{$endpointId}/docker/containers/json", $filters, $this->client->session()->headers);

        return $info;
    }

    /**
     * Docker stacks API
     * @return Path
     */
    public function stacks(): Path
    {
        $path = $this->client->createPath("stacks");

        return $path;
    }

    /**
     * Users API
     * @return Path
     */
    public function users(): Path
    {
        $path = $this->client->createPath('users');

        return $path;
    }

    /**
     * User memberships API
     * @param int $userId
     * @return Path
     */
    public function userMemberships(int $userId): Path
    {
        $path = $this->client->createPath("users/{$userId}/memberships");

        return $path;
    }

    /**
     * Teams API
     * @return Path
     */
    public function teams(): Path
    {
        $path = $this->client->createPath('teams');

        return $path;
    }

    /**
     * Team memberships API
     * @param int $userId
     * @return Path
     */
    public function teamMemberships(int $teamId): Path
    {
        $path = $this->client->createPath("teams/{$teamId}/memberships");

        return $path;
    }
}

