<?php
/**
 * @file RsciExportPlugin.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class RsciExportPlugin
 * @brief An rsci plugin demonstrating how to write an import/export plugin.
 */

namespace APP\plugins\importexport\rsciExport;

use APP\core\Application;
use APP\facades\Repo;
use APP\submission\Submission;

use APP\template\TemplateManager;
use Illuminate\Support\LazyCollection;
use PKP\plugins\ImportExportPlugin;

class RsciExportPlugin extends ImportExportPlugin
{
    public function register($category, $path, $mainContextId = NULL)
    {
        $success = parent::register($category, $path);

        $this->addLocaleData();

        return $success;
    }

    public function getName()
    {
        return 'RsciExportPlugin';
    }

    public function getDisplayName()
    {
        return __('plugins.importexport.rsciExport.name');
    }

    public function getDescription()
    {
        return __('plugins.importexport.rsciExport.description');
    }

    public function display($args, $request)
    {
        parent::display($args, $request);

        // Get the journal, press or preprint server id
        $contextId = Application::get()->getRequest()->getContext()->getId();

        // Use the path to determine which action
        // should be taken.
        $path = array_shift($args);
        switch ($path) {

            // Stream a CSV file for download
            case 'exportAll':
                header('content-type: text/comma-separated-values');
                header('content-disposition: attachment; filename=articles-' . date('Ymd') . '.xml');

                $submissions = $this->getAll($contextId);
                //$issues = $this->getAllIssues($contextId);
                //$this->export($submissions, 'php://output');

                break;

            // When no path is requested, display a list of submissions
            // to export and a button to run the `exportAll` path.
            case 'exportByIssue':
                $issueId = (int)$request->getUserVar('issueId');

                if ($issueId) {
                    header('content-type: text/comma-separated-values');
                    header('content-disposition: attachment; filename=issue-' . $issueId . '-' . date('Ymd') . '.xml');
                    $journal = new RsciJournal(); // Добавить отображение нескольких журналов
                    $xmlStr= $journal->getXML($issueId);
                    //$submissions = $this->getByIssue($contextId, $issueId);
                    file_put_contents("php://output", $xmlStr);
                    //$this->export($submissions, 'php://output');
                } else {
                    // Если номер не выбран, вернуть на страницу с сообщением об ошибке
                    $request->redirect(null, 'index', null, ['error' => 'noIssueSelected']);
                }
                break;
            default:
                $templateMgr = TemplateManager::getManager($request);
                $issues= $this->getAllIssues($contextId);
                $templateMgr->assign([
                    'pageTitle' => __('plugins.importexport.rsciExport.name'),
                    'submissions' => $this->getAll($contextId),
                    'issues' => $issues,
                ]);

                $templateMgr->display(
                    $this->getTemplateResource('export.tpl')
                );
        }
    }

    public function executeCLI($scriptName, &$args)
    {
        $csvFile = array_shift($args);
        $contextId = array_shift($args);

        if (!$csvFile || !$contextId) {
            $this->usage('');
        }

        $submissions = $this->getAll($contextId);

        $this->export($submissions, $csvFile);
    }

    public function usage($scriptName)
    {
        echo __('plugins.importexport.rsciExport.cliUsage', [
            'scriptName' => $scriptName,
            'pluginName' => $this->getName()
        ]) . "\n";
    }

    /**
     * A helper method to get all published submissions for export
     *
     * @param int contextId Which journal, press or preprint server to get submissions for
     */
    public function getAll(int $contextId): LazyCollection
    {
        return Repo::submission()
            ->getCollector()
            ->filterByContextIds([$contextId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->getMany();
    }
    public function getAllIssues(int $contextId): LazyCollection
    {
        return Repo::issue()
            ->getCollector()
            ->filterByContextIds([$contextId])
            ->getMany();
    }
    /**
     * A helper method to stream all published submissions
     * to a CSV file
     */
    public function export(LazyCollection $submissions, $filename)
    {
        $fp = fopen($filename, 'wt');
        fputcsv($fp, ['ID', 'Title']);

        /** @var Submission $submission */
        foreach ($submissions as $submission) {
            fputcsv(
                $fp,
                [
                    $submission->getId(),
                    $submission->getCurrentPublication()->getLocalizedFullTitle()
                ]
            );
        }

        fclose($fp);
    }

}
