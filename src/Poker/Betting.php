<?php

Namespace Poker
{
    use Database\Broker;
    USE Rules\RulesInterface;

    Abstract Class Betting Implements RulesInterface
    {
        public      $bet        = 0;


        protected   $total      = 0;        //dunno if this should be public or not


        public function __construct()
        {
            // ....
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

    Final Class NoLimit
    {
        public function __construct()
        {
            parent::__construct();
        }
    }

    Final Class FixedLimit
    {
        public function __construct()
        {
            parent::__construct();
        }

    }

    Final Class PotLimit
    {
        public function __construct()
        {
            parent::__construct();
        }

    }
}
