<?php

Namespace Poker
{
    USE ServiceProvider\ConfigServiceProvider AS Config;

    Class DataAdapter
    {
        public static   $db;
        private static  $_err = array();

        public static function init()
        {
            $config = New Config(); //self::config();
            $config->register();    //get base config

            $config = (object) $config->getConfig();

            try {
                self::$db = New \PDO("mysql:host={$config->DBHost};port={$config->DBPort};dbname={$config->DBName}", $config->DBUser, $config->DBPass,
                    [ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION ]
                );
                self::$_err['connects'] = json_encode(array('outcome' => true));
            }
            catch(PDOException $ex)
            {
                self::$_err['connects'] = json_encode(array('outcome' => false, 'error' => $ex, 'message' => 'Unable to connect'));
                die;
            }

            return 'Connection established';
        }

//        Legacy Code
//        private static function config()
//        {
//            return (object) parse_ini_file(APP_PATH .'/config/offshore.ini');
//        }

    }

    Class Session Extends DataAdapter
    {
        public static function push($vendor)
        {
            $st = self::$db->prepare(
                'INSERT INTO session_store SET utid = :utid, hand = :hand, winner = :winner, active = 1'
            );

            $vendor = self::cleanLogMessage($vendor);

            $st->execute([
                ':utid'     => $vendor->i,
                ':hand'     => $vendor->d,
                ':winner'   => $vendor->c,
            ]);
        }

        private static function cleanLogMessage($vendor)
        {
            // stage blacklisted files
            $blacklist = self::parse();
            $blacklist = array_flip(array_pop($blacklist)); // strict standard violation on pass-by-ref

            // trim marker, make base array
            $subject = preg_split ('/$\R?^/m', trim($vendor->c));

            if (! is_array($subject)) return $vendor;

            $vendor->c = array();
            foreach ($subject AS $mod)
            {
                // short circuit set if outside of blacklist
                (isset($blacklist[trim(substr($mod, 1))])) ?: $vendor->c[$mod[0]][] = trim(substr($mod, 1));
            }
            $vendor->c = json_encode(array('t' => $vendor->t, 'c' => $vendor->c));
            return $vendor;
        }

        private static function parse()
        {
            return parse_ini_file(APP_PATH . '/config/blacklist.ini');
        }
    }
}