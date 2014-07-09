<?php

Namespace Players
{
    USE Actions\ActionsInterface,
        Poker\Betting;

    Abstract Class Actions Extends Betting Implements ActionsInterface
    {
        public function __construct()
        {
            // .....
        }
    }
}