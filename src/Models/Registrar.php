<?php

Namespace Models
{
    USE Database\Adapter;

    Class Registrar Extends Adapter
    {
        protected $utid = false;

        public function __construct($utid)
        {
            parent::__construct($utid);

            $this->utid = $utid;
        }

        public function register()
        {
            $st = self::$db->prepare(
                'INSERT INTO user SET utid = :utid'
            );

                $st->execute([
                    ':utid'     => $this->utid,
                ]);

            $st = self::$db->prepare(
                'INSERT INTO bank SET utid = :=utid'
            );

                $st->execute([
                    ':utid'     => $this->utid,
                ]);
        }
    }
}