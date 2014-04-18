<?php

Namespace ServiceProvider
{
    Class JsonConfigDriver Implements ConfigDriver
    {
        public function load($filename)
        {
            $config = $this->parseJson($filename);

            if (JSON_ERROR_NONE !== json_last_error())
            {
                $jsonError = $this->getJsonError(json_last_error());
                Throw New   \RuntimeException(
                    sprintf('Invalid JSON provided "%s" in "%s"', $jsonError, $filename));
            }

            return $config ?: [];
        }

        public function supports($filename)
        {
            return (bool) preg_match('#\.json(\.dist)?$#', $filename);
        }

        private function parseJson($filename, $array = true)
        {
            $json = file_get_contents($filename);

                return json_decode($json, $array);
        }

        private function getJsonError($code)
        {
            $errorMessages = [
                JSON_ERROR_DEPTH            => 'The maximum stack depth has been exceeded',
                JSON_ERROR_STATE_MISMATCH   => 'Invalid or malformed JSON',
                JSON_ERROR_CTRL_CHAR        => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX           => 'Syntax error',
                JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            ];

            return isset($errorMessages[$code]) ? $errorMessages[$code] : 'Unknown';
        }
    }
}