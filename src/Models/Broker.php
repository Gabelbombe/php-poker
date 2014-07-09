<?php

Namespace Models
{
    USE Database\Adapter;

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

            print_r($this->session);

            parent::init();
        }

        public function push()
        {
            /**
             * INNER JOIN
             *
             * SELECT ss.utid AS 'UniversalID', ss.hand AS 'MainPlayersHand', ss.winner AS 'GameWinner', cg.serial AS 'SerializedData'
             * FROM current_game cg, session_store ss
             * WHERE ss.utid = :utid AND ss.active = 1 AND cg.utid = ss.utid
             *
             */

            $st = self::$db->prepare(
                'INSERT INTO session_store SET utid = :utid, hand = :hand, winner = :winner, active = 1'
            );

            $st->execute([
                ':utid'     => $this->session->u,
                ':hand'     => $this->session->h,
                ':winner'   => $this->session->w,
            ]);

            $st = self::$db->prepare(
                'INSERT INTO current_game SET utid = :utid, players = :players, serial = :serial'
            );

            $st->execute([
                ':utid'     => $this->session->u,
                ':players'  => $this->session->c,
                ':serial'   => $this->session->d,
            ]);

        }

        /**
         * Alias for JSON_ENCODE
         *
         * @param $data
         * @return string
         */
        private function encode($data)
        {
            return json_encode($data);
        }
    }
}