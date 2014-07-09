<?php

Namespace Helpers
{
    Class Accounts
    {
        protected $utid  = false,
                  $action = false;

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
            return $this->utid = md5(time() + rand()); // not very unique ;)
        }

        public function fetch()
        {
            //$this->fetchFromDB($utid);
        }
    }
}