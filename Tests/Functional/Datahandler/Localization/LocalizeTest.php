<?php

declare(strict_types=1);
namespace B13\Container\Tests\Functional\Datahandler\Localization;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Functional\Datahandler\DatahandlerTest;

class LocalizeTest extends DatahandlerTest
{

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \TYPO3\TestingFramework\Core\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/sys_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/pages.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_translations_container_free_mode.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_translations_container_connected_mode.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/container/Tests/Functional/Fixtures/tt_content_default_language_second_container.xml');
    }

    /**
     * @test
     */
    public function copyToLanguageContainerCopiesChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'copyToLanguage' => 1
                ]
            ]
        ];

        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedChildRow = $this->fetchOneRecord('t3_origuid', 2);
        $translatedContainerRow = $this->fetchOneRecord('t3_origuid', 1);
        self::assertSame($translatedContainerRow['uid'], $translatedChildRow['tx_container_parent']);
        self::assertSame(200, $translatedChildRow['colPos']);
        self::assertSame(1, $translatedChildRow['pid']);
        self::assertSame(0, $translatedChildRow['l18n_parent']);
    }

    /**
     * @test
     */
    public function localizeContainerLocalizeChildren(): void
    {
        $cmdmap = [
            'tt_content' => [
                1 => [
                    'localize' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedChildRow = $this->fetchOneRecord('t3_origuid', 2);
        self::assertSame(1, $translatedChildRow['tx_container_parent']);
        self::assertSame(200, $translatedChildRow['colPos']);
        self::assertSame(1, $translatedChildRow['pid']);
        self::assertSame(2, $translatedChildRow['l18n_parent']);
    }

    /**
     * @test
     */
    public function localizeChildFailedIfContainerIsInFreeMode(): void
    {
        $cmdmap = [
            'tt_content' => [
                72 => [
                    'localize' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter(72, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        self::assertFalse($row);
    }

    /**
     * @test
     */
    public function localizeChildFailedIfContainerIsNotTranslated(): void
    {
        $cmdmap = [
            'tt_content' => [
                2 => [
                    'localize' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $queryBuilder = $this->getQueryBuilder();
        $row = $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    't3_origuid',
                    $queryBuilder->createNamedParameter(2, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        self::assertFalse($row);
    }

    /**
     * @test
     */
    public function localizeChildKeepsRelationsIfContainerIsInConnectedMode(): void
    {
        $cmdmap = [
            'tt_content' => [
                82 => [
                    'localize' => 1
                ]
            ]
        ];
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedChildRow = $this->fetchOneRecord('t3_origuid', 82);
        self::assertSame(81, $translatedChildRow['tx_container_parent']);
        self::assertSame(200, $translatedChildRow['colPos']);
        self::assertSame(1, $translatedChildRow['pid']);
        self::assertSame(82, $translatedChildRow['l18n_parent']);
    }

    /**
     * @return array
     */
    public function localizeTwoContainerKeepsParentIndependedOnOrderDataProvider(): array
    {
        return [
            ['cmdmap' => [
                'tt_content' => [
                    91 => ['localize' => 1],
                    1 => ['localize' => 1]
                ]
            ]],
            ['cmdmap' => [
                'tt_content' => [
                    1 => ['localize' => 1],
                    91 => ['localize' => 1]
                ]
            ]]
        ];
    }

    /**
     * @test
     * @dataProvider localizeTwoContainerKeepsParentIndependedOnOrderDataProvider
     */
    public function localizeTwoContainerKeepsParentIndependedOnOrder(array $cmdmap): void
    {
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedChildRow = $this->fetchOneRecord('t3_origuid', 2);
        self::assertSame(1, $translatedChildRow['tx_container_parent']);
        $secondChildRow = $this->fetchOneRecord('t3_origuid', 92);
        self::assertSame(91, $secondChildRow['tx_container_parent']);
    }

    /**
     * @return array
     */
    public function localizeWithCopyTwoContainerChangeParentIndependedOnOrderDataProvider(): array
    {
        return [
            ['cmdmap' => [
                'tt_content' => [
                    91 => ['copyToLanguage' => 1],
                    1 => ['copyToLanguage' => 1]
                ]
            ]],
            ['cmdmap' => [
                'tt_content' => [
                    1 => ['copyToLanguage' => 1],
                    91 => ['copyToLanguage' => 1]
                ]
            ]]
        ];
    }

    /**
     * @test
     * @dataProvider localizeWithCopyTwoContainerChangeParentIndependedOnOrderDataProvider
     */
    public function localizeWithCopyTwoContainerChangeParentIndependedOnOrder(array $cmdmap): void
    {
        $this->dataHandler->start([], $cmdmap, $this->backendUser);
        $this->dataHandler->process_cmdmap();
        $translatedChildRow = $this->fetchOneRecord('t3_origuid', 2);
        $translatedContainer = $this->fetchOneRecord('t3_origuid', 1);
        self::assertSame($translatedContainer['uid'], $translatedChildRow['tx_container_parent']);
        $secondChildRow = $this->fetchOneRecord('t3_origuid', 92);
        $secondContainer = $this->fetchOneRecord('t3_origuid', 91);
        self::assertSame($secondContainer['uid'], $secondChildRow['tx_container_parent']);
    }
}
