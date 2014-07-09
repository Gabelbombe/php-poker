<?php
/**
 * http://www.pokerlistings.com/texas-holdem-betting-rules
 */
Namespace Poker
{
    USE Database\Broker;
    USE Rules\RulesInterface;

    Abstract Class Betting Implements RulesInterface
    {
        public      $bet        = 0;


        protected   $total      = 0;        //dunno if this should be public or not


        public function __construct($broker)
        {
            $this->broker = $broker;
        }

        public function check()
        {
            return $this;
        }

        public function bet($bet)
        {
            $this->setBet($bet)->setTotal();
        }

        public function fold()
        {
            $cachier = New Cachier();
            $cachier->payout($this->total);
        }

    ////////////////////////////////////////
    ////////////////////////////////////////
    ////////////////////////////////////////

        public function getBet()
        {
            return $this->bet;
        }

        private function setBet($bet)
        {
            $this->bet = $bet;

                return $this;
        }

        public function setTotal()
        {
            $this->total += $this->getBet();

                return $this;
        }

    }


    /**
     * Class NoLimit
     * @package Poker
     */
    Final Class NoLimit Extends Betting
    {
        public function __construct($broker)
        {
            parent::__construct($broker);
        }
    }


    /**
     * Class FixedLimit
     * @package Poker
     */
    Final Class FixedLimit Extends Betting
    {
        public function __construct($broker)
        {
            parent::__construct($broker);
        }
    }


    /**
     * Class PotLimit
     * @package Poker
     */
    Final Class PotLimit Extends Betting
    {
        public function __construct($broker)
        {
            parent::__construct($broker);
        }
    }
}
