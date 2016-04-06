<?php

namespace Eole\Silex\Doctrine;

use Doctrine\DBAL\Logging\SQLLogger;

class FileLogger implements SQLLogger
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        file_put_contents($this->filename, $sql.PHP_EOL, FILE_APPEND);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
