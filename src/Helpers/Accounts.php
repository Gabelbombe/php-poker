<?php

Namespace Helpers
{
    Class Accounts
    {

        const WALLET_SIZE = 1000;

        protected $utid   = false,
                  $action = false,
                  $wallet = 0;

        public function __construct($utid = false)
        {
            $action = (! $utid ? 'generate' : 'fetch');

            $this->action = [
                $action
            ];

                return $this->$action();
        }

        public function generate()
        {
            $this->utid = md5(time() + rand()); // not very unique ;)
            $this->wallet = self::WALLET_SIZE;

            return $this;
        }

        public function fetch()
        {
            //$this->fetchFromDB($utid);
        }

        public function getWallet()
        {
            return (isset($this->wallet) && ! empty($this->wallet))
                ? $this->wallet
                : null;
        }


        public function getUtid()
        {
            return (isset($this->utid) && ! empty($this->utid))
                ? $this->utid
                : null;
        }

    }
}