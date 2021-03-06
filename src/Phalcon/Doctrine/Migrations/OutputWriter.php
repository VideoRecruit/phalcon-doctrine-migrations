<?php

/*
 * This file is part of Zenify
 * Copyright (c) 2014 Tomas Votruba (http://tomasvotruba.cz)
 */

namespace VideoRecruit\Phalcon\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\OutputWriter as DoctrineOutputWriter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OutputWriter
 *
 * @package VideoRecruit\Phalcon\Doctrine\Migrations
 *
 */
class OutputWriter extends DoctrineOutputWriter
{

	/**
	 * @var OutputInterface
	 */
	private $consoleOutput;

	/**
	 * @param OutputInterface $consoleOutput
	 */
	public function setConsoleOutput(OutputInterface $consoleOutput)
	{
		$this->consoleOutput = $consoleOutput;
	}

	/**
	 * @param string $message
	 */
	public function write($message)
	{
		$this->getConsoleOutput()->writeln($message);
	}

	/**
	 * @return ConsoleOutput
	 */
	private function getConsoleOutput()
	{
		if ($this->consoleOutput === NULL) {
			$this->consoleOutput = new ConsoleOutput;
		}

		return $this->consoleOutput;
	}
}
