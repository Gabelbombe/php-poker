<?php

/**
 * NOTE: This is NOT what a bootstrap is for, I know, stop talking smack =P
 */

Namespace Helpers
{
    USE Poker\HoldEm        AS Game;        //backwards, should be using Poker\HoldEm AS Game or something
    USE Database\Broker     AS Broker;      //DB Connector
    USE ServiceProvider     AS Config;      //base config selector


    Class Bootstrap
    {
        private     $players = FALSE,
                    $utid    = FALSE;

        protected   $game    = NULL;


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


        /**
         * Crates and assigns a current game for session
         *
         * @return $this
         */
        public function createGame()
        {
            $this->game = New Game($this->players);

                return $this;
        }


        /**
         * Returns the current game
         *
         * @return object|null
         */
        public function getGame()
        {
            return (isset($this->game) && ! empty($this->game))
                ? $this->game
                : false;
        }

        public function getPlayers()
        {
            return (true === (0 < $this->players) && is_numeric($this->players))
                ? $this->players
                : false;
        }

        public function doView()
        {
            $game = New Game($this->players);

            echo "Let's go guys!!!\n";

            echo "\n\n" . print_r($game->showPlayerHands(),  1);

            foreach (['flop', 'turn', 'river'] AS $part)
            {
                $action  = ('show' . ucfirst($part));
                echo "\n" . strtoupper($part) . ':' . implode(' ', $game->$action()); // cheap action calling
                sleep(3);
            }

            echo "\n\n" . ucwords(str_replace('_', ' ', $game->getWinner()))
                . " wins!!! ({$game->getWinningDescription()})"; // the winner

            # echo "\n\n" . print_r($game->showPlayerPoints(), 1);

        }

        private function createNewSession()
        {
            $this->utid = md5(time() + rand());

            $session = New Broker($this->utid, $this->createGame());
            $session->push();

            echo "Let's go guys!!!\nYou're hand: " . implode(' ', $this->getUsersHand());
            echo "\n\n";


            die;
            $this->doView();
        }

        private function getUsersHand()
        {

            return $this->game->convert($this->game->getHands(1, 2), 1);
        }
    }
}