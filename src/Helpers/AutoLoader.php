<?php

//require APP_PATH . '/src/Helpers/ServiceProvider/ConfigServiceProvider.php';

//USE ServiceProvider\ConfigServiceProvider AS Config;

Class AutoLoader
{
    protected $rootPath;

    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function registerNamespaces($namespace = false, $path = false)
    {
        $this->registerGenericNamespace($namespace, $path)
             ->registerLibraryNamespace()
             ->registerModelsNamespace()
             ->registerConfigNamespace()
             ->registerHelperNamespace();

        return $this;
    }

    protected function getAutoloader($namespace, $directory)
    {
        return function($className) USE ($namespace, $directory)
        {
            $path = explode('\\', $className);

            if ($path[0] === $namespace)
            {
                array_shift($path);
                $file = $directory.implode(DIRECTORY_SEPARATOR, $path).'.php';
                if (file_exists($file)) require_once $file;
            }
        };
    }

    public function registerGenericNamespace($namespace, $path = false)
    {
        if (! isset($namespace) || empty($namespace)) return $this; // borked

        $path = (FALSE === $path) 
            ? $namespace 
            : $path;

        if (is_dir($this->rootPath . "/$path/"))
        {
            $autoloader = $this->getAutoloader($path, $this->rootPath . "/$path/");
            $this->registerAutoloader($autoloader);
        }

        return $this;
    }

    public function registerLibraryNamespace()
    {
        $autoloader = $this->getAutoloader('Library', $this->rootPath .'/Library/');
        $this->registerAutoloader($autoloader);

        return $this;
    }

    public function registerModelsNamespace()
    {
        $autoloader = $this->getAutoloader('Models', $this->rootPath .'/Models/');
        $this->registerAutoloader($autoloader);

        return $this;
    }

    public function registerConfigNamespace()
    {
        $autoloader = $this->getAutoloader('ServiceProvider', $this->rootPath .'/Helpers/ServiceProvider/');
        $this->registerAutoloader($autoloader);

        return $this;
    }

    public function registerHelperNamespace()
    {
        $autoloader = $this->getAutoloader('Helper', $this->rootPath .'/Helper/');
        $this->registerAutoloader($autoloader);

        return $this;
    }

    protected function registerAutoloader($autoloader)
    {
        \spl_autoload_register($autoloader);
    }
}