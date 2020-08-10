<?php

declare(strict_types=1);
namespace B13\Container\Tests\Acceptance\Backend;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Tests\Acceptance\Support\BackendTester;
use B13\Container\Tests\Acceptance\Support\PageTree;

class LayoutCest
{

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->useExistingSession('admin');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     */
    public function connectedModeShowCorrectContentElements(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');

        $pageTree->openPath(['home', 'pageWithLocalization']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->see('2cols-header-0');
        $I->see('header-header-0');
        $I->dontSee('2cols-header-1');
        $I->dontSee('header-header-1');
        $I->selectOption('select[name="languageMenu"]', 'german');

        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->see('2cols-header-1');
        $I->see('header-header-1');
        $I->dontSee('2cols-header-0');
        $I->dontSee('header-header-0');

        $I->selectOption('select[name="actionMenu"]', 'Languages');
        $I->waitForElementNotVisible('#t3js-ui-block');

        // td.t3-grid-cell:nth-child(1)
        // default language
        $languageCol = 'td.t3-grid-cell:nth-child(1)';
        $I->see('2cols-header-0', $languageCol);
        $I->see('header-header-0', $languageCol);
        $I->dontSee('2cols-header-1', $languageCol);
        $I->dontSee('header-header-1', $languageCol);
        //td.t3-grid-cell:nth-child(2)
        // german language
        $languageCol = 'td.t3-grid-cell:nth-child(2)';
        $I->see('2cols-header-1', $languageCol);
        $I->see('header-header-1', $languageCol);
        $I->dontSee('2cols-header-0', $languageCol);
        $I->dontSee('header-header-0', $languageCol);
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     */
    public function connectedModeShowNoAddContentButton(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithLocalization']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->selectOption('select[name="languageMenu"]', 'german');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->dontSee('Content', '#element-tt_content-102');
        $I->selectOption('select[name="actionMenu"]', 'Languages');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->dontSee('Content', '#element-tt_content-102');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canCreateContainerContentElement(BackendTester $I, PageTree $pageTree)
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'emptyPage']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $I->click('Content');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
        $I->click('Container');
        $I->click('2 Column Container With Header');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->canSee('header', '.t3-grid-container');
        $I->canSee('left side', '.t3-grid-container');
        $I->canSee('right side', '.t3-grid-container');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     */
    public function newElementInHeaderColumnHasExpectedColPosAndParentSelected(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        // header
        $I->click('Content', '#element-tt_content-1 div[data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->see('header [200]');
        $I->see('b13-2cols-with-header-container [1]');
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canCreateContentElementInContainer(BackendTester $I, PageTree $pageTree)
    {
        //@depends canCreateContainer
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();
        $selecor = '#element-tt_content-1 div:nth-child(1) div:nth-child(2)';
        $I->dontSee('english', $selecor);
        $I->click('Content', '#element-tt_content-1 div[data-colpos="1-200"]');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->see('english', $selecor);
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canCreateContentElementInTranslatedContainerInFreeMode(BackendTester $I, PageTree $pageTree)
    {
        //@depends canCreateContainer
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithLocalizationFreeModeWithContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $I->selectOption('select[name="languageMenu"]', 'german');
        $I->waitForElementNotVisible('#t3js-ui-block');

        $uid = 104;

        $selecor = '#element-tt_content-' . $uid . ' div:nth-child(1) div:nth-child(2)';
        $I->dontSee('german', $selecor);
        $I->click('Content', '#element-tt_content-' . $uid . ' div[data-colpos="' . $uid . '-200"]');
        $I->switchToIFrame();
        $I->waitForElement('#NewContentElementController');
        $I->click('Header Only');
        $I->switchToContentFrame();
        $I->click('Save');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('Close');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->see('german', $selecor);
    }

    /**
     * @param BackendTester $I
     * @param PageTree $pageTree
     * @throws \Exception
     */
    public function canTranslateChild(BackendTester $I, PageTree $pageTree): void
    {
        $I->click('Page');
        $pageTree->openPath(['home', 'pageWithTranslatedContainer']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $I->click('headerOfChild', '#element-tt_content-212');

        $I->selectOption('select[name="_langSelector"]', 'german [NEW]');
        $I->see('[Translate to language-1:] headerOfChild');
    }
}
