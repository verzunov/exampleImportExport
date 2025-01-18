<?php

namespace APP\plugins\importexport\rsciExport;

use SimpleXMLElement;
use APP\issue\Issue;
use APP\facades\Repo;
use PKP\i18n\Locale;
class RsciJournal
{
    // Свойства для хранения данных журнала
    public int $titleid;
    public string $issn;
    public string $eissn;
    public RsciJournalInfo $rsciJournalInfo;
    public array $issues;

    public function __construct() {

        $this->titleid = 0;
        $this->issn = '';
        $this->eissn = '';
        $this->issues = array();

    }



    public function getXML(int $issueId)
    {
        $xml = new SimpleXMLElement('<journal/>');

        $xml->addChild('titleid', $this->titleid);
        $xml->addChild('issn', $this->issn);
        $xml->addChild('eissn', $this->eissn);

        // Добавляем журналинфо
        $journalInfoElement = $xml->addChild('journalInfo');
        $this->rsciJournalInfo = new RsciJournalInfo();
        $this->rsciJournalInfo->getXML($journalInfoElement);

        // Добавляем выпуска
        $issuesElemnent = $xml->addChild('issues');
        $issueElement = $issuesElemnent->addChild('issue');
        $issue=Repo::issue()->get($issueId);
        $rsciIssue = new RsciIssue($issueElement, $issue);
        $rsciIssue->getXML();
        return $xml->asXML();
    }
}