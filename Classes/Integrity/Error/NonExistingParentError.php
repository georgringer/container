<?php

declare(strict_types=1);

namespace B13\Container\Integrity\Error;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Messaging\AbstractMessage;

class NonExistingParentError implements ErrorInterface
{
    /**
     * @var array
     */
    protected $childRecord;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @param array $childRecord
     */
    public function __construct(array $childRecord)
    {
        $this->childRecord = $childRecord;
        $this->errorMessage = 'container child with uid ' . $childRecord['uid'] .
            ' has non existing tx_container_parent ' . $childRecord['tx_container_parent'];
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return int
     */
    public function getSeverity(): int
    {
        return AbstractMessage::ERROR;
    }
}
