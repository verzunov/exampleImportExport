<?php

namespace APP\plugins\importexport\rsciExport;

class RsciJournalInfo
{

    public string $title;

    public function __construct()
    {
        $this->title = '';
    }

    public function getXML(?\SimpleXMLElement $journalInfo)
    {
        $journalInfo->addChild('title', $this->title);
    }
}