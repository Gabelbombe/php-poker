<?php

Namespace ServiceProvider
{
    Class IniConfigDriver Implements ConfigDriver
    {
        public function load($filename)
        {
            return ($config = $this->parseIni($filename)) ? $config : [];
        }

        public function supports($filename)
        {
            return (bool) preg_match('#\.ini(\.dist)?$#', $filename);
        }

        private function parseIni($filename)
        {
            return $this->parseIniString($filename);
        }

        private function parseIniString($str)
        {
            if(empty($str)) return false;

            $lines = explode("\n", $str);
            $insideSection = false;
            $ret = [];

            foreach($lines AS $line)
            {
                $line = trim($line);
                if(! $line || '#' === $line[0] || ';' === $line[0]) continue;

                if($line[0] == "[" && $endIdx = strpos($line, "]"))
                {
                    $insideSection = substr($line, 1, $endIdx-1);
                    continue;
                }

                if(! strpos($line, '=')) continue;

                $tmp = explode("=", $line, 2);

                if($insideSection)
                {
                    $key = rtrim($tmp[0]);
                    $val = ltrim($tmp[1]);

                    if(preg_match("/^\".*\"$/", $val) || preg_match("/^'.*'$/", $val)) $val = mb_substr($val, 1, mb_strlen($val) - 2);

                    $t = preg_match("^\[(.*?)\]^", $key, $matches); unset($t);

                    if(! empty($matches) && isset($matches[0]))
                    {
                        $arrName = preg_replace('#\[(.*?)\]#is', '', $key);

                        if(!isset($ret[$insideSection][$arrName]) || !is_array($ret[$insideSection][$arrName])) $ret[$insideSection][$arrName] = [];

	                if(isset($matches[1]) && !empty($matches[1]))
                    {
                        $ret[$insideSection][$arrName][$matches[1]] = $val;
                    } else {
                        $ret[$insideSection][$arrName][] = $val;
                    }
	            }

                    else
                    {
                        $ret[$insideSection][trim($tmp[0])] = $val;
                    }
                }

                else
                {
                    $ret[trim($tmp[0])] = ltrim($tmp[1]);
                }
            }
            return $ret;
        }
   }
}