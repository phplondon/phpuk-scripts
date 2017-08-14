<?php

class Eventbrite {

    /**
     * Eventbrite API endpoint
     */
    var $api_endpoint = "https://www.eventbriteapi.com/v3/";
    var $auth_tokens;

    /**
     * Eventbrite Oauth Token (REQUIRED)
     *    https://www.eventbrite.co.uk/developer/v3/api_overview/authentication/
     */
    function __construct($tokens = null) {
        $this->auth_tokens = $tokens;
    }

    // For information about available API methods, see: https://www.eventbrite.co.uk/developer/v3/
    function DoRequest($endpoint, $params) {
        // Add authentication tokens to querystring
        if (isset($this->auth_tokens['token'])) {
            $params = array_merge($params, $this->auth_tokens);
        }
        
        if (isset($params['method'])) {
            $options = array(
                'http' => array('method' => $params['method'])
            );
            unset($params['method']);
        } else {
            $options = array(
                'http' => array('method' => 'GET')
            );
        }

        // Build our request url, urlencode querystring params
        $request_url = $this->api_endpoint . $endpoint . '?' . http_build_query($params, '', '&');

        // Call the API
        // echo "Calling: $request_url with options: ". print_r($options, 1);
        
        $resp = file_get_contents($request_url, false, stream_context_create($options));

        // parse our response
        if ($resp) {
            $resp = json_decode($resp);
            if (isset($resp->error) && isset($resp->error->error_message)) {
                throw new \Exception($resp->error->error_message);
            }
        }
        return $resp;
    }
}
