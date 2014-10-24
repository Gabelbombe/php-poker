<?php

Namespace Helpers
{
    USE Database\Adapter;

    Class Accounts Extends Adapter
    {

        const WALLET_SIZE = 1000;

        protected $utid   = false,
                  $action = false,
                  $wallet = 0;

        private   $user   = false;

        public function __construct($utid = false)
        {
            parent::__construct($utid);

                $this->utid = $utid;

            $action = (! $utid ? 'generate' : 'fetch');

                $this->action = [
                    $action
                ];
print_r($this);

            return $this->$action();
        }

        public function generate()
        {
            $this->utid = md5(time() + rand()); // not very unique ;)
            $this->wallet = self::WALLET_SIZE;

            return $this;
        }


        public function getUser()
        {
            $st = self::$db->prepare(
                'SELECT * FROM users WHERE utid = :utid'
            );

                $st->execute([
                    ':utid'     => $this->utid,
                ]);

            $result = $st->fetchAll();
            print_r($this);

print_r($result); die;
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