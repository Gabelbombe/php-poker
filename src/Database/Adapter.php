<?php

Namespace Database
{
    USE ServiceProvider\ConfigServiceProvider AS Config;

    Class Adapter
    {
        public  static      $db;
        private static      $_err = [];
        protected           $utid = null;

        public function __construct($utid)
        {
            $this->utid = $utid;
        }

        public static function init()
        {
            $config = New Config();
            $config->register();    //get base config

            $config = (object) $config->getConfig();
            try {
                self::$db = New \PDO("mysql:host={$config->DBHost};port={$config->DBPort};dbname={$config->DBName}", $config->DBUser, $config->DBPass,
                    [ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION ]
                );
                self::$_err['connects'] = json_encode(['outcome' => true]);
            }
            catch(PDOException $ex)
            {
                self::$_err['connects'] = json_encode(['outcome' => false, 'error' => $ex, 'message' => 'Unable to connect']);

                return false;
            }

            return true;
        }
    }
}