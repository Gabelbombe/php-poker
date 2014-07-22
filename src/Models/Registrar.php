<?php

Namespace Models
{
    USE Database\Adapter;

    Class Registrar Extends Adapter
    {
        protected $user = false;

        public function __construct($utid)
        {
            parent::__construct($utid);

                $this->user = $utid;

            self::init();
        }

        public function register()
        {
            $st = self::$db->prepare(
                'INSERT INTO users SET utid = :utid'
            );

                $st->execute([
                    ':utid'     => $this->getUser()->getUtid(),
                ]);

           $st = self::$db->prepare(
                'INSERT INTO bank SET utid = :utid, wallet = :wallet'
           );

                $st->execute([
                    ':utid'     => $this->getUser()->getUtid(),
                    ':wallet'   => $this->getUser()->getWallet(),
                ]);
        }

        public function getUser()
        {
            return (isset($this->user) && ! empty($this->user))
                ? $this->user
                : null;
        }
    }
}