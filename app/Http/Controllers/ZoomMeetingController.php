<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ZoomMeeting;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class ZoomMeetingController extends Controller
{
    public $client;
    public $jwt;
    public $headers;
    public $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->jwt = $this->generateZoomToken();
        $this->headers = [
            'Authorization' => 'Bearer '.$this->jwt,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
        $this->baseUrl = config('zoom.url');
    }

    public function getNextPage($nextPageToken='')
    {
        $path = 'users/me/meetings';

        $content = [
            'headers' => $this->headers,
            'query' => $nextPageToken ? ["next_page_token" => $nextPageToken] : ''
        ];

        $response =  $this->client->get($this->baseUrl.$path, $content);

        return json_decode($response->getBody(), true);
    }

    public function index()
    {
        if(cache('meetings')) {
            return cache('meetings');
        }

        $meetings = [];
        $data = $this->getNextPage();
        $meetings = array_merge($meetings, $data['meetings']);

        while ($data['next_page_token']) {
            $data = $this->getNextPage($data['next_page_token']);
            $meetings = array_merge($meetings, $data['meetings']);
        }

        foreach ($meetings as &$meeting) {
            $meeting['report_id'] = ZoomMeeting::find($meeting['id'])->report_id;
        }

        cache(['meetings' => $meetings]);

        return $meetings;
    }
}
