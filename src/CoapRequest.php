<?php

namespace IkeaLightsPhp;

class CoapRequest
{
    protected string $method;
    protected string $user;
    protected string $key;
    protected string $body;
    protected string $uri;

    public function __construct(array $args) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function setMethod(string $method) {
        $this->method = $method;
        return $this;
    }

    public function setUser(string $user) {
        $this->user = $user;
        return $this;
    }

    public function setKey(string $key) {
        $this->key = $key;
        return $this;
    }

    public function setBody(string $body) {
        $this->body = $body;
        return $this;
    }

    public function setUri(string $uri) {
        $this->uri = $uri;
        return $this;
    }

    public function send() {

        $args = [
            'm' => $this->method,
            'u' => $this->user,
            'k' => $this->key,
        ];

        if(isset($this->body)) {
            $args['e'] = $this->body;
        }

        $options = $this->formatOptions($args);

        $uri = 'coaps://' . $this->uri;

        $command = "coap-client $options '$uri'";

        //echo $command . PHP_EOL;

        exec($command, $output);

        return $output;
    }

    /**
     * @param array $options
     * @return string
     */
    private function formatOptions(array $options): string {
        $formatted_options = '';
        foreach ($options as $option => $value) {
            $formatted_options .= "-$option '$value' ";
        }
        return $formatted_options;
    }
}