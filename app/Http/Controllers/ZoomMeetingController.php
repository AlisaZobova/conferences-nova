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

    public function generateZoomToken()
    {
        $key = config('zoom.key');
        $secret = config('zoom.secret');
        $payload = [
            'iss' => $key,
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    public function toZoomTimeFormat(string $dateTime)
    {
        $date = new \DateTime($dateTime);
        return $date->format('Y-m-d\TH:i:s');
    }

    public function getDuration(Report $report)
    {
        $start = new \DateTime($report->start_time);
        $end = new \DateTime($report->end_time);
        $timeDiff = $end->diff($start);

        return $timeDiff->h * 60 + $timeDiff->i;
    }

    public function createZoomConference($response, Report $report, $updReport)
    {
        $conferenceData = [];
        $conferenceData['id'] = $response['id'];
        $conferenceData['report_id'] = $report->id;
        $conferenceData['join_url'] = $response['join_url'];
        $conferenceData['start_url'] = $response['start_url'];

        try{
            ZoomMeeting::create($conferenceData);
            return true;
        } catch (Exception $e) {
            if (!$updReport) {
                $report->forceDelete();
            }
            return false;
        }
    }

    public function store(Report $report, $updReport=false)
    {
        $path = 'users/me/meetings';

        $body = [
            'headers' => $this->headers,
            'body'    => json_encode(
                [
                'topic'      => $report->topic,
                'type'       => 2,
                'start_time' => $this->toZoomTimeFormat($report->start_time),
                'duration'   => $this->getDuration($report),
                ]
            ),
        ];

        try {
            $response =  $this->client->post($this->baseUrl.$path, $body);
            $response = json_decode($response->getBody(), true);
            return $this->createZoomConference($response, $report, $updReport);
        } catch (Exception $e) {
            if (!$updReport) {
                $report->forceDelete();
            }
            return false;
        }
    }

    public function update($id, $report)
    {
        $path = 'meetings/'.$id;

        $body = [
            'headers' => $this->headers,
            'body'    => json_encode(
                [
                'topic'      => $report->topic,
                'start_time' => $this->toZoomTimeFormat($report->start_time),
                'duration'   => $this->getDuration($report),
                ]
            ),
        ];

        try {
            $this->client->patch($this->baseUrl.$path, $body);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($id)
    {
        $path = 'meetings/'.$id;
        $headers = ['headers' => $this->headers];

        try {
            $this->client->delete($this->baseUrl.$path, $headers);
            return true;
        } catch (Exception $e) {
            return false;
        }
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
