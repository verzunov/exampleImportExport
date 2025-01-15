<?php

namespace APP\plugins\importexport\rsciExport;

class Journal

{
    // Свойства для хранения данных журнала
    public int $titleid;
    public string $issn;
    public string $eissn;
    public array $journalInfo;
    public array $issues;
}
public function __construct()
{

}