<?php

namespace B13\Container\Tests\Functional\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class DatahandlerTest extends FunctionalTestCase
{
    /**
     * @var DataHandler
     */
    protected $dataHandler;

    /**
     * @var BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/container',
        'typo3conf/ext/container_example'
    ];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = ['workspaces'];

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        Bootstrap::initializeLanguageObject();
        $this->backendUser = $this->setUpBackendUserFromFixture(1);
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        return $queryBuilder;
    }

    /**
     * @param string $field
     * @param int $id
     * @return array
     */
    protected function fetchOneRecord(string $field, int $id): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    $field,
                    $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        self::assertIsArray($row);
        return $row;
    }
}
