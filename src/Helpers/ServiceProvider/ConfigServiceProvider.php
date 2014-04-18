<?php

Namespace ServiceProvider
{
    Class ConfigServiceProvider
    {
        private $filename,
                $driver;

        private $replacements = [],
                $config       = [];

        public function __construct($filename = ENV_FILE, array $replacements = array(), ConfigDriver $driver = null)
        {
            $this->filename = $filename;

            if ($replacements)
            {
                foreach ($replacements as $key => $value) $this->replacements['%'.$key.'%'] = $value;
            }

            $this->driver = $driver ?: New ChainConfigDriver([
                New IniConfigDriver(),
                New JsonConfigDriver(),
            ]);

            return $this;
        }


        public function register($app = [])
        {
            $config = $this->readConfig();

            foreach ($config AS $name => $value)

                if ('%' === substr($name, 0, 1)) $this->replacements[$name] = (string) $value;

            $this->merge($app, $config);

            return $this;
        }


        public function getConfig($obj = false)
        {
            return ($obj) ? $this->arrToObj($this->config) : $this->config;
        }


        private function merge($app, array $config)
        {
            foreach ($config AS $name => $value)
            {
                if (isset($app[$name]) && is_array($value))
                {
                    $app[$name] = $this->mergeRecursively($app[$name], $value);
                }

                else
                {
                    $app[$name] = $this->doReplacements($value);
                }
            }

            return $this->config = $config;
        }

        private function mergeRecursively(array $currentValue, array $newValue)
        {
            foreach ($newValue AS $name => $value)
            {
                if (is_array($value) && isset($currentValue[$name]))
                {
                    $currentValue[$name] = $this->mergeRecursively($currentValue[$name], $value);
                }

                else
                {
                    $currentValue[$name] = $this->doReplacements($value);
                }
            }

            return $currentValue;
        }

        private function doReplacements($value)
        {
            if (! $this->replacements) return $value;

            if (is_array($value))
            {
                foreach ($value as $k => $v) $value[$k] = $this->doReplacements($v);

                    return $value;
            }

            if (is_string($value)) return strtr($value, $this->replacements);

                return $value;
        }

        private function readConfig()
        {
            if (! $this->filename)
            {
                Throw New   \RuntimeException('A valid configuration file must be passed before reading the config.');
            }

            if (! file_exists($this->filename))
            {
                Throw New   \InvalidArgumentException(
                    sprintf("The config file '%s' does not exist.", $this->filename)
               );
            }

            if ($this->driver->supports($this->filename)) return $this->driver->load($this->filename);

            Throw New   \InvalidArgumentException(
                sprintf("The config file '%s' appears to have an invalid format.", $this->filename)
           );
        }


        protected function arrToObj($array)
        {
            if (is_array($array)) return (object) array_map(__METHOD__, $array);

                return $array;
        }
    }
}