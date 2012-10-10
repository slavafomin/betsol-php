<?php

/**
 * Simple Url abstraction and manipulation class.
 *
 * @author Slava Fomin II <s.fomin@betsol.ru>
 *
 * Latest update: 10/10/12 05:10
 *
 * Usage example:
 *
 * 1). Using request's URI from $_SERVER:
 *
 * print (new Url())
 *     ->addQueryArgument('foo', 'Foo')
 *     ->addQueryArgument('bar', 'Bar')
 * ;
 *
 * print (new Url())
 *     ->addQueryArguments(array(
 *         'foo' => 'Foo',
 *         'bar' => 'Bar',
 *     ))
 * ;
 *
 * 2). Using custom Url (in CLI mode for example):
 *
 * print (new Url('http://example.com/foo?bar=Bar'))
 *     ->addQueryArgument('bar', 'Baz')
 * ;
 *
 * 3). Sorting query parameters before output:
 *
 * print (new Url('', array(
 *     'sort' => true,
 * )))
 *     ->addQueryArgument('z', 'z')
 *     ->addQueryArgument('b', 'B')
 *     ->addQueryArgument('a', 'A')
 * ;
 *
 * From Russia with Love.
 * Let's make this World a Better place!
 */

namespace Betsol\Utils;

class Url
{
    protected $path = '';
    protected $arguments = array();
    protected $cache_string = '';

    protected static $default_settings = array(
        'sort'       => false,
        'sort_flags' => SORT_STRING,
    );

    public function __construct($url = '', array $settings = array())
    {
        // Handling settings.
        if (!is_array($settings)) {
            throw new Exception('settings must be an array');
        }
        $this->settings = array_merge(self::$default_settings, $settings);
        unset($settings);

        $url = trim($url);
        if (!$url) {
            $url = $_SERVER['REQUEST_URI'];
        }

        // Parsing original Url.
        if (strpos($url, '?') !== false) {
            $parts = explode('?', $url, 2);
            $this->path = $parts[0];
            $query_part = $parts[1];

            foreach (explode('&', $query_part) as $query_argument) {
                $query_argument = trim($query_argument);

                if (strpos($query_argument, '=') !== false) {
                    $parts = explode('=', $query_argument, 2);
                    $key   = trim($parts[0]);
                    $value = trim($parts[1]);
                    $this->arguments[$key] = $value;
                } else {
                    $this->arguments[$query_argument] = '';
                }
            }

        } else {
            $this->path = $url;
        }
    }

    public function addQueryArgument($key, $value)
    {
        $this->_addQueryArgument($key, $value);

        // Clearing cache.
        $this->cache_string = '';

        // Maintaining chainability.
        return $this;
    }

    public function addQueryArguments(array $arguments = array())
    {
        if (!is_array($arguments)) {
            throw new Exception('arguments must be an array');
        }

        foreach ($arguments as $key => $value) {
            $this->_addQueryArgument($key, $value);
        }

        // Clearing cache.
        $this->cache_string = '';

        // Maintaining chainability.
        return $this;
    }

    public function updateSettings(array $settings = array())
    {
        if (!is_array($settings)) {
            throw new Exception('settings must be an array');
        }
        $this->settings = array_merge($this->settings, $settings);

        // Clearing cache.
        $this->cache_string = '';

        // Maintaining chainability.
        return $this;
    }

    /**
     * Casting itself to string.
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->cache_string) {
            $arguments = $this->arguments;
            if ($this->settings['sort']) {
                ksort($arguments, $this->settings['sort_flags']);
            }

            $args = array();
            foreach ($arguments as $key => $value) {
                $args[] = $key . '=' . $value;
            }

            $this->cache_string =
                  $this->path
                . (count($args) > 0 ? '?' . implode('&', $args) : '')
            ;
        }

        return $this->cache_string;
    }

    protected function _addQueryArgument($key, $value)
    {
        $key   = trim($key);
        $value = trim($value);

        if (!$key) {
            throw new Exception('key must not be empty');
        }

        $this->arguments[$key] = $value;
    }
}