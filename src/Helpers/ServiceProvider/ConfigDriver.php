<?php

Namespace ServiceProvider
{
    Interface ConfigDriver
    {
        function load($filename);
        function supports($filename);
    }
}