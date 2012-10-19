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
    /** @var BASE_URL The base url for the API calls */
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
        $endpoint = sprintf('/forecast/%s/%s,%s', $this->apiKey, $lat, $long);
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
        $endpoint = sprintf('/forecast/%s/%s,%s', $this->apiKey, $lat, $long);
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
     *      'lat'  => 37.126617,    // float, latitude, required
     *      'long' => -87.842756,   // float, longitude, required
     *      'time' => 1350531963    // integer, unix timestamp, optional
     *  )
     *
     * If the 'time' key isn't included, the current time will be used. The given
     * timestamp will be automatically converted to GMT time, so do not pre-convert
     * this value.
     *
     * @param array Associative array of lat/long/[time] to pull information from.
     *
     * @return array The decoded JSON response from the API call
     *
     * @throws \InvalidArgumentException If a given time is outside the -8hrs to +1hr range
     */
    public function getPrecipitation()
    {
        $now = time();
        $params = array();
        foreach(func_get_args() as $arg) {
            $lat  = $arg['lat'];
            $long = $arg['long'];
            $time = (isset($arg['time'])) ? $arg['time'] : $now;

            $dt = new \DateTime();
            $dt->setTimestamp($time);

            // The DarkSky API requires the time be between -8hrs and +1hrs from now
            $min = new \DateTime("-8 hours");
            $max = new \DateTime("+1 hours");

            if ($dt < $min || $dt > $max) {
                throw new \InvalidArgumentException('Time value must greater than -8hrs and less the +1hr from now.');
            }

            // The DarkSky API expects this timestamp to be in GMT.
            $dt->setTimezone(new DateTimeZone('GMT'));

            $params[] = implode(',', array($lat, $long, $dt->getTimestamp()));
        }

        $endpoint = sprintf('/precipitation/%s/%s', $this->apiKey, implode(';', $params));
        return $this->makeAPIRequest($endpoint);
    }

    /**
     * Retrieves a list of interesting storms from the Dark Sky API.
     *
     * @return array The decoded JSON response from the API call
     */
    public function getInterestingStorms() {
        $endpoint = sprintf('/interesting/%s', $this->apiKey);
        return $this->makeAPIRequest($endpoint);
    }

    /**
     * Makes a request to the Dark Sky API. Does *not* use the cURL library; however,
     * it does require the server to have allow_url_fopen enabled.
     *
     * @param $url string The URL endpoint to hit
     *
     * @return array The decoded JSON response from the API call
     *
     * @throws \Exception If we can't contact the API or
     *                    the API call returns a response that can't be decoded
     */
    private function makeAPIRequest($endpoint)
    {
        $url = self::BASE_URL . $endpoint;

        if ($this->options['suppress_errors']) {
            $response = @file_get_contents($url);
        } else {
            $response = file_get_contents($url);
        }

        if ($response === false) {
            throw new \Exception('There was an error contacting the DarkSky API.');
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
                    $reason = sprintf('Unknown error. Error code %s', $error_code);
                    break;
            }

            throw new \Exception(sprintf('Unable to decode JSON response: %s', $reason));
        }

        return $json;
    }
}
