<?php

Namespace Helpers
{
    Class Metrics
    {
        public      $input   = [];

        protected   $players = 0;

        public function __construct(array $input)
        {
            $this->input = $input;
        }

        /**
         * Must have args to run
         *
         * @return $this
         * @throws \RuntimeException
         */
        public function hasInput()
        {
            // Should be a global setter
            if (empty($this->input)) Throw New \RuntimeException('Input cannot be empty..');

                return $this;
        }


        /**
         * Count players than can play, 1 >= $_GET
         *
         * @return $this
         * @throws \LogicException
         */
        public function cntPlyrs()
        {
            $players = filter_var($this->input['players'], FILTER_VALIDATE_INT );

                $this->players = (isset($players) && ! empty($players))
                    ? $players
                    : false;

            // better way to handle none equal length strings than throw?
            if (false === $this->players || 1 >= $this->players) Throw New \LogicException('Players must be present or greater than 1...');

                return $this;
        }
    }
}