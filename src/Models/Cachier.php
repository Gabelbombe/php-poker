<?php

Namespace Models
{
    USE Database\Adapter;

    Class Cachier Extends Adapter
    {
        protected $user = false;

        public function __construct($utid)
        {
            parent::__construct($utid);

                $this->user = $utid;

            self::init();
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