<?php

/**
 * NOTE: This is NOT what a bootstrap is for, I know, stop talking smack =P
 */

Namespace Helpers
{
    USE Players\Live;                       /** not sure what this is */

    USE Poker\HoldEm        AS Game,        //backwards, should be using Poker\HoldEm AS Game or something
        Models\Registrar    AS Registrar,
        ServiceProvider     AS Config;      //base config selector


    Class Bootstrap
    {
        private     $players = false,
                    $utid    = false;

        protected   $game    = false;

        public function __construct(array $payload = [])
        {
            define('CLI', (! $payload['type'] ?: 0));

            /**
             * Move below to some other parsing class
             * GET will be altered with POST sometime
             * later(ish)
             */

            // convert CLI opts to GET params if you're playing from the command line
            if (CLI) parse_str(implode("&", array_slice($payload['args'], 1)), $_GET);


            // session isn't being stored over CLI so we need some witchcraft...
            $this->session = (isset($_GET['session'])) ? $_GET['session'] : false;

            // normally wouldn't do this without filtering but I"m already over time....
            $this->players = (isset($_GET['players'])) ? $_GET['players']  : false; // filter_input

            // available rules: NoLimit,
            $this->rules = (isset($_GET['rules']))
                ? $this->setRules($_GET['rules'])
                : 'nolimit';

        }


        public function run()
        {
            header('Content-type: text/plain; charset=UTF-8');

            if (false === $this->session)
            {
                $this->newUser(New Accounts());
            } else {
                die('lookup');
            }
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

        private function createSession()
        {
            print_r($this);
            print_r($_COOKIE);
            print_r($_SESSION);

            $_SESSION['utid'] = $this->utid;
            $_COOKIE['utid']  = $this->utid;

            print_r($this);
            print_r($_COOKIE);
            print_r($_SESSION);
die;
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

        private function newUser($utid)
        {

            $account = New Registrar($utid);
            $account->register();

            $this->setUtid($account->getUser()->getUtid());

                echo "New user: {$this->utid} registered!!";

            $this->createSession();
        }

        private function setUtid($utid)
        {
            return $this->utid = $utid;
        }
    }
}