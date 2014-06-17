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
                'h'     => $this->encode($payload->getGame()->getHands(1)),
                'w'     => $payload->getGame()->getWinner(),

                // current_game
                'c'     => $payload->getPlayers(),
                'd'     => $this->encode($payload->getGame()->getCompletedPlayerData())
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

        private function encode($data)
        {
            return json_encode($data);
        }

        private function decode($data)
        {
            return json_decode($data);
        }
    }

    Class Cachier Extends Adapter
    {
        public function __construct($utid)
        {
            parent::__construct($utid);
        }

        public function payout($total)
        {
            $st = self::$db->prepare(
                'INSERT INTO session_store SET utid = :utid, hand = :hand, winner = :winner, active = 1'
            );

            $st->execute([
                ':utid'     => $this->u,
                ':hand'     => $this->h,
                ':winner'   => $this->w,
            ]);
        }
    }
}