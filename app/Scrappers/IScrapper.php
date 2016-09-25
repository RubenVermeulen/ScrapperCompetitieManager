<?php


namespace Project\Scrappers;


interface IScrapper
{
    /**
     * Scrapes DOMDocument for data.
     *
     * @return mixed
     */
    public function scrape();
}