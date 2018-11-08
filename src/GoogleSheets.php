<?php

namespace Awescode\GoogleSheets;

use League\Csv\Reader;
use League\Csv\Exception;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Awescode\GoogleSheets\Contracts\GoogleSheets as GoogleSheetsContract;

class GoogleSheets implements GoogleSheetsContract
{
    /** @var Model */
    protected $model;

    public function __construct()
    {
        $modelClassName = config('googlesheets.model');

        $this->model = new $modelClassName;
    }


    public function parse(string $key, string $sheet = ''): string
    {
        if (is_link($key)) {
            if (!$key = $this->getKeyParam($key)){
                return $this->json_response(false, 'The link is broken.');
            }
        }

        $csvUrl = $this->getCSVUrl($key, $sheet);

        $csvString = $this->downloadFileCurl($csvUrl);

        try {
            $csv = Reader::createFromString($csvString);
            $csv->setHeaderOffset(0);
            $csv->setDelimiter(',');
            $data = [];
            $headers = $this->trimHeader($csv->getHeader());
            foreach ($csv->getRecords($headers) as $record) {
                $data[] = $record;
            }
            return $this->json_response(true, 'The data was collected.', $data);
        }
        catch (Exception $e) {
            return $this->json_response(false, 'The table is broken.');
        }
        catch (InvalidArgumentException $e) {
            return $this->json_response(false, 'The table is broken.');
        }
    }

    private function trimHeader($array)
    {
        if (!is_array($array)) {
            return [];
        }
        $trimmedArray = [];
        foreach($array as $key => $value) {
            if (trim($value) != "") {
                $trimmedArray[$key] = trim($value);
            }
        }
        return $trimmedArray;
    }

    private function json_response($status = false, $msg = '', $data = [])
    {
        $status_msg = ($status) ? "OK" : "ERROR";
        return json_encode(['status' => $status_msg, 'msg' => $msg, 'data' => $data]);
    }

    private function getKeyParam($url)
    {
        $tempArr = explode("/", $url);
        if (count($tempArr) > 5) {
            return $tempArr[count($tempArr) - 2];
        }
        return '';
    }

    public function downloadFileCurl($url)
    {
        set_time_limit(360);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $fileContent = curl_exec($ch);
        curl_close($ch);
        if ($fileContent) {
            return $fileContent;
        } else {
            return false;
        }
    }

    private function getCSVUrl($key, $sheet = '')
    {
        $url = str_replace("{key}", $key, "https://docs.google.com/spreadsheets/d/{key}/gviz/tq?tqx=out:csv");
        //$url = str_replace("{key}", $key, "https://docs.google.com/spreadsheets/d/{key}/export?format=csv&id={key}");

        if ($sheet != '') {
            $url .= '&sheet='.$sheet;
        }
        return $url;
    }

}
