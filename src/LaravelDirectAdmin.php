<?php

namespace Solitweb\LaravelDirectAdmin;

use Solitweb\DirectAdmin\DirectAdmin;

class LaravelDirectAdmin
{
    protected $connection;

    protected $command;

    public function __construct(DirectAdmin $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set method to GET.
     *
     * @return $this
     */
    public function get() {
        $this->connection->set_method('GET');
        return $this;
    }

    /**
     * Set method to POST.
     *
     * @return $this
     */
    public function post() {
        $this->connection->set_method('POST');
        return $this;
    }

    /**
     * Do an API request.
     *
     * @return array result parsed
     */
    public function request($command, $options = [])
    {
        $this->connection->query('/CMD_API_'.$command, $options);
        return $this->connection->fetch_parsed_body();
    }

    /**
     * Magic Method
     *
     * @param $methodName
     * @param $arguments
     * @return bool
     * @throws \Exception
     */
    public function __call($methodName, $arguments)
    {
        $arguments = count($arguments) > 0 ? $arguments[0] : $arguments ;

        if(!$response = $this->extractMethod($methodName, $arguments)) {
            throw new \Exception("Invalid method called");
        }

        return $response;
    }

    /**
     * Extract command name from magic method
     *
     * @param $methodName
     * @param $arguments
     * @return bool
     */
    private function extractMethod($methodName, $arguments)
    {
        if(strpos($methodName, "get") !== false) {
            return $this->extractCommand("get", substr($methodName, 3), $arguments);
        }

        if(strpos($methodName, "post") !== false) {
            return $this->extractCommand("post", substr($methodName, 4), $arguments);
        }

        return false;
    }

    /**
     * Set the command based on the magic method name
     *
     * @param $method
     * @param $command
     * @param $arguments
     * @return array
     */
    private function extractCommand($method, $command, $arguments)
    {
        $this->connection->set_method(strtoupper($method));

        return $this->request(
            $this->camelToSnake($command),
            $arguments
        );
    }

    /**
     * Convert CamelCase to snake_case
     *
     * @param $string
     * @return string
     */
    private function camelToSnake($string)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return strtoupper(implode('_', $ret));
    }

}