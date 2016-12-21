<?php

namespace VideoRecruit\Phalcon\Doctrine\Migrations;

/**
 * Common exception interface.
 */
interface Exception
{
}

/**
 * Class InvalidStateException
 */
class InvalidStateException extends \RuntimeException implements Exception
{
}

/**
 * Class InvalidArgumentException
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
}
