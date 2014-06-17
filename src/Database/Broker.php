<?php

Namespace Poker
{
    USE ServiceProvider\ConfigServiceProvider AS Config;

    Class Adapter
    {
        public  static      $db;

        protected    $utid = null;

        private static      $_err = [];

        public function __construct($utid)
        {
            $this->utid = $utid;
        }

        public static function init()
        {
            $config = New Config(); //self::config();
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
                die;
            }

            return 'Connection established';
        }
    }

    /**
     * Class Session
     * @package Poker
     */
    Class Broker Extends Adapter
    {
        private $session   = [];

        public function __construct($utid, $payload)
        {
            parent::__construct($utid);

            $this->session = (object) [

                // globally available
                'u'     => $this->utid,

                // session_store
                'h'     => $payload->getGame()->getHands(1,0),
                'w'     => $payload->getGame()->getWinner(),

                // current_game
                'c'     => $payload->getPlayers(),

            ];

            parent::init();
        }

        public function push()
        {


            print_r($this); die;
            $st = self::$db->prepare(
                'INSERT INTO session_store SET utid = :utid, hand = :hand, winner = :winner, active = 1'
            );

            $st->execute([
                ':utid'     => $this->u,
                ':hand'     => $this->h,
                ':winner'   => $this->w,
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

            $vendor->c = [];
            foreach ($subject AS $mod)
            {
                // short circuit set if outside of blacklist
                (isset($blacklist[trim(substr($mod, 1))])) ?: $vendor->c[$mod[0]][] = trim(substr($mod, 1));
            }
            $vendor->c = json_encode(['t' => $vendor->t, 'c' => $vendor->c]);
            return $vendor;
        }

        private static function parse()
        {
            return parse_ini_file(APP_PATH . '/config/blacklist.ini');
        }
    }
}