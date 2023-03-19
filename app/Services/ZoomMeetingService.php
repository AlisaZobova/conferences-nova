<?php

namespace  App\Services;

use App\Models\Report;
use App\Models\ZoomMeeting;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class ZoomMeetingService
{
    public function __construct()
    {
        //
    }

    public static function getHeaders() {
        return [
            'Authorization' => 'Bearer ' . self::generateZoomToken(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public static function getClient() {
        return new Client();
    }

    public static function getUrl() {
        return config('zoom.url');
    }

    public static function generateZoomToken()
    {
        $key = config('zoom.key');
        $secret = config('zoom.secret');
        $payload = [
            'iss' => $key,
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    public static function toZoomTimeFormat(string $dateTime)
    {
        $date = new \DateTime($dateTime);
        return $date->format('Y-m-d\TH:i:s');
    }

    public static function getDuration(Report $report)
    {
        $start = new \DateTime($report->start_time);
        $end = new \DateTime($report->end_time);
        $timeDiff = $end->diff($start);

        return $timeDiff->h * 60 + $timeDiff->i;
    }

    public static function createZoomConference($response, Report $report, $updReport)
    {
        $conferenceData = [];
        $conferenceData['id'] = $response['id'];
        $conferenceData['uuid'] = $response['uuid'];
        $conferenceData['host_id'] = $response['host_id'];
        $conferenceData['topic'] = $response['topic'];
        $conferenceData['type'] = $response['type'];
        $conferenceData['start_time'] = $response['start_time'];
        $conferenceData['timezone'] = $response['timezone'];
        $conferenceData['report_id'] = $report->id;
        $conferenceData['join_url'] = $response['join_url'];
        $conferenceData['start_url'] = $response['start_url'];

        try {
            ZoomMeeting::create($conferenceData);
            return true;
        } catch (Exception $e) {
            if (!$updReport) {
                $report->forceDelete();
            }
            return false;
        }
    }

    public static function store(Report $report, $updReport = false)
    {
        $path = 'users/me/meetings';

        $body = [
            'headers' => self::getHeaders(),
            'body' => json_encode(
                [
                    'topic' => $report->topic,
                    'type' => 2,
                    'start_time' => self::toZoomTimeFormat($report->start_time),
                    'duration' => self::getDuration($report),
                ]
            ),
        ];

        try {
            $response = self::getClient()->post(self::getUrl() . $path, $body);
            $response = json_decode($response->getBody(), true);
            return self::createZoomConference($response, $report, $updReport);
        } catch (Exception $e) {
            if (!$updReport) {
                $report->forceDelete();
            }
            return false;
        }
    }

    public static function update($id, $report)
    {
        $path = 'meetings/' . $id;

        $body = [
            'headers' => self::getHeaders(),
            'body' => json_encode(
                [
                    'topic' => $report->topic,
                    'start_time' => self::toZoomTimeFormat($report->start_time),
                    'duration' => self::getDuration($report),
                ]
            ),
        ];

        try {
            self::getClient()->patch(self::getUrl() . $path, $body);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function delete($id)
    {
        $path = 'meetings/' . $id;
        $headers = ['headers' => self::getHeaders()];

        try {
            self::getClient()->delete(self::getUrl() . $path, $headers);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
