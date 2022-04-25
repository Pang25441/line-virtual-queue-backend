<?php

namespace App\Http\Services;

use App\Models\Line\LineConfig;
use App\Models\Line\LineMember;
use Exception;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;

class LineService
{

    private $lineConfig = null;
    private $oa_access_token = null;

    private $lineUserId = null;
    private $accessToken = null;

    public $profile = null;

    function __construct($config = ['lineUserId' => null, 'accessToken' => null])
    {
        $lineUserId = null;
        $accessToken = null;

        extract($config);

        if ($accessToken) {
            Log::debug('LineService: accessToken ' . $accessToken);
            if($this->verifyToken($accessToken)) {
                $this->accessToken = $accessToken;
                if($profile = $this->getProfile($accessToken)) {
                    $this->profile = $profile;
                }
            } else {
                throw new AuthenticationException('Line Account Unauthenticated.');
            }
        } else if ($lineUserId) {
            Log::debug('LineService: lineUserId ' . $lineUserId);
            if ($this->getLineConfigByUserId($lineUserId)) {
                $this->lineUserId = $lineUserId;
            }
        }
    }

    function getLineConfig()
    {
        return $this->lineConfig;
    }

    function getLineConfigByUserId(string $lineUserId)
    {
        $lineMember = LineMember::whereUserId($lineUserId)->with('line_config')->first();

        if ($lineMember) {
            $this->oa_access_token = $lineMember->line_config->channel_access_token;
            $this->lineConfig = $lineMember->line_config;
            return $lineMember->LineConfig;
        }

        return false;
    }

    function verifyToken(string $accessToken = '')
    {
        $statusCode = null;
        $body = null;
        $stringBody = null;

        // Verify token and matching channel ID
        try {
            $headers = ['Content-Type' => 'application/json; charset=utf-8'];
            $client = new Client(['headers' => $headers]);
            $response = $client->request('GET', 'https://api.line.me/oauth2/v2.1/verify?access_token=' . urlencode($accessToken));
            $statusCode = $response->getStatusCode();
        } catch (\Throwable $th) {
            Log::error('verifyToken: ' . $th->getMessage());
            return false;
        }

        if ($statusCode == 200) {
            $body = $response->getBody();
            $stringBody = (string) $body;
        } else {
            Log::error('verifyToken: Status Code ' . $statusCode);
            Log::error($response->getReasonPhrase());
            return false;
        }

        $line_login_channel_id = null;

        if ($res = json_decode($stringBody)) {
            $line_login_channel_id = $res['client_id'];
        }

        $lineConfig = LineConfig::whereLoginChannelId($line_login_channel_id)->first();

        if ($lineConfig) {
            $this->oa_access_token = $lineConfig->channel_access_token;
            $this->lineConfig = $lineConfig;
            return $lineConfig;
        }

        return false;
    }

    private function profileRegister(string $userId, array $profile)
    {
        $lineMember = LineMember::whereUserId($userId)->whereHas('line_config', function (Builder $query) {
            $query->whereId($this->lineConfig->id);
        })->first();

        if (!$lineMember) {
            try {
                $lineMember = new LineMember();
                $lineMember->line_config_id = $this->lineConfig->id;
                $lineMember->user_id = $userId;
                $lineMember->display_name = $profile['displayName'];
                $lineMember->picture = $profile['pictureUrl'];
                $lineMember->save();
            } catch (\Throwable $th) {
                Log::error('profileRegister: ' . $th->getMessage());
            }
        }
    }

    function getProfile(string $accessToken = null)
    {
        if(!$accessToken && $this->profile) {
            return $this->profile;
        }

        $accessToken = $accessToken ? $accessToken : $this->accessToken;

        if(!$accessToken) {
            Log::error("getProfile : No Access token");
            return false;
        }

        if (!$this->oa_access_token) {
            Log::error("getProfile : OA Access Token not set");
            return false;
        }

        $statusCode = null;
        $body = null;
        $stringBody = null;

        try {
            $headers = ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $accessToken];
            $client = new Client(['headers' => $headers]);
            $response = $client->request('GET', 'https://api.line.me/v2/profile');
            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {
                $body = $response->getBody();
                $stringBody = (string) $body;
                $profile = json_decode($stringBody);
                // $profile['userId'];
                // $profile['displayName'];
                // $profile['pictureUrl'];
                // $profile['statusMessage'];
                $this->profileRegister($profile['userId'], $profile);
                return $profile;
            }
        } catch (\Throwable $th) {
            Log::error("getProfile: " . $th->getMessage());
            return false;
        }
    }

    function sendPushMessage(array $messageObject, string $userId = null)
    {
        $endpoint = '/v2/bot/message/push';

        $userId = $userId ? $userId : $this->lineUserId;

        if (!$userId) {
            Log::error("sendPushMessage : User ID Not Set");
            return false;
        }

        $params = [
            'to' => $userId,
            'messages' => json_encode($messageObject)
        ];

        try {
            $response = $this->botRequest($endpoint, $params);
            if ($response === false) {
                Log::error("sendPushMessage : OA Access Token not set");
                return false;
            }
        } catch (\Throwable $th) {
            Log::error("sendPushMessage : " . $th->getMessage());
            return false;
        }
    }

    private function botRequest(string $endpoint, array $params)
    {
        if (!$this->oa_access_token) {
            return false;
        }

        try {
            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($this->oa_access_token);
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => '']);
            $headers = ['Content-Type: application/json; charset=utf-8'];
            $response = $bot->httpClient->post($bot->DEFAULT_ENDPOINT_BASE . $endpoint, $params, $headers);

            if ($response->isSucceeded()) {
                return $response;
            } else {
                throw new Exception($response->getHTTPStatus() . ' ' . $response->getRawBody());
            }
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
