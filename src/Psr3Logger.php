<?php

/*
 * This file is part of DBAL PSR-3 logger package
 *
 * Copyright (c) 2017 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/dbal-psr3-logger
 *
 */

namespace Tuupola\DBAL\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\SQLParserUtils;

class Psr3Logger implements SQLLogger
{
    public $logger;
    public $connection;
    public $sql = "";
    public $start = null;

    public function __construct($logger, Connection $connection)
    {
        $this->logger = $logger;
        $this->connection = $connection;
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->start = microtime(true);

        if ($params) {
            list($sql, $params, $types) = SQLParserUtils::expandListParameters($sql, $params, $types);

            $quotedParams = array();
            foreach ($params as $typeIndex => $value) {
                $quotedParams[] = $this->connection->quote($value, isset($types[$typeIndex]) ? $types[$typeIndex] : \PDO::PARAM_STR);
            }

            $this->sql = vsprintf(str_replace("?", "%s", $sql), $quotedParams);
        } else {
            $this->sql = $sql;
        }
    }

    public function stopQuery()
    {
        $elapsed = microtime(true) - $this->start;
        $this->sql .= " -- {$elapsed}";
        $this->logger->debug($this->sql);
    }
}
