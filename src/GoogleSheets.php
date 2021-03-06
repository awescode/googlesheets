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

    public function docs(string $key, $option = []): string
    {
        $url =  $this->getHTMLUrl($key);

        $html = $this->downloadFileCurl($url);

        $content = $this->filterHTML($html, $option);
        return $content;
    }

    public function sheets(string $key, string $sheet = ''): string
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

    /**
     * @param $html
     * @param $option
     * @return mixed|null|string|string[]
     */
    private function filterHTML($html, $option)
    {
        $html = iconv(mb_detect_encoding($html, mb_detect_order(), true), "UTF-8", $html);

        $document = new \DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);
        $document->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($document);

        // Remove all comments from string
        $elements = $document->getElementsByTagName('sup');
        for ($i = $elements->length; --$i >= 0; ) {
            $node = $elements->item($i);
            $node->parentNode->removeChild($node);
        }
        $elements = $document->getElementsByTagName('div');
        for ($i = $elements->length; --$i >= 0; ) {
            $node = $elements->item($i);
            $node->parentNode->removeChild($node);
        }

        // Remove all necessary tags
        foreach ($xpath->query('//span') as $element) {
            // Move all span tag content to its parent node just before it.
            while ($element->hasChildNodes()) {
                $child = $element->removeChild($element->firstChild);
                $element->parentNode->insertBefore($child, $element);
            }
            // Remove the span tag.
            $element->parentNode->removeChild($element);
        }

        $nodes = $xpath->query('//*[@class]');  // Find elements with a style attribute
        foreach ($nodes as $key => $node) {              // Iterate over found elements

            $classes = array();
            for ($i = 0; $i < $node->attributes->length; ++$i){
                $attrsItem = $node->attributes->item($i);
                $classes[] = $attrsItem->nodeValue;
            }

        }

        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            $node->parentNode->removeAttribute($node->nodeName);
        }

        $htmlFiltered = '';
        $body = $document->getElementsByTagName('body');
        if ( $body && 0<$body->length ) {
            $body = $body->item(0);
            $htmlFiltered = $document->savehtml($body);
        }

        // ">>" markdown tipp blockquote
        $htmlFiltered = preg_replace("/<p>\&gt;\&gt;(.*?)<\/p>/", "<blockquote>$1</blockquote>", $htmlFiltered);

        // ">" markdown blockquote
        $htmlFiltered = preg_replace("/<p>\&gt;(.*?)<\/p>/", "<blockquote class='tf__quote i i-quote'>$1</blockquote>", $htmlFiltered);

        // "***" markdown strong
        $htmlFiltered = preg_replace("/\*\*\*(.+?)\*\*\*/", '<strong>$1</strong>', $htmlFiltered);

        // "[value of link](sku)" - links
        $htmlFiltered = preg_replace("/\[(.+?)\]\((.+?)\)/", '{{link:$2:$1}}', $htmlFiltered);

        // Remove empty <p> tag
        $htmlFiltered = str_replace(["<p></p>", "<p> </p>", "<a></a>", "<body>", "</body>"], ["", "", ""], $htmlFiltered);

        return $htmlFiltered;
    }

    private static function changeTagName(&$node, $name) {
        $childnodes = array();
        foreach ($node->childNodes as $child){
            $childnodes[] = $child;
        }
        $newnode = $node->ownerDocument->createElement($name);
        foreach ($childnodes as $child){
            $child2 = $node->ownerDocument->importNode($child, true);
            $newnode->appendChild($child2);
        }
        foreach ($node->attributes as $attrName => $attrNode) {
            $attrName = $attrNode->nodeName;
            $attrValue = $attrNode->nodeValue;
            $newnode->setAttribute($attrName, $attrValue);
        }
        $node->parentNode->replaceChild($newnode, $node);
        return $newnode;
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

    private function getHTMLUrl($key)
    {
        $url = str_replace("{key}", $key, "https://docs.google.com/document/d/{key}/export?format=html&id={key}");
        return $url;
    }

    private function getCSVUrl($key, $sheet = '')
    {
        $url = str_replace("{key}", $key, "https://docs.google.com/spreadsheets/d/{key}/gviz/tq?tqx=out:csv");
        if ($sheet != '') {
            $url .= '&sheet='.$sheet;
        }
        return $url;
    }

}
