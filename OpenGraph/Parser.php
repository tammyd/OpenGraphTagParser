<?php

namespace OpenGraph;

/**
 * Class Parser
 *
 * Utility class to retrieve the open graph tags from a snippet (or full page) of html.
 *
 * @package OpenGraph
 */
class Parser
{
    protected $properties;

    /**
     * @param string $html
     */
    public function __construct($html)
    {
        $this->properties = self::parse((string) $html);

    }

    /**
     * @param string $html
     *
     * @return array
     */
    public static function parse($html)
    {
        $result = [];

        libxml_use_internal_errors(true); //needed as DOMDocument will throw errors with malformed html otherwise

        $domDoc = new \DOMDocument();
        $domDoc->loadHTML((string) $html);
        $xpath = new \DOMXpath($domDoc);
        $query = '//*/meta[starts-with(@property, \'og:\')]';
        $metas = $xpath->query($query);

        foreach ($metas as $meta) {
            $key = substr($meta->getAttribute('property'), 3);
            $value = $meta->getAttribute('content');

            if (strpos($key, ":", 1) !== false) {
                //this is a structured object
                $result = self::updateStructuredObject($result, $key, $value);
            } else {
                $result = self::updateSimpleKey($result, $key, $value);
            }
        }

        libxml_clear_errors(); //putting things back to where they should be

        return $result;
    }

    /**
     * @param array  $currResults
     * @param string $key
     * @param mixed  $value
     *
     * @return array mixed
     */
    private static function updateSimpleKey(array $currResults, $key, $value)
    {
        if (isset($currResults[$key])) {
            if (is_array($currResults[$key])) {
                $currResults[$key][] = $value;
            } else {
                $currResults[$key] = [$currResults[$key], $value];
            }
        } else {
            $currResults[$key] = $value;
        }

        return $currResults;
    }

    /**
     * @param array  $currResults
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    private static function updateStructuredObject($currResults, $key, $value)
    {
        list($obj, $property)  = explode(':', $key);

        if (strtolower($property) === 'url') {
            $currResults = self::updateSimpleKey($currResults, $obj, $value);

            return $currResults;
        }

        //Remove the most recent matching item and work with it
        if (!isset($currResults[$obj])) {
            //The root tag has not yet been defined, so there's nothing we should do here. Moving on...
            return $currResults;
        }
        if (is_array($currResults[$obj])) {
            $workingObject = array_pop($currResults[$obj]);
        } else {
            $workingObject = $currResults[$obj];
            unset($currResults[$key]);
        }

        //Force unspecified default properties to be 'url'
        if (!is_array($workingObject)) {
            $workingObject = ['url'=>$workingObject];
        }

        $workingObject[$property] = $value;

        $currResults = self::updateSimpleKey($currResults, $obj, $workingObject);

        return $currResults;
    }

    /**
     * Allows access to og properties through simple accessors matching their tag counterparts, ie
     * $parser->site_name or $parser->image
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        } else {

            throw new \Exception("OpenGraph property $name is not defined");
        }
    }


    /**
     * @param string $name
     *
     * @return bool
     *
     */
    public function __isset($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->properties;
    }

}
