<?php

namespace APP\plugins\importexport\rsciExport;
use SimpleXMLElement;
class RsciIssue
{
    private int $volume;
    private string $number;
    private int $dateUni;
    private array $issTitle;
    private SimpleXMLElement $issueElement;

    public function __construct($issueElement, $issue)
    {
        $this->issueElement = $issueElement;
        $this->volume = (int)$issue->getVolume();
        $this->number = $issue->getNumber();
        $this->dateUni= $issue->getYear();
        $this->issTitle =  $issue->getData('title');
    }
    public function getXML(){
        $this->issueElement->addChild('volume', $this->volume);
        $this->issueElement->addChild('number', $this->number);
        $this->issueElement->addChild('dateUni', $this->dateUni);
    }
}