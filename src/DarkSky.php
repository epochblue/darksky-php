<?php

/**
 * A simple wrapper for the Dark Sky API.
 * More information about the Dark Sky API can be found here:
 *
 *      https://developer.darkskyapp.com/docs
 *
 * @package DarkSky
 * @author  Bill Israel <bill.israel@gmail.com>
 * @license MIT
 */
class DarkSky
{
    /** @const BASE_URL The base url for the API calls */
    const BASE_URL = 'https://api.darkskyapp.com/v1';

    /** @var string $apiKey The Dark Sky developer API key */
    private $apiKey;

    /** @var array $options An array of possible options */
    private $options = array(
        'suppress_errors' => false
    );

    /**
     * Constructor.
     *
     * @param string $apiKey  The developer's API key
     * @param array  $options An array of options
     */
    public function __construct($apiKey, $options = array())
    {
        $this->apiKey = $apiKey;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Retrieves the forecast for the given latitude and longitude.
     *
     * @param $lat  float The latitude
     * @param $long float The longitude
     *
     * @return array The decoded JSON response from the API call
     */
    public function getForecast($lat, $long)
    {
        $endpoint = '/forecast/' . $this->apiKey . '/' . (string) $lat . ',' . (string) $long;
        return $this->makeAPIRequest($endpoint);
    }

    /**
     * Retrieves the (slightly more brief) forecast for the given latitude and longitude.
     *
     * @param $lat  float The latitude
     * @param $long float The longitude
     *
     * @return array The decoded JSON response from the API call
     */
    public function getBriefForecast($lat, $long)
    {
        $endpoint = '/forecast/' . $this->apiKey . '/' . (string) $lat . ',' . (string) $long;
        return $this->makeAPIRequest($endpoint);
    }

    /**
     * Returns forecasts for a collection of arbitrary points.
     *
     * NOTE:
     * This method takes an arbitrary number of parameters, but each
     * parameter must be an associative array with 2 required keys
     * and 1 optional key.
     *
     * Each parameter should follow this format:
     *
     *  array(
     *      'lat'  => 37.126617,    // float, required
     *      'long' => -87.842756,   // float, required
     *      'time' => 1350531963    // integer, optional*
     *  )
     *
     * If the 'time' key isn't included, the current time will be used.
     *
     * @param array Associative array of lat/long/[time] to pull information from.
     */
    public function getPrecipitation()
    {
        $params = '';
        $now = time();
        foreach(func_get_args() as $arg) {
            $lat  = $arg['lat'];
            $long = $arg['long'];
            
            //TODO: convert default time to GMT, check time against constraints
            $time = (isset($arg['time'])) ? $arg['time'] : $now;

            $params .= $lat . ',' . $long . ',' . $time . ';';
        }

        $endpoint = '/precipitation/' . $this->apiKey . '/' . $params;
        return $this->makeAPIRequest($endpoint);
    }

    /**
     * Retrieves a list of interesting storms from the Dark Sky API.
     *
     * @return array The decoded JSON response from the API call
     */
    public function getInterestingStorms() {
        $endpoint = '/interesting/' . $this->apiKey;
        return $this->makeAPIRequest($endpoint);
    }

    /**
     * @param $url string The URL endpoint to hit
     *
     * @return array The decoded JSON response from the API call
     *
     * @throws \Exception If the API call returns a response that can't be decoded
     */
    private function makeAPIRequest($endpoint)
    {
        $url = self::BASE_URL . $endpoint;

        if ($this->options['suppress_errors']) {
            $response = @file_get_contents($url);
        } else {
            $response = file_get_contents($url);
        }

        $json = json_decode($response, true);

        if ($json === null) {
            switch($error_code = json_last_error()) {
                case JSON_ERROR_SYNTAX:
                    $reason = 'Bad JSON Syntax';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $reason = 'Unexpected control character found';
                    break;
                default:
                    $reason = 'Unknown error, code ' . $error_code;
                    break;
            }

            throw new \Exception(sprintf('Unable to decode JSON response: %s', $reason));
        }

        return $json;
    }
}
