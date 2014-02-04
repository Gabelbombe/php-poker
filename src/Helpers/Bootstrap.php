<?php

/**
 * NOTE: This is NOT what a bootstrap is for, I know, stop talking smack =P
 */

Namespace Helpers
{
    USE Poker\DataAdapter   AS Adapter;
    USE Poker\HoldEm        AS Game; //backwards, should be using Poker\HoldEm AS Game or something

    Class Bootstrap
    {
        private $players = FALSE,
                $utid    = FALSE;


        public function __construct(array $payload = array())
        {
            // convert CLI opts to GET params if you're playing from the command line
            if (! $payload['type']) parse_str(implode("&", array_slice($payload['args'], 1)), $_GET);

            // normally wouldn't do this without filtering but I"m already over time....
            $this->players = (isset($_GET['players'])) ? $_GET['players'] : FALSE; // filter_input
        }


        public function run()
        {
            header('Content-type: text/plain; charset=UTF-8');

            if (! isset($_SESSION['utid'])) $this->createNewSession();


        }

        protected function assign()
        {
            if (isset($this->payload) && ! empty($this->payload))
            {
                // do nothing atm
            }
        }

        public function doView()
        {
            $game = New Game($this->players);

            echo "Let's go guys!!!\n";

            foreach (['flop', 'turn', 'river'] AS $part)
            {
                $action  = ('show' . ucfirst($part));
                echo "\n" . strtoupper($part) . ':' . implode(' ', $game->$action()); // cheap action calling
            }

            echo "\n\n" . ucwords(str_replace('_', ' ', $game->getWinner()))
                . " wins!!! ({$game->getWinningDescription()})"; // the winner

            echo "\n\n" . print_r($game->showPlayerHands(),  1);
            # echo "\n\n" . print_r($game->showPlayerPoints(), 1);

        }

        private function createNewSession()
        {
            $this->utid = md5(time() + rand());

            $adapter = New Adapter();

            print_r($adapter);

        }

    }
}