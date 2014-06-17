<?php

Namespace Poker
{
    /**
     * Class Deck
     * @package Poker
     */
    Class Actions
    {
        private $resetIndex    = [
            'deck', 'deckIndex', 'deckShuffle', 'playerHands'
        ];

        /**
         * 52 card poker deck
         */
        private $deck           = [
            'AC', '2C', '3C', '4C', '5C', '6C', '7C', '8C', '9C', '10C', 'JC', 'QC', 'KC',
            'AQ', '2Q', '3Q', '4Q', '5Q', '6Q', '7Q', '8Q', '9Q', '10Q', 'JQ', 'QQ', 'KQ',
            'AF', '2F', '3F', '4F', '5F', '6F', '7F', '8F', '9F', '10F', 'JF', 'QF', 'KF',
            'AP', '2P', '3P', '4P', '5P', '6P', '7P', '8P', '9P', '10P', 'JP', 'QP', 'KP',
        ];

        private $deckIndex 	    = 0;

        private $deckShuffle	= [],
                $playerHands 	= [];


        /**
         * Shuffles the decks order
         *
         * @return array
         */
        public function shuffle()
        {
            $this->deckIndex        = 0;
            $this->deckShuffle      = $this->deck;

                shuffle($this->deckShuffle);

            return $this->deckShuffle;
        }


        /**
         * Get each players hand
         *
         * @param $players
         * @param $cards
         * @return array
         * @throws \InvalidArgumentException
         */
        public function getHands($players, $cards)
        {
            if (FALSE === (is_int($players) || is_int($cards))) Throw New \InvalidArgumentException('Method only accepts integers.');

            $player = 0;
            $this->playerHands = [];

            for ($i = 1; $i <= $players; ++$i)
            {
                ++$player;

                $this->playerHands["player_{$player}"][] = $this->deckShuffle[$i];

                for ($j = 1; $j < $cards; ++$j)
                {
                    $this->playerHands["player_{$player}"][] = $this->deckShuffle[(($players * $j) + $player)];
                }
            }

            $this->deckIndex = ($players * $cards);

            return $this->playerHands;
        }

        /**
         * Dump all the hands in play
         *
         * @return array|bool
         */
        public function getAllHands()
        {
            return (isset($this->playerHands) && ! empty($this->playerHands))
                ? $this->playerHands
                : false;
        }


        public function encodePlayersHands()
        {
            return json_encode($this->playerHands);
        }

        /**
         * Pull {$number} of cards
         *
         * @param $number
         * @return array
         */
        public function draw($number)
        {
            ++$this->deckIndex;

            $drawCards = [];

            for ($i = 0; $i < $number; ++$i)
            {
                $drawCards[] = $this->deckShuffle[++$this->deckIndex];
            }

            return $drawCards;
        }


        /**
         * Returns to value of a card, without the consideration of its seed
         *
         * @param $card
         * @return string
         */
        public function value($card)
        {
            return substr($card, 0, -1);
        }


        /**
         * Returns the seed of a card
         *
         * @param $card
         * @return string
         */
        public function seed($card)
        {
            return substr($card, -1);
        }


        /**
         * Resets overall index
         */
        public function reset()
        {
            foreach ($this->resetIndex AS $item)
            {
                if (isset($this->$item)) unset($this->$item);
            }
        }
    }


    /**
     * Class Game
     * @package Poker
     */
    Class HoldEm Extends Actions
    {
        /**
         * @var int
         */
        protected $minPlayers       = 2;
        protected $maxPlayers       = 6;

        private $winner             = FALSE;

        /**
         * @var int
         */
        private $cards		        = 2;

        /**
         * @var array
         */
        private $playerHands 	    = [],
                $playerPts 	        = [],
                $state		        = [];


        /**
         * Description of cards / combinations
         *
         * @var array
         */
        private $descCards		    = [
            200	    => 'High',
            201     => 'Four',
            202     => 'Full House',
            203     => 'Kicker',
            204     => 'Pair of',
            205     => 'Three',
            14	    => 'Ace', #(high)    // per http://www.learn-texas-holdem.com/questions/is-ace-high-or-low-in-holdem.htm
            13 	    => 'King',
            12	    => 'Queen',
            11	    => 'Jack',
            10	    => 'Ten',
            9	    => 'Nine',
            8	    => 'Eight',
            7	    => 'Seven',
            6	    => 'Six',
            5	    => 'Five',
            4	    => 'Four',
            3	    => 'Three',
            2	    => 'Two',
            1	    => 'Ace', #(low)     // per http://www.learn-texas-holdem.com/questions/is-ace-high-or-low-in-holdem.htm
        ];


        private $descSuits           = [
            'C' => '♠',
            'Q' => '♣',
            'F' => '♦',
            'P' => '♥',
        ];

        /**
         * Description of points
         *
         * @var array
         */
        private $descPts		    = [
            1000 	=> 'Royal Flush',
            999		=> 'Straight Flush',
            900		=> 'Four of a Kind',
            800 	=> 'Full House',
            700 	=> 'Flush',
            600 	=> 'Straight',
            500 	=> 'Three of a Kind',
            400		=> 'Two Pair',
            300		=> 'One Pair',
            200		=> 'High Card',
        ];


        private $resetIndex    = [
            'cards', 'playerHands', 'playerPts', 'state', 'descCards', 'descPts',
        ];


        /**
         * Perhaps you'd better start from the beginning
         *
         * @param int $players
         * @throws \LogicException
         */
        public function __construct($players = 2)
        {
            if ($players > $this->maxPlayers) Throw New \LogicException("Max allowed players is {$this->maxPlayers}");

            // if players less than min requirement assign someone to play
            $players = (1 > $players) ? $this->minPlayers : $players;

            $this->shuffle();
            $this->playerHands = $this->getHands($players, $this->cards);
            $this->drawState();

            for ($p = 1; $p <= $players; ++$p)
            {
                $this->appraise($p);
            }
        }


        /**
         * Draw cards
         */
        private function drawState()
        {
            $this->state = // respect order of operations while assigning
            ([
                'flop'  => $this->draw(3),
                'turn'  => $this->draw(1),
                'river' => $this->draw(1),
            ] + $this->state);

            foreach ($this->state AS $cards)
            {
                foreach ($cards AS $card) $this->state['all'][] = $card;
            }
        }


    /**
     * TODO:
     * idk if this is a good idea muddies up code but calling
     * $this->show('flop'); seems like its a fun getter, prob
     * wasteful though and hard to read...
     *
     * @param $type
     * @return string
     *
        public function show($type)
        {
            return (isset($this->state[$type]))
                ? $this->state[$type]
                : ('hands' === strtolower($type)
                    ? $this->hands
                    : 'Must be of type flop, turn, river, all or hands'
                );
        }
     */

        /**
         * Get winning player
         *
         * @return mixed
         */
        public function getWinner()
        {
            $winner = [];

            foreach($this->playerPts AS $player => $set)
            {
                $winner[$player] = $set['value'];
            }

            return $this->winner = array_search(max($winner), $winner);
        }

        public function getWinningDescription()
        {
            return $this->playerPts[$this->winner]['description'];
        }

        public function convert($cards, $subArray = FALSE)
        {
            $cCards = [];
            $cards  = ($subArray)
                ? array_pop($cards)
                : $cards;

            foreach($cards AS $card)
            {
                $cCards[] = substr($card, 0, -1) . $this->descSuits[substr($card, -1)];
            }

            return $cCards;
        }

        public function getGame()
        {
            print_r($this); die;
        }

        /**
         * Return the players hand
         *
         * @return array
         */
        public function showPlayerHands()
        {
            $players = [];

            foreach($this->playerHands AS $player => $hand)
            {
                $players[$player] = $this->convert($hand);
            }

            return $players;
        }


        /**
         * Return the players points
         *
         * @return array
         */
        public function showPlayerPoints()
        {
            return $this->playerPts;
        }


        /**
         * Return the flop
         *
         * @return mixed
         */
        public function showFlop()
        {
            return $this->convert($this->state['flop']);
        }


        /**
         * Return the turn
         *
         * @return mixed
         */
        public function showTurn()
        {
            return $this->convert($this->state['turn']);
        }


        /**
         * Return the river
         *
         * @return mixed
         */
        public function showRiver()
        {
            return $this->convert($this->state['river']);
        }


        /**
         * Return the Game objects state
         *
         * @return mixed
         */
        public function showGameState()
        {
            return $this->state['all'];
        }


        /**
         * Appraise a players hand, assemble the value,
         * description and cards for it
         *
         * @param $player
         */
        private function appraise($player)
        {
            $playerCards = $this->getPlayersCards($player);
            $point = 0;


            ##### Checking for Royal Straight  #####


            // Order by seed
            $higher = $this->cardOrder($playerCards['higher']);
            $lower  = $this->cardOrder($playerCards['lower']);

            for ($i = 2; $i >= 0; --$i)
            {
                $rs = $this->straightMatch($i, $higher);

                if (60 === $this->straightValue($higher[$i]) && $rs['is_straight'])
                {
                    if ($this->seedMatch($i, $higher))
                    {
                        $this->playerPts["player_{$player}"] = [
                            'value' 		=> 1000,
                            'description' 	=> $this->descPts[1000],
                            'cards'			=> $rs['cards']
                        ];
                    }

                    $i = -1;
                }
            }


            ##### Checking for other Flush/Straight  #####


            for ($i = 2; $i >= 0; --$i)
            {
                $fs = $this->straightMatch($i, $higher);

                # only if player doesn't have a better point yet
                if (! isset($this->playerPts["player_{$player}"]['value']) && $fs['is_straight'])
                {
                    if ($this->seedMatch($i, $lower))
                    {
                        switch ($this->straightValue($lower[$i]))
                        {
                            case 55: $point = 999;	break; // 9 - K
                            case 50: $point = 998;	break; // 8 - Q
                            case 45: $point = 997;	break; // 7 - J
                            case 40: $point = 996;	break; // 6 - 10
                            case 35: $point = 995;	break; // 5 - 9
                            case 30: $point = 994;	break; // 4 - 8
                            case 25: $point = 993;	break; // 3 - 7
                            case 20: $point = 992;	break; // 2 - 6
                            case 15: $point = 991;	break; // 1 - 5
                        }

                        $this->playerPts["player_{$player}"] =
                        [
                            'value'         => $point,
                            'description' 	=> 	"{$this->descPts[999]}, {$this->descCards[$this->value($lower[($i + 4)])]} {$this->descCards[200]}",
                            'cards'			=> 	$fs['cards']
                        ];

                        $i = -1;
                    }

                }
            }


            ##### Checking for Four of a Kind  #####


            // Order cards
            $higher = $this->cardOrder($playerCards['higher'], 'natural');
            $kicker = NULL;

            for ($i = 3; $i >= 0; --$i)
            {
                $poker = $this->pokerMatch($i, $higher);

                if (! isset($this->playerPts["player_{$player}"]['value']) && $poker['is_poker'])
                {
                    switch ($i)
                    {
                        case 3:     $kicker = $higher[2]; 	break;
                        case 2:     // follows
                        case 1:     // follows
                        case 0:     $kicker = $higher[6];   break;
                    }

                    switch ($this->value($kicker))
                    {
                        case 14:	$point = 900;	break;
                        case 13: 	$point = 899;	break;
                        case 12: 	$point = 898;	break;
                        case 11: 	$point = 897;	break;
                        case 10: 	$point = 896;	break;
                        case 9: 	$point = 895;	break;
                        case 8: 	$point = 894;	break;
                        case 7: 	$point = 893;	break;
                        case 6: 	$point = 892;	break;
                        case 5: 	$point = 891;	break;
                        case 4: 	$point = 890;	break;
                        case 3: 	$point = 889;	break;
                        case 2: 	$point = 888;	break;
                    }

                    $poker['cards'][] = $kicker;

                    $this->playerPts["player_{$player}"] =
                    [
                        'value' 		=> $point,

                        // rem: {$this->descPts[900]}
                        'description' 	=> "{$this->descCards[201]} {$this->descCards[$this->value($higher[($i + 3)])]}s, {$this->descCards[$this->value($kicker)]} {$this->descCards[203]}",
                        'cards'			=> $poker['cards'],
                    ];
                }
            }


            ##### Checking for Full Houses #####


            for ($i = 4; $i >= 0; --$i)
            {
                $full = $this->matchFull($i, $higher);

                if (! isset($this->playerPts["player_{$player}"]['value']) && $full['is_full'])
                {
                    // Notice: Undefined offset: 3 in /var/www/holdem.dev/src/Poker/Game.php on line 487
                    // Notice: Undefined offset: 4 in /var/www/holdem.dev/src/Poker/Game.php on line 488
                    // was originally due to trying to compact arrays, prob a better way, reverted to
                    // lazy adding instead for now.....

                    $point =
                    (int) $this->value($full['cards'][0])   +
                    (int) $this->value($full['cards'][1])   +
                    (int) $this->value($full['cards'][2])   +
                    (int) $this->value($full['cards'][3])   +
                    (int) $this->value($full['cards'][4])   +
                    732;

                    $this->playerPts["player_{$player}"] =
                    [
                        'value' 		=> $point,
                        'description' 	=> "{$this->descCards[202]}, {$this->descCards[$this->value($full['cards'][2])]}s Over {$this->descCards[$this->value($full['cards'][0])]}s",
                        'cards'			=> $full['cards'],
                    ];
                }
            }


            ##### Checking for flushes #####


            // Order cards by seeds
            $higher = $this->cardOrder($playerCards['higher']);

            for ($i = 2; $i >= 0; --$i)
            {
                $flush = $this->flushMatch($i, $higher);

                # only if player doesn't have a better point yet
                if (! isset($this->playerPts["player_{$player}"]['value']) && $flush['is_flush'])
                {
                    switch ($this->value($flush['cards'][4]))
                    {
                        case 14:    $point = 700;   break;
                        case 13:    $point = 699;   break;
                        case 12:    $point = 698;   break;
                        case 11:    $point = 697;   break;
                        case 10:    $point = 696;   break;
                        case 9:     $point = 695;   break;
                        case 8:     $point = 694;   break;
                        case 7:     $point = 693;   break;
                    }

                    $this->playerPts["player_{$player}"] =
                    [
                        'value' 	    => $point,
                        'description'   => "{$this->descPts[700]}, {$this->descCards[$this->value($flush['cards'][4])]} {$this->descCards[200]}",
                        'cards'			=> $flush['cards'],
                    ];

                    $i = -1;
                }
            }


            ##### Checking for normal high straights #####


            // Order cards by their values, w/o considering the seed
            $higher = $this->cardOrder($higher, FALSE);
            $lower  = $this->cardOrder($lower,  FALSE);

            for ($i = 2; $i >= 0; --$i)
            {
                $straight = $this->straightMatch($i, $higher);

                # only if player doesn't have a better point yet
                if (! isset($this->playerPts["player_{$player}"]['value']) && 60 === $this->straightValue($higher[$i]) && $straight['is_straight'])
                {
                    $this->playerPts["player_{$player}"] =
                    [
                        'value' 	  => 600,
                        'description' => "{$this->descPts[600]}, {$this->descCards[$this->value($straight['cards'][4])]} {$this->descCards[200]}",
                        'cards' 	  => $straight['cards'],
                    ];

                    $i = -1;
                }
            }


            ##### Checking for normal straights #####


            for ($i = 2; $i >= 0; --$i)
            {
                $straight = $this->straightMatch($i, $lower);

                # only if player doesn't have a better point yet
                if (! isset($this->playerPts["player_{$player}"]['value']) && $straight['is_straight'])
                {
                    switch ($this->straightValue($lower[$i]))
                    {
                        case 55: $point = 699;	break; // 9 - K
                        case 50: $point = 698;	break; // 8 - Q
                        case 45: $point = 697;	break; // 7 - J
                        case 40: $point = 696;	break; // 6 - 10
                        case 35: $point = 695;	break; // 5 - 9
                        case 30: $point = 694;	break; // 4 - 8
                        case 25: $point = 693;	break; // 3 - 7
                        case 20: $point = 692;	break; // 2 - 6
                        case 15: $point = 691;	break; // 1 - 5
                    }

                    $this->playerPts["player_{$player}"] =
                    [
                        'value' 	  => $point,
                        'description' => "{$this->descPts[600]}, {$this->descCards[$this->value($straight['cards'][4])]} {$this->descCards[200]}",
                        'cards'		  => $straight['cards'],
                    ];

                    $i = -1;

                }
            }


            ##### Checking for Trips #####


            # Order cards
            $higher = $this->cardOrder($playerCards['higher'], 'natural');

            for ($i = 4; $i >= 0; --$i)
            {
                $trips = $this->matchTrips($i, $higher);

                # only if player doesn't have a better point yet
                if (! isset($this->playerPts["player_{$player}"]['value']) && $trips['is_trips'])
                {
                    switch ($this->value($trips['cards'][4]))
                    {
                        case 14:    $point = 500;   break;
                        case 13:    $point = 499;   break;
                        case 12:    $point = 498;   break;
                        case 11:    $point = 497;   break;
                        case 10:    $point = 496;   break;
                        case 9:     $point = 495;   break;
                        case 8:     $point = 494;   break;
                        case 7:     $point = 493;   break;
                        case 6:     $point = 492;   break;
                        case 5:     $point = 491;   break;
                        case 4:     $point = 490;   break;
                        case 3:     $point = 489;   break;
                        case 2:     $point = 488;   break;
                    }

                    $this->playerPts["player_{$player}"] =
                    [
                        'value'         => $point,

                        // rem from desc: {$this->descPts[500]},
                        'description'   => "{$this->descCards[205]} {$this->descCards[$this->value($trips['cards'][0])]}s, {$this->descCards[$this->value($trips['cards'][4])]} {$this->descCards[203]}",
                        'cards'         =>  $trips['cards'],
                    ];

                    $i = -1;
                }
            }


            ##### Checking for 2 Pairs #####


            for ($i = 3; $i >= 0; --$i)
            {
                $doubles = $this->matchTwoPair($i, $higher);

                # only if player doesn't have a better point yet
                if (! isset($this->playerPts["player_{$player}"]['value']) && $doubles['is_doubles'])
                {
                    $point =
                    (int) $this->value($doubles['cards'][0])	+
                    (int) $this->value($doubles['cards'][1])	+
                    (int) $this->value($doubles['cards'][2])	+
                    (int) $this->value($doubles['cards'][3])	+
                    (int) $this->value($doubles['cards'][4])	+
                    334;

                    $this->playerPts["player_{$player}"] =
                    [
                        'value' 		=> $point,

                        // rem: {$this->descPts[400]},
                        'description' 	=> "{$this->descCards[204]} {$this->descCards[$this->value($doubles['cards'][1])]}s and {$this->descCards[$this->value($doubles['cards'][3])]}s, {$this->descCards[$this->value($doubles['cards'][4])]} {$this->descCards[203]}",
                        'cards'			=> $doubles['cards'],
                    ];

                    $i = -1;
                }
            }


            ##### Checking for Single Pairs  #####


            for ($i = 5; $i >= 0; --$i)
            {
                $pair = $this->matchOnePair($i, $higher);

                # only if player doesn't have a better point yet
                if (! isset($this->playerPts["player_{$player}"]['value']) && $pair['is_pair'])
                {
                    $point =
                    (int) $this->value($pair['cards'][0])	+
                    (int) $this->value($pair['cards'][1])	+
                    (int) $this->value($pair['cards'][2])	+
                    (int) $this->value($pair['cards'][3])	+
                    (int) $this->value($pair['cards'][4])	+
                    236;

                    $this->playerPts["player_{$player}"] =
                    [
                        'value' 		=> 	$point,

                        // rem: {$this->descPts[300]},
                        'description' 	=>	"{$this->descCards[204]} {$this->descCards[$this->value($pair['cards'][1])]}s, {$this->descCards[$this->value($pair['cards'][4])]} {$this->descCards[203]}",
                        'cards'			=> $pair['cards'],
                    ];
                }
            }


            ##### Checking for High Card #####


            // only if player doesn't have a better point yet
            if (! isset($this->playerPts["player_{$player}"]['value']))
            {
                $point =
                (int) $this->value($higher[6])	+
                (int) $this->value($higher[5])	+
                (int) $this->value($higher[4])	+
                (int) $this->value($higher[3])	+
                (int) $this->value($higher[2])	+
                141;

                $this->playerPts["player_{$player}"] =
                [
                    'value' 		=> $point,
                    'description' 	=> "{$this->descPts[200]}, {$this->descCards[$this->value($higher[6])]} {$this->descCards[200]}",
                    'cards'			=> [$higher[6], $higher[5], $higher[4], $higher[3], $higher[2]],
                ];
            }

            $this->playerPts["player_{$player}"]['hand'] =
            [
                $playerCards['higher'][5],
                $playerCards['higher'][6],
            ];
        }


        /**
         * Simple single pairs check
         *
         * @param $id
         * @param $cards
         * @return array
         */
        private function matchOnePair($id, $cards)
        {
            $pair = ['is_pair' => FALSE];

            if ($this->value($cards[$id]) == $this->value($cards[($id + 1)]))
            {
                switch ($id)
                {
                    case 5: $pair = ['is_pair' => TRUE, 'cards' => [$cards[5], $cards[6], $cards[2], $cards[3], $cards[4]]]; break;
                    case 4: $pair = ['is_pair' => TRUE, 'cards' => [$cards[4], $cards[5], $cards[3], $cards[4], $cards[6]]]; break;
                    case 3: $pair = ['is_pair' => TRUE, 'cards' => [$cards[3], $cards[4], $cards[2], $cards[5], $cards[6]]]; break;
                    case 2: $pair = ['is_pair' => TRUE, 'cards' => [$cards[2], $cards[3], $cards[4], $cards[5], $cards[6]]]; break;
                    case 1: $pair = ['is_pair' => TRUE, 'cards' => [$cards[1], $cards[2], $cards[4], $cards[5], $cards[6]]]; break;
                    case 0: $pair = ['is_pair' => TRUE, 'cards' => [$cards[0], $cards[1], $cards[4], $cards[5], $cards[6]]]; break;
                }
            }

            return $pair;
        }


        /**
         * Simple Doubles check
         *
         * @param $id
         * @param $cards
         * @return array
         */
        private function matchTwoPair($id, $cards)
        {
            $twopair = ['is_doubles' => FALSE];

            switch ($id)
            {
                case 3:

                    if ($this->value($cards[3]) === $this->value($cards[4]) && $this->value($cards[5]) === $this->value($cards[6]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[3], $cards[4], $cards[5], $cards[6], $cards[2]]];
                    }

                    elseif ($this->value($cards[3]) === $this->value($cards[4]) && $this->value($cards[1]) === $this->value($cards[2]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[1], $cards[2], $cards[3], $cards[4], $cards[6]]];
                    }

                    elseif ($this->value($cards[3]) === $this->value($cards[4]) && $this->value($cards[0]) === $this->value($cards[1]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[0], $cards[1], $cards[3], $cards[4], $cards[6]]];
                    }

                break;

                case 2:

                    if ($this->value($cards[2]) === $this->value($cards[3]) && $this->value($cards[4]) === $this->value($cards[5]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[2], $cards[3], $cards[4], $cards[5], $cards[6]]];
                    }

                    elseif ($this->value($cards[2]) === $this->value($cards[3]) && $this->value($cards[5]) === $this->value($cards[6]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[2], $cards[3], $cards[5], $cards[6], $cards[4]]];
                    }

                    elseif ($this->value($cards[2]) === $this->value($cards[3]) && $this->value($cards[0]) === $this->value($cards[1]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[0], $cards[1], $cards[2], $cards[3], $cards[6]]];
                    }

                break;

                case 1:

                    if ($this->value($cards[1]) === $this->value($cards[2]) && $this->value($cards[3]) === $this->value($cards[4]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[1], $cards[2], $cards[3], $cards[4], $cards[6]]];
                    }

                    elseif ($this->value($cards[1]) === $this->value($cards[2]) && $this->value($cards[4]) === $this->value($cards[5]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[1], $cards[2], $cards[4], $cards[5], $cards[6]]];
                    }

                    elseif ($this->value($cards[1]) === $this->value($cards[2]) && $this->value($cards[5]) === $this->value($cards[6]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[1], $cards[2], $cards[5], $cards[6], $cards[4]]];
                    }

                break;

                case 0:

                    if ($this->value($cards[0]) === $this->value($cards[1]) && $this->value($cards[2]) === $this->value($cards[3]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[0], $cards[1], $cards[2], $cards[3], $cards[6]]];
                    }

                    elseif ($this->value($cards[0]) === $this->value($cards[1]) && $this->value($cards[3]) === $this->value($cards[4]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[0], $cards[1], $cards[3], $cards[4], $cards[6]]];
                    }

                    elseif ($this->value($cards[0]) === $this->value($cards[1]) && $this->value($cards[4]) === $this->value($cards[5]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[0], $cards[1], $cards[4], $cards[5], $cards[6]]];
                    }

                    elseif ($this->value($cards[0]) === $this->value($cards[1]) && $this->value($cards[5]) === $this->value($cards[6]))
                    {
                        $twopair = ['is_doubles' => TRUE, 'cards' => [$cards[0], $cards[1], $cards[5], $cards[6], $cards[4]]];
                    }

                break;
            }
            return $twopair;
        }


        /**
         * Simple Trips check
         *
         * @param $id
         * @param $cards
         * @return array
         */
        private function matchTrips($id, $cards)
        {
            $constraint = ['is_trips' => FALSE];

            if ($this->value($cards[$id]) === $this->value($cards[($id + 1)]) && $this->value($cards[$id]) === $this->value($cards[($id + 2)]))
            {
                switch ($id)
                {
                    case 4:
                        $constraint = ['is_trips' => TRUE, 'cards' => [$cards[$id], $cards[($id + 1)], $cards[($id + 2)], $cards[2], $cards[3]]];
                    break;

                    case 3:
                        $constraint = ['is_trips' => TRUE, 'cards' => [$cards[$id], $cards[($id + 1)], $cards[($id + 2)], $cards[2], $cards[6]]];
                    break;

                    case 2:
                        $constraint = ['is_trips' => TRUE, 'cards' => [$cards[$id], $cards[($id + 1)], $cards[($id + 2)], $cards[5], $cards[6]]];
                    break;

                    case 1:
                        $constraint = ['is_trips' => TRUE, 'cards' => [$cards[$id], $cards[($id + 1)], $cards[($id + 2)], $cards[5], $cards[6]]];
                    break;

                    case 0:
                        $constraint = ['is_trips' => TRUE, 'cards' => [$cards[$id], $cards[($id + 1)], $cards[($id + 2)], $cards[5], $cards[6]]];
                    break;
                }
            }
            return $constraint;
        }


        /**
         * Simple Flushes check
         *
         * @param $id
         * @param $cards
         * @return array
         */
        private function flushMatch($id, $cards)
        {
            $constraint = ['is_flush' => FALSE];

            if ($this->seed($cards[$id]) === $this->seed($cards[($id + 1)])	&& $this->seed($cards[$id]) === $this->seed($cards[($id + 2)])
            &&  $this->seed($cards[$id]) === $this->seed($cards[($id + 3)])	&& $this->seed($cards[$id]) === $this->seed($cards[($id + 4)]))
            {
                $constraint = ['is_flush' => TRUE, 'cards' => [$cards[$id], $cards[($id + 1)], $cards[($id + 2)], $cards[($id + 3)], $cards[($id + 4)]]];
            }
            return $constraint;
        }


        /**
         * Simple Full House check
         *
         * @param $id
         * @param $cards
         * @return array
         */
        private function matchFull($id, $cards)
        {
            $constraint = ['is_full' => FALSE];
            $full = FALSE;

            switch ($id)
            {
                case 4:
                    if ($this->value($cards[$id]) === $this->value($cards[($id + 1)]) && $this->value($cards[$id]) === $this->value($cards[($id + 2)]))
                    {
                        if ($this->value($cards[($id - 1)]) === $this->value($cards[($id - 2)])) $full = [$cards[($id - 1)], $cards[($id - 2)]];
                        if ($this->value($cards[($id - 2)]) === $this->value($cards[($id - 3)])) $full = [$cards[($id - 2)], $cards[($id - 3)]];
                        if ($this->value($cards[($id - 3)]) === $this->value($cards[($id - 4)])) $full = [$cards[($id - 3)], $cards[($id - 4)]];

                        if (is_array($full))
                        {
                            // Doesn't work =/
                            // $constraint = ['is_full' => TRUE, 'cards' => ([$cards[$id], $cards[($id + 1)], $cards[($id + 2)]] + $full)];

                                $full[] = $cards[$id];
                                $full[] = $cards[($id + 1)];
                                $full[] = $cards[($id + 2)];

                            $constraint = array('is_full' => TRUE, 'cards' => $full);
                        }
                    }
                break;

                case 3:
                    if ($this->value($cards[$id]) === $this->value($cards[($id + 1)]) && $this->value($cards[$id]) === $this->value($cards[($id + 2)]))
                    {
                        if ($this->value($cards[($id - 1)]) === $this->value($cards[($id - 2)])) $full = [$cards[($id - 1)], $cards[($id - 2)]];
                        if ($this->value($cards[($id - 2)]) === $this->value($cards[($id - 3)])) $full = [$cards[($id - 2)], $cards[($id - 3)]];

                        if (is_array($full))
                        {
                            // Doesn't work =/
                            // $constraint = ['is_full' => TRUE, 'cards' => ([$cards[$id], $cards[($id + 1)], $cards[($id + 2)]] + $full)];

                                $full[] = $cards[$id];
                                $full[] = $cards[($id + 1)];
                                $full[] = $cards[($id + 2)];

                            $constraint = array('is_full' => TRUE, 'cards' => $full);

                        }
                    }
                break;

                case 2:
                    if ($this->value($cards[$id]) === $this->value($cards[($id + 1)]) && $this->value($cards[$id]) == $this->value($cards[($id + 2)]))
                    {
                        if ($this->value($cards[($id - 1)]) === $this->value($cards[($id - 2)])) $full = [$cards[($id - 1)], $cards[($id - 2)]];
                        if ($this->value($cards[5])         === $this->value($cards[6]))         $full = [$cards[5], $cards[6]];

                        if (is_array($full))
                        {
                            // Doesn't work =/
                            // $constraint = ['is_full' => TRUE, 'cards' => ([$cards[$id], $cards[($id + 1)], $cards[($id + 2)]] + $full)];

                                $full[] = $cards[$id];
                                $full[] = $cards[($id + 1)];
                                $full[] = $cards[($id + 2)];

                            $constraint = array('is_full' => TRUE, 'cards' => $full);
                        }
                    }
                break;

                case 1:
                    if ($this->value($cards[$id]) === $this->value($cards[($id + 1)]) && $this->value($cards[$id]) === $this->value($cards[($id + 2)]))
                    {
                        if ($this->value($cards[4]) === $this->value($cards[5])) $full = [$cards[4], $cards[5]];
                        if ($this->value($cards[5]) === $this->value($cards[6])) $full = [$cards[5], $cards[6]];

                        if (is_array($full))
                        {
                            // Doesn't work =/
                            // $constraint = ['is_full' => TRUE, 'cards' => ([$cards[$id], $cards[($id + 1)], $cards[($id + 2)]] + $full)];

                                $full[] = $cards[$id];
                                $full[] = $cards[($id + 1)];
                                $full[] = $cards[($id + 2)];

                            $constraint = array('is_full' => TRUE, 'cards' => $full);
                        }
                    }
                break;

                case 0:
                    if ($this->value($cards[$id]) === $this->value($cards[($id + 1)]) && $this->value($cards[$id]) === $this->value($cards[($id + 2)]))
                    {
                        if ($this->value($cards[3]) === $this->value($cards[4])) $full = [$cards[3], $cards[4]];
                        if ($this->value($cards[4]) === $this->value($cards[5])) $full = [$cards[4], $cards[5]];
                        if ($this->value($cards[5]) === $this->value($cards[6])) $full = [$cards[5], $cards[6]];

                        if (is_array($full))
                        {
                            // Doesn't work =/
                            // $constraint = ['is_full' => TRUE, 'cards' => ([$cards[$id], $cards[($id + 1)], $cards[($id + 2)]] + $full)];

                                $full[] = $cards[$id];
                                $full[] = $cards[($id + 1)];
                                $full[] = $cards[($id + 2)];

                            $constraint = array('is_full' => TRUE, 'cards' => $full);
                        }
                    }
                break;
            }
            return $constraint;
        }


        /**
         * Simple Match check
         *
         * @param $id
         * @param $cards
         * @return array
         */
        private function pokerMatch($id, $cards)
        {
            $poker = ['is_poker' => FALSE];

            if ($this->value($cards[$id]) === $this->value($cards[($id + 1)])
            &&  $this->value($cards[$id]) === $this->value($cards[($id + 2)])
            &&  $this->value($cards[$id]) === $this->value($cards[($id + 3)]))
            {
                $poker = ['is_poker' => TRUE, 'cards' => [$cards[$id], $cards[($id + 1)], $cards[($id + 2)], $cards[($id + 3)]]];
            }
            return $poker;
        }


        /**
         * Return the value of the straight
         *
         * @param $val
         * @return int
         */
        private function straightValue($val)
        {
            return (((int) $this->value($val) * 5) + 10);
        }


        /**
         * Straights should be matched on their unique 4 number series
         *
         * @param $id
         * @param $cards
         * @return array
         */
        private function straightMatch($id, $cards)
        {
            $straight = ['is_straight' => FALSE];

            if (($this->straightValue($cards[$id])             ===
                    (
                        (int) $this->value($cards[$id])         +
                        (int) $this->value($cards[($id + 1)])   +
                        (int) $this->value($cards[($id + 2)])   +
                        (int) $this->value($cards[($id + 3)])   +
                        (int) $this->value($cards[($id + 4)])
                    )
                )

                &&

                (($this->straightValue($cards[$id]) - 15) / 5) ===
                (
                    (
                        (int) $this->value($cards[$id])         +
                        (int) $this->value($cards[($id + 1)])   +
                        (int) $this->value($cards[($id + 2)])   +
                        (int) $this->value($cards[($id + 3)])   -
                        10
                    ) / 4
                )

                &&

                (($this->straightValue($cards[$id]) - 15) / 5) ===
                (
                    (
                        (int) $this->value($cards[$id])         +
                        (int) $this->value($cards[($id + 1)])   +
                        (int) $this->value($cards[($id + 2)])   -
                        6
                    ) / 3
                )

                &&

                (($this->straightValue($cards[$id]) - 15) / 5) ===
                (
                    (
                        (int) $this->value($cards[$id])         +
                        (int) $this->value($cards[($id + 1)])   -
                        3
                    ) / 2
                )
              )
            {
                $straight = ['is_straight' => TRUE, 'cards' => [$cards[$id], $cards[($id + 1)], $cards[($id + 2)], $cards[($id + 3)], $cards[($id + 4)]]];
            }
            return $straight;
        }


        /**
         * Simple seed matching
         *
         * @param $id
         * @param $seeds
         * @return bool
         */
        private function seedMatch($id, $seeds)
        {
            return
            (
                $this->seed($seeds[$id]) === $this->seed($seeds[($id + 1)]) &&
                $this->seed($seeds[$id]) === $this->seed($seeds[($id + 2)]) &&
                $this->seed($seeds[$id]) === $this->seed($seeds[($id + 3)]) &&
                $this->seed($seeds[$id]) === $this->seed($seeds[($id + 4)])
            );
        }


        /**
         * Order cards by seed or value
         *
         * @param $cards
         * @param bool $bySeed
         * @return array
         */
        private function cardOrder($cards, $bySeed = TRUE)
        {
            $ordCards = [];
            $order = [];

            // sort by intval reduction
            usort($cards, function ($a, $b)
            {
                return (intval(substr($a, 0, -1)) - intval(substr($b, 0, -1)));
            });

            if (TRUE === $bySeed)
            {
                foreach ($cards AS $card)
                {
                    $order[$this->seed($card)][] = $card;
                }

                foreach ($order AS $seedOrdCards)
                {
                    foreach ($seedOrdCards AS $card) $ordCards[] = $card;
                }
            }
            
            elseif (FALSE === $bySeed )
            {
                $order =
                [
                    'primary'   => [],
                    'secondary' => [],
                ];

                foreach ($cards AS $card)
                {
                    if (! in_array($this->value($card), $order['primary']))
                    {
                        $ordCards[] = $card;
                        $order['primary'][] = $this->value($card);
                    }

                    else
                    {
                        $order['secondary'][] = $card;
                    }
                }

                foreach ($order['secondary'] AS $card)
                {
                    $ordCards[] = $card;
                }
            }

            else
            {
                $ordCards = $cards;
            }

            unset($order);

            return $ordCards;
        }

        /**
         * Get the players cards
         *
         * @param $player
         * @return array
         */
        private function getPlayersCards($player)
        {
            $allCards = $this->state['all'];
            $higher = [];
            $lower 	= [];

            foreach ($this->playerHands["player_{$player}"] AS $card)
            {
                    $allCards[] = $card;
            }

            foreach ($allCards AS $card)
            {
                $card = str_replace('J', 11, $this->value($card)) . $this->seed($card);
                $card = str_replace('Q', 12, $this->value($card)) . $this->seed($card);
                $card = str_replace('K', 13, $this->value($card)) . $this->seed($card);
                $card = str_replace('A', 14, $this->value($card)) . $this->seed($card);

                $higher[] = $card;

                    $card = str_replace(14, 1, $this->value($card)) . $this->seed($card);

                $lower[] = $card;
            }
            return ['all' => $allCards, 'higher' => $higher, 'lower' => $lower];
        }


        /**
         * Resets overall index, along with parents
         */
        public function reset()
        {
            parent::reset(); // reset parent too

            foreach ($this->resetIndex AS $item)
            {
                if (isset($this->$item)) unset($this->$item);
            }
        }
    }
}