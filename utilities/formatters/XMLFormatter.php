<?php

namespace wizarphics\wizarframework\utilities\formatters;

use RuntimeException;
use SimpleXMLElement;
use wizarphics\wizarframework\interfaces\FormatterInterface;

class XMLFormatter implements FormatterInterface
{
    /**
     * Takes the given data and formats it.
     *
     * @param mixed $data
     *
     * @return false|string (XML string | false)
     */
    public function format($data)
    {

        // SimpleXML is installed but default
        // but best to check, and then provide a fallback.
        if (!extension_loaded('simplexml')) {
            throw new RuntimeException('The SimpleXML extension is required to format XML.');
        }

        $options = 0;
        $output  = new SimpleXMLElement('<?xml version="1.0"?><response></response>', $options);

        $this->arrayToXML((array) $data, $output);

        return $output->asXML();
    }

    /**
     * A recursive method to convert an array into a valid XML string.
     *
     * Written by CodexWorld. Received permission by email on Nov 24, 2016 to use this code.
     *
     * @see http://www.codexworld.com/convert-array-to-xml-in-php/
     *
     * @param SimpleXMLElement $output
     */
    protected function arrayToXML(array $data, &$output)
    {
        foreach ($data as $key => $value) {
            $key = $this->normalizeXMLTag($key);

            if (is_array($value)) {
                $subnode = $output->addChild("{$key}");
                $this->arrayToXML($value, $subnode);
            } else {
                $output->addChild("{$key}", htmlspecialchars("{$value}"));
            }
        }
    }

    /**
     * Normalizes tags into the allowed by W3C.
     * Regex adopted from this StackOverflow answer.
     *
     * @param int|string $key
     *
     * @return string
     *
     * @see https://stackoverflow.com/questions/60001029/invalid-characters-in-xml-tag-name
     */
    protected function normalizeXMLTag($key)
    {
        $startChar = 'A-Z_a-z' .
            '\\x{C0}-\\x{D6}\\x{D8}-\\x{F6}\\x{F8}-\\x{2FF}\\x{370}-\\x{37D}' .
            '\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}' .
            '\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}' .
            '\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}';
        $validName = $startChar . '\\.\\d\\x{B7}\\x{300}-\\x{36F}\\x{203F}-\\x{2040}';

        $key = trim($key);
        $key = preg_replace("/[^{$validName}-]+/u", '', $key);
        $key = preg_replace("/^[^{$startChar}]+/u", 'item$0', $key);

        return preg_replace('/^(xml).*/iu', 'item$0', $key); // XML is a reserved starting word
    }
}
